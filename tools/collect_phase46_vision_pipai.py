#!/usr/bin/env python3
from __future__ import annotations

import csv
import gzip
import io
import json
import os
import threading
import time
import zipfile
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import date, datetime, timedelta, timezone
from pathlib import Path
from typing import Any

import requests

VISION = "https://data.binance.vision/data/futures/um/daily/klines"
PIPAI = "https://api.pipai.org"
START_DAY = date(2026, 8, 1)
END_DAY_EXCLUSIVE = date(2026, 8, 21)
FUNDING_START_MS = int(datetime(2026, 8, 1, tzinfo=timezone.utc).timestamp() * 1000)
USER_AGENT = "FABLE-phase46-vision-pipai/1.0"
_LOCAL = threading.local()
KLINE_COLUMNS = [
    "open_time", "open", "high", "low", "close", "volume",
    "close_time", "quote_volume", "count", "taker_buy_volume",
    "taker_buy_quote_volume", "ignore",
]


def session() -> requests.Session:
    if not hasattr(_LOCAL, "session"):
        value = requests.Session()
        value.headers.update({"User-Agent": USER_AGENT})
        _LOCAL.session = value
    return _LOCAL.session


def get_json(url: str, attempts: int = 5) -> Any:
    last: str | None = None
    for attempt in range(attempts):
        try:
            response = session().get(url, timeout=(15, 75))
            if response.status_code in (429, 500, 502, 503, 504):
                last = f"HTTP {response.status_code}: {response.text[:300]}"
                response.close()
                time.sleep(min(12.0, 0.75 * (2**attempt)))
                continue
            response.raise_for_status()
            payload = response.json()
            response.close()
            return payload
        except Exception as exc:
            last = repr(exc)
            time.sleep(min(12.0, 0.75 * (2**attempt)))
    raise RuntimeError(f"GET {url} failed: {last}")


def fetch_kline(symbol: str, day: date) -> dict[str, Any] | None:
    stamp = day.isoformat()
    url = f"{VISION}/{symbol}/1d/{symbol}-1d-{stamp}.zip"
    for attempt in range(3):
        try:
            response = session().get(url, timeout=(15, 45))
            if response.status_code == 404:
                response.close()
                return None
            if response.status_code in (429, 500, 502, 503, 504):
                response.close()
                time.sleep(0.4 * (2**attempt))
                continue
            response.raise_for_status()
            body = response.content
            response.close()
            with zipfile.ZipFile(io.BytesIO(body)) as archive:
                members = archive.namelist()
                if len(members) != 1:
                    raise RuntimeError(f"unexpected members for {symbol} {stamp}: {members}")
                text = archive.read(members[0]).decode("utf-8")
            reader = csv.reader(io.StringIO(text))
            rows = list(reader)
            if rows and rows[0] and rows[0][0] == "open_time":
                rows = rows[1:]
            if len(rows) != 1 or len(rows[0]) < 8:
                raise RuntimeError(f"unexpected kline rows for {symbol} {stamp}: {rows[:2]}")
            item = dict(zip(KLINE_COLUMNS, rows[0]))
            return {
                "symbol": symbol,
                "timestamp": datetime.fromtimestamp(int(item["open_time"]) / 1000, tz=timezone.utc).isoformat(),
                "open": float(item["open"]),
                "high": float(item["high"]),
                "low": float(item["low"]),
                "close": float(item["close"]),
                "quote_volume": float(item["quote_volume"]),
            }
        except Exception:
            if attempt == 2:
                raise
            time.sleep(0.4 * (2**attempt))
    return None


def normalize_list(payload: Any) -> list[dict[str, Any]]:
    if isinstance(payload, list):
        return [row for row in payload if isinstance(row, dict)]
    if isinstance(payload, dict):
        for key in ("data", "rows", "result", "items"):
            if isinstance(payload.get(key), list):
                return [row for row in payload[key] if isinstance(row, dict)]
    return []


def fetch_funding(symbol: str, capture_ms: int) -> dict[str, Any]:
    url = (
        f"{PIPAI}/funding/rates/{symbol}/history"
        f"?start_time={FUNDING_START_MS}&end_time={capture_ms}&limit=100"
    )
    try:
        payload = get_json(url)
        rows = []
        for item in normalize_list(payload):
            raw_time = item.get("fundingTime", item.get("funding_time", item.get("timestamp", item.get("time"))))
            raw_rate = item.get("fundingRate", item.get("funding_rate", item.get("rate")))
            if raw_time is None or raw_rate is None:
                continue
            stamp = int(raw_time)
            if stamp < FUNDING_START_MS or stamp > capture_ms:
                continue
            rows.append(
                {
                    "symbol": symbol,
                    "timestamp": datetime.fromtimestamp(stamp / 1000, tz=timezone.utc).isoformat(),
                    "funding": float(raw_rate),
                }
            )
        rows.sort(key=lambda row: row["timestamp"])
        return {"symbol": symbol, "success": True, "rows": rows}
    except Exception as exc:
        return {"symbol": symbol, "success": False, "rows": [], "error": repr(exc)}


def write_gzip_csv(path: Path, rows: list[dict[str, Any]], fields: list[str]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    buffer = io.StringIO()
    writer = csv.DictWriter(buffer, fieldnames=fields)
    writer.writeheader()
    for row in rows:
        writer.writerow({field: row.get(field) for field in fields})
    with path.open("wb") as raw:
        with gzip.GzipFile(filename="", mode="wb", fileobj=raw, mtime=0) as compressed:
            compressed.write(buffer.getvalue().encode("utf-8"))


def current_marks(tickers: list[dict[str, Any]]) -> tuple[list[dict[str, Any]], dict[str, Any]]:
    ticker_map = {str(row.get("symbol")): row for row in tickers if row.get("symbol")}
    audit: dict[str, Any] = {"funding_all_success": False, "fallback_ticker_marks": 0}
    funding_rows: list[dict[str, Any]] = []
    try:
        funding_rows = normalize_list(get_json(f"{PIPAI}/funding/rates"))
        audit["funding_all_success"] = True
        audit["funding_all_rows"] = len(funding_rows)
    except Exception as exc:
        audit["funding_all_error"] = repr(exc)
    output: dict[str, dict[str, Any]] = {}
    for item in funding_rows:
        symbol = str(item.get("symbol", ""))
        raw_mark = item.get("markPrice", item.get("mark_price"))
        raw_time = item.get("time", item.get("timestamp", item.get("fundingTime")))
        try:
            mark = float(raw_mark)
            stamp = int(raw_time)
        except Exception:
            continue
        if symbol and mark > 0:
            output[symbol] = {
                "symbol": symbol,
                "markPrice": f"{mark:.16g}",
                "time": stamp,
                "source": "pipai_funding_current_mark",
            }
    for symbol, item in ticker_map.items():
        if symbol in output:
            continue
        try:
            mark = float(item["lastPrice"])
            stamp = int(item["closeTime"])
        except Exception:
            continue
        if mark > 0:
            output[symbol] = {
                "symbol": symbol,
                "markPrice": f"{mark:.16g}",
                "time": stamp,
                "source": "pipai_active_ticker_last_price",
            }
            audit["fallback_ticker_marks"] += 1
    return sorted(output.values(), key=lambda row: row["symbol"]), audit


def main() -> None:
    output_root = Path(os.environ.get("OUTPUT_ROOT", "market_data"))
    symbols_file = Path(os.environ.get("SYMBOLS_FILE", "phase46_symbols.txt"))
    workers = int(os.environ.get("PRICE_WORKERS", "80"))
    funding_workers = int(os.environ.get("FUNDING_WORKERS", "24"))
    symbols = sorted({line.strip() for line in symbols_file.read_text().splitlines() if line.strip()})
    days = []
    cursor = START_DAY
    while cursor < END_DAY_EXCLUSIVE:
        days.append(cursor)
        cursor += timedelta(days=1)

    tickers = normalize_list(get_json(f"{PIPAI}/ticker/24hr/active"))
    ticker_map = {str(row.get("symbol")): row for row in tickers if row.get("symbol")}
    marks, marks_audit = current_marks(tickers)
    capture_ms = max(
        [int(row["time"]) for row in marks]
        + [int(row.get("closeTime", 0)) for row in tickers if row.get("closeTime")]
    )

    price_rows: list[dict[str, Any]] = []
    price_errors: list[dict[str, Any]] = []
    tasks = [(symbol, day) for symbol in symbols for day in days]
    with ThreadPoolExecutor(max_workers=workers) as executor:
        futures = {executor.submit(fetch_kline, symbol, day): (symbol, day) for symbol, day in tasks}
        for index, future in enumerate(as_completed(futures), 1):
            symbol, day = futures[future]
            try:
                row = future.result()
                if row is not None:
                    price_rows.append(row)
            except Exception as exc:
                price_errors.append({"symbol": symbol, "day": day.isoformat(), "error": repr(exc)})
            if index % 1000 == 0 or index == len(futures):
                print(
                    json.dumps(
                        {
                            "price_files_completed": index,
                            "price_files_total": len(futures),
                            "price_rows": len(price_rows),
                            "errors": len(price_errors),
                        }
                    ),
                    flush=True,
                )
    price_rows.sort(key=lambda row: (row["symbol"], row["timestamp"]))
    price_symbols = sorted({row["symbol"] for row in price_rows})

    funding_results: list[dict[str, Any]] = []
    with ThreadPoolExecutor(max_workers=funding_workers) as executor:
        futures = {executor.submit(fetch_funding, symbol, capture_ms): symbol for symbol in price_symbols}
        for index, future in enumerate(as_completed(futures), 1):
            funding_results.append(future.result())
            if index % 100 == 0 or index == len(futures):
                print(
                    json.dumps(
                        {
                            "funding_completed": index,
                            "funding_total": len(futures),
                            "success": sum(item["success"] for item in funding_results),
                            "rows": sum(len(item["rows"]) for item in funding_results),
                        }
                    ),
                    flush=True,
                )
    funding_results.sort(key=lambda item: item["symbol"])
    funding_rows = [row for item in funding_results for row in item["rows"]]
    funding_rows.sort(key=lambda row: (row["symbol"], row["timestamp"]))

    output_root.mkdir(parents=True, exist_ok=True)
    write_gzip_csv(
        output_root / "recent_prices.csv.gz",
        price_rows,
        ["symbol", "timestamp", "open", "high", "low", "close", "quote_volume"],
    )
    write_gzip_csv(
        output_root / "recent_funding.csv.gz",
        funding_rows,
        ["symbol", "timestamp", "funding"],
    )
    (output_root / "premium_index.json").write_text(json.dumps(marks, indent=2) + "\n")
    (output_root / "active_tickers.json").write_text(json.dumps(tickers, indent=2) + "\n")

    btc_rows = [row for row in price_rows if row["symbol"] == "BTCUSDT"]
    btc_ticker = ticker_map.get("BTCUSDT", {})
    if btc_ticker:
        try:
            btc_rows.append(
                {
                    "symbol": "BTCUSDT",
                    "timestamp": datetime.fromtimestamp(int(btc_ticker["openTime"]) / 1000, tz=timezone.utc).isoformat(),
                    "open": float(btc_ticker["openPrice"]),
                    "high": float(btc_ticker["highPrice"]),
                    "low": float(btc_ticker["lowPrice"]),
                    "close": float(btc_ticker["lastPrice"]),
                    "quote_volume": float(btc_ticker.get("quoteVolume", 0.0)),
                }
            )
        except Exception:
            pass
    write_gzip_csv(
        output_root / "btc_1h.csv.gz",
        btc_rows,
        ["timestamp", "open", "high", "low", "close", "quote_volume"],
    )

    btc_mark = next((row for row in marks if row["symbol"] == "BTCUSDT"), None)
    audit = {
        "created_at": datetime.now(timezone.utc).isoformat(),
        "data_sources": {
            "daily_prices": "Binance Vision official USD-M futures daily klines",
            "active_tickers": "PipAI Binance USD-M active ticker gateway",
            "current_marks": "PipAI current funding/mark gateway; ticker fallback",
            "funding_history": "PipAI historical funding gateway",
        },
        "panel_symbols": len(symbols),
        "calendar_days": len(days),
        "requested_price_files": len(tasks),
        "price_rows": len(price_rows),
        "price_symbols": len(price_symbols),
        "price_errors": price_errors,
        "active_tickers": len(tickers),
        "active_panel_intersection": len(set(symbols) & set(ticker_map)),
        "current_marks": len(marks),
        "marks_audit": marks_audit,
        "capture_time": datetime.fromtimestamp(capture_ms / 1000, tz=timezone.utc).isoformat(),
        "funding_symbols_requested": len(price_symbols),
        "funding_symbols_success": sum(item["success"] for item in funding_results),
        "funding_rows": len(funding_rows),
        "funding_failures": [
            {"symbol": item["symbol"], "error": item.get("error")}
            for item in funding_results if not item["success"]
        ],
        "btc_mark": btc_mark,
        "btc_ticker": btc_ticker,
        "btc_daily_rows": len([row for row in price_rows if row["symbol"] == "BTCUSDT"]),
    }
    (output_root / "audit.json").write_text(json.dumps(audit, indent=2) + "\n")
    print(json.dumps({key: value for key, value in audit.items() if key not in ("price_errors", "funding_failures", "btc_ticker")}, indent=2))


if __name__ == "__main__":
    main()
