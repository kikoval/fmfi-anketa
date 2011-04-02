# This is a script that clears symfony cache files.
# @copyright   Copyright (c) 2011 Peter Peresini 
# @author      Peter Peresini <ppershing+agiltech@gmail.com>
#

CACHE=`dirname "$0"`/../app/cache
echo "Clearing cache at $CACHE "
rm -rf "$CACHE/"
