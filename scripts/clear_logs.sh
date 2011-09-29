#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

echo "${bold}clearing logs${normal}"

mkdir -p app/logs
chmod 777 app/logs
rm -rf app/logs/*
