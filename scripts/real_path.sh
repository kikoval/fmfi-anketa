SCRIPT_PATH=`dirname $0`
. $SCRIPT_PATH/platform.sh

. $SCRIPT_PATH/platform.sh

real_path () {
    REAL_PATH=""
    PLATFORM=`platform`
    if [[ $PLATFORM == 'linux' ]]; then
       REAL_PATH='readlink -f'
    elif [[ $PLATFORM == 'freebsd' ]]; then
       REAL_PATH='greadlink -f'
    elif [[ $PLATFORM == 'osx' ]]; then
       REAL_PATH='greadlink -f'
    fi
    echo `$REAL_PATH $1`
}