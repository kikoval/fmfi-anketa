# @author Ivan Trancik <descent89@gmail.com>
#

LOGS=`dirname "$0"`/../app/logs

chmod 777 "$LOGS"
rm -rf "$LOGS"/*
