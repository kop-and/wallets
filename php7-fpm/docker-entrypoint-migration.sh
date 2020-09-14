#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

cd /var/www/symfony

sleep 20;

php bin/console doctrine:database:create --no-interaction --if-not-exists

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result

php bin/console doctrine:cache:clear-metadata --env=prod
php bin/console doctrine:cache:clear-query --env=prod
php bin/console doctrine:cache:clear-result --env=prod

chown -R www-data:www-data var/logs && chown -R www-data:www-data var/cache

exec "$@"
