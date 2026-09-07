#!/bin/bash

# ============================
#  FAST MAGENTO DEPLOY SCRIPT
#  Theme: Hidden/apparel
#  Locale: fr_FR
# ============================

echo ">> Clearing cache..."
#bin/magento cache:clean
#bin/magento cache:flush

#echo ">> Deleting generated static files..."
rm -rf pub/static/frontend/Hidden/apparel/
rm -rf var/view_preprocessed


#echo ">> Running DI compilation..."
#bin/magento setup:di:compile

echo ">> Deploying static content for theme Hidden/apparel (ar and fr)..."
bin/magento setup:static-content:deploy --theme Hidden/apparel -f

#echo ">> Setting permissions..."
#find var vendor pub/static pub/media app/etc -type f -exec chmod 664 {} \;
#find var vendor pub/static pub/media app/etc -type d -exec chmod 775 {} \;
#chmod u+x bin/magento

echo ">> DONE! Fast deploy [--theme Hidden/apparel] completed successfully 🎉"
