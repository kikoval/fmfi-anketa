#!/bin/sh
if [ $# -ne 2 ] ; then
 echo "usage $0 source_dir title";
else
echo "Making documentation Source:$1 Title:$2";
cd $1
if [ $? -eq 0 ] ; then
    # tento subor nevie phpdoc poparsovat:
    # /home/ppershing/fmfi-anketa/vendor/symfony/src/Symfony/Component/Locale/Resources/data/update-data.php

    IGNORES="--ignore *.res,.svn/,*/cache/*,update-data.php"
    OUTPUT="-t ./report/documentation -o HTML:frames:DOM/earthli"
    rm -rf ./report/documentation/*
    phpdoc -d . $OUTPUT  -s -ti "$2" -pp $IGNORES
    chmod a+w -R ./report/documentation
  else
    echo "Problems entering directory $1, exitting";
  fi;
fi
