<!DOCTYPE html>
<html lang="ja" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ログイン' }} - ContractFlow</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            background-color: #faf8ff;
            color: #131b2e;
            -webkit-font-smoothing: antialiased;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1;
        }
    </style>
</head>
<body class="min-h-screen bg-[#faf8ff] text-[#131b2e] font-sans antialiased flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    {{ $slot }}
    @livewireScripts
</body>
</html>
