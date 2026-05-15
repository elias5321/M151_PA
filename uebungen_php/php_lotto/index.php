<?php
session_start();

// Guthaben initialisieren
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100.00;
}

$mode  = 'form';
$error = null;
$result = null;

// Reset Guthaben
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $_SESSION['balance'] = 10000000.00;
    header('Location: index.php');
    exit;
}

// Spielzug verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['spielen'])) {
    $einsatz          = floatval($_POST['einsatz'] ?? 0);
    $userZahlenRaw    = $_POST['zahlen'] ?? '';
    $userZahlen       = array_filter(array_map('intval', explode(',', $userZahlenRaw)));
    $userGlueckszahl  = intval($_POST['glueckszahl'] ?? 0);

    if ($einsatz <= 0) {
        $error = 'Einsatz muss grösser als 0 sein.';
    } elseif ($einsatz > $_SESSION['balance']) {
        $error = 'Nicht genug Guthaben.';
    } elseif (count($userZahlen) !== 6 || count(array_unique($userZahlen)) !== 6) {
        $error = 'Bitte genau 6 verschiedene Zahlen wählen.';
    } elseif ($userGlueckszahl < 1 || $userGlueckszahl > 6) {
        $error = 'Bitte eine Glückszahl (1–6) wählen.';
    } else {
        // Ziehung
        $zahlen      = range(1, 42);
        shuffle($zahlen);
        $lottoZahlen = array_slice($zahlen, 0, 6);
        sort($lottoZahlen);
        $zusatzzahl  = random_int(1, 6);

        // Treffer
        sort($userZahlen);
        $treffer      = array_values(array_intersect($userZahlen, $lottoZahlen));
        $trefferCount = count($treffer);
        $glueckMatch  = ($userGlueckszahl === $zusatzzahl);

        // Auszahlungstabelle (Multiplikator * Einsatz)
        $payouts = [
            '6_bonus' => 100000,
            '6'       => 10000,
            '5_bonus' => 1000,
            '5'       => 200,
            '4_bonus' => 50,
            '4'       => 20,
            '3_bonus' => 8,
            '3'       => 3,
            '2_bonus' => 1,
        ];
        $key        = $trefferCount . ($glueckMatch ? '_bonus' : '');
        $multiplier = $payouts[$key] ?? 0;
        $gewinn     = $einsatz * $multiplier;
        $netto      = $gewinn - $einsatz;

        $_SESSION['balance'] -= $einsatz;
        $_SESSION['balance'] += $gewinn;

        $mode   = 'result';
        $result = compact(
            'lottoZahlen','zusatzzahl','userZahlen','userGlueckszahl',
            'treffer','trefferCount','glueckMatch','einsatz',
            'multiplier','gewinn','netto'
        );
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lotto // Future Draw</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --cyan: #00f0ff;
            --magenta: #ff00d4;
            --yellow: #ffe600;
            --green: #00ff88;
            --red: #ff3a6c;
            --bg-1: #05060f;
            --bg-2: #0d0a24;
        }

        html, body { min-height: 100%; }

        body {
            font-family: 'Orbitron', sans-serif;
            background: radial-gradient(ellipse at center, var(--bg-2) 0%, var(--bg-1) 70%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 20px;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,240,255,0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.08) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: gridShift 12s linear infinite;
            mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
            pointer-events: none;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(to bottom, transparent 0px, transparent 3px, rgba(255,255,255,0.015) 3px, rgba(255,255,255,0.015) 4px);
            pointer-events: none;
            z-index: 2;
        }

        @keyframes gridShift {
            from { background-position: 0 0; }
            to { background-position: 40px 40px; }
        }

        .container {
            position: relative;
            z-index: 3;
            text-align: center;
            padding: 30px 40px;
            max-width: 800px;
            width: 100%;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 12px;
            color: var(--cyan);
            letter-spacing: 2px;
        }

        .balance {
            border: 1px solid rgba(0,240,255,0.4);
            padding: 8px 14px;
            background: rgba(0,240,255,0.05);
            box-shadow: 0 0 10px rgba(0,240,255,0.2);
        }

        .balance b {
            color: var(--green);
            text-shadow: 0 0 6px var(--green);
            margin-left: 6px;
        }

        .reset-btn {
            background: transparent;
            border: 1px solid rgba(255,58,108,0.5);
            color: var(--red);
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            letter-spacing: 2px;
            padding: 6px 12px;
            cursor: pointer;
        }
        .reset-btn:hover { background: rgba(255,58,108,0.15); }

        .header { margin-bottom: 30px; }

        .tag {
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            color: var(--cyan);
            letter-spacing: 4px;
            opacity: 0.7;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 6px;
            background: linear-gradient(90deg, var(--cyan), #fff, var(--magenta));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: hueShift 6s linear infinite;
            text-transform: uppercase;
        }

        h1::after {
            content: "";
            display: block;
            width: 80px;
            height: 2px;
            margin: 12px auto 0;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            animation: barPulse 2s ease-in-out infinite;
        }

        @keyframes hueShift { 0%,100%{filter:hue-rotate(0deg);} 50%{filter:hue-rotate(40deg);} }
        @keyframes barPulse { 0%,100%{opacity:.4;transform:scaleX(.7);} 50%{opacity:1;transform:scaleX(1.3);} }

        /* ----- FORM ----- */
        .panel {
            border: 1px solid rgba(0,240,255,0.25);
            background: rgba(10,10,30,0.55);
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 0 16px rgba(0,240,255,0.08), inset 0 0 16px rgba(0,240,255,0.05);
            backdrop-filter: blur(4px);
        }

        .panel-title {
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            letter-spacing: 3px;
            color: var(--cyan);
            text-align: left;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .counter {
            color: var(--magenta);
            text-shadow: 0 0 6px var(--magenta);
        }

        .num-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .num-cell {
            aspect-ratio: 1;
            border: 1px solid rgba(0,240,255,0.3);
            background: rgba(0,240,255,0.04);
            color: #ccefff;
            font-family: 'Orbitron', sans-serif;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .num-cell:hover {
            background: rgba(0,240,255,0.15);
            box-shadow: 0 0 10px rgba(0,240,255,0.4);
        }
        .num-cell.selected {
            background: rgba(0,240,255,0.25);
            color: #fff;
            border-color: var(--cyan);
            box-shadow: 0 0 14px var(--cyan), inset 0 0 10px rgba(0,240,255,0.4);
            text-shadow: 0 0 6px var(--cyan);
        }
        .num-cell.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .glueck-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            max-width: 380px;
            margin: 0 auto;
        }
        .glueck-grid .num-cell.selected {
            background: rgba(255,230,0,0.25);
            border-color: var(--yellow);
            box-shadow: 0 0 14px var(--yellow), inset 0 0 10px rgba(255,230,0,0.4);
            color: var(--yellow);
            text-shadow: 0 0 6px var(--yellow);
        }

        .bet-row {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .bet-input {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(0,240,255,0.4);
            color: var(--green);
            font-family: 'Orbitron', sans-serif;
            font-size: 22px;
            font-weight: 700;
            padding: 12px 18px;
            width: 160px;
            text-align: center;
            outline: none;
            text-shadow: 0 0 6px var(--green);
        }
        .bet-input:focus {
            box-shadow: 0 0 14px var(--cyan);
            border-color: var(--cyan);
        }
        .quick {
            display: flex;
            gap: 8px;
        }
        .quick button {
            font-family: 'Share Tech Mono', monospace;
            background: transparent;
            border: 1px solid rgba(0,240,255,0.4);
            color: var(--cyan);
            padding: 8px 12px;
            font-size: 12px;
            letter-spacing: 1px;
            cursor: pointer;
        }
        .quick button:hover { background: rgba(0,240,255,0.15); }

        .error {
            color: var(--red);
            border: 1px solid rgba(255,58,108,0.5);
            background: rgba(255,58,108,0.1);
            padding: 10px 14px;
            margin-bottom: 18px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .quick-pick {
            font-family: 'Share Tech Mono', monospace;
            background: transparent;
            border: 1px dashed rgba(255,0,212,0.5);
            color: var(--magenta);
            padding: 8px 14px;
            font-size: 11px;
            letter-spacing: 2px;
            cursor: pointer;
            text-transform: uppercase;
        }
        .quick-pick:hover {
            background: rgba(255,0,212,0.12);
            box-shadow: 0 0 10px var(--magenta);
        }

        /* ----- BIG BUTTON ----- */
        .play-btn, .again-btn {
            position: relative;
            font-family: 'Orbitron', sans-serif;
            background: transparent;
            color: var(--cyan);
            border: 1px solid var(--cyan);
            padding: 16px 44px;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 5px;
            cursor: pointer;
            text-transform: uppercase;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0 12px rgba(0,240,255,0.3), inset 0 0 12px rgba(0,240,255,0.1);
            margin-top: 8px;
        }
        .play-btn::before, .again-btn::before {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,240,255,0.4), transparent);
            transition: left 0.5s;
        }
        .play-btn:hover, .again-btn:hover {
            color: #fff;
            background: rgba(0,240,255,0.15);
            box-shadow: 0 0 24px var(--cyan), inset 0 0 24px rgba(0,240,255,0.3);
            text-shadow: 0 0 8px var(--cyan);
        }
        .play-btn:hover::before, .again-btn:hover::before { left: 100%; }

        /* ----- BALLS (RESULT) ----- */
        .zahlen {
            display: flex;
            gap: 36px;
            margin: 20px 0 30px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .gruppe { display: flex; flex-direction: column; align-items: center; }
        .label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            letter-spacing: 3px;
            color: var(--cyan);
            margin-bottom: 16px;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .gruppe.zusatz-gruppe .label { color: var(--yellow); }
        .reihe { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }

        .zahl {
            position: relative;
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 18px; font-weight: 900;
            color: #fff;
            background:
                radial-gradient(circle at 32% 28%, rgba(255,255,255,0.5), transparent 35%),
                radial-gradient(circle at center, #1a1a3a 0%, #0a0a1f 70%, #050510 100%);
            border: 1px solid rgba(0,240,255,0.6);
            box-shadow:
                0 0 0 1px rgba(0,240,255,0.2),
                0 0 18px rgba(0,240,255,0.55),
                inset 0 0 14px rgba(0,240,255,0.25),
                inset -4px -6px 10px rgba(0,0,0,0.6);
            text-shadow: 0 0 8px var(--cyan);
            opacity: 0;
            transform: translateY(-60px) scale(0.4);
            animation: dropIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards, floatBall 3s ease-in-out infinite;
        }
        .zahl:nth-child(1) { animation-delay: 0.1s, 0.8s; }
        .zahl:nth-child(2) { animation-delay: 0.25s, 0.95s; }
        .zahl:nth-child(3) { animation-delay: 0.4s, 1.1s; }
        .zahl:nth-child(4) { animation-delay: 0.55s, 1.25s; }
        .zahl:nth-child(5) { animation-delay: 0.7s, 1.4s; }
        .zahl:nth-child(6) { animation-delay: 0.85s, 1.55s; }

        .zahl::before {
            content: "";
            position: absolute;
            top: 7px; left: 11px;
            width: 14px; height: 8px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            filter: blur(2px);
            pointer-events: none;
        }
        .zahl::after {
            content: "";
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 1px dashed rgba(0,240,255,0.4);
            animation: rotate 8s linear infinite;
            pointer-events: none;
        }

        .zusatz {
            background:
                radial-gradient(circle at 32% 28%, rgba(255,255,255,0.6), transparent 35%),
                radial-gradient(circle at center, #3a2e00 0%, #1a1400 70%, #0a0800 100%);
            border-color: rgba(255,230,0,0.7);
            box-shadow:
                0 0 0 1px rgba(255,230,0,0.25),
                0 0 22px rgba(255,230,0,0.7),
                inset 0 0 16px rgba(255,230,0,0.35),
                inset -4px -6px 10px rgba(0,0,0,0.6);
            text-shadow: 0 0 8px var(--yellow);
            color: var(--yellow);
            animation: dropIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 1s forwards, pulseYellow 2s ease-in-out 1.7s infinite;
        }
        .zusatz::after { border-color: rgba(255,230,0,0.5); }

        /* User-Zahlen kleiner (Anzeige) */
        .user-row .zahl {
            width: 44px; height: 44px;
            font-size: 14px;
            opacity: 0.45;
            box-shadow: 0 0 0 1px rgba(0,240,255,0.15), inset 0 0 8px rgba(0,240,255,0.15);
            animation: fadeIn 0.5s 1.6s forwards;
        }
        .user-row .zahl::after { display: none; }
        .user-row .zahl::before { display: none; }
        .user-row .zahl.hit {
            opacity: 1;
            border-color: var(--green);
            box-shadow: 0 0 16px var(--green), inset 0 0 10px rgba(0,255,136,0.4);
            color: var(--green);
            text-shadow: 0 0 8px var(--green);
            animation: fadeIn 0.5s 1.6s forwards, hitPulse 1.2s 2.2s ease-in-out infinite;
        }
        .user-row .zahl.hit-bonus {
            opacity: 1;
            border-color: var(--yellow);
            box-shadow: 0 0 16px var(--yellow), inset 0 0 10px rgba(255,230,0,0.4);
            color: var(--yellow);
            text-shadow: 0 0 8px var(--yellow);
            animation: fadeIn 0.5s 1.8s forwards, hitPulse 1.2s 2.4s ease-in-out infinite;
        }

        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes hitPulse {
            0%,100% { transform: scale(1); }
            50%     { transform: scale(1.12); }
        }
        @keyframes dropIn {
            0%   { opacity: 0; transform: translateY(-80px) scale(0.3) rotate(-180deg); }
            60%  { opacity: 1; transform: translateY(8px) scale(1.1) rotate(10deg); }
            100% { opacity: 1; transform: translateY(0) scale(1) rotate(0deg); }
        }
        @keyframes floatBall {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-5px); }
        }
        @keyframes pulseYellow {
            0%,100% {
                box-shadow: 0 0 0 1px rgba(255,230,0,0.25), 0 0 22px rgba(255,230,0,0.7),
                            inset 0 0 16px rgba(255,230,0,0.35), inset -4px -6px 10px rgba(0,0,0,0.6);
                transform: translateY(0) scale(1);
            }
            50% {
                box-shadow: 0 0 0 2px rgba(255,230,0,0.4), 0 0 38px rgba(255,230,0,1),
                            inset 0 0 22px rgba(255,230,0,0.5), inset -4px -6px 10px rgba(0,0,0,0.6);
                transform: translateY(-3px) scale(1.06);
            }
        }
        @keyframes rotate { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }

        .divider {
            width: 1px; height: 80px;
            background: linear-gradient(to bottom, transparent, rgba(0,240,255,0.4), transparent);
        }

        /* ----- RESULT BOX ----- */
        .result-box {
            margin-top: 18px;
            padding: 28px 24px;
            border: 1px solid rgba(0,240,255,0.3);
            background: rgba(0,0,20,0.4);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s 2.6s forwards, floatBall 4s 3.4s ease-in-out infinite;
        }
        .result-box.win {
            border-color: var(--green);
            box-shadow: 0 0 30px rgba(0,255,136,0.4), inset 0 0 20px rgba(0,255,136,0.08);
        }
        .result-box.lose {
            border-color: var(--red);
            box-shadow: 0 0 20px rgba(255,58,108,0.3), inset 0 0 14px rgba(255,58,108,0.08);
        }
        .result-headline {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 4px;
            margin-bottom: 12px;
        }
        .result-headline.win { color: var(--green); text-shadow: 0 0 12px var(--green); }
        .result-headline.lose { color: var(--red); text-shadow: 0 0 12px var(--red); }

        .result-stats {
            display: flex;
            justify-content: center;
            gap: 28px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 13px;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.7);
            flex-wrap: wrap;
        }
        .result-stats b { color: var(--cyan); margin-left: 6px; }
        .result-stats .gain b { color: var(--green); text-shadow: 0 0 6px var(--green); }
        .result-stats .loss b { color: var(--red); text-shadow: 0 0 6px var(--red); }

        .footer {
            margin-top: 22px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: rgba(0,240,255,0.5);
            letter-spacing: 2px;
        }
        .blink { animation: blink 1s steps(2, start) infinite; }
        @keyframes blink { to { visibility: hidden; } }

        /* ----- PAYOUT TABLE ----- */
        details.payout {
            margin-top: 18px;
            border: 1px solid rgba(0,240,255,0.2);
            background: rgba(0,0,20,0.3);
            font-family: 'Share Tech Mono', monospace;
            text-align: left;
        }
        details.payout summary {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 11px;
            letter-spacing: 2px;
            color: var(--cyan);
            text-transform: uppercase;
            list-style: none;
        }
        details.payout summary::after { content: " ▾"; }
        details.payout[open] summary::after { content: " ▴"; }
        .payout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
            padding: 10px 18px 16px;
            font-size: 12px;
            color: rgba(255,255,255,0.75);
        }
        .payout-grid b { color: var(--green); }
    </style>
</head>
<body>
    <div class="container">

        <div class="topbar">
            <div class="balance">CREDITS: <b><?= number_format($_SESSION['balance'], 2) ?></b></div>
            <form method="post" style="margin:0;">
                <button type="submit" name="reset" value="1" class="reset-btn">Reset</button>
            </form>
        </div>

        <div class="header">
            <div class="tag">// SYSTEM ONLINE</div>
            <h1>Lotto Draw</h1>
        </div>

        <?php if ($error): ?>
            <div class="error">! ERROR: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($mode === 'form'): ?>
            <!-- ============ FORM ============ -->
            <form method="post" id="lottoForm">
                <input type="hidden" name="zahlen" id="zahlenInput" value="">
                <input type="hidden" name="glueckszahl" id="glueckInput" value="">
                <input type="hidden" name="spielen" value="1">

                <div class="panel">
                    <div class="panel-title">
                        <span>// 1. WÄHLE 6 ZAHLEN [1–42]</span>
                        <span>
                            <button type="button" class="quick-pick" id="quickPick">⚡ Zufalls-Tipp</button>
                            <span class="counter" style="margin-left:10px;"><span id="count">0</span>/6</span>
                        </span>
                    </div>
                    <div class="num-grid" id="numGrid">
                        <?php for ($i = 1; $i <= 42; $i++): ?>
                            <div class="num-cell" data-num="<?= $i ?>"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">
                        <span>// 2. GLÜCKSZAHL [1–6]</span>
                        <span class="counter"><span id="gCount">0</span>/1</span>
                    </div>
                    <div class="num-grid glueck-grid" id="glueckGrid">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div class="num-cell" data-num="<?= $i ?>"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">
                        <span>// 3. EINSATZ</span>
                        <span class="counter">CREDITS</span>
                    </div>
                    <div class="bet-row">
                        <input type="number" step="0.5" min="0.5" max="<?= $_SESSION['balance'] ?>" name="einsatz" id="einsatz" class="bet-input" placeholder="0.00" value="1">
                        <div class="quick">
                            <button type="button" data-bet="1">1</button>
                            <button type="button" data-bet="5">5</button>
                            <button type="button" data-bet="10">10</button>
                            <button type="button" data-bet="50">50</button>
                            <button type="button" data-bet="max">MAX</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="play-btn">&gt;&gt; ZIEHUNG STARTEN</button>
            </form>

            <details class="payout">
                <summary>// AUSZAHLUNGSTABELLE</summary>
                <div class="payout-grid">
                    <span>6 Treffer + Glückszahl</span><b>×100'000</b>
                    <span>6 Treffer</span><b>×10'000</b>
                    <span>5 Treffer + Glückszahl</span><b>×1'000</b>
                    <span>5 Treffer</span><b>×200</b>
                    <span>4 Treffer + Glückszahl</span><b>×50</b>
                    <span>4 Treffer</span><b>×20</b>
                    <span>3 Treffer + Glückszahl</span><b>×8</b>
                    <span>3 Treffer</span><b>×3</b>
                    <span>2 Treffer + Glückszahl</span><b>×1</b>
                </div>
            </details>

        <?php else: ?>
            <!-- ============ RESULT ============ -->
            <?php
                $isWin = $result['gewinn'] > 0;
                $isJackpot = ($result['trefferCount'] === 6 && $result['glueckMatch']);
            ?>

            <div class="zahlen">
                <div class="gruppe">
                    <div class="label">Ziehung</div>
                    <div class="reihe">
                        <?php foreach ($result['lottoZahlen'] as $z): ?>
                            <div class="zahl"><?= $z ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="gruppe zusatz-gruppe">
                    <div class="label">Bonus</div>
                    <div class="reihe">
                        <div class="zahl zusatz"><?= $result['zusatzzahl'] ?></div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-title">
                    <span>// DEINE ZAHLEN</span>
                    <span class="counter"><?= $result['trefferCount'] ?> + <?= $result['glueckMatch'] ? '1' : '0' ?></span>
                </div>
                <div class="zahlen user-row" style="margin: 10px 0 0;">
                    <div class="gruppe">
                        <div class="reihe">
                            <?php foreach ($result['userZahlen'] as $z): ?>
                                <?php $hit = in_array($z, $result['lottoZahlen']); ?>
                                <div class="zahl <?= $hit ? 'hit' : '' ?>"><?= $z ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="gruppe">
                        <div class="reihe">
                            <div class="zahl <?= $result['glueckMatch'] ? 'hit-bonus' : '' ?>"><?= $result['userGlueckszahl'] ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="result-box <?= $isWin ? 'win' : 'lose' ?>">
                <div class="result-headline <?= $isWin ? 'win' : 'lose' ?>">
                    <?php if ($isJackpot): ?>
                        ★ JACKPOT! ★
                    <?php elseif ($isWin): ?>
                        ✦ GEWONNEN ✦
                    <?php else: ?>
                        × LEIDER VERLOREN ×
                    <?php endif; ?>
                </div>
                <div class="result-stats">
                    <span>EINSATZ: <b><?= number_format($result['einsatz'], 2) ?></b></span>
                    <span>MULTIPLIKATOR: <b>×<?= number_format($result['multiplier']) ?></b></span>
                    <span class="<?= $result['netto'] >= 0 ? 'gain' : 'loss' ?>">
                        <?= $result['netto'] >= 0 ? 'GEWINN:' : 'VERLUST:' ?>
                        <b><?= ($result['netto'] >= 0 ? '+' : '') . number_format($result['netto'], 2) ?></b>
                    </span>
                </div>
            </div>

            <form method="get" style="margin-top: 20px;">
                <button type="submit" class="again-btn">&gt;&gt; Erneut spielen</button>
            </form>

        <?php endif; ?>

        <div class="footer">DRAW_ID: <?= strtoupper(bin2hex(random_bytes(4))) ?> // <?= date('Y-m-d H:i:s') ?> <span class="blink">_</span></div>
    </div>

    <script>
        // Number-Picker Logik
        (function () {
            const grid       = document.getElementById('numGrid');
            const glueckGrid = document.getElementById('glueckGrid');
            if (!grid) return; // Result-View

            const zahlenInput = document.getElementById('zahlenInput');
            const glueckInput = document.getElementById('glueckInput');
            const countEl     = document.getElementById('count');
            const gCountEl    = document.getElementById('gCount');
            const MAX = 6;
            const selected = new Set();
            let glueck = null;

            function updateMain() {
                grid.querySelectorAll('.num-cell').forEach(cell => {
                    const n = parseInt(cell.dataset.num);
                    cell.classList.toggle('selected', selected.has(n));
                    cell.classList.toggle('disabled', !selected.has(n) && selected.size >= MAX);
                });
                const arr = Array.from(selected).sort((a,b)=>a-b);
                zahlenInput.value = arr.join(',');
                countEl.textContent = selected.size;
            }

            function updateGlueck() {
                glueckGrid.querySelectorAll('.num-cell').forEach(cell => {
                    const n = parseInt(cell.dataset.num);
                    cell.classList.toggle('selected', n === glueck);
                });
                glueckInput.value = glueck ?? '';
                gCountEl.textContent = glueck ? 1 : 0;
            }

            grid.addEventListener('click', (e) => {
                const cell = e.target.closest('.num-cell');
                if (!cell) return;
                const n = parseInt(cell.dataset.num);
                if (selected.has(n)) selected.delete(n);
                else if (selected.size < MAX) selected.add(n);
                updateMain();
            });

            glueckGrid.addEventListener('click', (e) => {
                const cell = e.target.closest('.num-cell');
                if (!cell) return;
                glueck = parseInt(cell.dataset.num);
                updateGlueck();
            });

            // Quick-Pick: 6 zufällige + Glückszahl
            document.getElementById('quickPick').addEventListener('click', () => {
                selected.clear();
                while (selected.size < MAX) {
                    selected.add(Math.floor(Math.random() * 42) + 1);
                }
                glueck = Math.floor(Math.random() * 6) + 1;
                updateMain();
                updateGlueck();
            });

            // Quick-Bet
            document.querySelectorAll('.quick button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const v = btn.dataset.bet;
                    const inp = document.getElementById('einsatz');
                    inp.value = (v === 'max') ? <?= $_SESSION['balance'] ?> : v;
                });
            });

            // Validation vor Submit
            document.getElementById('lottoForm').addEventListener('submit', (e) => {
                if (selected.size !== MAX) {
                    e.preventDefault();
                    alert('Bitte 6 Zahlen wählen.');
                } else if (!glueck) {
                    e.preventDefault();
                    alert('Bitte eine Glückszahl wählen.');
                }
            });
        })();
    </script>
</body>
</html>
