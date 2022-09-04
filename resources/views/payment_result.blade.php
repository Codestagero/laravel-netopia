{{ '<?xml version="1.0" encoding="utf-8"?>' }}
@if($result->errorCode === 0)
    <crc>{{ $result->errorText }}</crc>
@else
    <crc error_type="{{ $result->errorType }}" error_code="{{ $result->errorCode }}">
        {{ $result->errorMessage }}
    </crc>
@endif
