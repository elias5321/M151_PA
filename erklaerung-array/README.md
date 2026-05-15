# PHP Arrays – Erklärungsprojekt

Dieses Projekt erklärt die vier wichtigsten Array-Konzepte in PHP anhand
lebendiger Beispiele mit direkter PHP-Ausgabe.

## Themen

| # | Thema |
|---|-------|
| 01 | Indexiertes Array |
| 02 | Assoziatives Array |
| 03 | Zweidimensionales Array |
| 04 | foreach-Schleife (3 Varianten) |

## Projektstruktur

```
erklaerung-array/
├── index.php        ← Hauptdatei (PHP + HTML)
├── css/
│   └── style.css    ← Styling
└── README.md
```

## Starten

1. XAMPP / WAMP / Laragon starten
2. Projekt in den `htdocs`-Ordner legen (oder per Virtual Host)
3. Im Browser öffnen: `http://localhost/erklaerung-array/`

## Verwendete PHP-Konzepte

- `$array = [...]`            – Array erstellen
- `$array[0]`                 – Indexierter Zugriff
- `$array["key"]`             – Assoziativer Zugriff
- `$array[0]["key"]`          – 2D-Zugriff
- `foreach ($arr as $val)`    – Einfache Schleife
- `foreach ($arr as $k => $v)` – Schleife mit Key
- `htmlspecialchars()`        – Sicheres Ausgeben
- `match()`                   – PHP 8 Ausdruck
