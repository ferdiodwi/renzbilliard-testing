<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Renz Billiard - Tempat Main Billiard Terbaik di Bondowoso</title>
    <meta name="description" content="Renz Billiard - Tempat bermain billiard terbaik di Bondowoso, Jawa Timur. Meja berkualitas, suasana nyaman, harga terjangkau. Buka setiap hari mulai jam 08:00.">
    <meta name="keywords" content="billiard bondowoso, renz billiard, tempat billiard bondowoso, billiard jawa timur, main billiard, sewa meja billiard">
    <meta name="author" content="Renz Billiard">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="Jd0KEiMWarhxdSMEteSeYQHOyUbV7w7RAFf1FduAt_s" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="business.business">
    <meta property="og:title" content="Renz Billiard - Tempat Main Billiard Terbaik di Bondowoso">
    <meta property="og:description" content="Nikmati pengalaman bermain billiard terbaik di Bondowoso dengan meja berkualitas dan suasana nyaman.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="Renz Billiard">
    <meta property="og:locale" content="id_ID">
    <meta property="business:contact_data:street_address" content="Jl. Letnan Sudiono No. 5">
    <meta property="business:contact_data:locality" content="Bondowoso">
    <meta property="business:contact_data:region" content="Jawa Timur">
    <meta property="business:contact_data:country_name" content="Indonesia">
    <meta property="business:contact_data:phone_number" content="+6285258838664">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Renz Billiard - Tempat Main Billiard Terbaik di Bondowoso">
    <meta name="twitter:description" content="Nikmati pengalaman bermain billiard terbaik di Bondowoso dengan meja berkualitas dan suasana nyaman.">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">
    
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="theme-color" content="#01026E">
    <meta name="mobile-web-app-capable" content="yes">
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
        const baseUrl = window.location.origin;
        const manifest = {
            name: "Renz Billiard - Billing System",
            short_name: "RenzBilliard",
            description: "Sistem Billing untuk Renz Billiard",
            start_url: baseUrl + "/",
            display: "standalone",
            background_color: "#1f2937",
            theme_color: "#01026E",
            orientation: "any",
            icons: [
                { src: baseUrl + "/favicon.png", sizes: "128x128", type: "image/png", purpose: "any maskable" },
                { src: baseUrl + "/images/icon-192.png", sizes: "192x192", type: "image/png", purpose: "any maskable" },
                { src: baseUrl + "/images/icon-512.png", sizes: "512x512", type: "image/png", purpose: "any maskable" }
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
