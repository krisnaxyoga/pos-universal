<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline - POS Universal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1f2937;
            color: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .container {
            text-align: center;
            max-width: 400px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        p {
            color: #9ca3af;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover { background: #2563eb; }
        .status {
            margin-top: 2rem;
            padding: 0.75rem 1rem;
            background: #374151;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">&#128268;</div>
        <h1>Anda Sedang Offline</h1>
        <p>Halaman ini tidak tersedia dalam mode offline. Pastikan koneksi internet Anda aktif, lalu coba lagi.</p>
        <button class="btn" onclick="window.location.reload()">Coba Lagi</button>
        <div class="status">
            Halaman POS dan Dashboard tetap bisa diakses offline jika sudah pernah dibuka sebelumnya.
        </div>
    </div>

    <script>
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
