php app/console doctrine:database:drop --force
php app/console doctrine:database:create
chmod 777 db/anketa.sqlite
php app/console doctrine:schema:create
php app/console doctrine:data:load
sudo -u www-data php app/console anketa:import-otazky other/anketa.yml
