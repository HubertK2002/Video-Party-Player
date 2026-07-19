<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverb Broadcast</title>
    @vite(['resources/js/app.js']) {{-- Load JS --}}
</head>
<body>
    <h1>Listening for Messages...</h1>
    <pre id="output"></pre>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            window.Echo.channel("public-messages")
                .listen(".message.sent", (event) => {
                    console.log("Received:", event);
                    document.getElementById("output").textContent = JSON.stringify(event, null, 2);
                });
        });
    </script>
</body>
</html>