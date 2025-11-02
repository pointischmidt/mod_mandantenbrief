# 📄 Mandantenbrief Modul v2.0

**Professional Joomla 4.5 Module** für die automatisierte Darstellung von Steuerinformationen von onlineinfodienst.de mit intelligenter Bild-Extraktion, Caching und **YOOtheme Pro Integration**.

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
3. URL eingeben: https://github.com/pointischmidt/mod_mandantenbrief/archive/main.zip
4. "Überprüfen und installieren" klicken
```

### **Neue Features v2.0**
- **82 Parameter → 20 Parameter** reduziert für bessere UX
- **YOOtheme-konforme Struktur** mit Grid-Format `1@s 2@m 3@l`
- **GitHub-Integration** für automatische Updates
- **Tools-Seiten-Support** für `/tools.html`
- **Enhanced Image-Caching** mit Error-Handling
- **Namespace-Struktur** für Joomla 4.5+

---

## ⚙️ **Konfiguration**

### **Inhalte & Quelle**
- **Infodienst URL**: `https://onlineinfodienst.de/meine-steuer/index/`
- **Maximale Artikel**: 1-50 Artikel pro Seite
- **Angezeigte Elemente**: Titel, Teaser, Datum, Bild, Weiterlesen
- **Cache-Dauer**: 1 Tag bis 1 Monat

### **Layout & Grid (YOOtheme-kompatibel)**
- **Layout-Typ**: Grid, List, Masonry
- **Grid-Spalten**: `1@s 2@m 3@l` Format
- **Grid-Abstand**: Small, Medium, Large, No Gap
- **Karten-Stil**: Default, Primary, Secondary, Hover

### **YOOtheme Integration**
- **Theme-Farben übernehmen**: Automatisch aus YOOtheme
- **Theme-Typografie übernehmen**: Schriftarten erben
- **Eigene CSS-Klasse**: Zusätzliche Container-Klassen

---

## 🔧 **Supported Content Types**

### **Index-Seiten** (`/index/`)
- ✅ `moses_index_item` Artikel-Extraktion
- ✅ H4-Teaser-Text-Parsing  
- ✅ Featured-Image-Extraktion
- ✅ Datum-Extraktion
- ✅ Link-Generierung zu Einzelartikeln

### **Tools-Seiten** (`/tools.html`)
- ✅ H3-Überschriften als Titel
- ✅ Folgende Paragraph als Beschreibung
- ✅ Link-Extraktion zu Tools
- ✅ Spezielle "Tool"-Kennzeichnung

### **Einzelartikel** (`/text/`)
- ✅ Vollständige HTML-Darstellung
- ✅ URL-Replacement für interne Links
- ✅ Responsive Container

---

## 🐛 **Debug & Entwicklung**

### **Debug-Modi**
- **Basic**: Grundlegende Infos (URL, Artikel-Count, Cache-Status)
- **Detailed**: Erweiterte Infos (Parsing-Details, Image-Cache)
- **Verbose**: Vollständige Debug-Ausgabe (alle Variablen)

### **Cache-Verwaltung**
- **Cache-Statistiken**: Anzahl, Größe, Älteste/Neueste Datei
- **Cache-Clearing**: Einmaliges Löschen aller Cache-Dateien
- **TTL-Management**: Automatische Bereinigung alter Dateien

---

## 📁 **Datei-Struktur**

```
mod_mandantenbrief/
├── mod_mandantenbrief.php          # Entry Point
├── mod_mandantenbrief.xml          # YOOtheme-optimierte Parameter
├── src/Helper/
│   ├── Module.php                  # Haupt-Logik
│   ├── ParserHelper.php            # Content-Parsing
│   └── CacheHelper.php             # Image-Cache
├── tmpl/default.php                # YOOtheme-Template
└── language/                       # DE/EN Sprachdateien
```

---

## 🔄 **Updates**

**Automatische Updates** aus GitHub sind konfiguriert. Das Modul überprüft automatisch auf neue Versionen.

---

## 👨‍💻 **Author**

**Steuerberater Karl Heinz Schmidt**  
📧 info@djumla.dev | 🌐 https://djumla.dev

---

**Made with ❤️ for the Joomla & YOOtheme community**