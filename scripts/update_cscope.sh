#!/bin/bash
cd `dirname $0`/..
find . -name '*.php' > ./cscope.files
cscope -b
rm ./cscope.files
