#!/bin/bash
/var/www/app/bin/console doctrine:schema:update --env=prod --force
/var/www/app/bin/console assetic:dump --env=prod --no-debug
/var/www/app/bin/console assets:install --env=prod --no-debug
mkdir /var/www/app/var/cache
mkdir /var/www/app/var/cache/prod
/var/www/app/bin/console cache:clear --env=prod
chmod -R 777 /var/www/app/var/logs
chmod -R 777 /var/www/app/var/cache/prod
chmod -R 777 /tmp
chmod -R 777 /var/www/app/web/uploads
rm /run/apache2/apache2.pid
/usr/bin/supervisord -n
