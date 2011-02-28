#!/bin/bash

# See http://www.phpunit.de/manual/current/en/ on how to write tests

SCRIPT_PATH=`dirname $0`
SCRIPT_PATH=`readlink -f $SCRIPT_PATH`
TEST_PATH=`readlink -f $SCRIPT_PATH/../tests`
REPORT_PATH=`readlink -f $SCRIPT_PATH/..`/report/tests
rm -rf "$REPORT_PATH"
mkdir -p $REPORT_PATH

cd $TEST_PATH && phpunit $PARAMS $@ $TEST_PATH
chmod a+rw -R $REPORT_PATH
