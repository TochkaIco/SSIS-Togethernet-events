<!DOCTYPE html>
<html lang="sv" class="h-full bg-zinc-950 dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden m-0 p-0">
    {{ $slot }}
</body>
</html>
