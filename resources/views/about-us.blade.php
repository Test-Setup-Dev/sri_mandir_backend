<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $about?->title ?? 'About Us' }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f1e8;
            --paper: #fffdf9;
            --paper-soft: #fdf7ef;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: rgba(31, 41, 55, 0.12);
            --accent: #c2410c;
            --accent-soft: rgba(194, 65, 12, 0.10);
            --shadow: 0 30px 70px rgba(31, 41, 55, 0.08);
            --radius-xl: 34px;
            --radius-lg: 24px;
            --radius-md: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                linear-gradient(135deg, rgba(194, 65, 12, 0.06), transparent 28%),
                linear-gradient(180deg, #fcf8f2 0%, var(--bg) 100%);
        }

        .page {
            max-width: 1240px;
            margin: 0 auto;
            padding: 40px 18px 56px;
        }

        .frame {
            background: var(--paper);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(320px, 0.92fr) minmax(0, 1.08fr);
            min-height: 420px;
        }

        .hero-panel {
            padding: 48px 34px;
            background:
                linear-gradient(180deg, rgba(194, 65, 12, 0.08), rgba(194, 65, 12, 0.02)),
                var(--paper-soft);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 18px 0 0;
            font-size: clamp(2.4rem, 4vw, 4.9rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .hero-meta {
            display: grid;
            gap: 14px;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
        }

        .meta-box {
            padding: 16px 18px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(31, 41, 55, 0.08);
        }

        .meta-label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--ink);
        }

        .hero-story {
            padding: 56px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background:
                radial-gradient(circle at top right, rgba(194, 65, 12, 0.08), transparent 26%),
                linear-gradient(180deg, #fffdfa 0%, #fffaf4 100%);
        }

        .hero-story p {
            margin: 0;
            font-size: clamp(1.1rem, 1.8vw, 1.45rem);
            line-height: 1.95;
            color: #374151;
        }

        .hero-story p + p {
            margin-top: 20px;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 270px minmax(0, 1fr);
            gap: 0;
            border-top: 1px solid var(--line);
        }

        .sidebar {
            padding: 30px 24px;
            background: #fff9f2;
            border-right: 1px solid var(--line);
        }

        .sidebar-box + .sidebar-box {
            margin-top: 28px;
        }

        .sidebar-title {
            margin: 0 0 14px;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .toc {
            display: grid;
            gap: 10px;
        }

        .toc a,
        .toc span {
            display: block;
            padding: 10px 12px;
            border-radius: 12px;
            color: #374151;
            text-decoration: none;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            font-size: 0.95rem;
            line-height: 1.45;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(31, 41, 55, 0.06);
        }

        .content-area {
            padding: 34px;
            background: var(--paper);
        }

        .section {
            padding: 0 0 26px;
            margin-bottom: 28px;
            border-bottom: 1px solid rgba(31, 41, 55, 0.08);
        }

        .section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: baseline;
            gap: 14px;
            margin-bottom: 14px;
        }

        .section-index {
            color: var(--accent);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .section h2 {
            margin: 0;
            font-size: clamp(1.4rem, 2vw, 2rem);
            line-height: 1.2;
            letter-spacing: -0.03em;
        }

        .section p,
        .section li {
            margin: 0;
            color: #374151;
            font-size: 1.06rem;
            line-height: 1.95;
        }

        .section p + p {
            margin-top: 16px;
        }

        .section ul {
            margin: 0;
            padding-left: 22px;
            display: grid;
            gap: 10px;
        }

        .empty {
            padding: 70px 28px;
            text-align: center;
        }

        .empty h1 {
            margin: 0 0 12px;
            font-size: clamp(2rem, 4vw, 3rem);
        }

        .empty p {
            margin: 0;
            color: var(--muted);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            line-height: 1.8;
            font-size: 1rem;
        }

        @media (max-width: 980px) {
            .hero,
            .content-layout {
                grid-template-columns: 1fr;
            }

            .hero-panel,
            .sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .hero-story {
                padding: 34px 26px;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 18px 12px 34px;
            }

            .hero-panel,
            .content-area,
            .sidebar {
                padding: 24px 18px;
            }

            .hero-story {
                padding: 28px 18px;
            }

            .frame {
                border-radius: 24px;
            }

            .hero-title {
                margin-top: 14px;
            }
        }
    </style>
</head>
<body>
    @php
        $title = trim((string) ($about?->title ?? ''));
        $description = trim((string) ($about?->description ?? ''));
        $blocks = $description !== '' ? array_values(array_filter(preg_split('/\R{2,}/', $description), fn ($block) => trim($block) !== '')) : [];

        $sections = [];
        foreach ($blocks as $index => $block) {
            $lines = array_values(array_filter(preg_split('/\R/', trim($block)), fn ($line) => trim($line) !== ''));
            $firstLine = $lines[0] ?? '';
            $looksLikeHeading = count($lines) > 1 && mb_strlen($firstLine) <= 80 && !str_contains($firstLine, '.');
            $heading = $looksLikeHeading ? trim($firstLine) : (($index === 0 && $title !== '') ? $title : 'Section ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT));
            $bodyLines = $looksLikeHeading ? array_slice($lines, 1) : $lines;
            $isList = !empty($bodyLines) && count(array_filter($bodyLines, fn ($line) => preg_match('/^[-*]\s+/', trim($line)))) === count($bodyLines);

            $sections[] = [
                'id' => 'section-' . ($index + 1),
                'heading' => $heading,
                'body' => $bodyLines,
                'is_list' => $isList,
            ];
        }

        $heroTag = $sections[0]['heading'] ?? ($title !== '' ? $title : 'About');
        $heroTag = mb_strlen($heroTag) > 32 ? mb_substr($heroTag, 0, 32) . '...' : $heroTag;
        $introLines = $sections[0]['body'] ?? [];
        $introText = implode(' ', array_slice($introLines, 0, min(2, count($introLines))));
        $sectionCount = count($sections);
        $textLineCount = array_sum(array_map(fn ($section) => !$section['is_list'] ? count($section['body']) : 0, $sections));
    @endphp

    <main class="page">
        <div class="frame">
            @if($about && ($title !== '' || $description !== ''))
                <section class="hero">
                    <div class="hero-panel">
                        <div>
                            <span class="hero-tag">{{ $heroTag }}</span>
                            <h1 class="hero-title">{{ $title !== '' ? $title : 'About Us' }}</h1>
                        </div>

                        <div class="hero-meta">
                            <div class="meta-box">
                                <span class="meta-label">Sections</span>
                                <span class="meta-value">{{ $sectionCount > 0 ? $sectionCount : 1 }} content block{{ $sectionCount === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="meta-box">
                                <span class="meta-label">Reading Focus</span>
                                <span class="meta-value">{{ $introText !== '' ? $introText : 'Dynamic content from your saved About Us description appears here.' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-story">
                        @if(!empty($introLines))
                            @foreach(array_slice($introLines, 0, 2) as $line)
                                <p>{{ preg_replace('/^[-*]\s+/', '', trim($line)) }}</p>
                            @endforeach
                        @else
                            <p>Dynamic content from your About Us description will appear here once it is added.</p>
                        @endif
                    </div>
                </section>

                <section class="content-layout">
                    <aside class="sidebar">
                        <div class="sidebar-box">
                            <h2 class="sidebar-title">Explore</h2>
                            <div class="toc">
                                @foreach($sections as $index => $section)
                                    <a href="#{{ $section['id'] }}">
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}. {{ $section['heading'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="sidebar-box">
                            <h2 class="sidebar-title">Overview</h2>
                            <div class="toc">
                                <span>{{ $sectionCount }} section{{ $sectionCount === 1 ? '' : 's' }}</span>
                                <span>{{ count($blocks) }} content group{{ count($blocks) === 1 ? '' : 's' }}</span>
                                <span>{{ $textLineCount }} text line{{ $textLineCount === 1 ? '' : 's' }}</span>
                            </div>
                        </div>
                    </aside>

                    <div class="content-area">
                        @foreach($sections as $index => $section)
                            <article class="section" id="{{ $section['id'] }}">
                                <div class="section-header">
                                    <span class="section-index">Part {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <h2>{{ $section['heading'] }}</h2>
                                </div>

                                @if($section['is_list'])
                                    <ul>
                                        @foreach($section['body'] as $line)
                                            <li>{{ preg_replace('/^[-*]\s+/', '', trim($line)) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    @foreach($section['body'] as $line)
                                        <p>{{ $line }}</p>
                                    @endforeach
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @else
                <section class="empty">
                    <h1>About Us</h1>
                    <p>Content not available. Add the About Us title and description from the admin panel or API to show the full dynamic page.</p>
                </section>
            @endif
        </div>
    </main>
</body>
</html>
