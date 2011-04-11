SCRIPT_PATH=`dirname $0`
. $SCRIPT_PATH/real_path.sh

SCRIPT_PATH=`real_path $SCRIPT_PATH`
echo "Script path: "$SCRIPT_PATH
DB_PATH=`real_path $SCRIPT_PATH/../db/anketa.sqlite`
echo "DB path: "$DB_PATH
CONSOLE_PATH=`real_path $SCRIPT_PATH/../app/console`
echo "Console path: "$CONSOLE_PATH
php $CONSOLE_PATH doctrine:database:drop --force
php $CONSOLE_PATH doctrine:database:create
chmod 664 $DB_PATH
php $CONSOLE_PATH doctrine:schema:create
php $CONSOLE_PATH doctrine:data:load