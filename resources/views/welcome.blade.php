<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Renz Billiard - Billing System</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="theme-color" content="#465fff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/images/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Inline PWA Manifest (bypass InfinityFree filter) -->
    <script>
        const manifest = {
            name: "Renz Billiard - Billing System",
            short_name: "RenzBilliard",
            description: "Sistem Billing untuk Renz Billiard",
            start_url: "/",
            display: "standalone",
            background_color: "#1f2937",
            theme_color: "#465fff",
            orientation: "any",
            icons: [
                { src: "/favicon.png", sizes: "128x128", type: "image/png", purpose: "any maskable" },
                { src: "/images/icon-192.png", sizes: "192x192", type: "image/png", purpose: "any maskable" },
                { src: "/images/icon-512.png", sizes: "512x512", type: "image/png", purpose: "any maskable" }
            ]
        };
        const blob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
        const manifestLink = document.createElement('link');
        manifestLink.rel = 'manifest';
        manifestLink.href = URL.createObjectURL(blob);
        document.head.appendChild(manifestLink);
    </script>
</head>
<body>
    <div id="app"></div>
    
    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</body>
</html>
