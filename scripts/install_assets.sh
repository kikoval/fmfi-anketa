#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

echo "${bold}vyrabam symlinky na assety${normal}"

# rationale: kedze app/console potrebuje pristup do app/cache a app/logs, ale
# tam moze pristupovat iba webserver, nemozme spravit assets:install. takze
# tuto hardcodujeme vsetky symlinky, co by to vyrobilo. vid issue #21.

mkdir -p web/bundles
cd web/bundles
ln -sfT ../../src/AnketaBundle/Resources/public anketa
ln -sfT ../../vendor/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/public framework
ln -sfT ../../vendor/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/public webprofiler

