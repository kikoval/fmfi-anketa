#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

console=app/console
parameters=app/config/parameters.ini

! [ -f "$parameters" ] && echo "CHYBA: neviem najst parameters.ini" && exit 1

zisti () { grep "$1" "$parameters" | grep -Eo '=.*$' | cut -c2-; }
sprav () { echo "$bold> $*$normal"; "$@"; }

[ "`zisti db_allow_reset`" != "true" ] && echo "${bold}PRESKAKUJEM restart databazy lebo neni db_allow_reset=true${normal}" && exit 0

echo "${bold}resetujem databazu${normal}"

db_backend=`zisti db_backend`
db_sqlite_file=`zisti db_sqlite_file`

mysql_client=
type mysql &>/dev/null && mysql_client=mysql
type mysql5 &>/dev/null && mysql_client=mysql5
[ "$db_backend" == "mysql" ] && [ "$mysql_client" == "" ] && echo "CHYBA: neviem najst mysql klienta." && exit 1

# odtialto sa zacne aj nieco diat

[ "$db_backend" == "sqlite" ] && sprav mkdir -p db
[ "$db_backend" == "sqlite" ] && sprav chmod 777 db
sprav "$console" doctrine:database:drop --force
sprav "$console" doctrine:database:create
[ "$db_backend" == "sqlite" ] && sprav chmod 777 "db/$db_sqlite_file"

sprav "$console" doctrine:schema:create
sprav "$console" doctrine:data:load
sprav "$console" anketa:import-otazky other/anketa.yml

[ "$db_backend" == "sqlite" ] && sprav sqlite3 "db/$db_sqlite_file" <other/teachers_subjects.sql
[ "$db_backend" == "mysql" ] && sprav "$mysql_client" -u"`zisti db_mysql_user`" -p"`zisti db_mysql_pass`" "`zisti db_mysql_name`" <other/teachers_subjects.sql
