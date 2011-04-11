php app/console doctrine:database:drop --force
php app/console doctrine:database:create
chmod 664 db/anketa.sqlite
php app/console doctrine:schema:create
php app/console doctrine:data:load