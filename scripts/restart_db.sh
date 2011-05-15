set -x
SCRIPT_PATH=`dirname "$0"`

CONFIG_PATH="$SCRIPT_PATH"/../app/config/config.yml
PARAMETERS_PATH="$SCRIPT_PATH"/../app/config/parameters.ini
BACKEND=`grep "db_backend" "$PARAMETERS_PATH" | grep -Eo '=.*$' | cut -c2-`

SQLITE_DATABASE_FOLDER_PATH="$SCRIPT_PATH"/../db
SQLITE_DATABASE_FILE_PATH="$SQLITE_DATABASE_FOLDER_PATH"/"`grep "db_sqlite_file=" "$PARAMETERS_PATH" | grep -Eo '=.*$' | cut -c2-`"

MYSQL_DATABASE_NAME=`grep "db_mysql_name=" "$PARAMETERS_PATH" | grep -Eo '=.*$' | cut -c2-`
MYSQL_USER=`grep "db_mysql_user=" "$PARAMETERS_PATH" | grep -Eo '=.*$' | cut -c2-`
MYSQL_PASS=`grep "db_mysql_password=" "$PARAMETERS_PATH" | grep -Eo '=.*$' | cut -c2-`
MYSQL_CLIENT="unknown"
# check if client exists and set
if test -n "`which "mysql"`"; then
MYSQL_CLIENT="mysql";
elif test -n "`which "mysql5"`"; then
MYSQL_CLIENT="mysql5";
fi

CONSOLE="$SCRIPT_PATH"/../app/console

TEACHERS_SUBJECTS_IMPORT_FILE="$SCRIPT_PATH"/../other/teachers_subjects.sql
QUESTIONS_IMPORT_FILE="$SCRIPT_PATH"/../other/anketa.yml

if test "$BACKEND" = "sqlite"; then
chmod 777 "$SQLITE_DATABASE_FOLDER_PATH"
fi

$CONSOLE doctrine:database:drop --force

$CONSOLE doctrine:database:create

if test "$BACKEND" = "sqlite"; then
chmod 777 "$SQLITE_DATABASE_FILE_PATH"
fi

$CONSOLE doctrine:schema:create

$CONSOLE doctrine:data:load

$CONSOLE anketa:import-otazky $QUESTIONS_IMPORT_FILE

if test "$BACKEND" = "mysql"; then
$MYSQL_CLIENT  -u"$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DATABASE_NAME" < "$TEACHERS_SUBJECTS_IMPORT_FILE"
elif test "$BACKEND" = "sqlite"; then
sqlite3 "$SQLITE_DATABASE_FILE_PATH" < "$TEACHERS_SUBJECTS_IMPORT_FILE"
fi