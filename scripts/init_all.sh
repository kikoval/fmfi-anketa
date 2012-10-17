#!/bin/bash

cd "`dirname "$0"`/.."
bold=$'\e[37;40;1m'
normal=$'\e[0m'

if [ "$1" == "" ] || [ "${1#-}" != "$1" ]; then
  echo "usage: $0 webserver-username"
  echo "(t.j. uzivatel pod ktorym bezi webserver, napriklad www-data)"
  exit 1
fi

if ! [ -w app ]; then
  echo "${bold}init_all.sh treba spustat pod uzivatelom, co vlastni zdrojaky.${normal}"
  exit 1
fi

if ! [ -O app ]; then
  echo "${bold}init_all.sh treba spustat pod uzivatelom, co vlastni zdrojaky.${normal}
tento ich sice moze prepisovat, ale nevlastni ich, takze ked vyrobi novy subor,
uz nebudu mat vsetky subory toho isteho vlastnika.
(ak vies, co robis, docasne tuto kontrolu mozes v init_all.sh zakomentovat.)"
  exit 1
fi

rm -rf app/cache/ app/logs/
for dir in app/cache/ app/logs/ db/; do
  if ! [ -d "$dir" ]; then
    echo "vyrabam $dir"
    mkdir $dir
    if [ "$1" != "`whoami`" ]; then
      setfacl -R -m u:$1:rwx -m u:`whoami`:rwx -m o::--- $dir
      setfacl -dR -m u:$1:rwx -m u:`whoami`:rwx -m o::--- $dir
    fi
  fi
done

if ! [ -f app/config/config_local.yml ]; then
  echo "vyrabam app/config/config_local.yml"
  sed "s/secret:.*/secret: `head -c1000 /dev/urandom | md5sum | head -c32`/" app/config/config_local.yml.dist > app/config/config_local.yml
  setfacl -b -m u::rw- -m u:$1:r-- -m g::--- -m o::--- app/config/config_local.yml
fi

if [ -d vendor/symfony/symfony ]; then
  app/console assets:install --symlink --relative web
fi

echo "
1. stiahni dependencies: ${bold}path/to/composer.phar install${normal}
2. ak chces ${bold}cosign proxy${normal}, nastav to v app/config/config_local.yml
3. ak chces ${bold}mysql${normal} namiesto sqlite, nastav to v app/config/config_local.yml
4. vyrob novu databazu: ${bold}sudo -u $1 ./scripts/reset.sh${normal}
5. ak mas ${bold}produkcne data${normal} a nechces db uz nikdy resetovat, nastav to v app/config/config_local.yml
"
