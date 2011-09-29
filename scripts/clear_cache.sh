#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

echo "${bold}clearing cache${normal}"

mkdir -p app/cache
chmod 777 app/cache
rm -rf app/cache/*
