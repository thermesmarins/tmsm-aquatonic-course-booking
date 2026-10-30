#!/usr/bin/env bash
# Lance les tests PHP : MySQL jetable (port 33306) + PHPUnit dans l'image PHP du projet.
# Usage : tests/php/run.sh [options phpunit]   ex. --filter Equivalence
set -euo pipefail
cd "$(dirname "$0")"

PLUGIN_DIR="$(cd ../.. && pwd)"
DB_NAME="tmsm-test-mysql"
PHP_IMAGE="${PHP_IMAGE:-aquatonic-wordpress}"

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_NAME"; then
  docker run -d --rm --name "$DB_NAME" -e MYSQL_ROOT_PASSWORD=test -e MYSQL_DATABASE=tmsm_test \
    -p 127.0.0.1:33306:3306 mysql:8.0 >/dev/null
fi
until docker exec "$DB_NAME" mysqladmin ping -uroot -ptest --silent >/dev/null 2>&1; do sleep 1; done

docker run --rm --network host -v "$PLUGIN_DIR":/plugin -w /plugin/tests/php "$PHP_IMAGE" \
  php vendor/bin/phpunit "$@"
