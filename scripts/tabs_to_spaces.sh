#!/bin/bash
NEWFILE=$1.tabs-to-spaces
expand -t 2 $1 >$NEWFILE && mv $NEWFILE $1
