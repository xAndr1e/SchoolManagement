<?php
$currentPage = 'mobile-attendance';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#16213e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Mobile Attendance Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        /* ============================================================
           DESIGN TOKENS
           ============================================================ */
        :root {
            color-scheme: light;

            --brand-navy: #1a1a2e;
            --brand-navy-deep: #16213e;

            /* Accent split into a readable "fill" tone and a light "tint" tone.
               The old #4fc3f7 fill failed contrast behind white text. */
            --accent: #0b76b8;
            --accent-strong: #095c90;
            --accent-tint: #e6f3fb;
            --accent-ink: #0b5c8f;

            --success: #1e7e34;
            --success-tint: #e4f4e8;
            --danger: #c62828;
            --danger-tint: #fdecec;
            --warning: #b26a00;
            --warning-tint: #fff3e0;

            --ink: #16213e;
            --ink-soft: #3d4859;
            --ink-muted: #6b7684;
            --ink-faint: #9aa4b2;

            --surface: #ffffff;
            --surface-app: #f2f5f9;
            --surface-muted: #f6f8fb;
            --surface-inset: #eef1f6;
            --line: #e4e8ef;
            --line-strong: #d3d9e3;

            /* Spacing scale — every gap in the UI comes from here */
            --sp-1: 4px;
            --sp-2: 8px;
            --sp-3: 12px;
            --sp-4: 16px;
            --sp-5: 20px;
            --sp-6: 24px;

            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;
            --r-xl: 20px;
            --r-pill: 999px;

            --shadow-sm: 0 1px 2px rgba(16, 24, 40, 0.06);
            --shadow-md: 0 2px 10px rgba(16, 24, 40, 0.08);
            --shadow-lg: 0 -4px 24px rgba(16, 24, 40, 0.12);

            --tap: 44px;
            --header-h: 0px;
            --dock-h: 70px; /* Reduced from 88px to prevent overlap */
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --accent: #3aa8e6;
                --accent-strong: #7cc8f0;
                --accent-tint: #14283a;
                --accent-ink: #8fd0f4;
                --ink: #eaeef5;
                --ink-soft: #c4ccd9;
                --ink-muted: #99a4b3;
                --ink-faint: #74808f;
                --surface: #161c27;
                --surface-app: #0e131b;
                --surface-muted: #1b222e;
                --surface-inset: #212936;
                --line: #2a3342;
                --line-strong: #3a4557;
                --success-tint: #16301f;
                --danger-tint: #34191b;
                --warning-tint: #33260f;
                --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.4);
                --shadow-md: 0 2px 10px rgba(0, 0, 0, 0.45);
                --shadow-lg: 0 -4px 24px rgba(0, 0, 0, 0.55);
            }
        }

        /* ============================================================
           BASE
           ============================================================ */
        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }

        html, body { height: 100%; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: var(--surface-app);
            color: var(--ink);
            padding-bottom: calc(var(--dock-h) + env(safe-area-inset-bottom));
            overscroll-behavior-y: contain;
            -webkit-overflow-scrolling: touch;
            margin: 0;
        }

        body.is-locked { overflow: hidden; }

        input, select, textarea { font-size: 16px !important; }
        button { touch-action: manipulation; font-family: inherit; }

        /* One visible focus treatment for the whole app */
        :focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
            border-radius: var(--r-sm);
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }

        .app-shell {
            max-width: 620px;
            margin: 0 auto;
            position: relative;
        }

        .page-body {
            padding: var(--sp-4);
            padding-left: calc(var(--sp-4) + env(safe-area-inset-left));
            padding-right: calc(var(--sp-4) + env(safe-area-inset-right));
        }

        /* ============================================================
           HEADER
           ============================================================ */
        .mobile-header {
            background: linear-gradient(135deg, var(--brand-navy), var(--brand-navy-deep));
            color: #fff;
            padding: var(--sp-4) var(--sp-5) var(--sp-4);
            padding-top: calc(var(--sp-4) + env(safe-area-inset-top));
            padding-left: calc(var(--sp-5) + env(safe-area-inset-left));
            padding-right: calc(var(--sp-5) + env(safe-area-inset-right));
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.22);
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-3);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: var(--sp-2);
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .header-title i { font-size: 1.1rem; opacity: 0.9; }

        .header-sub {
            display: block;
            margin-top: 2px;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.68);
            line-height: 1.3;
        }

        .header-pill {
            flex: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
            padding: 6px 12px;
            border-radius: var(--r-pill);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Progress rail lives in the header so it is always visible */
        .header-progress {
            margin-top: var(--sp-3);
        }

        .header-progress__meta {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: var(--sp-2);
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.72);
            margin-bottom: 6px;
        }

        .header-progress__meta strong { color: #fff; font-weight: 700; }

        .progress-rail {
            height: 6px;
            border-radius: var(--r-pill);
            background: rgba(255, 255, 255, 0.16);
            overflow: hidden;
        }

        .progress-rail__fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #4fc3f7, #7ee0a1);
            transition: width 0.35s ease;
        }

        /* ============================================================
           STICKY TOOLBAR (tabs + search)
           ============================================================ */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 90;
            background: var(--surface-app);
            padding: var(--sp-3) var(--sp-4) var(--sp-2);
            padding-left: calc(var(--sp-4) + env(safe-area-inset-left));
            padding-right: calc(var(--sp-4) + env(safe-area-inset-right));
            border-bottom: 1px solid transparent;
        }

        .toolbar.is-stuck { border-bottom-color: var(--line); }

        .segmented {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--sp-1);
            background: var(--surface-inset);
            border-radius: var(--r-md);
            padding: var(--sp-1);
        }

        .segmented button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            border: none;
            background: transparent;
            border-radius: var(--r-sm);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ink-muted);
            transition: background 0.18s ease, color 0.18s ease;
        }

        .segmented button .seg-count {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: var(--r-pill);
            background: var(--line);
            color: var(--ink-soft);
        }

        .segmented button.active {
            background: var(--surface);
            color: var(--ink);
            box-shadow: var(--shadow-sm);
        }

        .segmented button.active .seg-count {
            background: var(--accent-tint);
            color: var(--accent-ink);
        }

        .search-row {
            position: relative;
            margin-top: var(--sp-2);
        }

        .search-row i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-faint);
            font-size: 0.95rem;
            pointer-events: none;
        }

        .search-row input {
            width: 100%;
            min-height: var(--tap);
            padding: 10px 40px 10px 38px;
            border: 1.5px solid var(--line);
            border-radius: var(--r-md);
            background: var(--surface);
            color: var(--ink);
        }

        .search-row input:focus {
            border-color: var(--accent);
            outline: none;
        }

        .search-row .search-clear {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            color: var(--ink-muted);
            border-radius: 50%;
            font-size: 1.1rem;
            line-height: 1;
        }

        /* ============================================================
           SUMMARY
           ============================================================ */
        .summary-bar {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: var(--sp-3) var(--sp-2);
            margin-bottom: var(--sp-4);
            box-shadow: var(--shadow-sm);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            align-items: center;
        }

        .summary-bar .stat {
            text-align: center;
            padding: 0 var(--sp-2);
            border-right: 1px solid var(--line);
        }

        .summary-bar .stat:last-child { border-right: none; }

        .summary-bar .stat-number {
            display: block;
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }

        .summary-bar .stat-label {
            display: block;
            margin-top: 2px;
            font-size: 0.68rem;
            font-weight: 600;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .summary-bar .stat-number.total { color: var(--ink); }
        .summary-bar .stat-number.marked { color: var(--success); }
        .summary-bar .stat-number.pending { color: var(--warning); }

        /* ============================================================
           ROOM CARD
           ============================================================ */
        .card-list { display: flex; flex-direction: column; gap: var(--sp-3); }

        .room-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-left: 4px solid var(--line-strong);
            border-radius: var(--r-lg);
            padding: var(--sp-4);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: var(--sp-3);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.12s ease;
        }

        .room-card.is-now {
            border-left-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent-tint), var(--shadow-md);
        }

        .room-card.is-done { border-left-color: var(--success); }
        .room-card.is-past { border-left-color: var(--line-strong); }
        .room-card.is-past .card-head { opacity: 0.72; }

        .card-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: var(--sp-3);
        }

        .card-head__main { min-width: 0; }

        .room-number {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--ink);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .room-number i { color: var(--ink-faint); font-size: 0.95rem; flex: none; }

        .room-number span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .faculty-name {
            margin-top: 2px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--ink-soft);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-head__side {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            flex: none;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--sp-2);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            max-width: 100%;
            padding: 4px 10px;
            border-radius: var(--r-pill);
            font-size: 0.74rem;
            font-weight: 600;
            line-height: 1.4;
            background: var(--surface-inset);
            color: var(--ink-soft);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chip i { font-size: 0.78rem; opacity: 0.8; flex: none; }
        .chip--time { background: var(--surface-inset); color: var(--ink-soft); font-variant-numeric: tabular-nums; }
        .chip--subject { background: var(--accent-tint); color: var(--accent-ink); }
        .chip--now { background: var(--success-tint); color: var(--success); }
        .chip--past { background: var(--surface-inset); color: var(--ink-faint); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 11px;
            border-radius: var(--r-pill);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .status-badge.present { background: #d7f0dd; color: #14532d; }
        .status-badge.late    { background: #ffeeba; color: #6b4a00; }
        .status-badge.absent  { background: #fadbdc; color: #7f1d1d; }
        .status-badge.online  { background: #d3ecf3; color: #0b4f5e; }
        .status-badge.pending { background: #e6e8ec; color: #3a4250; }
        .status-badge.excused { background: #d6e6ff; color: #123a75; }
        .status-badge.nt      { background: #ecdcbe; color: #5f430f; }
        .status-badge.eb      { background: #ffe3bc; color: #7a5714; }
        .status-badge.ed      { background: #ffd3c4; color: #a02d0a; }
        .status-badge.ob      { background: #c4dcf5; color: #0d3f80; }
        .status-badge.at      { background: #cdeacf; color: #17501b; }

        /* Full-width action — one predictable tap target per card */
        .card-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-2);
            width: 100%;
            min-height: var(--tap);
            border: none;
            border-radius: var(--r-md);
            background: var(--accent);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            transition: transform 0.12s ease, background 0.18s ease;
        }

        .card-action:active { transform: scale(0.985); background: var(--accent-strong); }

        .card-action.is-done {
            background: var(--success-tint);
            color: var(--success);
            border: 1px solid rgba(30, 126, 52, 0.25);
        }

        .card-action:disabled { opacity: 1; }

        /* ============================================================
           STATES
           ============================================================ */
        .empty-state {
            text-align: center;
            padding: 56px var(--sp-5);
            background: var(--surface);
            border: 1px dashed var(--line-strong);
            border-radius: var(--r-lg);
        }

        .empty-state i { font-size: 2.75rem; color: var(--ink-faint); }
        .empty-state h5 { color: var(--ink-soft); margin: var(--sp-3) 0 var(--sp-1); font-size: 1rem; font-weight: 700; }
        .empty-state p { color: var(--ink-muted); font-size: 0.85rem; margin: 0; }

        .empty-state .retry-btn {
            margin-top: var(--sp-4);
            min-height: var(--tap);
            padding: 0 var(--sp-5);
            border: none;
            border-radius: var(--r-md);
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
        }

        /* Skeletons read as "content is coming" better than a bare spinner */
        .skeleton-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-left: 4px solid var(--line);
            border-radius: var(--r-lg);
            padding: var(--sp-4);
            margin-bottom: var(--sp-3);
        }

        .sk-line {
            height: 12px;
            border-radius: var(--r-pill);
            background: linear-gradient(90deg, var(--surface-inset) 25%, var(--line) 37%, var(--surface-inset) 63%);
            background-size: 400% 100%;
            animation: sk-shimmer 1.4s ease infinite;
            margin-bottom: var(--sp-2);
        }

        .sk-line.w-40 { width: 40%; }
        .sk-line.w-60 { width: 60%; }
        .sk-line.w-80 { width: 80%; }
        .sk-line.tall { height: 40px; margin-top: var(--sp-3); margin-bottom: 0; }

        @keyframes sk-shimmer {
            0% { background-position: 100% 50%; }
            100% { background-position: 0 50%; }
        }

        /* ============================================================
           BOTTOM DOCK - FIXED OVERLAP
           ============================================================ */
        .floating-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            gap: var(--sp-2);
            max-width: 620px;
            margin: 0 auto;
            background: var(--surface);
            border-top: 1px solid var(--line);
            padding: var(--sp-2) var(--sp-3);
            padding-bottom: calc(var(--sp-2) + env(safe-area-inset-bottom));
            padding-left: calc(var(--sp-3) + env(safe-area-inset-left));
            padding-right: calc(var(--sp-3) + env(safe-area-inset-right));
            box-shadow: var(--shadow-lg);
        }

        .floating-actions button {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-1);
            border: none;
            min-height: 40px;
            border-radius: var(--r-md);
            font-weight: 700;
            font-size: 0.8rem;
            transition: transform 0.12s ease;
            padding: 0 var(--sp-2);
        }

        .floating-actions button i {
            font-size: 0.9rem;
        }

        .floating-actions button:disabled { opacity: 0.5; }
        .btn-refresh { background: var(--surface-inset); color: var(--ink-soft); }
        .btn-sync { background: var(--accent); color: #fff; }
        .btn-refresh:active, .btn-sync:active { transform: scale(0.97); }

        /* Hide text on very small screens, keep only icon */
        @media (max-width: 400px) {
            .floating-actions button .btn-text {
                display: none;
            }
            .floating-actions button {
                min-height: 36px;
                font-size: 0;
                gap: 0;
            }
            .floating-actions button i {
                font-size: 1.1rem;
            }
        }

        /* ============================================================
           BOTTOM SHEET
           ============================================================ */
        .modal-backdrop-custom {
            position: fixed;
            inset: 0;
            background: rgba(10, 16, 28, 0.55);
            backdrop-filter: blur(2px);
            z-index: 1000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .modal-sheet {
            background: var(--surface);
            color: var(--ink);
            width: 100%;
            max-width: 620px;
            border-radius: var(--r-xl) var(--r-xl) 0 0;
            max-height: 94vh;
            max-height: 94dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .modal-sheet .drag-handle {
            width: 44px;
            height: 4px;
            background: var(--line-strong);
            border-radius: var(--r-pill);
            margin: 10px auto 2px;
            flex: none;
        }

        .modal-sheet .modal-header {
            border-bottom: 1px solid var(--line);
            padding: var(--sp-3) var(--sp-5);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-3);
            flex: none;
        }

        .modal-sheet .modal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--sp-2);
        }

        .sheet-close {
            width: var(--tap);
            height: var(--tap);
            flex: none;
            border: none;
            background: var(--surface-inset);
            color: var(--ink-soft);
            border-radius: 50%;
            font-size: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .modal-sheet .modal-body {
            padding: var(--sp-4) var(--sp-5) var(--sp-5);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: var(--sp-4);
        }

        .modal-sheet .modal-footer {
            padding: var(--sp-3) var(--sp-5);
            padding-bottom: calc(var(--sp-3) + env(safe-area-inset-bottom));
            display: flex;
            gap: var(--sp-3);
            border-top: 1px solid var(--line);
            background: var(--surface);
            flex: none;
        }

        .modal-sheet .modal-footer .btn {
            flex: 1;
            min-height: 50px;
            border-radius: var(--r-md);
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-2);
        }

        .modal-sheet .modal-footer .btn-ghost {
            flex: 0 0 34%;
            background: var(--surface-inset);
            color: var(--ink-soft);
            border: none;
        }

        .modal-sheet .modal-footer .btn-primary-solid {
            background: var(--accent);
            color: #fff;
            border: none;
        }

        .modal-sheet .modal-footer .btn-primary-solid.is-blocked { background: var(--line-strong); color: var(--surface); }

        .footer-hint {
            padding: var(--sp-2) var(--sp-5) 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--warning);
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--surface);
        }

        /* Context block — label/value pairs on a shared grid so they line up */
        .context-card {
            background: var(--surface-muted);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            padding: var(--sp-3) var(--sp-4);
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: var(--sp-2) var(--sp-3);
            align-items: baseline;
        }

        .context-card dt {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 0;
        }

        .context-card dd {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--ink);
            min-width: 0;
            overflow-wrap: anywhere;
        }

        /* ============================================================
           FIELD GROUPS
           ============================================================ */
        .field-group {
            background: var(--surface-muted);
            border: 1.5px solid var(--line);
            border-radius: var(--r-lg);
            padding: var(--sp-4);
            transition: border-color 0.2s, background 0.2s;
        }

        .field-group__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-2);
            margin-bottom: var(--sp-3);
        }

        .field-group__label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--ink);
            margin: 0;
        }

        .field-group__label i { color: var(--ink-muted); }

        .required-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--danger);
            display: inline-block;
        }

        .field-group__status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .field-group__hint {
            display: block;
            margin-top: var(--sp-3);
            color: var(--ink-muted);
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .field-group__hint--error {
            color: var(--danger);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-group--invalid {
            border-color: var(--danger);
            background: var(--danger-tint);
            animation: field-shake 0.35s ease;
        }

        @keyframes field-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: var(--sp-2);
        }

        .field-label .req { color: var(--danger); }

        .text-input {
            width: 100%;
            min-height: var(--tap);
            padding: 10px 14px;
            border: 1.5px solid var(--line);
            border-radius: var(--r-md);
            background: var(--surface);
            color: var(--ink);
        }

        .text-input:focus { border-color: var(--accent); outline: none; }
        .text-input::placeholder { color: var(--ink-faint); }

        .field-help {
            display: block;
            margin-top: 6px;
            font-size: 0.75rem;
            color: var(--ink-muted);
        }

        /* Stepper — symmetric so the number stays optically centred */
        .student-count-input {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-2);
        }

        .student-count-input input {
            width: 76px;
            flex: none;
            text-align: center;
            font-size: 1.35rem !important;
            font-weight: 700;
            border: 2px solid var(--line);
            border-radius: var(--r-md);
            padding: 8px 4px;
            min-height: 48px;
            background: var(--surface);
            color: var(--ink);
            -moz-appearance: textfield;
        }

        .student-count-input input::-webkit-outer-spin-button,
        .student-count-input input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .student-count-input input::placeholder { color: var(--ink-faint); font-weight: 600; }
        .student-count-input input:focus { border-color: var(--accent); outline: none; }
        .student-count-input input.is-invalid { border-color: var(--danger); }

        .step-btn {
            width: 40px;
            height: 40px;
            flex: none;
            border-radius: 50%;
            border: 1.5px solid var(--line-strong);
            background: var(--surface);
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink-soft);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.12s ease, background 0.18s ease;
        }

        .step-btn:active { transform: scale(0.9); background: var(--surface-inset); }
        .step-btn.step-btn--lg { font-size: 0.82rem; }

        .count-caption {
            display: block;
            text-align: center;
            margin-top: var(--sp-2);
            font-size: 0.75rem;
            color: var(--ink-muted);
        }

        /* ============================================================
           STATUS PICKER — fixed 3-col grid, no orphan row
           ============================================================ */
        .status-quick-select {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--sp-2);
        }

        .status-quick-select button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 10px 4px;
            border: 2px solid var(--line);
            border-radius: var(--r-md);
            background: var(--surface);
            font-size: 0.72rem;
            font-weight: 700;
            transition: transform 0.12s ease, border-color 0.18s ease, background 0.18s ease;
            min-height: 62px;
            text-align: center;
            color: var(--ink-muted);
        }

        .status-quick-select button i { font-size: 1.05rem; line-height: 1; }
        .status-quick-select button:active { transform: scale(0.95); }
        .status-quick-select button.active { border-color: var(--accent); background: var(--accent-tint); color: var(--ink); }

        .status-quick-select button.present.active { border-color: #1e7e34; background: #d7f0dd; color: #14532d; }
        .status-quick-select button.late.active    { border-color: #b26a00; background: #ffeeba; color: #6b4a00; }
        .status-quick-select button.absent.active  { border-color: #c62828; background: #fadbdc; color: #7f1d1d; }
        .status-quick-select button.online.active  { border-color: #0b7fa0; background: #d3ecf3; color: #0b4f5e; }
        .status-quick-select button.nt.active      { border-color: #8a6210; background: #ecdcbe; color: #5f430f; }
        .status-quick-select button.eb.active      { border-color: #b26a00; background: #ffe3bc; color: #7a5714; }
        .status-quick-select button.ed.active      { border-color: #bf360c; background: #ffd3c4; color: #a02d0a; }
        .status-quick-select button.ob.active      { border-color: #0d47a1; background: #c4dcf5; color: #0d3f80; }
        .status-quick-select button.at.active      { border-color: #1b5e20; background: #cdeacf; color: #17501b; }

        .status-meaning {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: var(--sp-3);
            padding: 10px 12px;
            border-radius: var(--r-sm);
            background: var(--surface);
            border: 1px solid var(--line);
            font-size: 0.78rem;
            color: var(--ink-soft);
        }

        .status-meaning i { color: var(--accent); }

        /* ============================================================
           CAPTURE / UPLOAD
           ============================================================ */
        .capture-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--sp-3);
        }

        .capture-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: var(--sp-4) var(--sp-2);
            border-radius: var(--r-md);
            font-weight: 700;
            font-size: 0.8rem;
            min-height: 84px;
            border: none;
            transition: transform 0.12s ease;
        }

        .capture-btn:active { transform: scale(0.97); }
        .capture-btn i { font-size: 1.35rem; }
        .capture-btn--primary { background: var(--accent); color: #fff; }
        .capture-btn--secondary { background: var(--surface); border: 2px dashed var(--line-strong); color: var(--ink-muted); }

        .file-upload-wrapper { position: relative; width: 100%; }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--sp-2);
            padding: var(--sp-3);
            border: 2px dashed var(--line-strong);
            border-radius: var(--r-md);
            background: var(--surface);
            color: var(--ink-muted);
            font-weight: 600;
            font-size: 0.85rem;
            min-height: 56px;
            text-align: center;
        }

        .file-upload-label.has-file { border-style: solid; border-color: var(--success); background: var(--success-tint); color: var(--success); }
        .file-upload-label i { font-size: 1.15rem; flex: none; }

        .file-preview {
            margin-top: var(--sp-3);
            padding: var(--sp-2);
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            align-items: center;
            gap: var(--sp-3);
        }

        .file-preview img {
            width: 64px;
            height: 64px;
            border-radius: var(--r-sm);
            object-fit: cover;
            display: block;
        }

        .file-preview .file-info { min-width: 0; font-size: 0.82rem; color: var(--ink); }

        .file-preview .file-info strong {
            display: block;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-preview .file-info small { color: var(--ink-muted); display: block; margin-top: 2px; }

        .file-preview .remove-file {
            width: var(--tap);
            height: var(--tap);
            border: none;
            background: var(--surface-inset);
            color: var(--danger);
            border-radius: 50%;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .visually-hidden-input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* ============================================================
           CAMERA OVERLAY
           ============================================================ */
        .camera-overlay {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 9000;
            display: flex;
            flex-direction: column;
        }

        .camera-overlay__stage {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .camera-overlay__video { width: 100%; height: 100%; object-fit: cover; }

        .camera-overlay__hint {
            position: absolute;
            top: calc(var(--sp-5) + env(safe-area-inset-top));
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: var(--r-pill);
            white-space: nowrap;
        }

        .camera-overlay__controls {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: var(--sp-5) var(--sp-6);
            padding-bottom: calc(var(--sp-5) + env(safe-area-inset-bottom));
            background: #000;
        }

        .camera-overlay__btn--cancel {
            justify-self: start;
            background: none;
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 10px 14px;
            min-height: var(--tap);
        }

        .camera-overlay__shutter {
            justify-self: center;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid rgba(255, 255, 255, 0.35);
            padding: 0;
            transition: transform 0.12s ease;
        }

        .camera-overlay__shutter:active { transform: scale(0.92); }

        /* ============================================================
           CONFIRM DIALOG + TOASTS
           ============================================================ */
        .confirm-card {
            background: var(--surface);
            color: var(--ink);
            width: 100%;
            max-width: 400px;
            border-radius: var(--r-lg);
            padding: var(--sp-5);
            box-shadow: var(--shadow-md);
        }

        .confirm-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1100;
            background: rgba(10, 16, 28, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--sp-5);
        }

        .confirm-card h6 { font-weight: 700; margin: 0 0 var(--sp-2); font-size: 1rem; }
        .confirm-card p { color: var(--ink-soft); font-size: 0.88rem; margin: 0 0 var(--sp-4); line-height: 1.5; }
        .confirm-card .confirm-actions { display: flex; gap: var(--sp-3); }

        .confirm-card .confirm-actions button {
            flex: 1;
            min-height: var(--tap);
            border: none;
            border-radius: var(--r-md);
            font-weight: 700;
            font-size: 0.85rem;
        }

        .confirm-card .btn-cancel { background: var(--surface-inset); color: var(--ink-soft); }
        .confirm-card .btn-go { background: var(--accent); color: #fff; }

        .toast-container {
            position: fixed;
            top: calc(var(--sp-4) + env(safe-area-inset-top));
            right: var(--sp-4);
            left: var(--sp-4);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            pointer-events: none;
        }

        .toast-custom {
            width: 100%;
            max-width: 560px;
            background: var(--surface);
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            padding: var(--sp-3) var(--sp-4);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--sp-2);
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: var(--sp-3);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .toast-custom.success { border-left: 4px solid var(--success); }
        .toast-custom.error { border-left: 4px solid var(--danger); }
        .toast-custom.warning { border-left: 4px solid var(--warning); }
        .toast-custom.info { border-left: 4px solid var(--accent); }

        .toast-custom i { font-size: 1.25rem; }
        .toast-custom.success i { color: var(--success); }
        .toast-custom.error i { color: var(--danger); }
        .toast-custom.warning i { color: var(--warning); }
        .toast-custom.info i { color: var(--accent); }

        /* ============================================================
           TRANSITIONS
           ============================================================ */
        .sheet-enter-active, .sheet-leave-active { transition: opacity 0.25s ease; }
        .sheet-enter-from, .sheet-leave-to { opacity: 0; }
        .sheet-enter-active .modal-sheet, .sheet-leave-active .modal-sheet { transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1); }
        .sheet-enter-from .modal-sheet, .sheet-leave-to .modal-sheet { transform: translateY(100%); }

        .toast-enter-active, .toast-leave-active { transition: all 0.28s ease; }
        .toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-10px); }

        .card-fade-enter-active, .card-fade-leave-active { transition: all 0.22s ease; }
        .card-fade-enter-from, .card-fade-leave-to { opacity: 0; transform: translateY(6px); }
        .card-fade-leave-active { position: absolute; width: 100%; }

        .fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }

        /* ============================================================
           NARROW SCREENS
           ============================================================ */
        @media (max-width: 359px) {
            .status-quick-select button { font-size: 0.66rem; min-height: 56px; }
            .step-btn { width: 36px; height: 36px; }
            .student-count-input { gap: 4px; }
            .student-count-input input { width: 60px; }
            .capture-actions { grid-template-columns: 1fr; }
            .context-card { grid-template-columns: 84px minmax(0, 1fr); }
        }

        /* ============================================================
           EXTRA SMALL BOTTOM PADDING FOR SHORT SCREENS
           ============================================================ */
        @media (max-height: 700px) {
            body {
                padding-bottom: calc(60px + env(safe-area-inset-bottom));
            }
            .floating-actions {
                padding: var(--sp-1) var(--sp-2);
                padding-bottom: calc(var(--sp-1) + env(safe-area-inset-bottom));
            }
            .floating-actions button {
                min-height: 34px;
                font-size: 0.75rem;
            }
            .room-card {
                padding: var(--sp-3);
                gap: var(--sp-2);
            }
        }
    </style>
</head>
<body>

<div id="app" class="app-shell">

    <!-- ==========================================================
         HEADER
         ========================================================== -->
    <header class="mobile-header">
        <div class="header-top">
            <div>
                <h1 class="header-title"><i class="bi bi-broadcast-pin"></i> Room Monitoring</h1>
                <small class="header-sub">
                    {{ currentDateTime }}
                    <template v-if="lastUpdated"> · updated {{ lastUpdated }}</template>
                </small>
            </div>
            <span class="header-pill">
                <i class="bi bi-door-open"></i> {{ schedules.length }} {{ schedules.length === 1 ? 'room' : 'rooms' }}
            </span>
        </div>

        <div class="header-progress" v-if="!loading && schedules.length > 0">
            <div class="header-progress__meta">
                <span><strong>{{ counts.marked }}</strong> of {{ counts.total }} marked</span>
                <span>{{ progressPct }}%</span>
            </div>
            <div class="progress-rail" role="progressbar" :aria-valuenow="progressPct" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-rail__fill" :style="{ width: progressPct + '%' }"></div>
            </div>
        </div>
    </header>

    <!-- ==========================================================
         TOOLBAR — filter + search
         ========================================================== -->
    <div class="toolbar" v-if="!loading && !error && schedules.length > 0">
        <div class="segmented" role="tablist" aria-label="Filter schedules">
            <button type="button" role="tab"
                    :class="{ active: activeFilter === 'all' }"
                    :aria-selected="activeFilter === 'all'"
                    @click="activeFilter = 'all'">
                All <span class="seg-count">{{ counts.total }}</span>
            </button>
            <button type="button" role="tab"
                    :class="{ active: activeFilter === 'pending' }"
                    :aria-selected="activeFilter === 'pending'"
                    @click="activeFilter = 'pending'">
                Pending <span class="seg-count">{{ counts.pending }}</span>
            </button>
            <button type="button" role="tab"
                    :class="{ active: activeFilter === 'marked' }"
                    :aria-selected="activeFilter === 'marked'"
                    @click="activeFilter = 'marked'">
                Marked <span class="seg-count">{{ counts.marked }}</span>
            </button>
        </div>

        <div class="search-row" v-if="schedules.length > 5">
            <i class="bi bi-search"></i>
            <label class="visually-hidden-input" for="room-search">Search rooms</label>
            <input id="room-search" type="search" v-model="searchTerm"
                   placeholder="Search room, faculty or subject">
            <button v-if="searchTerm" type="button" class="search-clear"
                    @click="searchTerm = ''" aria-label="Clear search">&times;</button>
        </div>
    </div>

    <main class="page-body">

        <!-- Summary -->
        <div class="summary-bar" v-if="!loading">
            <div class="stat">
                <span class="stat-number total">{{ counts.total }}</span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat">
                <span class="stat-number marked">{{ counts.marked }}</span>
                <span class="stat-label">Marked</span>
            </div>
            <div class="stat">
                <span class="stat-number pending">{{ counts.pending }}</span>
                <span class="stat-label">Pending</span>
            </div>
        </div>

        <!-- Loading skeletons -->
        <div v-if="loading">
            <div class="skeleton-card" v-for="n in 3" :key="'sk-' + n">
                <div class="sk-line w-40"></div>
                <div class="sk-line w-60"></div>
                <div class="sk-line w-80"></div>
                <div class="sk-line tall"></div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="empty-state">
            <i class="bi bi-wifi-off"></i>
            <h5>{{ error === 'network' ? 'Can\'t reach the server' : 'Something went wrong' }}</h5>
            <p>{{ error === 'network' ? 'Check your connection and try again.' : error }}</p>
            <button type="button" class="retry-btn" @click="refreshData">
                <i class="bi bi-arrow-clockwise"></i> Try again
            </button>
        </div>

        <!-- Empty (no schedules at all) -->
        <div v-else-if="schedules.length === 0" class="empty-state">
            <i class="bi bi-calendar-check"></i>
            <h5>No schedules for today</h5>
            <p>All done for the day. 🎉</p>
        </div>

        <!-- Empty (filter/search returned nothing) -->
        <div v-else-if="filteredSchedules.length === 0" class="empty-state">
            <i class="bi bi-funnel"></i>
            <h5>Nothing matches this view</h5>
            <p v-if="searchTerm">No room matches “{{ searchTerm }}”.</p>
            <p v-else-if="activeFilter === 'pending'">Every room has been marked.</p>
            <p v-else>No rooms have been marked yet.</p>
            <button type="button" class="retry-btn" @click="resetFilters">Show all rooms</button>
        </div>

        <!-- Schedule cards -->
        <transition-group v-else name="card-fade" tag="div" class="card-list">
            <article class="room-card"
                     v-for="schedule in filteredSchedules"
                     :key="schedule.id"
                     :class="{
                        'is-now': isHappeningNow(schedule) && !schedule.has_attendance,
                        'is-done': schedule.has_attendance,
                        'is-past': schedule.is_past && !schedule.has_attendance
                     }">

                <div class="card-head">
                    <div class="card-head__main">
                        <h2 class="room-number">
                            <i class="bi bi-door-open"></i>
                            <span>{{ schedule.room || schedule.room_name || 'N/A' }}</span>
                        </h2>
                        <div class="faculty-name">{{ schedule.faculty_name || 'N/A' }}</div>
                    </div>
                    <div class="card-head__side">
                        <span class="status-badge" :class="getStatusBadge(schedule.attendance_status)">
                            {{ getShortStatusLabel(schedule.attendance_status) }}
                        </span>
                        <span class="chip chip--now" v-if="isHappeningNow(schedule)">
                            <i class="bi bi-record-circle"></i> Now
                        </span>
                        <span class="chip chip--past" v-else-if="schedule.is_past">Passed</span>
                    </div>
                </div>

                <div class="meta-row">
                    <span class="chip chip--time">
                        <i class="bi bi-clock"></i> {{ formatTime(schedule.start_time, schedule.end_time) }}
                    </span>
                    <span class="chip chip--subject">
                        <i class="bi bi-journal-bookmark"></i>
                        {{ schedule.subject_code || schedule.subject_name || 'N/A' }}
                    </span>
                    <span class="chip">
                        <i class="bi bi-people"></i>
                        {{ schedule.course_section || schedule.section_name || 'N/A' }}
                    </span>
                </div>

                <button v-if="schedule.has_attendance" class="card-action is-done" type="button" disabled>
                    <i class="bi bi-check-circle-fill"></i> Marked · {{ getShortStatusLabel(schedule.attendance_status) }}
                </button>
                <button v-else class="card-action" type="button" @click="openMarkModal(schedule)">
                    <i class="bi bi-pencil-square"></i> Mark attendance
                </button>
            </article>
        </transition-group>
    </main>

    <!-- ==========================================================
         BOTTOM DOCK - FIXED OVERLAP
         ========================================================== -->
    <div class="floating-actions">
        <button type="button" class="btn-refresh" @click="refreshData" :disabled="loading || isRefreshing">
            <i class="bi bi-arrow-clockwise" :class="{ spin: isRefreshing }"></i>
            <span class="btn-text">{{ isRefreshing ? 'Refreshing' : 'Refresh' }}</span>
        </button>
        <button type="button" class="btn-sync" @click="syncAll" :disabled="loading || counts.pending === 0">
            <i class="bi bi-cloud-arrow-up"></i>
            <span class="btn-text">Sync all</span>
        </button>
    </div>

    <!-- ==========================================================
         MARK ATTENDANCE SHEET
         ========================================================== -->
    <transition name="sheet">
        <div class="modal-backdrop-custom" v-if="showModal" @click.self="closeModal">
            <div class="modal-sheet" role="dialog" aria-modal="true" aria-labelledby="sheet-title">
                <div class="drag-handle"></div>

                <div class="modal-header">
                    <h6 class="modal-title" id="sheet-title"><i class="bi bi-person-check"></i> Mark attendance</h6>
                    <button type="button" class="sheet-close" @click="closeModal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body" ref="sheetBody">

                    <!-- Context -->
                    <dl class="context-card">
                        <dt>Faculty</dt>
                        <dd>{{ currentSchedule ? currentSchedule.faculty_name : '-' }}</dd>
                        <dt>Section</dt>
                        <dd>{{ currentSchedule ? (currentSchedule.course_section || currentSchedule.section_name) : '-' }}</dd>
                        <dt>Subject</dt>
                        <dd>{{ currentSchedule ? (currentSchedule.subject_code || currentSchedule.subject_name) : '-' }}</dd>
                        <dt>Room</dt>
                        <dd>{{ currentSchedule ? (currentSchedule.room || currentSchedule.room_name) : '-' }}</dd>
                        <dt>Time</dt>
                        <dd>{{ currentSchedule ? formatTime(currentSchedule.start_time, currentSchedule.end_time) : '-' }}</dd>
                    </dl>

                    <!-- Student count -->
                    <div class="field-group" ref="countField"
                         :class="{ 'field-group--invalid': studentCountTouched && !isStudentCountValid }">
                        <div class="field-group__header">
                            <p class="field-group__label">
                                <i class="bi bi-people-fill"></i> Students present
                                <span class="required-dot" aria-hidden="true"></span>
                            </p>
                            <span class="field-group__status" v-if="isStudentCountValid">
                                <i class="bi bi-check-circle-fill"></i> Recorded
                            </span>
                        </div>

                        <div class="student-count-input">
                            <button type="button" class="step-btn step-btn--lg"
                                    @click="adjustStudentCount(-5)" aria-label="Decrease by 5">−5</button>
                            <button type="button" class="step-btn"
                                    @click="adjustStudentCount(-1)" aria-label="Decrease by 1">−</button>
                            <input type="number"
                                   inputmode="numeric"
                                   v-model.number="studentCount"
                                   @blur="studentCountTouched = true"
                                   @input="studentCountTouched = true"
                                   min="0" max="100" placeholder="0"
                                   aria-label="Number of students"
                                   :class="{ 'is-invalid': studentCountTouched && !isStudentCountValid }">
                            <button type="button" class="step-btn"
                                    @click="adjustStudentCount(1)" aria-label="Increase by 1">+</button>
                            <button type="button" class="step-btn step-btn--lg"
                                    @click="adjustStudentCount(5)" aria-label="Increase by 5">+5</button>
                        </div>
                        <span class="count-caption">students in the room</span>

                        <small class="field-group__hint field-group__hint--error"
                               v-if="studentCountTouched && !isStudentCountValid">
                            <i class="bi bi-exclamation-circle-fill"></i> Enter a count between 0 and 100
                        </small>
                        <small class="field-group__hint" v-else>Count the students physically in the room right now.</small>
                    </div>

                    <!-- Status -->
                    <div class="field-group">
                        <div class="field-group__header">
                            <p class="field-group__label">
                                <i class="bi bi-flag-fill"></i> Status
                                <span class="required-dot" aria-hidden="true"></span>
                            </p>
                        </div>

                        <div class="status-quick-select" role="radiogroup" aria-label="Attendance status">
                            <button
                                v-for="opt in statusOptions"
                                :key="opt.value"
                                type="button"
                                role="radio"
                                :aria-checked="selectedStatus === opt.value"
                                :class="[opt.value, { active: selectedStatus === opt.value }]"
                                @click="selectStatus(opt.value)">
                                <i :class="opt.icon"></i>
                                <span>{{ opt.label }}</span>
                            </button>
                        </div>

                        <div class="status-meaning">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>{{ getStatusLabel(selectedStatus) }}</span>
                        </div>
                    </div>

                    <!-- Face-to-face photo -->
                    <div class="field-group" v-if="selectedStatus !== 'online'" ref="photoField"
                         :class="{ 'field-group--invalid': submitAttempted && !faceToFaceFile }">
                        <div class="field-group__header">
                            <p class="field-group__label">
                                <i class="bi bi-camera-fill"></i> Room photo
                                <span class="required-dot" aria-hidden="true"></span>
                            </p>
                            <span class="field-group__status" v-if="faceToFaceFile">
                                <i class="bi bi-check-circle-fill"></i> Attached
                            </span>
                        </div>

                        <input type="file" accept="image/*"
                               @change="handleFaceToFaceUpload" ref="faceToFaceInput"
                               class="visually-hidden-input" tabindex="-1" aria-hidden="true">
                        <input type="file" accept="image/*" capture="environment"
                               @change="handleFaceToFaceUpload" ref="faceToFaceCameraInput"
                               class="visually-hidden-input" tabindex="-1" aria-hidden="true">

                        <div v-if="!faceToFaceFile" class="capture-actions">
                            <button type="button" class="capture-btn capture-btn--primary" @click="openFaceToFaceCamera">
                                <i class="bi bi-camera-fill"></i> Take photo
                            </button>
                            <button type="button" class="capture-btn capture-btn--secondary" @click="faceToFaceInput.click()">
                                <i class="bi bi-images"></i> From gallery
                            </button>
                        </div>

                        <div v-else class="file-preview">
                            <img v-if="faceToFacePreview" :src="faceToFacePreview" alt="Room photo preview">
                            <div class="file-info">
                                <strong>{{ faceToFaceFile.name }}</strong>
                                <small>{{ formatFileSize(faceToFaceFile.size) }} · tap ✕ to retake</small>
                            </div>
                            <button type="button" class="remove-file" @click="removeFaceToFaceFile" aria-label="Remove photo">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <small class="field-group__hint">Photo of the class or room, used for verification.</small>
                    </div>

                    <!-- Online fields -->
                    <div class="field-group" v-if="selectedStatus === 'online'" ref="onlineField"
                         :class="{ 'field-group--invalid': submitAttempted && (!meetingLink.trim() || !uploadedFile) }">
                        <div class="field-group__header">
                            <p class="field-group__label">
                                <i class="bi bi-camera-video-fill"></i> Online session proof
                                <span class="required-dot" aria-hidden="true"></span>
                            </p>
                        </div>

                        <label class="field-label" for="meeting-link">Meeting link <span class="req">*</span></label>
                        <input id="meeting-link" type="url" class="text-input" v-model="meetingLink"
                               placeholder="https://meet.google.com/...">

                        <div style="margin-top: 16px;">
                            <span class="field-label">Screenshot <span class="req">*</span></span>
                            <div class="file-upload-wrapper">
                                <input type="file" accept="image/*" @change="handleFileUpload" ref="fileInput"
                                       aria-label="Upload meeting screenshot">
                                <div class="file-upload-label" :class="{ 'has-file': uploadedFile }">
                                    <i class="bi" :class="uploadedFile ? 'bi-check-circle-fill' : 'bi-cloud-upload'"></i>
                                    <span>{{ uploadedFile ? uploadedFile.name : 'Tap to upload screenshot' }}</span>
                                </div>
                            </div>

                            <div v-if="uploadedFile" class="file-preview">
                                <img v-if="uploadedFilePreview" :src="uploadedFilePreview" alt="Screenshot preview">
                                <div class="file-info">
                                    <strong>{{ uploadedFile.name }}</strong>
                                    <small>{{ formatFileSize(uploadedFile.size) }}</small>
                                </div>
                                <button type="button" class="remove-file" @click="removeFile" aria-label="Remove screenshot">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <small class="field-group__hint">Screenshot of the live meeting session.</small>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div ref="remarksField">
                        <template v-if="specialStatuses.includes(selectedStatus)">
                            <label class="field-label" for="remarks-required">Remarks <span class="req">*</span></label>
                            <input id="remarks-required" type="text" class="text-input" v-model="remarks"
                                   :class="{ 'is-invalid': submitAttempted && !remarks.trim() }"
                                   placeholder="Why is this status being used?">
                            <small class="field-help">Required for {{ getStatusLabel(selectedStatus) }}.</small>
                        </template>
                        <template v-else-if="selectedStatus !== 'online'">
                            <label class="field-label" for="remarks-optional">Remarks <span style="color: var(--ink-faint); font-weight: 600;">(optional)</span></label>
                            <input id="remarks-optional" type="text" class="text-input" v-model="remarks"
                                   placeholder="Any notes...">
                        </template>
                    </div>
                </div>

                <div class="footer-hint" v-if="missingRequirement">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ missingRequirement }}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" @click="closeModal" :disabled="isSubmitting">Cancel</button>
                    <button type="button" class="btn btn-primary-solid"
                            :class="{ 'is-blocked': !!missingRequirement }"
                            @click="attemptSubmit" :disabled="isSubmitting">
                        <span v-if="isSubmitting" class="spinner-border spinner-border-sm"></span>
                        <i v-else class="bi bi-check-lg"></i>
                        {{ isSubmitting ? 'Saving…' : 'Save attendance' }}
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <!-- ==========================================================
         CAMERA
         ========================================================== -->
    <div v-if="showCameraCapture" class="camera-overlay">
        <div class="camera-overlay__stage">
            <video ref="cameraVideo" autoplay playsinline muted class="camera-overlay__video"></video>
            <canvas ref="cameraCanvas" class="visually-hidden-input"></canvas>
            <div class="camera-overlay__hint">Frame the class, then tap the shutter</div>
        </div>
        <div class="camera-overlay__controls">
            <button type="button" class="camera-overlay__btn--cancel" @click="closeFaceToFaceCamera">Cancel</button>
            <button type="button" class="camera-overlay__shutter" @click="captureFaceToFacePhoto" aria-label="Capture photo"></button>
            <span></span>
        </div>
    </div>

    <!-- ==========================================================
         CONFIRM DIALOG
         ========================================================== -->
    <transition name="fade">
        <div class="confirm-backdrop" v-if="confirmState.open" @click.self="resolveConfirm(false)">
            <div class="confirm-card" role="alertdialog" aria-modal="true">
                <h6>{{ confirmState.title }}</h6>
                <p>{{ confirmState.message }}</p>
                <div class="confirm-actions">
                    <button type="button" class="btn-cancel" @click="resolveConfirm(false)">Cancel</button>
                    <button type="button" class="btn-go" @click="resolveConfirm(true)">{{ confirmState.confirmLabel }}</button>
                </div>
            </div>
        </div>
    </transition>

    <!-- Toasts -->
    <div class="toast-container" aria-live="polite">
        <transition-group name="toast">
            <div v-for="toast in toasts" :key="toast.id" class="toast-custom" :class="toast.type">
                <i class="bi" :class="toastIcon(toast.type)"></i>
                <span>{{ toast.message }}</span>
            </div>
        </transition-group>
    </div>

</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
// Passed safely from PHP session
const VERIFIED_BY = <?php echo json_encode(isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Monitor'); ?>;

const { createApp, ref, reactive, computed, onMounted, onUnmounted, nextTick, watch } = Vue;

createApp({
    setup() {
        /* ---------------- state ---------------- */
        const schedules = ref([]);
        const summary = reactive({ total: 0, marked: 0, pending: 0 });
        const loading = ref(true);
        const isRefreshing = ref(false);
        const error = ref(null);
        const currentDateTime = ref('');
        const lastUpdated = ref('');
        const nowMinutes = ref(0);

        const activeFilter = ref('all');
        const searchTerm = ref('');

        const showModal = ref(false);
        const currentSchedule = ref(null);
        const selectedStatus = ref('present');
        const meetingLink = ref('');
        const remarks = ref('');
        const isSubmitting = ref(false);
        const submitAttempted = ref(false);

        const studentCount = ref(null);
        const studentCountTouched = ref(false);
        const isStudentCountValid = computed(() => {
            const v = studentCount.value;
            if (v === null || v === undefined || v === '') return false;
            const num = Number(v);
            return Number.isInteger(num) && num >= 0 && num <= 100;
        });

        const faceToFaceFile = ref(null);
        const faceToFacePreview = ref(null);
        const faceToFaceInput = ref(null);
        const faceToFaceCameraInput = ref(null);

        const showCameraCapture = ref(false);
        const cameraVideo = ref(null);
        const cameraCanvas = ref(null);
        let cameraStream = null;

        const uploadedFile = ref(null);
        const uploadedFilePreview = ref(null);
        const fileInput = ref(null);

        // Field wrappers, used to scroll the first blocking field into view
        const sheetBody = ref(null);
        const countField = ref(null);
        const photoField = ref(null);
        const onlineField = ref(null);
        const remarksField = ref(null);

        const toasts = ref([]);
        let toastSeq = 0;
        let autoRefreshTimer = null;
        let clockTimer = null;

        const specialStatuses = ['nt', 'eb', 'ed', 'ob', 'at'];

        const statusOptions = [
            { value: 'present', label: 'Present', icon: 'bi bi-check-circle' },
            { value: 'late', label: 'Late', icon: 'bi bi-clock-history' },
            { value: 'absent', label: 'Absent', icon: 'bi bi-x-circle' },
            { value: 'online', label: 'Online', icon: 'bi bi-camera-video' },
            { value: 'nt', label: 'NT', icon: 'bi bi-person-x' },
            { value: 'eb', label: 'EB', icon: 'bi bi-cup-hot' },
            { value: 'ed', label: 'ED', icon: 'bi bi-door-closed' },
            { value: 'ob', label: 'OB', icon: 'bi bi-briefcase' },
            { value: 'at', label: 'AT', icon: 'bi bi-backpack' }
        ];

        const statusLabels = {
            present: 'Present',
            late: 'Late',
            absent: 'Absent',
            online: 'Online class',
            nt: 'NT — No Teacher',
            eb: 'EB — Early Break',
            ed: 'ED — Early Dismissal',
            ob: 'OB — Official Business',
            at: 'AT — Academic Tour',
            pending: 'Pending',
            excused: 'Excused'
        };

        // Short forms keep the card badge on one line
        const shortStatusLabels = {
            present: 'Present', late: 'Late', absent: 'Absent', online: 'Online',
            nt: 'NT', eb: 'EB', ed: 'ED', ob: 'OB', at: 'AT',
            pending: 'Pending', excused: 'Excused'
        };

        /* ---------------- derived ---------------- */
        const counts = computed(() => {
            const total = schedules.value.length;
            const marked = schedules.value.filter(s => s.has_attendance).length;
            return { total, marked, pending: total - marked };
        });

        const progressPct = computed(() => {
            if (!counts.value.total) return 0;
            return Math.round((counts.value.marked / counts.value.total) * 100);
        });

        const filteredSchedules = computed(() => {
            const term = searchTerm.value.trim().toLowerCase();
            return schedules.value.filter(s => {
                if (activeFilter.value === 'pending' && s.has_attendance) return false;
                if (activeFilter.value === 'marked' && !s.has_attendance) return false;
                if (!term) return true;
                return [s.room, s.room_name, s.faculty_name, s.subject_code, s.subject_name, s.course_section, s.section_name]
                    .filter(Boolean)
                    .some(v => String(v).toLowerCase().includes(term));
            });
        });

        const missingRequirement = computed(() => {
            if (!isStudentCountValid.value) return 'Enter the number of students to continue';

            if (selectedStatus.value === 'online') {
                const link = meetingLink.value.trim();
                if (!link) return 'Add the meeting link';
                try { new URL(link); } catch (e) { return 'The meeting link is not a valid URL'; }
                if (!uploadedFile.value) return 'Upload a screenshot of the meeting';
            } else if (!faceToFaceFile.value) {
                return 'Take or upload a room photo';
            }

            if (specialStatuses.includes(selectedStatus.value) && !remarks.value.trim()) {
                return `Add remarks for ${statusLabels[selectedStatus.value] || selectedStatus.value}`;
            }
            return '';
        });

        /* ---------------- helpers ---------------- */
        function updateDateTime() {
            const now = new Date();
            currentDateTime.value = now.toLocaleDateString('en-US', {
                weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            nowMinutes.value = now.getHours() * 60 + now.getMinutes();
        }

        function stampUpdated() {
            lastUpdated.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }

        function toMinutes(time) {
            if (!time) return null;
            const parts = String(time).split(':');
            if (parts.length < 2) return null;
            const h = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            if (Number.isNaN(h) || Number.isNaN(m)) return null;
            return h * 60 + m;
        }

        function isHappeningNow(schedule) {
            const start = toMinutes(schedule.start_time);
            const end = toMinutes(schedule.end_time);
            if (start === null || end === null || end <= start) return false;
            return nowMinutes.value >= start && nowMinutes.value < end;
        }

        function getStatusBadge(status) {
            const map = {
                present: 'present', late: 'late', absent: 'absent',
                online: 'online', pending: 'pending', excused: 'excused',
                nt: 'nt', eb: 'eb', ed: 'ed', ob: 'ob', at: 'at'
            };
            return map[status] || 'pending';
        }

        function getStatusLabel(status) {
            return statusLabels[status] || status || 'Pending';
        }

        function getShortStatusLabel(status) {
            return shortStatusLabels[status] || status || 'Pending';
        }

        function formatTime(startTime, endTime) {
            if (!startTime) return 'N/A';
            try {
                const formatSingle = (time) => {
                    if (!time) return '';
                    const parts = time.split(':');
                    if (parts.length < 2) return time;
                    const h = parseInt(parts[0], 10);
                    const m = parts[1];
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    const h12 = h % 12 || 12;
                    return `${h12}:${m} ${ampm}`;
                };
                const start = formatSingle(startTime);
                const end = endTime ? formatSingle(endTime) : '';
                return end ? `${start} – ${end}` : start;
            } catch (e) {
                return startTime || 'N/A';
            }
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function toastIcon(type) {
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };
            return icons[type] || icons.info;
        }

        function showToast(message, type = 'info') {
            const id = ++toastSeq;
            toasts.value.push({ id, message, type });
            setTimeout(() => {
                toasts.value = toasts.value.filter(t => t.id !== id);
            }, 3200);
        }

        function resetFilters() {
            activeFilter.value = 'all';
            searchTerm.value = '';
        }

        function scrollToField(fieldRef) {
            nextTick(() => {
                const el = fieldRef && fieldRef.value;
                if (el && typeof el.scrollIntoView === 'function') {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        /* ---------------- confirm dialog ---------------- */
        const confirmState = reactive({
            open: false, title: '', message: '', confirmLabel: 'Confirm', resolve: null
        });

        function askConfirm(title, message, confirmLabel = 'Confirm') {
            confirmState.title = title;
            confirmState.message = message;
            confirmState.confirmLabel = confirmLabel;
            confirmState.open = true;
            return new Promise(resolve => { confirmState.resolve = resolve; });
        }

        function resolveConfirm(value) {
            confirmState.open = false;
            if (confirmState.resolve) confirmState.resolve(value);
            confirmState.resolve = null;
        }

        /* ---------------- files ---------------- */
        function adjustStudentCount(amount) {
            const base = (studentCount.value === null || studentCount.value === undefined || studentCount.value === '')
                ? 0
                : Number(studentCount.value);
            const newCount = Math.min(100, Math.max(0, base + amount));
            studentCount.value = newCount;
            studentCountTouched.value = true;
        }

        function validateImage(file) {
            if (!file.type.startsWith('image/')) {
                showToast('That file is not an image', 'warning');
                return false;
            }
            if (file.size > 5 * 1024 * 1024) {
                showToast('Image must be smaller than 5MB', 'warning');
                return false;
            }
            return true;
        }

        function handleFaceToFaceUpload(event) {
            const file = event.target.files[0];
            if (!file || !validateImage(file)) return;

            faceToFaceFile.value = file;
            const reader = new FileReader();
            reader.onload = (e) => { faceToFacePreview.value = e.target.result; };
            reader.readAsDataURL(file);
        }

        function removeFaceToFaceFile() {
            faceToFaceFile.value = null;
            faceToFacePreview.value = null;
            if (faceToFaceInput.value) faceToFaceInput.value.value = '';
            if (faceToFaceCameraInput.value) faceToFaceCameraInput.value.value = '';
        }

        async function openFaceToFaceCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (faceToFaceCameraInput.value) faceToFaceCameraInput.value.click();
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });
                cameraStream = stream;
                showCameraCapture.value = true;
                await nextTick();
                if (cameraVideo.value) {
                    cameraVideo.value.srcObject = stream;
                    await cameraVideo.value.play();
                }
            } catch (e) {
                console.error('Camera error:', e);
                showToast('Could not open the camera — pick a photo instead', 'warning');
                if (faceToFaceInput.value) faceToFaceInput.value.click();
            }
        }

        function stopFaceToFaceCameraStream() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            if (cameraVideo.value) cameraVideo.value.srcObject = null;
        }

        function closeFaceToFaceCamera() {
            stopFaceToFaceCameraStream();
            showCameraCapture.value = false;
        }

        function captureFaceToFacePhoto() {
            const video = cameraVideo.value;
            const canvas = cameraCanvas.value;
            if (!video || !canvas || !video.videoWidth) return;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                if (!blob) {
                    showToast('Could not capture the photo. Try again.', 'error');
                    return;
                }
                const fileName = `face-to-face-${Date.now()}.jpg`;
                faceToFaceFile.value = new File([blob], fileName, { type: 'image/jpeg' });
                faceToFacePreview.value = canvas.toDataURL('image/jpeg', 0.9);
                closeFaceToFaceCamera();
                showToast('Photo attached', 'success');
            }, 'image/jpeg', 0.9);
        }

        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file || !validateImage(file)) return;

            uploadedFile.value = file;
            const reader = new FileReader();
            reader.onload = (e) => { uploadedFilePreview.value = e.target.result; };
            reader.readAsDataURL(file);
        }

        function removeFile() {
            uploadedFile.value = null;
            uploadedFilePreview.value = null;
            if (fileInput.value) fileInput.value.value = '';
        }

        /* ---------------- data ---------------- */
         /* Updated API fetch methods inside Vue setup() in mobile-attendance.php */

async function loadMobileData(opts = {}) {
    const silent = opts.silent === true;
    if (!silent) loading.value = true;
    error.value = null;

    try {
        // Changed absolute URL to relative URL
        const response = await fetch('api/schedule/mobile-view');

        if (!response.ok) {
            if (response.status === 401 || response.status === 302) {
                window.location.href = 'login';
                return;
            }
            throw new Error('HTTP error: ' + response.status);
        }

        const data = await response.json();

        if (data.error) {
            error.value = data.error;
            schedules.value = [];
            return;
        }

        const normalizedSchedules = (data.schedules || []).map(s => ({
            ...s,
            room: s.room || s.room_name || 'N/A',
            course_section: s.course_section || s.section_name || 'N/A',
            subject_code: s.subject_code || s.subject_name || 'N/A',
            faculty_name: s.faculty_name || 'N/A',
            start_time: s.start_time || '00:00:00',
            end_time: s.end_time || '00:00:00',
            has_attendance: s.has_attendance || false,
            attendance_status: s.attendance_status || 'pending',
            is_past: s.is_past || false
        }));

        schedules.value = normalizedSchedules;
        summary.total = data.summary?.total || 0;
        summary.marked = data.summary?.marked || 0;
        summary.pending = data.summary?.pending || 0;
        stampUpdated();
    } catch (e) {
        console.error('Error loading mobile data:', e);
        error.value = 'network';
        schedules.value = [];
    } finally {
        loading.value = false;
    }
}

async function submitMobileAttendance() {
    if (isSubmitting.value || !currentSchedule.value) {
        if (!currentSchedule.value) showToast('No schedule selected', 'warning');
        return;
    }

    isSubmitting.value = true;
    try {
        const formData = new FormData();
        formData.append('schedule_id', currentSchedule.value.id);
        formData.append('status', selectedStatus.value);
        formData.append('remarks', remarks.value.trim());
        formData.append('meeting_link', meetingLink.value.trim());
        formData.append('verification_method', selectedStatus.value === 'online' ? 'online_monitoring' : 'physical_check');
        formData.append('verified_by', VERIFIED_BY);
        formData.append('attendance_date', new Date().toISOString().split('T')[0]);
        formData.append('student_count', studentCount.value);

        formData.append('course_section', currentSchedule.value.course_section);
        formData.append('subject_code', currentSchedule.value.subject_code);
        formData.append('room', currentSchedule.value.room);
        formData.append('faculty_name', currentSchedule.value.faculty_name);

        if (faceToFaceFile.value) formData.append('face_to_face_image', faceToFaceFile.value);
        if (uploadedFile.value) formData.append('meeting_screenshot', uploadedFile.value);

        // Changed absolute URL to relative URL
        const response = await fetch('api/attendance/mark', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            const statusLabel = statusLabels[selectedStatus.value] || selectedStatus.value;
            showToast(`Marked as ${statusLabel}`, 'success');
            showModal.value = false;
            currentSchedule.value = null;
            await loadMobileData({ silent: true });
        } else {
            const msg = result.errors ? result.errors.join(', ') : (result.error || 'Failed to mark attendance');
            showToast(msg, 'error');
        }
    } catch (e) {
        console.error('Error marking attendance:', e);
        showToast('Could not save attendance. Please try again.', 'error');
    } finally {
        isSubmitting.value = false;
    }
}

async function syncAll() {
    const pending = schedules.value.filter(s => !s.has_attendance);
    if (pending.length === 0) {
        showToast('Every room is already marked', 'success');
        return;
    }

    const syncable = pending.filter(s => Number.isInteger(s.student_count) && s.student_count > 0);
    const needsManualEntry = pending.length - syncable.length;

    if (syncable.length === 0) {
        showToast('Each room still needs a student count — tap Mark on the cards', 'warning');
        return;
    }

    const message = needsManualEntry > 0
        ? `${syncable.length} room(s) with a known student count will be marked Present. ${needsManualEntry} room(s) have no count on file and will be skipped.`
        : `All ${syncable.length} remaining room(s) will be marked Present.`;

    const ok = await askConfirm('Sync remaining rooms?', message, `Mark ${syncable.length} present`);
    if (!ok) return;

    let successCount = 0;
    for (const schedule of syncable) {
        try {
            // Changed absolute URL to relative URL
            const response = await fetch('api/attendance/mark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    schedule_id: schedule.id,
                    status: 'present',
                    verification_method: 'room_visit',
                    verified_by: VERIFIED_BY,
                    attendance_date: new Date().toISOString().split('T')[0],
                    student_count: schedule.student_count,

                    course_section: schedule.course_section,
                    subject_code: schedule.subject_code,
                    room: schedule.room,
                    faculty_name: schedule.faculty_name
                })
            });
            const result = await response.json();
            if (result.success) successCount++;
        } catch (e) {
            console.error('Error syncing schedule', schedule.id, e);
        }
    }

    let msg = `Synced ${successCount} of ${syncable.length} room(s)`;
    if (needsManualEntry > 0) msg += ` · ${needsManualEntry} still need a manual count`;
    showToast(msg, successCount === syncable.length && needsManualEntry === 0 ? 'success' : 'warning');
    await loadMobileData({ silent: true });
}

        /* ---------------- sheet ---------------- */
        function openMarkModal(schedule) {
            currentSchedule.value = schedule;
            selectedStatus.value = 'present';
            meetingLink.value = '';
            remarks.value = '';
            isSubmitting.value = false;
            submitAttempted.value = false;

            studentCount.value = null;
            studentCountTouched.value = false;

            uploadedFile.value = null;
            uploadedFilePreview.value = null;
            faceToFaceFile.value = null;
            faceToFacePreview.value = null;

            if (fileInput.value) fileInput.value.value = '';
            if (faceToFaceInput.value) faceToFaceInput.value.value = '';
            if (faceToFaceCameraInput.value) faceToFaceCameraInput.value.value = '';

            closeFaceToFaceCamera();
            showModal.value = true;
        }

        function closeModal() {
            if (isSubmitting.value) return;
            closeFaceToFaceCamera();
            showModal.value = false;
            currentSchedule.value = null;
        }

        function selectStatus(status) {
            selectedStatus.value = status;
        }

        async function submitMobileAttendance() {
            if (isSubmitting.value || !currentSchedule.value) {
                if (!currentSchedule.value) showToast('No schedule selected', 'warning');
                return;
            }

            isSubmitting.value = true;
            try {
                const formData = new FormData();
                formData.append('schedule_id', currentSchedule.value.id);
                formData.append('status', selectedStatus.value);
                formData.append('remarks', remarks.value.trim());
                formData.append('meeting_link', meetingLink.value.trim());
                formData.append('verification_method', selectedStatus.value === 'online' ? 'online_monitoring' : 'physical_check');
                formData.append('verified_by', VERIFIED_BY);
                formData.append('attendance_date', new Date().toISOString().split('T')[0]);
                formData.append('student_count', studentCount.value);

                formData.append('course_section', currentSchedule.value.course_section);
                formData.append('subject_code', currentSchedule.value.subject_code);
                formData.append('room', currentSchedule.value.room);
                formData.append('faculty_name', currentSchedule.value.faculty_name);

                if (faceToFaceFile.value) formData.append('face_to_face_image', faceToFaceFile.value);
                if (uploadedFile.value) formData.append('meeting_screenshot', uploadedFile.value);

                const response = await fetch('/api/attendance/mark', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const statusLabel = statusLabels[selectedStatus.value] || selectedStatus.value;
                    showToast(`Marked as ${statusLabel}`, 'success');
                    showModal.value = false;
                    currentSchedule.value = null;
                    await loadMobileData({ silent: true });
                } else {
                    const msg = result.errors ? result.errors.join(', ') : (result.error || 'Failed to mark attendance');
                    showToast(msg, 'error');
                }
            } catch (e) {
                console.error('Error marking attendance:', e);
                showToast('Could not save attendance. Please try again.', 'error');
            } finally {
                isSubmitting.value = false;
            }
        }

        // The Save button stays tappable: tapping it explains what is missing
        // and scrolls to the field, instead of sitting greyed out with no reason.
        function attemptSubmit() {
            submitAttempted.value = true;

            const blocker = missingRequirement.value;
            if (blocker) {
                studentCountTouched.value = true;
                showToast(blocker, 'warning');

                if (!isStudentCountValid.value) scrollToField(countField);
                else if (selectedStatus.value === 'online') scrollToField(onlineField);
                else if (!faceToFaceFile.value) scrollToField(photoField);
                else scrollToField(remarksField);
                return;
            }

            submitMobileAttendance();
        }

        async function refreshData() {
            isRefreshing.value = true;
            await loadMobileData({ silent: true });
            isRefreshing.value = false;
            if (!error.value) showToast('Up to date', 'success');
        }

        async function syncAll() {
            const pending = schedules.value.filter(s => !s.has_attendance);
            if (pending.length === 0) {
                showToast('Every room is already marked', 'success');
                return;
            }

            const syncable = pending.filter(s => Number.isInteger(s.student_count) && s.student_count > 0);
            const needsManualEntry = pending.length - syncable.length;

            if (syncable.length === 0) {
                showToast('Each room still needs a student count — tap Mark on the cards', 'warning');
                return;
            }

            const message = needsManualEntry > 0
                ? `${syncable.length} room(s) with a known student count will be marked Present. ${needsManualEntry} room(s) have no count on file and will be skipped.`
                : `All ${syncable.length} remaining room(s) will be marked Present.`;

            const ok = await askConfirm('Sync remaining rooms?', message, `Mark ${syncable.length} present`);
            if (!ok) return;

            let successCount = 0;
            for (const schedule of syncable) {
                try {
                    const response = await fetch('/api/attendance/mark', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            schedule_id: schedule.id,
                            status: 'present',
                            verification_method: 'room_visit',
                            verified_by: VERIFIED_BY,
                            attendance_date: new Date().toISOString().split('T')[0],
                            student_count: schedule.student_count,

                            course_section: schedule.course_section,
                            subject_code: schedule.subject_code,
                            room: schedule.room,
                            faculty_name: schedule.faculty_name
                        })
                    });
                    const result = await response.json();
                    if (result.success) successCount++;
                } catch (e) {
                    console.error('Error syncing schedule', schedule.id, e);
                }
            }

            let msg = `Synced ${successCount} of ${syncable.length} room(s)`;
            if (needsManualEntry > 0) msg += ` · ${needsManualEntry} still need a manual count`;
            showToast(msg, successCount === syncable.length && needsManualEntry === 0 ? 'success' : 'warning');
            await loadMobileData({ silent: true });
        }

        /* ---------------- lifecycle ---------------- */
        function onKeydown(e) {
            if (e.key !== 'Escape') return;
            if (showCameraCapture.value) { closeFaceToFaceCamera(); return; }
            if (confirmState.open) { resolveConfirm(false); return; }
            if (showModal.value) closeModal();
        }

        // Keep the page behind the sheet from scrolling
        watch([showModal, showCameraCapture, () => confirmState.open], ([a, b, c]) => {
            document.body.classList.toggle('is-locked', a || b || c);
        });

        onMounted(() => {
            updateDateTime();
            loadMobileData();

            clockTimer = setInterval(updateDateTime, 30000);
            autoRefreshTimer = setInterval(() => {
                if (!showModal.value && !showCameraCapture.value) loadMobileData({ silent: true });
            }, 30000);

            document.addEventListener('keydown', onKeydown);
        });

        onUnmounted(() => {
            if (clockTimer) clearInterval(clockTimer);
            if (autoRefreshTimer) clearInterval(autoRefreshTimer);
            stopFaceToFaceCameraStream();
            document.removeEventListener('keydown', onKeydown);
            document.body.classList.remove('is-locked');
        });

        return {
            schedules, summary, loading, isRefreshing, error, currentDateTime, lastUpdated,
            activeFilter, searchTerm, filteredSchedules, counts, progressPct, resetFilters,
            showModal, currentSchedule, selectedStatus, meetingLink, remarks, isSubmitting, submitAttempted,
            studentCount, studentCountTouched, isStudentCountValid, missingRequirement,
            faceToFaceFile, faceToFacePreview, faceToFaceInput, faceToFaceCameraInput,
            showCameraCapture, cameraVideo, cameraCanvas,
            openFaceToFaceCamera, closeFaceToFaceCamera, captureFaceToFacePhoto,
            toasts, statusOptions, specialStatuses,
            uploadedFile, uploadedFilePreview, fileInput,
            sheetBody, countField, photoField, onlineField, remarksField,
            confirmState, resolveConfirm,
            getStatusBadge, getStatusLabel, getShortStatusLabel, formatTime, formatFileSize, toastIcon,
            isHappeningNow,
            openMarkModal, closeModal, selectStatus, submitMobileAttendance, attemptSubmit,
            handleFileUpload, removeFile,
            handleFaceToFaceUpload, removeFaceToFaceFile,
            adjustStudentCount,
            refreshData, syncAll
        };
    }
}).mount('#app');
</script>

</body>
</html>