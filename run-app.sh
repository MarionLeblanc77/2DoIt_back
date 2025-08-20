#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x run-app.sh`
# Run migrations, process the Nginx configuration template and start Nginx
php bin/console doctrine:migrations:migrate --no-interaction && (php-fpm -y /assets/php-fpm.conf)