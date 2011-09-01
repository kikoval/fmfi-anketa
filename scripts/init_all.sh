SCRIPT_PATH=`dirname "$0"`

sh "$SCRIPT_PATH"/clear_cache.sh
sh "$SCRIPT_PATH"/clear_logs.sh
sh "$SCRIPT_PATH"/restart_db.sh
sh "$SCRIPT_PATH"/install_assets.sh
sh "$SCRIPT_PATH"/clear_cache.sh
sh "$SCRIPT_PATH"/clear_logs.sh
