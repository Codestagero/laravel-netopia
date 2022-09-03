<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment</title>
</head>
<body>
    <form action="{{ $payment->url }}" method="post" id="payment-form">
        <input type="hidden" name="env_key" value="{{ $payment->environmentKey }}">
        <input type="hidden" name="data" value="{{ $payment->data }}">
    </form>
</body>
<script>
    // Auto-submit the form when the page loads.
    window.addEventListener('load', () => {
        const form = document.getElementById('payment-form');
        if (form instanceof HTMLFormElement) {
            form.submit();
        }
    });
</script>
</html>

