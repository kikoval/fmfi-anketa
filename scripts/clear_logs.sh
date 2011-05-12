# @author Ivan Trancik <descent89@gmail.com>
#

LOGS=`dirname "$0"`/../app/logs
chmod 777 "$LOGS"
echo "Clearing logs at $LOGS "
rm -rf "$LOGS"/*
touch "$LOGS/dev.log"
chmod 777 "$LOGS/dev.log"
