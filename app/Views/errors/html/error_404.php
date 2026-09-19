<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found — BSU SPMS</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/spms_logo.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <script>
        if (localStorage.getItem('theme') !== 'light') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-subtle: #f1f5f9;
            --border-color: #e2e8f0;
            --border-focus: #10b981;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-emerald: #059669;
            --accent-emerald-hover: #047857;
            --accent-amber: #d97706;
            --accent-amber-bg: rgba(217, 119, 6, 0.1);
            --card-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }

        html.dark {
            --bg-page: #031710;
            --bg-card: #08281d;
            --bg-subtle: #0d3829;
            --border-color: #144f3b;
            --border-focus: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-emerald: #10b981;
            --accent-emerald-hover: #34d399;
            --accent-amber: #fbbf24;
            --accent-amber-bg: rgba(251, 191, 36, 0.12);
            --card-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(16, 185, 129, 0.06);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            line-height: 1.5;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .error-container {
            max-width: 640px;
            width: 100%;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 2.75rem 2.25rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .header-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .header-brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        }

        .header-brand-text {
            text-align: left;
        }

        .header-brand-title {
            font-size: 0.875rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--accent-emerald);
        }

        .header-brand-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.4rem 0.85rem;
            border-radius: 9999px;
            background-color: var(--accent-amber-bg);
            color: var(--accent-amber);
            border: 1px solid rgba(251, 191, 36, 0.25);
            margin-bottom: 1.5rem;
        }

        .code-display {
            font-size: 6.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            color: var(--text-main);
            margin-bottom: 0.75rem;
            position: relative;
        }

        .code-display span {
            color: var(--accent-emerald);
        }

        .headline {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            color: var(--text-main);
        }

        .description {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2.25rem;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.35rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.85rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--accent-emerald);
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--accent-emerald-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--bg-subtle);
            color: var(--text-main) !important;
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            border-color: var(--accent-emerald);
            color: var(--accent-emerald) !important;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-muted) !important;
            border-color: var(--border-color);
        }

        .btn-outline:hover {
            color: var(--text-main) !important;
            border-color: var(--text-muted);
        }

        .btn svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.2;
        }

        .dev-details {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: left;
        }

        .dev-details summary {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            outline: none;
            user-select: none;
        }

        .dev-details summary:hover {
            color: var(--text-main);
        }

        .dev-code {
            margin-top: 0.75rem;
            padding: 0.85rem 1rem;
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: var(--text-main);
            word-break: break-word;
            white-space: pre-wrap;
        }

        .footer-note {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>

    <div class="error-container">
        <!-- Brand Header -->
        <div class="header-brand">
            <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="BSU SPMS Logo" onerror="this.style.display='none'">
            <div class="header-brand-text">
                <div class="header-brand-title">Benguet State University</div>
                <div class="header-brand-sub">Strategic Performance Management System</div>
            </div>
        </div>

        <!-- Warning Badge -->
        <div class="badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Error 404 &bull; Page or Record Missing
        </div>

        <!-- 404 Display -->
        <div class="code-display">4<span>0</span>4</div>
        <h1 class="headline">Page or Record Not Found</h1>
        <p class="description">
            The evaluation folder, target form, or requested page could not be located.
            It may have been revoked by the supervisor, archived, deleted, or the address might be outdated.
        </p>

        <!-- Navigation Actions -->
        <div class="button-group">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="<?= site_url('folders') ?>" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                My Folders
            </a>
            <a href="<?= site_url('ratings') ?>" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Ratings
            </a>
            <button onclick="window.history.back()" class="btn btn-outline" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Back
            </button>
        </div>

        <?php if (ENVIRONMENT !== 'production' && !empty($message)): ?>
            <details class="dev-details">
                <summary>System Diagnostics (Development Mode)</summary>
                <div class="dev-code"><?= nl2br(esc($message)) ?></div>
            </details>
        <?php endif; ?>
    </div>

    <div class="footer-note">
        &copy; <?= date('Y') ?> Benguet State University &bull; SPMS Quality Assurance
    </div>

</body>
</html>
