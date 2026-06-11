# IT Ausleihesystem – Installationsanleitung

Dieses Dokument beschreibt die vollständige Inbetriebnahme des IT Ausleihesystems als lokale Entwicklungsumgebung mit XAMPP.

---

## Voraussetzungen

| Software | Version | Download |
|----------|---------|---------|
| XAMPP | 8.2+ | https://www.apachefriends.org |
| PHP | 8.2+ | (in XAMPP enthalten) |
| MariaDB | 10.4+ | (in XAMPP enthalten) |
| Browser | aktuell | Chrome / Firefox / Edge |

---

## 1. XAMPP installieren und starten

1. XAMPP herunterladen und installieren (Standardpfad: `C:\xampp`)
2. XAMPP Control Panel öffnen
3. **Apache** und **MySQL** starten (beide Status müssen grün sein)

---

## 2. Projektdateien platzieren

Die Projektdateien müssen im XAMPP-Webverzeichnis abgelegt werden:

```
C:\xampp\htdocs\projektarbeit\
```

**Vorgehen:**

Den gesamten Inhalt des Ordners `projektarbeit/` aus diesem Repository nach `C:\xampp\htdocs\projektarbeit\` kopieren.

Danach sollte folgende Struktur vorhanden sein:

```
C:\xampp\htdocs\projektarbeit\
├── index.php
├── login.php
├── register.php
├── logout.php
├── borrow.php
├── my_loans.php
├── devices.php
├── change_password.php
├── auth.php
├── db.php
├── style.css
├── geraete_vorlage.csv
└── ausleihesystem.sql
```

---

## 3. Datenbank einrichten

### 3.1 phpMyAdmin öffnen

Im Browser aufrufen: `http://localhost/phpmyadmin`

### 3.2 SQL-Datei importieren

1. In phpMyAdmin links auf **„Neu"** klicken (neue Datenbank erstellen wird **nicht** benötigt)
2. Oben links auf **„Startseite"** (das phpMyAdmin-Logo) klicken
3. Oben auf den Reiter **„Importieren"** klicken
4. **„Datei auswählen"** → die Datei `ausleihesystem.sql` aus dem Projektordner auswählen
5. Auf **„OK"** / **„Importieren"** klicken

Der Import erstellt automatisch:
- Die Datenbank `ausleihesystem`
- Die Tabellen `users`, `devices` und `loans`
- Den Datenbankbenutzer `m151_user` mit eingeschränkten Rechten (nur `SELECT`, `INSERT`, `UPDATE`, `DELETE` auf `ausleihesystem`)
- Zwei Testkonten (user und admin)

---

## 4. Anwendung aufrufen

Im Browser aufrufen:

```
http://localhost/projektarbeit/login.php
```

---

## 5. Testkonten

Folgende Konten sind nach dem Import verfügbar:

| Rolle | Benutzername | Passwort |
|-------|-------------|---------|
| Benutzer | `user` | `user123` |
| Administrator | `admin` | `admin123` |

> **Hinweis:** Im produktiven Betrieb müssen diese Passwörter sofort geändert werden.

---

## 6. Funktionsübersicht

| Seite | Beschreibung | Zugang |
|-------|-------------|--------|
| `login.php` | Anmeldung (Username oder E-Mail) | öffentlich |
| `register.php` | Neues Konto erstellen | öffentlich |
| `index.php` | Dashboard mit Menü | angemeldet |
| `borrow.php` | Verfügbare Geräte ausleihen | angemeldet |
| `my_loans.php` | Eigene Ausleihen anzeigen & zurückgeben | angemeldet |
| `devices.php` | Geräte verwalten (hinzufügen/löschen) | nur Admin |
| `change_password.php` | Passwort ändern | angemeldet |
| `logout.php` | Abmelden | angemeldet |

---

## 7. Häufige Probleme

**Fehler: „Datenbankverbindung fehlgeschlagen"**
- MySQL in XAMPP läuft nicht → XAMPP Control Panel öffnen, MySQL starten
- SQL-Datei wurde nicht vollständig importiert → Import in phpMyAdmin wiederholen

**Seite zeigt PHP-Code statt HTML**
- Apache läuft nicht → XAMPP Control Panel öffnen, Apache starten
- Dateien liegen nicht unter `C:\xampp\htdocs\projektarbeit\`

**phpMyAdmin nicht erreichbar**
- Apache muss laufen
- Adresse prüfen: `http://localhost/phpmyadmin` (kein HTTPS)

**Session-Fehler / automatisch abgemeldet**
- Sessions laufen nach 30 Minuten Inaktivität ab – das ist beabsichtigt

---

## 8. Deinstallation

1. Den Ordner `C:\xampp\htdocs\projektarbeit\` löschen
2. In phpMyAdmin die Datenbank `ausleihesystem` löschen
3. Den Datenbankbenutzer `m151_user` löschen
