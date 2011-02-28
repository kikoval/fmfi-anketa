#!/bin/bash
OUT=$2/class_graph.dot;

echo "
digraph g {
  concentrate=false;
  rankdir=\"LR\";
  clusterrank=\"local\";
  ranksep=\"3.0\";
  nodesep=\"0.5\";
  size=\"30,30\";
  node[shape=box, fontsize=14, fillcolor=gray, style=filled];
" > $OUT;

function getcolor {
  echo "$@" | sha1sum | sed 's/\(......\).*/\1/'
}

ZOZNAM=$(find $1 | grep '\.php' | grep -v '\.svn' | \
    grep -v '\.swp' | LC_ALL='C' grep '[A-Z]' )

CLUSTER_PY=`readlink -f $(dirname $0)`/dependency_clusters.py;
echo $ZOZNAM | $CLUSTER_PY >> $OUT;
CLASS_RE="[a-zA-Z0-9_]+"
for file in $ZOZNAM; do
  classdef=`cat $file | tr "\n" "#" | \
    grep  '#\(abstract class\|class\|interface\) \([^{]*\)[{]' | \
    sed 's/#\(abstract class\|class\|interface\) \([^{]*\)[{].*$/@@@\1:\2/' | \
    sed 's/.*@@@//' | \
    sed 's/implements//' | sed 's/extends//' | \
    sed 's/abstract class/abstract_class/' | \
    tr '#' ' ' | tr ',' ' '`;

  if [ "$classdef" != "" ]; then
    FIRST="none";
    for token in $classdef; do
      if [ "$FIRST" == "none" ]; then
        FIRST=`echo $token | sed 's/.*://'`
        TMP=`echo $token | sed 's/:.*//'`
        if [ "$TMP" == "class" ]; then
          echo "$FIRST [ fillcolor=greenyellow;]" >> $OUT;
        elif [ "$TMP" == "abstract_class" ]; then
          echo "$FIRST [ fillcolor=yellow;]" >> $OUT;
        else
          echo "$FIRST [ fillcolor=lightblue;]" >> $OUT;
        fi
      else
        col=`getcolor $token`
        echo "$FIRST -> $token [color=\"#$col\"];" >> $OUT;
      fi
    done
  fi

done
echo "}" >> $OUT

dot $2/class_graph.dot -Tpng > $2/class_graph.png
