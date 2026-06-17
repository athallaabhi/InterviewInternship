<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Adaro Emission Tracker')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green-dark:   #1B4332;
            --green-mid:    #2D6A4F;
            --green-light:  #40916C;
            --green-pale:   #D8F3DC;
            --bg:           #F4F5F4;
            --white:        #FFFFFF;
            --border:       #E0E4E0;
            --text-primary: #1A1A1A;
            --text-muted:   #6B7280;
            --red:          #DC2626;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green-dark);
            letter-spacing: -0.3px;
        }

        nav { display: flex; gap: 0; }

        nav a {
            display: inline-flex;
            align-items: center;
            height: 56px;
            padding: 0 1.25rem;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-decoration: none;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            transition: color .15s, border-color .15s;
        }
        nav a:hover { color: var(--green-dark); }
        nav a.active { color: var(--green-dark); border-bottom-color: var(--green-dark); }

        /* ── Main ── */
        main { flex: 1; padding: 2.5rem 2rem; max-width: 900px; margin: 0 auto; width: 100%; }

        /* ── Card ── */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.75rem;
            margin-bottom: 1.25rem;
        }

        /* ── Form elements ── */
        .field { margin-bottom: 1rem; }
        .field label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: .4rem;
        }

        select, input[type="number"], input[type="text"], textarea {
            width: 100%;
            padding: .55rem .75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: .95rem;
            color: var(--text-primary);
            background: var(--white);
            transition: border-color .15s, box-shadow .15s;
            appearance: none;
        }
        select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236B7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; padding-right: 2.2rem; }
        select:focus, input:focus, textarea:focus { outline: none; border-color: var(--green-mid); box-shadow: 0 0 0 3px rgba(45,106,79,.12); }

        .input-group { display: flex; align-items: stretch; }
        .input-group input { border-radius: 6px 0 0 6px; flex: 1; }
        .input-unit { background: var(--bg); border: 1px solid var(--border); border-left: none; border-radius: 0 6px 6px 0; padding: .55rem .85rem; font-size: .875rem; color: var(--text-muted); white-space: nowrap; display: flex; align-items: center; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .6rem 1.25rem;
            font-size: .8rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase;
            border: none; border-radius: 6px; cursor: pointer; transition: background .15s, opacity .15s;
            text-decoration: none;
        }
        .btn-primary { background: var(--green-dark); color: #fff; }
        .btn-primary:hover { background: var(--green-mid); }
        .btn-secondary { background: var(--bg); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }
        .btn-danger { background: var(--red); color: #fff; }
        .btn-danger:hover { opacity: .85; }
        .btn-sm { padding: .3rem .7rem; font-size: .72rem; }
        .btn[disabled] { opacity: .5; cursor: not-allowed; }

        /* ── Formula box ── */
        .formula-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: .65rem 1rem;
            font-family: 'Courier New', monospace;
            font-size: .875rem;
            color: var(--text-muted);
        }
        .formula-box strong { color: var(--text-primary); font-family: inherit; }

        /* ── Result card ── */
        .result-card { position: relative; overflow: hidden; }
        .result-label { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .5rem; }
        .result-number { font-size: 2.8rem; font-weight: 800; color: var(--green-dark); line-height: 1; }
        .result-unit { font-size: 1.1rem; font-weight: 500; color: var(--green-mid); margin-left: .35rem; }
        .co2-watermark {
            position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%);
            font-size: 4rem; font-weight: 900; color: rgba(27,67,50,.06); letter-spacing: -2px; user-select: none;
            font-family: 'Courier New', monospace;
        }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th { text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--text-muted); padding: .5rem .75rem; border-bottom: 1px solid var(--border); }
        td { padding: .6rem .75rem; border-bottom: 1px solid var(--border); color: var(--text-primary); }
        tr:last-child td { border-bottom: none; }
        .td-num { text-align: right; font-variant-numeric: tabular-nums; }

        /* ── Evaluated expression ── */
        .expr-box { background: var(--bg); border-radius: 6px; border: 1px solid var(--border); padding: .75rem 1rem; margin-top: 1rem; }
        .expr-label { font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .25rem; }
        .expr-text { font-family: 'Courier New', monospace; font-size: .875rem; color: var(--green-dark); }

        /* ── Alert ── */
        .alert { padding: .85rem 1rem; border-radius: 6px; font-size: .9rem; margin-bottom: 1rem; }
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: var(--red); }
        .alert-success { background: var(--green-pale); border: 1px solid #B7E4C7; color: var(--green-dark); }

        /* ── Tabs (admin) ── */
        .tabs { display: flex; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem; gap: 0; }
        .tab-btn {
            padding: .65rem 1.1rem; font-size: .8rem; font-weight: 600; letter-spacing: .05em;
            text-transform: uppercase; background: none; border: none; border-bottom: 2px solid transparent;
            cursor: pointer; color: var(--text-muted); transition: color .15s, border-color .15s;
        }
        .tab-btn:hover { color: var(--green-dark); }
        .tab-btn.active { color: var(--green-dark); border-bottom-color: var(--green-dark); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Modal ── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: var(--white); border-radius: 10px; padding: 1.75rem; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; }
        .modal h3 { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--green-dark); }
        .modal-footer { display: flex; justify-content: flex-end; gap: .5rem; margin-top: 1.25rem; }

        /* ── Sub-selector ── */
        .sub-select-row { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
        .sub-select-row label { font-size: .8rem; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
        .sub-select-row select { max-width: 260px; }

        /* ── Misc ── */
        .page-title { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: .35rem; }
        .page-subtitle { font-size: .9rem; color: var(--text-muted); margin-bottom: 1.5rem; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .section-header h2 { font-size: 1rem; font-weight: 700; }
        .text-muted { color: var(--text-muted); }
        .hidden { display: none !important; }
        .mt-1 { margin-top: .5rem; }
        .mt-2 { margin-top: 1rem; }
        .flex-row { display: flex; gap: .5rem; align-items: center; }
        .badge { display: inline-block; background: var(--green-pale); color: var(--green-dark); font-size: .7rem; font-weight: 700; padding: .15rem .45rem; border-radius: 4px; }

        /* ── Checkbox list ── */
        .check-list { display: flex; flex-direction: column; gap: .4rem; margin-top: .35rem; }
        .check-list label { font-size: .9rem; font-weight: 400; text-transform: none; letter-spacing: 0; display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .check-list input[type="checkbox"] { width: auto; }

        /* ── Footer ── */
        footer { text-align: center; padding: 1.5rem 2rem; font-size: .78rem; color: var(--text-muted); border-top: 1px solid var(--border); background: var(--white); display: flex; justify-content: space-between; align-items: center; }
        footer a { color: var(--text-muted); text-decoration: none; }
        footer a:hover { color: var(--green-dark); }

        @media (max-width: 640px) {
            main { padding: 1.25rem 1rem; }
            header { padding: 0 1rem; }
            .result-number { font-size: 2rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<header>
    <div class="brand">Adaro Emission Tracker</div>
    <nav>
        <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Calculator</a>
        <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">Admin Panel</a>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer>
    <span>© {{ date('Y') }} Adaro Energy. Internal Use Only.</span>
    <div>
        <a href="#">Support</a> &nbsp;·&nbsp; <a href="#">Documentation</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>
