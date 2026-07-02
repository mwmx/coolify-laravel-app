<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} — Status</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #1e293b;
            --border: #334155;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --ok: #4ade80;
            --warn: #fbbf24;
            --bad: #f87171;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        .wrap { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
        h1 { font-size: 1.5rem; margin: 0 0 0.25rem; }
        h1 .env {
            font-size: 0.7rem;
            vertical-align: middle;
            background: var(--border);
            color: var(--accent);
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            margin-left: 0.5rem;
        }
        .sub { color: var(--muted); margin: 0 0 2rem; }
        section {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        section > h2 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            margin: 0;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 0.5rem 1rem; text-align: left; vertical-align: top; }
        tbody tr + tr td { border-top: 1px solid var(--border); }
        td.key { color: var(--muted); width: 14rem; white-space: nowrap; }
        td.val { word-break: break-word; }
        .badge {
            display: inline-block;
            padding: 0.1rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge.ok { background: rgba(74, 222, 128, 0.15); color: var(--ok); }
        .badge.warn { background: rgba(251, 191, 36, 0.15); color: var(--warn); }
        .badge.bad { background: rgba(248, 113, 113, 0.15); color: var(--bad); }
        thead th {
            font-size: 0.75rem;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }
        .count { color: var(--muted); font-weight: 400; text-transform: none; letter-spacing: 0; }
        .note {
            padding: 0.75rem 1rem;
            color: var(--bad);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>
            {{ config('app.name', 'Laravel') }}
            <span class="env">{{ $application['Environment'] }}</span>
        </h1>
        <p class="sub">Deployment status &middot; {{ now()->toDayDateTimeString() }} {{ config('app.timezone') }}</p>

        <section>
            <h2>Application</h2>
            <table>
                <tbody>
                    @foreach ($application as $label => $value)
                        <tr>
                            <td class="key">{{ $label }}</td>
                            <td class="val">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section>
            <h2>Drivers</h2>
            <table>
                <tbody>
                    @foreach ($drivers as $label => $value)
                        <tr>
                            <td class="key">{{ $label }}</td>
                            <td class="val">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section>
            <h2>Database</h2>
            <table>
                <tbody>
                    <tr>
                        <td class="key">Connection</td>
                        <td class="val">{{ $database['connection'] }} ({{ $database['driver'] }})</td>
                    </tr>
                    <tr>
                        <td class="key">Status</td>
                        <td class="val">
                            @if ($database['connected'])
                                <span class="badge ok">connected</span>
                            @else
                                <span class="badge bad">unreachable</span>
                            @endif
                        </td>
                    </tr>
                    @if ($database['version'])
                        <tr>
                            <td class="key">Server version</td>
                            <td class="val">{{ $database['version'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            @if ($database['error'])
                <div class="note">{{ $database['error'] }}</div>
            @endif
        </section>

        <section>
            <h2>
                Migrations
                @if ($migrations['available'])
                    <span class="count">— {{ $migrations['ran'] }} ran, {{ $migrations['pending'] }} pending</span>
                @endif
            </h2>
            @if ($migrations['available'])
                <table>
                    <thead>
                        <tr>
                            <th>Migration</th>
                            <th>Status</th>
                            <th>Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($migrations['items'] as $migration)
                            <tr>
                                <td class="val">{{ $migration['name'] }}</td>
                                <td>
                                    @if ($migration['status'] === 'Ran')
                                        <span class="badge ok">Ran</span>
                                    @else
                                        <span class="badge warn">Pending</span>
                                    @endif
                                </td>
                                <td class="val">{{ $migration['batch'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="note">{{ $migrations['error'] }}</div>
            @endif
        </section>
    </div>
</body>
</html>
