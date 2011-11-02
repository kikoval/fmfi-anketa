#!/bin/bash

set -e
cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

mkdir -p report/tests
cd app
phpunit "$@"

