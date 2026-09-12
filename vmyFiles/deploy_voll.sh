#!/bin/bash

# ============================================================
# Magento 2 - FULL CLEAN & REBUILD
# Projekt: bijouterie
# Löscht alle generierten/gecachten Verzeichnisse und baut
# anschließend Compile, Static Content und Caches neu auf.
#
# ACHTUNG: var/session/* wird gelöscht -> alle Nutzer (auch
# Admins) werden ausgeloggt. Bei Bedarf Zeile auskommentieren.
# ============================================================

set -e  # Script bricht bei jedem Fehler sofort ab

echo "=============================================="
echo "  MAGENTO 2 FULL CLEAN & REBUILD - START: $(date)"
echo "=============================================="

# ------------------------------------------------------------
# 1. MAINTENANCE MODE
# ------------------------------------------------------------

echo ""
echo "----- [1/16] Maintenance-Mode aktivieren -----"
php bin/magento maintenance:enable
echo "Maintenance-Mode ist jetzt AKTIV."

# ------------------------------------------------------------
# 2. CLEAN (löschen)
# ------------------------------------------------------------

echo ""
echo "----- [2/16] Frontend Static Content löschen -----"
# Kompilierte CSS/JS/Bilder fürs Frontend
rm -rf pub/static/frontend/*
echo "pub/static/frontend geleert."

echo ""
echo "----- [3/16] Admin Static Content löschen -----"
# Kompilierte Assets fürs Admin-Panel
rm -rf pub/static/adminhtml/*
echo "pub/static/adminhtml geleert."

echo ""
echo "----- [4/16] Static Content Cache löschen -----"
rm -rf pub/static/_cache/*
echo "pub/static/_cache geleert."

echo ""
echo "----- [5/16] View Preprocessed löschen -----"
# Vorverarbeitete LESS/SASS-Dateien
rm -rf var/view_preprocessed/*
echo "var/view_preprocessed geleert."

echo ""
echo "----- [6/16] Cache-Ordner löschen -----"
rm -rf var/cache/*
echo "var/cache geleert."

echo ""
echo "----- [7/16] Page Cache löschen -----"
rm -rf var/page_cache/*
echo "var/page_cache geleert."

echo ""
echo "----- [8/16] Generated Code löschen -----"
# Proxies, Factories, Interceptoren
rm -rf generated/code/*
echo "generated/code geleert."

echo ""
echo "----- [9/16] Generated Metadata löschen -----"
# DI-Metadaten - gehört zusammen mit generated/code gelöscht
rm -rf generated/metadata/*
echo "generated/metadata geleert."

echo ""
echo "----- [10/16] DI-Compile-Zwischendateien löschen -----"
rm -rf var/di/*
echo "var/di geleert."

echo ""
echo "----- [11/16] Logs und Reports löschen -----"
rm -rf var/log/*
rm -rf var/report/*
echo "var/log und var/report geleert."

echo ""
echo "----- [12/16] Sessions löschen (loggt alle Nutzer aus!) -----"
rm -rf var/session/*
echo "var/session geleert."

# ------------------------------------------------------------
# 3. REBUILD (neu aufbauen)
# ------------------------------------------------------------

echo ""
echo "----- [13/16] Setup Upgrade ausführen -----"
php bin/magento setup:upgrade
echo "Setup Upgrade abgeschlossen."

echo ""
echo "----- [14/16] Dependency Injection kompilieren -----"
# WICHTIG unter Windows: kann wegen '|' im Dateinamen fehlschlagen,
# im Zweifel in WSL2/Docker ausführen
#php bin/magento setup:di:compile
echo "DI-Compile abgeschlossen. oder gesprungen"

echo ""
echo "----- [15/16] Statischen Content deployen -----"
# Sprachen ggf. an dein Projekt anpassen
php bin/magento setup:static-content:deploy -f
echo "Static Content Deploy abgeschlossen."

echo ""
echo "----- [16/16] Cache leeren und aktivieren -----"
php bin/magento cache:flush
php bin/magento cache:enable
echo "Cache geleert und aktiviert."

# ------------------------------------------------------------
# 4. ABSCHLUSS
# ------------------------------------------------------------

echo ""
echo "----- Indexer neu aufbauen -----"
#php bin/magento indexer:reindex
#echo "Reindex abgeschlossen."
echo "Reindex gesprungen."

echo ""
echo "----- Maintenance-Mode deaktivieren -----"
php bin/magento maintenance:disable
echo "Maintenance-Mode ist jetzt DEAKTIVIERT."

echo ""
echo "=============================================="
echo "  MAGENTO 2 FULL CLEAN & REBUILD - FERTIG: $(date)"
echo "=============================================="