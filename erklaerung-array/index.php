<?php
// ============================================================
// ARRAYS IN PHP – Erklärungsprojekt
// ============================================================

// ── 1. INDEXIERTES ARRAY ────────────────────────────────────
$früchte = ["Apfel", "Banane", "Kirsche", "Mango"];

// ── 2. ASSOZIATIVES ARRAY ───────────────────────────────────
$person = [
    "name"  => "Maria Muster",
    "alter" => 25,
    "stadt" => "Basel",
    "beruf" => "Entwicklerin"
];

// ── 3. ZWEIDIMENSIONALES ARRAY ──────────────────────────────
$schüler = [
    ["name" => "Anna",  "fach" => "Mathe",       "note" => 1],
    ["name" => "Ben",   "fach" => "Informatik",  "note" => 2],
    ["name" => "Clara", "fach" => "Deutsch",     "note" => 3],
    ["name" => "David", "fach" => "Englisch",    "note" => 2],
    ["name" => "Eva",   "fach" => "Geschichte",  "note" => 1],
];

// Notenfarben-Funktion
function notefarbe($note) {
    return match((int)$note) {
        1 => "#22c55e",
        2 => "#84cc16",
        3 => "#eab308",
        4 => "#f97316",
        default => "#ef4444"
    };
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Arrays erklärt</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <div class="header-inner">
        <div class="badge">PHP</div>
        <h1>Arrays in PHP</h1>
        <p class="subtitle">Indexiert · Assoziativ · Zweidimensional · foreach</p>
    </div>
</header>

<main>

    <!-- ══════════════════════════════════════════════════════
         ABSCHNITT 1 – INDEXIERTES ARRAY
    ═══════════════════════════════════════════════════════ -->
    <section class="card" id="indexiert">
        <div class="card-label">01</div>
        <h2>Indexiertes Array</h2>
        <p class="beschreibung">
            Eine einfache Liste. Jeder Wert bekommt automatisch eine Nummer (Index), 
            beginnend bei <code>0</code>.
        </p>

        <div class="code-block">
            <div class="code-title">📝 Code</div>
<pre><span class="var">$früchte</span> = [<span class="str">"Apfel"</span>, <span class="str">"Banane"</span>, <span class="str">"Kirsche"</span>, <span class="str">"Mango"</span>];
<span class="comment">//          Index 0    Index 1    Index 2    Index 3</span>

<span class="comment">// Einzelner Zugriff:</span>
<span class="kw">echo</span> <span class="var">$früchte</span>[<span class="num">0</span>]; <span class="comment">// Apfel</span>
<span class="kw">echo</span> <span class="var">$früchte</span>[<span class="num">2</span>]; <span class="comment">// Kirsche</span></pre>
        </div>

        <div class="result-block">
            <div class="result-title">▶ Ausgabe – Direkter Zugriff</div>
            <div class="index-grid">
                <?php foreach ($früchte as $i => $frucht): ?>
                <div class="index-item">
                    <span class="index-badge">[<?= $i ?>]</span>
                    <span class="index-wert"><?= htmlspecialchars($frucht) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="zugriff-demo">
                <span>$früchte[<strong>0</strong>] → <em><?= $früchte[0] ?></em></span>
                <span>$früchte[<strong>2</strong>] → <em><?= $früchte[2] ?></em></span>
                <span>$früchte[<strong>3</strong>] → <em><?= $früchte[3] ?></em></span>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════════════════
         ABSCHNITT 2 – ASSOZIATIVES ARRAY
    ═══════════════════════════════════════════════════════ -->
    <section class="card" id="assoziativ">
        <div class="card-label">02</div>
        <h2>Assoziatives Array</h2>
        <p class="beschreibung">
            Wie ein Wörterbuch: Du gibst jedem Wert einen eigenen Namen (Key) statt einer Nummer.
            Syntax: <code>"key" =&gt; "wert"</code>
        </p>

        <div class="code-block">
            <div class="code-title">📝 Code</div>
<pre><span class="var">$person</span> = [
    <span class="str">"name"</span>  <span class="op">=></span> <span class="str">"Maria Muster"</span>,
    <span class="str">"alter"</span> <span class="op">=></span> <span class="num">25</span>,
    <span class="str">"stadt"</span> <span class="op">=></span> <span class="str">"Basel"</span>,
    <span class="str">"beruf"</span> <span class="op">=></span> <span class="str">"Entwicklerin"</span>
];

<span class="comment">// Zugriff über den Key:</span>
<span class="kw">echo</span> <span class="var">$person</span>[<span class="str">"name"</span>];  <span class="comment">// Maria Muster</span>
<span class="kw">echo</span> <span class="var">$person</span>[<span class="str">"stadt"</span>]; <span class="comment">// Basel</span></pre>
        </div>

        <div class="result-block">
            <div class="result-title">▶ Ausgabe – Direkter Zugriff</div>
            <div class="assoc-grid">
                <?php foreach ($person as $key => $wert): ?>
                <div class="assoc-row">
                    <span class="assoc-key">$person["<?= $key ?>"]</span>
                    <span class="arrow">→</span>
                    <span class="assoc-val"><?= htmlspecialchars((string)$wert) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════════════════
         ABSCHNITT 3 – ZWEIDIMENSIONALES ARRAY
    ═══════════════════════════════════════════════════════ -->
    <section class="card" id="zweidimensional">
        <div class="card-label">03</div>
        <h2>Zweidimensionales Array</h2>
        <p class="beschreibung">
            Ein Array das selbst wieder Arrays enthält – wie eine Tabelle mit Zeilen und Spalten.
            Zugriff: <code>$array[zeile][key]</code>
        </p>

        <div class="code-block">
            <div class="code-title">📝 Code</div>
<pre><span class="var">$schüler</span> = [
    [<span class="str">"name"</span> <span class="op">=></span> <span class="str">"Anna"</span>,  <span class="str">"fach"</span> <span class="op">=></span> <span class="str">"Mathe"</span>,      <span class="str">"note"</span> <span class="op">=></span> <span class="num">1</span>],
    [<span class="str">"name"</span> <span class="op">=></span> <span class="str">"Ben"</span>,   <span class="str">"fach"</span> <span class="op">=></span> <span class="str">"Informatik"</span>, <span class="str">"note"</span> <span class="op">=></span> <span class="num">2</span>],
    [<span class="str">"name"</span> <span class="op">=></span> <span class="str">"Clara"</span>, <span class="str">"fach"</span> <span class="op">=></span> <span class="str">"Deutsch"</span>,    <span class="str">"note"</span> <span class="op">=></span> <span class="num">3</span>],
];

<span class="comment">// Zugriff: erst äussere Zeile, dann innerer Key</span>
<span class="kw">echo</span> <span class="var">$schüler</span>[<span class="num">0</span>][<span class="str">"name"</span>]; <span class="comment">// Anna</span>
<span class="kw">echo</span> <span class="var">$schüler</span>[<span class="num">1</span>][<span class="str">"note"</span>]; <span class="comment">// 2</span></pre>
        </div>

        <div class="result-block">
            <div class="result-title">▶ Ausgabe – als Tabelle</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Index</th>
                        <th>["name"]</th>
                        <th>["fach"]</th>
                        <th>["note"]</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schüler as $i => $s): ?>
                    <tr>
                        <td><code>[<?= $i ?>]</code></td>
                        <td><?= htmlspecialchars($s["name"]) ?></td>
                        <td><?= htmlspecialchars($s["fach"]) ?></td>
                        <td>
                            <span class="note-badge" style="background:<?= notefarbe($s["note"]) ?>">
                                <?= $s["note"] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="zugriff-demo">
                <span>$schüler[<strong>0</strong>]["name"] → <em><?= $schüler[0]["name"] ?></em></span>
                <span>$schüler[<strong>1</strong>]["note"] → <em><?= $schüler[1]["note"] ?></em></span>
                <span>$schüler[<strong>4</strong>]["fach"] → <em><?= $schüler[4]["fach"] ?></em></span>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════════════════
         ABSCHNITT 4 – FOREACH
    ═══════════════════════════════════════════════════════ -->
    <section class="card" id="foreach">
        <div class="card-label">04</div>
        <h2>foreach-Schleife</h2>
        <p class="beschreibung">
            Mit <code>foreach</code> gehst du ein Array automatisch Schritt für Schritt durch –
            ohne Zähler, ohne Index verwalten.
        </p>

        <!-- 4a: Indexiertes -->
        <div class="sub-section">
            <h3>4a – Über indexiertes Array</h3>
            <div class="code-block">
                <div class="code-title">📝 Code</div>
<pre><span class="kw">foreach</span> (<span class="var">$früchte</span> <span class="kw">as</span> <span class="var">$frucht</span>) {
    <span class="kw">echo</span> <span class="var">$frucht</span> . <span class="str">"&lt;br&gt;"</span>;
}</pre>
            </div>
            <div class="result-block">
                <div class="result-title">▶ Ausgabe</div>
                <div class="foreach-list">
                    <?php foreach ($früchte as $frucht): ?>
                        <div class="foreach-item">🍎 <?= htmlspecialchars($frucht) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 4b: Assoziativ mit Key -->
        <div class="sub-section">
            <h3>4b – Assoziativ mit Key &amp; Wert</h3>
            <div class="code-block">
                <div class="code-title">📝 Code</div>
<pre><span class="kw">foreach</span> (<span class="var">$person</span> <span class="kw">as</span> <span class="var">$key</span> <span class="op">=></span> <span class="var">$wert</span>) {
    <span class="kw">echo</span> <span class="var">$key</span> . <span class="str">": "</span> . <span class="var">$wert</span> . <span class="str">"&lt;br&gt;"</span>;
}</pre>
            </div>
            <div class="result-block">
                <div class="result-title">▶ Ausgabe</div>
                <div class="assoc-grid">
                    <?php foreach ($person as $key => $wert): ?>
                    <div class="assoc-row">
                        <span class="assoc-key"><?= $key ?></span>
                        <span class="arrow">→</span>
                        <span class="assoc-val"><?= htmlspecialchars((string)$wert) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 4c: 2D-Array -->
        <div class="sub-section">
            <h3>4c – Zweidimensionales Array</h3>
            <div class="code-block">
                <div class="code-title">📝 Code</div>
<pre><span class="kw">foreach</span> (<span class="var">$schüler</span> <span class="kw">as</span> <span class="var">$s</span>) {
    <span class="kw">echo</span> <span class="var">$s</span>[<span class="str">"name"</span>] . <span class="str">" hat Note "</span> . <span class="var">$s</span>[<span class="str">"note"</span>] . <span class="str">"&lt;br&gt;"</span>;
}</pre>
            </div>
            <div class="result-block">
                <div class="result-title">▶ Ausgabe</div>
                <div class="foreach-list">
                    <?php foreach ($schüler as $s): ?>
                        <div class="foreach-item">
                            <span><?= htmlspecialchars($s["name"]) ?> – <?= htmlspecialchars($s["fach"]) ?></span>
                            <span class="note-badge" style="background:<?= notefarbe($s["note"]) ?>">
                                Note <?= $s["note"] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

</main>

<footer>
    <p>PHP Array Erklärungsprojekt &nbsp;·&nbsp; m151_pa</p>
</footer>

</body>
</html>
