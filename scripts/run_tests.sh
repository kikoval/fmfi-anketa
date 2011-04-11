#!/bin/bash
SCRIPT_PATH=`dirname $0`
. $SCRIPT_PATH/real_path.sh

# See http://www.phpunit.de/manual/current/en/ on how to write tests

SCRIPT_PATH=`real_path $SCRIPT_PATH`
echo "Script path: "$SCRIPT_PATH
TEST_PATH=`real_path $SCRIPT_PATH/../app/`
echo "Test path: "$TEST_PATH
REPORT_PATH=`real_path $SCRIPT_PATH/..`/report/tests
echo "Report path: "$REPORT_PATH
rm -rf "$REPORT_PATH"
mkdir -p $REPORT_PATH

cd $TEST_PATH && phpunit $@
chmod a+rw -R $REPORT_PATH
