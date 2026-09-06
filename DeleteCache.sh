echo ""
echo "----- [1/7] Cache-Ordner löschen -----"
rm -rf var/cache/*
echo "var/cache geleert."

echo ""
echo "----- [2/7] Page Cache löschen -----"
rm -rf var/page_cache/*
echo "var/page_cache geleert."

echo ""
echo "----- [3/7] Generated Code löschen -----"
# Proxies, Factories, Interceptoren
rm -rf generated/*
echo "generated/code geleert."

echo ""
echo "----- [4/7] View Preprocessed löschen -----"
# Vorverarbeitete LESS/SASS-Dateien
rm -rf var/view_preprocessed/*
echo "var/view_preprocessed geleert."

echo ""
echo "----- [5/7] Logs und Reports löschen -----"
rm -rf var/log/*
rm -rf var/report/*
echo "var/log und var/report geleert."

echo ""
echo "----- [6/7] Sessions löschen (loggt alle Nutzer aus!) -----"
rm -rf var/session/*
echo "var/session geleert."
echo ""

echo "----- [7/7] Cache leeren und aktivieren -----"
php bin/magento cache:flush
php bin/magento cache:enable
echo "Cache geleert und aktiviert."
