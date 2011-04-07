# This is a wrapper for symfony asset:install
# @copyright   Copyright (c) 2011 Martin Sucha 
# @author      Martin Sucha <anty.sk+svt+anketa@gmail.com>
#

ROOT=`dirname "$0"`/../
cd "$ROOT"
SUFFIX="Resources/public/"
SF_BUNDLES="./vendor/symfony/src/Symfony/Bundle/"

rm -rf "./web/bundles/anketa"
rm -rf "./web/bundles/framework"
rm -rf "./web/bundles/webprofiler"
rm -rf "./web/bundles/symfonywebconfigurator"

svn export "./src/AnketaBundle/$SUFFIX" "./web/bundles/anketa"
svn export "$SF_BUNDLES/FrameworkBundle/$SUFFIX" "./web/bundles/framework"
svn export "$SF_BUNDLES/WebProfilerBundle/$SUFFIX" "./web/bundles/webprofiler"
svn export "./vendor/bundles/Symfony/Bundle/WebConfiguratorBundle/$SUFFIX" "./web/bundles/symfonywebconfigurator"

