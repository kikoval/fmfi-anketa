#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

if ! [ -f app/config/config.yml ]; then
  echo "${bold}vyrabam app/config/config.yml${normal}"
  cp app/config/config.yml.template app/config/config.yml
fi

if ! [ -f app/config/parameters.ini ]; then
  echo "${bold}vyrabam app/config/parameters.ini s db_backend=sqlite${normal}"
  echo "ak chces MySQL, zmen tam db_backend a nastav si tam prihlasovacie udaje"
  sed 's/db_backend=mysql/db_backend=sqlite/' app/config/parameters.ini.template |
    sed "s/secret=.*/secret=`head -c1000 /dev/urandom | md5sum | head -c32`/" > app/config/parameters.ini
fi
