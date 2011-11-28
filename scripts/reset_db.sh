#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

! [ -f app/config/parameters.ini ] && echo "CHYBA: neviem najst parameters.ini" && exit 1

zisti () { grep "$1" app/config/parameters.ini | grep -Eo '=.*$' | cut -c2-; }
sprav () { echo "$bold> $*$normal"; "$@"; }

[ "`zisti db_allow_reset`" != "true" ] && echo "${bold}PRESKAKUJEM reset databazy lebo neni db_allow_reset=true${normal}" && exit 0

echo "${bold}resetujem databazu${normal}"

db_backend=`zisti db_backend`
db_sqlite_file=`zisti db_sqlite_file`

mysql_client=
type mysql &>/dev/null && mysql_client=mysql
type mysql5 &>/dev/null && mysql_client=mysql5
[ "$db_backend" == "mysql" ] && [ "$mysql_client" == "" ] && echo "CHYBA: neviem najst mysql klienta." && exit 1

# odtialto sa zacne aj nieco diat

sprav app/console doctrine:database:drop --force
[ "$db_backend" == "sqlite" ] && sprav app/console doctrine:database:create
[ "$db_backend" == "mysql" ] && echo "${bold}Vytvaram novu databazu${normal}" && echo "CREATE DATABASE `zisti db_mysql_name` CHARSET utf8;" | "$mysql_client" -u"`zisti db_mysql_user`" -p"`zisti db_mysql_pass`" "`zisti d_mysql_name`"

sprav app/console doctrine:schema:create
sprav app/console doctrine:data:load
sprav app/console anketa:import-otazky other/anketa.yml

echo "${bold}> importujem other/teachers_subjects.sql..."
[ "$db_backend" == "sqlite" ] && sqlite3 "db/$db_sqlite_file" <other/teachers_subjects.sql
[ "$db_backend" == "mysql" ] && "$mysql_client" -u"`zisti db_mysql_user`" -p"`zisti db_mysql_pass`" "`zisti db_mysql_name`" <other/teachers_subjects.sql

echo "${bold}databaza resetnuta${normal}"
