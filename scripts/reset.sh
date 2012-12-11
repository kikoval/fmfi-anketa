#!/bin/bash

set -e
cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'


if ! [ -e app/config/config_local.yml ]; then
  echo "${bold}najprv treba spustit scripts/init_all.sh.${normal}"
  exit 1
fi

if ! [ -e vendor/symfony/symfony ]; then
  echo "${bold}najprv treba spustit path/to/composer.phar install${normal}"
  exit 1
fi

if ! [ -w app/cache ]; then
  echo "${bold}spustaj ma bud pod webserver userom alebo pod vlastnikom zdrojakov.${normal}"
  exit 1
fi


reset_cache () {
  for env in `ls app/cache/ 2>/dev/null`; do
    app/console cache:clear -e $env
  done
}


reset_cache_total () {
  echo "${bold}clearing cache${normal}"
  rm -rf app/cache/*
}


reset_logs () {
  echo "${bold}clearing logs${normal}"
  rm -rf app/logs/*
}


reset_db () {
  if ! ./app/console anketa:db-reset-allowed; then
    echo "${bold}PRESKAKUJEM reset databazy lebo neni zapnute allow_db_reset${normal}"
    return 0
  fi

  read -p "${bold}Mozem zmazat celu databazu a spravit novu? (y/n) [y] ${normal}"
  if [ "$REPLY" != "y" ] && [ "$REPLY" != "Y" ] && [ "$REPLY" != "" ]; then
    echo "${bold}ok, PRESKAKUJEM reset databazy${normal}"
    echo "(odporucam v config_local.yml vypnut allow_db_reset, nech si to pamatam)"
    return 0
  fi

  ./app/console doctrine:database:drop --force

  ./app/console doctrine:database:create
  # TODO: vyrobi sa mysql so spravnym charsetom?
  if [ -f db/anketa.sqlite ]; then
    getfacl -a app/cache/ | setfacl --set-file=- db/anketa.sqlite
    chmod a-x db/anketa.sqlite
  fi

  ./app/console doctrine:schema:create

  # TODO vytvor falosne fixtures (predmety, odpovede na otazky, atd)
}


reset_all () {
  reset_cache
  #reset_logs
  reset_db
}


reset_help () {
  echo "usage:
  $0 cache
  $0 cache-total   (nerobi cache warmup, proste zmaze vsetko)
  $0 logs
  $0 db
  $0 all           (default)"
  exit 0
}


[ "$#" == 0 ] && set all   # ked nemame argumenty, dame all
[ "${1#-}" != "$1" ] && reset_help   # ked zacina $1 na -, ukazeme help
cmd=reset_${1//-/_}   # nazov fcie bude reset_$1 ale s _ namiesto -
shift
[ "`type -t "$cmd"`" != "function" ] && echo "nepoznam: $1" && reset_help
"$cmd" "$@"
