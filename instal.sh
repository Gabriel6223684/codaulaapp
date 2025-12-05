#!/bin/bash

cd /home/ventreminex

rm -R /vendor
rm -R composer.lock

composer install --no-dev --no-progress -a
composer update --no-dev --no-progress -a
composer upgrade --no-dev --no-progress -a
composer do -o --no-dev --no-progress -a

PG_USER="senac"
PG_PASS="senac"
PG_DB="gabrielluiz"
PG_HOST="localhost"
PG_PORT="5432"


service nginx reload