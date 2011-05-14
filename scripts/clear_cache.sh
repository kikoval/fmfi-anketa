# This is a script that clears symfony cache files.
# @copyright   Copyright (c) 2011 Peter Peresini 
# @author      Peter Peresini <ppershing+agiltech@gmail.com>
#

CACHE=`dirname "$0"`/../app/cache

chmod 777 "$CACHE"
rm -rf "$CACHE"/*
