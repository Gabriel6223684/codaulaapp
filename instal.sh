#!/bin/bash

rm -R /vendor

composer install --no-dev --no-progress -a

sudo -u postgres psql

service nginx reload