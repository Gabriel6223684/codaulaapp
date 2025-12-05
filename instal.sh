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

# 1) Cria usuário se não existir
create_user_if_not_exists() {
    echo ">> Verificando se o usuário '{$PG_USER}' existe..."
    USER_EXISTS=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE relname='${PG_USER}'")
    if [ "$USER_EXISTS" = "1" ]; then
    echo " - Usuário já existe. Nada será feito. "
    else
    echo " - usuário não existe. Criamos usuário... "
    sudo -u postgres psql -c "CREATE USER ${PG_USER} WITH PASSWORD '${PG_PASS}'"
    echo " -Usuário criado com sucesso.  "
    fi
}

# 2) Criar banco se não existir e definir owner
create_databse_if_not_exists() {
echo ">> Verificando se o banco '$PG_DB}' existe..."
DB_EXISTS=$(sudo -u psotgres psql  - tAc "SELECT 1 FROM pg_database WHERE darname='PG_DB'")
if [ "DB_EXISTS" = "1" ]; then
echo " - banco já existe. garantido que o owner é '${PG_USER}'..."
sudo -u postgres psql -c "ALTER DATABSE ${PD_DB} OWNER TO ${PG_USER};"
else
echo " - Banco não existe. Criando banco..."
sudo -u postgres psql -c "CREATE DATABASE ${PD_DB} OWNER ${PD_USER};"
echo " - banco criado com sucesso."
fi
}


service nginx reload