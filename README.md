# 📄 Mandantenbrief Modul v2.0.2

**Professional Joomla 5+ Module** für die automatisierte Darstellung von Steuerinformationen von onlineinfodienst.de mit intelligenter Bild-Extraktion, Caching und **YOOtheme Pro Integration**.

## 🚀 **Key Features**

### ✅ **YOOtheme Pro Integration**
- **UIkit-kompatible CSS-Klassen** für nahtlose Integration
- **Responsive Grid-System**: `1@s 2@m 3@l` YOOtheme-Format
- **Card-Designs** mit Primary/Secondary/Hover-Styles
- **Automatische Theme-Vererbung** für Farben und Typografie

### ✅ **Intelligent Content Parsing**
- **Index-Seiten**: Automatische Extraktion von `moses_index_item` Artikeln
- **Tools-Seiten**: Spezielle Parser für `/tools.html` mit H3-Struktur  
- **H4-Teaser**: Intelligente Extraktion von Teaser-Texten
- **Featured Images**: Automatische Bild-Erkennung und Validation

### ✅ **Advanced Image Caching**
- **Lokale Speicherung** externer Bilder mit Hash-basierten Dateinamen
- **TTL-Cache** mit konfigurierbarer Laufzeit (1 Tag - 1 Monat)
- **Multiple Download-Methoden**: cURL + file_get_contents Fallback
- **Image Validation**: MIME-Type und Magic-Byte Prüfung
- **Fallback-System** mit SVG-Placeholder bei Fehlern

---

## 📦 **Installation**

### **Aus GitHub (Empfohlen)**
```
1. Joomla Admin → Erweiterungen → Installieren
2. "Von URL installieren" wählen
3. URL eingeben: https://github.com/pointischmidt/mod_mandantenbrief/archive/refs/heads/main.zip
4. "Überprüfen und installieren" klicken
```

### **Neue Features v2.0.2**
- **Vollständige XML-Feldsets** mit allen Konfigurationsoptionen
- **Stabile Module.php** mit allen benötigten Public-Methoden
- **YOOtheme-konforme Template-Struktur** 
- **GitHub-Integration** für automatische Updates
- **Enhanced Debugging** mit detaillierter Fehleranalyse
- **Namespace-Struktur** für moderne Joomla-Standards

---

## 🔧 **Kompatibilität**
- **Joomla 4.x**: Vollständig getestet und optimiert
- **Joomla 5.x**: API/Namespace-konform und ready
- **YOOtheme Pro**: Native Integration mit UIkit-Framework
- **PHP 8+**: Moderne PHP-Standards unterstützt

---

## ⚙️ **Konfiguration**

### **Grundeinstellungen**
- **Infodienst URL**: `https://onlineinfodienst.de/meine-steuer/`
- **Maximale Artikel**: 1-50 Artikel pro Seite
- **Modultitel**: Anpassbarer Titel mit Ein/Aus-Schalter

### **Anzeigeelemente**
- **Datum anzeigen**: Artikeldatum mit/ohne Icon
- **Kurzbeschreibung**: Automatisch generierte Excerpts
- **Bilder**: Intelligente Extraktion mit Fallback-System
- **Weiterlesen-Links**: Konfigurierbare Call-to-Actions

### **Layout & Responsive (7 Tabs verfügbar)**
- **Layout-Typ**: Grid, List, Masonry
- **Grid-Spalten**: Mobile (1-4), Tablet (1-6), Desktop (1-8)
- **Grid-Abstand**: Small, Medium, Large, No Gap
- **Karten-Design**: Default, Primary, Secondary, Muted, Hover

### **Performance & Cache**
- **Cache-TTL**: 1 Tag bis 1 Monat
- **Lazy Loading**: Für bessere Performance
- **Bild-Preloading**: Erste Bilder sofort laden

### **Erweiterte Optionen**
- **Debug-Modus**: Detaillierte Entwickler-Informationen
- **User-Agent**: Anpassbar für spezielle Anforderungen
- **Timeout**: Konfigurierbare Wartezeiten

---

## 🐛 **Debug & Entwicklung**

### **Debug-Modi**
- **Aktivierung**: Erweiterte Optionen → Debug-Modus → Ja
- **Ausgabe**: Timestamp, URL, Content-Length, Artikel-Count
- **Image-Debug**: Detaillierte Bild-Extraktion-Logs
- **Cache-Status**: Aktuelle Cache-Statistiken

### **Troubleshooting**
- **Internal Server Error**: Meist durch inkompatible Dateiversionen
- **Keine Artikel**: URL oder Parsing-Problem
- **Bilder fehlen**: Cache-Ordner Berechtigungen prüfen
- **Layout-Probleme**: YOOtheme-Integration in CSS

---

## 📁 **Datei-Struktur**

```
mod_mandantenbrief/
├── mod_mandantenbrief.php          # Entry Point (247 Zeichen)
├── mod_mandantenbrief.xml          # XML mit 7 Fieldsets (17KB)
├── src/Helper/
│   ├── Module.php                  # Haupt-Klasse (12KB)
│   ├── ParserHelper.php            # Content-Parsing
│   └── CacheHelper.php             # Image-Cache
├── tmpl/default.php                # YOOtheme-Template (8.4KB)
└── language/                       # DE/EN Sprachdateien
    ├── de-DE/de-DE.mod_mandantenbrief.ini
    └── en-GB/en-GB.mod_mandantenbrief.ini
```

---

## 🔄 **Updates**

**Automatische Updates** aus GitHub sind konfiguriert. Das Modul überprüft automatisch auf neue Versionen über den integrierten Update-Server.

**Aktuelle Version**: 2.0.2 (November 2025)

---

## 👨‍💻 **Author**

**Marcus Schmidt**  
📧 info@djumla.dev | 🌐 https://djumla.dev

---

**Made with ❤️ for the Joomla & YOOtheme community**