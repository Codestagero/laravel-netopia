<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Payment</title>
</head>
<body class="bg-gray-100 flex items-center justify-center min-w-screen min-h-screen">
    <div class="p-6 bg-white border shadow rounded-lg">
        <h1 class="text-2xl">
            Payment complete
        </h1>

        <p class="mt-4 text-gray-500 text-sm">
            You have successfully completed the payment.
            You can now safely close this tab
        </p>

        <div class="flex items-center justify-center mt-7">
            <button class="rounded-lg px-4 py-2 hover:bg-gray-900 bg-gray-800 text-gray-100" onclick="window.close()">
                Close window
            </button>
        </div>
    </div>
</body>
</html>
