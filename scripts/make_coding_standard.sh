#!/bin/sh
BASE=`dirname $0`/..
echo "Making coding standard for dir: $BASE ";

cd $BASE

FULL_BASE="$PWD"
CODING_STANDARD="Fajr"

if [ $? -eq 0 ] ; then
    mkdir -p report
    FILE="report/codingStandard.html";

    echo "First pass (detailed)"
    echo -e "<html> <pre> \n" >$FILE;
    echo -n "Built on " >> $FILE;
    date >> $FILE;
    echo -e "\n" >> $FILE;
    phpcs . --standard="$CODING_STANDARD" --report=full \
        --ignore=report,third_party | tr '<>' '()' >> $FILE;
    echo -e "\n\n\n++++++++++++++++++++++++++++++++++++++++++++++++++\n\n\n" >> $FILE;

    echo "Second pass (statistics)"
    phpcs . --standard="$CODING_STANDARD" --report=summary \
        --ignore=report,third_party | tr '<>' '()' >> $FILE;
    echo "</pre></html>" >> $FILE;

    echo "All done";
else
    echo "Problems entering directory $BASE, exitting";
fi;
