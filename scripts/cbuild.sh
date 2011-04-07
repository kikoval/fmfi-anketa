#!/bin/bash

# This is a continuous build script file.
# Note: do not run this file on your own, it is
# ran for each commit automatically at testing machine.
#
# @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
#             Use of this source code is governed by a MIT license that can be
#             found in the LICENSE file in the project root directory.
#
# @author     Peter Peresini <ppershing+fajr@gmail.com>
#

### Begin of the script

# if we are invoked from php, $PATH is unset
export PATH=/usr/local/bin:/usr/bin:/bin
umask 022

SCRIPT_PATH=`dirname $0`
SCRIPT_PATH=`readlink -f "$SCRIPT_PATH"`
cd "$SCRIPT_PATH";

## configuration
MAIL_TESTS_TO="fmfi-anketa-devel@googlegroups.com"
DIFF_CMD="/usr/bin/diff"
OLD_TEST_REPORT=tests_old.dat
NEW_TEST_REPORT=tests_new.dat
LOCK_FILE="./notify.pid"

###
 # Wait until there is no lock file and acquire it
 #
 # @param string $lockFile
 #
 # @returns void
 ##
function wait_and_create_lock {
  LOCK_FILE="$1"
  ## wait
  while PID=`cat "$LOCK_FILE"` ; do
    echo "Waiting for process $PID to stop"
    sleep 10
  done
  ## acquire
  echo "$$" > "$LOCK_FILE";
}

###
 # Print nicely color-formatted email message about test changes
 #
 # @param string $old_test_report
 # @param string $new_test_report
 # @param int $revision
 #
 # @prints email message
 ##
function print_test_diff {
  WWW_ROOT='http://agiltech.dcs.fmph.uniba.sk/fmfi_anketa_head'
  OLD="$1"
  NEW="$2"
  REV="$3"
  DIFF_DATA=`$DIFF_CMD -U 0 \
      --label "Old revision" "$OLD" \
      --label "Revision $SVN_REVISION" "$NEW" \
      | grep -v -e ^@ \
      | sed 's/&/&amp;/g' | sed 's/</&lt;/g' | sed 's/>/&gt;/g' \
      | sed 's/^\(-.*\)$/<span style="color:red  ">\1<\/span>/' \
      | sed 's/^\(+.*\)$/<span style="color:green">\1<\/span>/'`
  NEW_DATA=`cat "$NEW"`
  echo "<html>"
  echo "<pre style='font-size: 12px; white-space:pre'>"
  echo "There were some changes affecting failing tests in the HEAD revision. Details:"
  echo "--------"
  echo "$DIFF_DATA"
  echo "--------"
  echo "Full list of currently failing tests:"
  echo "--------"
  echo "<span style='color:blue'>"
  echo "$NEW_DATA"
  echo "</span>"
  echo "--------"
  echo "To see details go to $WWW_ROOT/tests"
  echo "Code coverage is at $WWW_ROOT/tests/coverage/"
  echo "Full script log is at $WWW_ROOT/build_last_run.log"
  echo "Your friendly continous build by ppershing ;-)"
  echo "</pre>"
  echo "</html>"
}


# remove lock at the script end, even in case of fatal failures!
trap "rm '$LOCK_FILE'" EXIT
wait_and_create_lock "$LOCK_FILE"

# now start intergation - update to latest point
# (currently we ignore revision info sent by request)

echo "Starting continuous build:"
date

echo "------------------Clear cache------------------------"
./clear_cache.sh


TRUNK_PATH="$SCRIPT_PATH/.."
echo "Updating svn"
svn up "$TRUNK_PATH"
SVN_REVISION=`svn info | grep Revision | sed 's/.* //'`

echo
echo "Updated. Current status:"
svn info "$TRUNK_PATH"
echo
svn status "$TRUNK_PATH"

echo "-------------------Running tests---------------------";
./run_tests.sh

cat ../report/tests/report.tap | \
  grep -E '(^not)|(SKIP)' | \
  sed -E 's/ok [0-9]+ - /ok: /' | sort \
  > "$NEW_TEST_REPORT"


#echo "-------------------Running javascript tests---------------------";
## do 2 dry runs to (re)load all tests. This shouldn't be necessary with
## patched jstestdriver
#./run_jstests.sh
#./run_jstests.sh
## actual tests
#./run_jstests.sh
#cat ../report/tests/js_tests.txt | \
#  grep -e FAILED -e ERROR | sort | uniq | sed 's/^/Javascript:/' \
#  >> "$NEW_TEST_REPORT"

if ! $DIFF_CMD "$OLD_TEST_REPORT" "$NEW_TEST_REPORT" ; then
  echo "Tests changed";
  print_test_diff "$OLD_TEST_REPORT" "$NEW_TEST_REPORT" "$SVN_REVISION" | \
    mail -s "Failed tests changed in r$SVN_REVISION" $MAIL_TESTS_TO \
      -a "Content-type: text/html; charset=utf-8"
  # update files
  mv "$NEW_TEST_REPORT" "$OLD_TEST_REPORT"
fi

echo "------------------------Making documentation--------------------";
./make_all_doc.sh
echo "------------------------Making coding standard report-----------";
./make_coding_standard.sh
echo "------------------------ALL DONE--------------------------------";
