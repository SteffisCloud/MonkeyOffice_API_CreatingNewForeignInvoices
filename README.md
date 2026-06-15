# API MonkeyOffice

## Voraussetzungen
Windows 11

Bitte herunterladen und in dem gleichem Verzeichnis entpacken, als Zusatzverzeichnis zum API-Ordner

SDK CubeSQL: https://github.com/andreaspfeil/CubeSQL.Python3

## Aufruf der API
http://127.0.0.1:8088/AAA_APIS/001_Verkauf/VK_Beleg_FW.php?kunde_id=480C644B85E2297DB23598B64C1B&datum=2026-06-04&waehrung=USD&betreff=Your%20new%20VPN&anrede=Dear%20Michel

Diese Variablen z.b. kunde_id können im Script über PHP übergeben und ausgebaut werden (s. Datum und Währung im Skript)

## Umgebung
Windows11 
mit "neue Aufgabe beim Start" - Starte PHP-Server lokal 

PHP herunterladen und eine neue Aufgabe (taskschd.msc) hinzufügen:

Programm starten D:\php-8.2.17/php.exe

## Funktionen
Das API-Skript prüft die Datenbank "Waehrung", um den Kurs für das Datum zu übernehmen und dann einen neuen VK-Belege mit dem korrekten Währungskurs zu übernehmen. 
