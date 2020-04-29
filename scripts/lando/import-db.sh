#! /usr/bin/env sh

set -e

MYSQL_DATABASE=wordpress /helpers/sql-import.sh --host=database --port=3306 /app/dump.sql.gz
