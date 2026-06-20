<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $terms?->title ?? 'Terms & Conditions' }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --surface: rgba(255, 255, 255, 0.82);
            --surface-strong: #ffffff;
            --border: rgba(15, 23, 42, 0.08);
            --text: #0f172a;
            --muted: #5b6475;
            --accent: #1d4ed8;
            --accent-soft: rgba(29, 78, 216, 0.12);
            --shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
            --radius-xl: 28px;
            --radius-lg: 20px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.18), transparent 32%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
        }

        .page-shell {
            position: relative;
            overflow: hidden;
            padding: 56px 20px;
        }

        .page-shell::before,
        .page-shell::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(10px);
            z-index: 0;
        }

        .page-shell::before {
            width: 220px;
            height: 220px;
            top: 100px;
            left: -70px;
            background: rgba(29, 78, 216, 0.12);
        }

        .page-shell::after {
            width: 280px;
            height: 280px;
            right: -80px;
            bottom: 80px;
            background: rgba(14, 165, 233, 0.1);
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 1120px;
            margin: 0 auto;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
            gap: 24px;
            align-items: stretch;
            margin-bottom: 24px;
        }

        .hero-card,
        .trust-card,
        .terms-card {
            background: var(--surface);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .hero-card {
            padding: 36px;
            border-radius: var(--radius-xl);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 18px 0 14px;
            font-size: clamp(2.2rem, 4vw, 3.9rem);
            line-height: 1.02;
            letter-spacing: -0.04em;
        }

        .hero p {
            margin: 0;
            max-width: 64ch;
            font-size: 1.02rem;
            line-height: 1.8;
            color: var(--muted);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .meta-pill {
            padding: 12px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(15, 23, 42, 0.07);
            color: #1f2937;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .trust-card {
            display: grid;
            gap: 14px;
            padding: 22px;
            border-radius: var(--radius-xl);
            align-content: start;
        }

        .trust-card h2 {
            margin: 0;
            font-size: 1.1rem;
            letter-spacing: -0.02em;
        }

        .trust-point {
            padding: 18px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .trust-point strong {
            display: block;
            margin-bottom: 8px;
            font-size: 0.98rem;
        }

        .trust-point span {
            color: var(--muted);
            line-height: 1.65;
            font-size: 0.95rem;
        }

        .terms-card {
            padding: 34px;
            border-radius: var(--radius-xl);
        }

        .terms-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            padding-bottom: 22px;
            margin-bottom: 26px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .terms-header h2 {
            margin: 0 0 8px;
            font-size: clamp(1.5rem, 2vw, 2rem);
            letter-spacing: -0.03em;
        }

        .terms-header p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .content {
            display: grid;
            gap: 18px;
        }

        .section {
            padding: 24px;
            border-radius: var(--radius-lg);
            background: var(--surface-strong);
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.05);
        }

        .section h3 {
            margin: 0 0 12px;
            font-size: 1.1rem;
            letter-spacing: -0.02em;
        }

        .section p,
        .section li {
            margin: 0;
            color: #334155;
            font-size: 1rem;
            line-height: 1.85;
        }

        .section p + p {
            margin-top: 14px;
        }

        .section ul {
            margin: 0;
            padding-left: 20px;
            display: grid;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 54px 24px;
            border-radius: var(--radius-lg);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.96));
            border: 1px dashed rgba(15, 23, 42, 0.16);
        }

        .empty-state h3 {
            margin: 0 0 10px;
            font-size: 1.3rem;
        }

        .empty-state p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-card,
            .trust-card,
            .terms-card {
                padding-left: 24px;
                padding-right: 24px;
            }

            .terms-header {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .page-shell {
                padding: 28px 14px 40px;
            }

            .hero-card,
            .trust-card,
            .terms-card {
                padding: 22px 18px;
                border-radius: 22px;
            }

            .section {
                padding: 18px;
            }

            .hero h1 {
                margin-top: 14px;
            }
        }
    </style>
</head>
<body>
    @php
        $title = $terms?->title ?: 'Terms & Conditions';
        $description = trim((string) ($terms?->description ?? ''));
        $blocks = $description !== '' ? preg_split('/\R{2,}/', $description) : [];
    @endphp

    <main class="page-shell">
        <div class="container">
            <section class="hero">
                <article class="hero-card">
                    <span class="eyebrow">Terms & Fair Use</span>
                    <h1>{{ $title }}</h1>
                    <p>
                        This page outlines the rules, responsibilities, and usage expectations in a cleaner format
                        so visitors can understand the terms clearly without digging through a dense wall of text.
                    </p>
                    <div class="hero-meta">
                        <span class="meta-pill">Readable structure</span>
                        <span class="meta-pill">Modern legal layout</span>
                        <span class="meta-pill">Mobile-friendly design</span>
                    </div>
                </article>

                <aside class="trust-card" aria-label="Terms highlights">
                    <h2>What this page emphasizes</h2>
                    <div class="trust-point">
                        <strong>Clear obligations</strong>
                        <span>Important usage terms are easier to follow with better visual hierarchy and spacing.</span>
                    </div>
                    <div class="trust-point">
                        <strong>Scannable sections</strong>
                        <span>Long text is broken into cleaner reading blocks so visitors can find key terms faster.</span>
                    </div>
                    <div class="trust-point">
                        <strong>Professional presentation</strong>
                        <span>The page now feels more trustworthy and polished while still using your current content source.</span>
                    </div>
                </aside>
            </section>

            <section class="terms-card">
                <div class="terms-header">
                    <div>
                        <h2>Terms Details</h2>
                        <p>
                            The content below is rendered from your existing terms entry and organized into
                            readable sections where possible.
                        </p>
                    </div>
                </div>

                @if($terms && $description !== '')
                    <div class="content">
                        @foreach($blocks as $block)
                            @php
                                $lines = array_values(array_filter(preg_split('/\R/', trim($block)), fn ($line) => trim($line) !== ''));
                                $firstLine = $lines[0] ?? '';
                                $looksLikeHeading = count($lines) > 1
                                    && mb_strlen($firstLine) <= 80
                                    && !str_contains($firstLine, '.');
                                $heading = $looksLikeHeading ? $firstLine : null;
                                $bodyLines = $looksLikeHeading ? array_slice($lines, 1) : $lines;
                                $listLines = !empty($bodyLines) && count(array_filter($bodyLines, fn ($line) => preg_match('/^[-*]\s+/', trim($line)))) === count($bodyLines);
                            @endphp

                            <article class="section">
                                @if($heading)
                                    <h3>{{ $heading }}</h3>
                                @endif

                                @if($listLines)
                                    <ul>
                                        @foreach($bodyLines as $line)
                                            <li>{{ preg_replace('/^[-*]\s+/', '', trim($line)) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    @foreach($bodyLines as $line)
                                        <p>{{ $line }}</p>
                                    @endforeach
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <h3>Terms & conditions content is not available yet</h3>
                        <p>Please add terms content from the admin panel or API, and it will appear here automatically.</p>
                    </div>
                @endif
            </section>
        </div>
    </main>
</body>
</html>
