<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dokumentasi interaktif API Integrasi BBWS Serayu Opak">
    <title>API Integrasi BBWS Serayu Opak</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        :root {
            --air-950: #08293a;
            --air-700: #0f6f8f;
            --kabut-100: #e9f2f5;
            --kertas: #ffffff;
            --sorot: #f0a92e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--kabut-100);
            color: var(--air-950);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .docs-header {
            background: linear-gradient(135deg, var(--air-950), var(--air-700));
            color: var(--kertas);
            padding: 40px 24px 32px;
        }

        .docs-kicker {
            margin: 0 0 6px;
            font-size: 12px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--sorot);
        }

        .docs-title { margin: 0 0 10px; font-size: clamp(24px, 4vw, 34px); line-height: 1.15; }

        .docs-summary { margin: 0; max-width: 68ch; line-height: 1.6; opacity: .93; }

        .docs-meta {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            font-size: 14px;
        }

        .docs-meta a { color: var(--sorot); }

        #swagger-ui {
            max-width: 1180px;
            /* Beri jarak supaya latar halaman terlihat sebagai celah antara
               pita gelap dan panel putih, bukan dua blok yang berdempetan. */
            margin: 28px auto 0;
            padding-inline: 16px;
            background: var(--kertas);
        }

        .swagger-ui .topbar { display: none; }

        /* Deskripsi sudah disampaikan di header halaman di atas; Swagger UI
           mengulangnya persis di bawahnya. Disembunyikan dari tampilan saja —
           teksnya TETAP ADA di dokumen OpenAPI, supaya konsumen non-UI
           (generator klien, Postman) tetap membaca batasan laju data dan
           cakupannya. */
        .swagger-ui .info .description { display: none; }

        /* Beri napas antara header gelap dan judul Swagger UI. */
        .swagger-ui .info { margin: 0; padding: 44px 0 16px; }

        @media (max-width: 640px) {
            #swagger-ui { padding-inline: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; }
        }
    </style>
</head>
<body>
    <header class="docs-header">
        <p class="docs-kicker">BBWS Serayu Opak · Referensi Integrasi</p>
        <h1 class="docs-title">API Integrasi BBWS Serayu Opak</h1>
        <p class="docs-summary">
            Tiga endpoint untuk menarik data pos telemetri: riwayat terbaru satu pos, snapshot
            seluruh pos aktif, dan riwayat per rentang tanggal.
        </p>
        <div class="docs-meta">
            <span>OpenAPI 3.0.3</span>
            <span>·</span>
            <span>HTTP Basic Auth &middot; akun admin</span>
            <span>·</span>
            <a href="<?php echo site_url('integrasi_api/openapi'); ?>">Buka raw OpenAPI JSON</a>
        </div>
    </header>

    <main id="swagger-ui" aria-label="Dokumentasi endpoint API Integrasi"></main>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin="anonymous"></script>
    <script>
        window.addEventListener('load', function () {
            window.ui = SwaggerUIBundle({
                url: '<?php echo site_url('integrasi_api/openapi'); ?>',
                dom_id: '#swagger-ui',
                deepLinking: true,
                displayRequestDuration: true,
                docExpansion: 'list',
                presets: [SwaggerUIBundle.presets.apis]
            });
        });
    </script>
</body>
</html>
