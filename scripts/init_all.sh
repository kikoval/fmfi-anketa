#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

vyrobeny_config=
vyrobeny_parameters=
nemame_adresare=

if ! [ -w app ]; then
  echo "${bold}init_all.sh treba spustat pod uzivatelom, co vlastni zdrojaky.${normal}"
  exit 1
fi

if ! [ -f app/config/config.yml ]; then
  echo "${bold}vyrabam defaultny app/config/config.yml${normal}"
  cp app/config/config.yml.template app/config/config.yml
  vyrobeny_config=t
fi

if ! [ -f app/config/parameters.ini ]; then
  echo "${bold}vyrabam defaultny app/config/parameters.ini s db_backend=sqlite${normal}"
  sed 's/db_backend=mysql/db_backend=sqlite/' app/config/parameters.ini.template |
    sed "s/secret=.*/secret=`head -c1000 /dev/urandom | md5sum | head -c32`/" > app/config/parameters.ini
  vyrobeny_parameters=t
fi

[ -d ./db ] || nemame_adresare=t
[ -d ./app/cache ] || nemame_adresare=t
[ -d ./app/logs ] || nemame_adresare=t

./scripts/install_assets.sh

if [ "$vyrobeny_config" ] || [ "$vyrobeny_parameters" ] || [ "$nemame_adresare" ]; then
  echo ""
  echo "${bold}ok, este sprav toto:${normal}"
  [ "$vyrobeny_config" ] && echo "- ak chces cosign alebo libfajr, nastav to v app/config/config.yml."
  [ "$vyrobeny_parameters" ] && echo "- ak chces mysql namiesto sqlite, nastav to v app/config/parameters.ini."
  [ "$nemame_adresare" ] && echo "- vyrob adresare ./db, ./app/cache a ./app/logs s pravami 700 a ownerom webserverom.
  napriklad takto: ${bold}sudo install -o www-data -g www-data -m700 -d ./db ./app/cache ./app/logs${normal}"
  echo "- spusti ./scripts/reset_all.sh pod uzivatelom webservera, cim vyrobis novu databazu.
  napriklad takto: ${bold}sudo -u www-data ./scripts/reset_all.sh${normal}"
  [ "$vyrobeny_parameters" ] && echo "- ak bude tato databaza produkcna a uz nikdy ju nechces vymazavat,
  nastav to v app/config/parameters.ini."
fi
