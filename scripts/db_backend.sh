SCRIPT_PATH=`dirname $0`
CONFIG_PATH="$SCRIPT_PATH"/../app/config/config.yml

grep "default_connection:" "$CONFIG_PATH" | grep -Eo '[a-z]+$'