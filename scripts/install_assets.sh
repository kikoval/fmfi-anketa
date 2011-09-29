#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

echo "${bold}installing assets${normal}"
php ./app/console assets:install web --symlink
