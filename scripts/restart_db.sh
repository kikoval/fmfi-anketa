set -x
SCRIPT_PATH=`dirname "$0"`

CONFIG_PATH="$SCRIPT_PATH"/../app/config/config.yml
BACKEND=`grep "default_connection:" "$CONFIG_PATH" | grep -Eo '[a-z]+$'`

SQLITE_DATABASE_FOLDER="$SCRIPT_PATH"/../db
SQLITE_DATABASE_FILE="$SQLITE_DATABASE_FOLDER"/anketa.sqlite

MYSQL_DATABASE_NAME="anketa"
MYSQL_LOGIN="anketa"
MYSQL_PASS="beeliyaebNeShoot"
MYSQL_CLIENT="unknown"
# check if client exists and set
command -v mysql &>/dev/null && { MYSQL_CLIENT="mysql"; }
command -v mysql5 &>/dev/null && { MYSQL_CLIENT="mysql5"; }

CONSOLE="$SCRIPT_PATH"/../app/console

TEACHERS_SUBJECTS_IMPORT_FILE="$SCRIPT_PATH"/../other/teachers_subjects.sql
QUESTIONS_IMPORT_FILE="$SCRIPT_PATH"/../other/anketa.yml

if test "$BACKEND" = "sqlite"; then
chmod 777 "$SQLITE_DATABASE_FOLDER"
fi

$CONSOLE doctrine:database:drop --force

$CONSOLE doctrine:database:create

if test "$BACKEND" = "sqlite"; then
chmod 777 "$SQLITE_DATABASE_FILE"
fi

$CONSOLE doctrine:schema:create

$CONSOLE doctrine:data:load

$CONSOLE anketa:import-otazky $QUESTIONS_IMPORT_FILE

if test "$BACKEND" = "mysql"; then
$MYSQL_CLIENT  -u"$MYSQL_LOGIN" -p"$MYSQL_PASS" "$MYSQL_DATABASE_NAME" < "$TEACHERS_SUBJECTS_IMPORT_FILE"
elif test "$BACKEND" = "sqlite"; then
sqlite3 "$SQLITE_DATABASE_FILE" < "$TEACHERS_SUBJECTS_IMPORT_FILE"
fi