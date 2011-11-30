#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

if ! [ -e app/config/parameters.ini ]; then
  echo "${bold}najprv treba spustit scripts/init_all.sh.${normal}"
  exit 1
fi

if ! [ -w app/cache ]; then
  echo "${bold}reset_all.sh treba spustat pod uzivatelom pod ktorym bezi webserver.${normal}"
  exit 1
fi

./scripts/clear_cache.sh
#./scripts/clear_logs.sh
./scripts/reset_db.sh "$1"
./scripts/clear_cache.sh
#./scripts/clear_logs.sh
