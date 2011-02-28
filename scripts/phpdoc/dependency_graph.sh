#!/bin/bash
REPORT=`readlink -f $1/report`;
CLUSTER_PY=`readlink -f $(dirname $0)`/dependency_clusters.py;
cd $1
ZOZNAM=$(find src | grep '\.php' | grep -v '\.svn' | grep -v 'report' | \
    grep -v '\.swp' | LC_ALL='C' grep '[A-Z]' | grep -v 'Test' | \
    grep -v 'scripts' )


OUT=$REPORT/class_dependency_graph.dot;

# classy, ktore su velmi pouzivane nechceme zobrazovat
# "Fajr" nie je pouzivana classa, ale poparsuje sa z @package
BLACKLIST="Fajr Trace"

echo "
digraph g {
  concentrate=false;
  rankdir=\"LR\";
  clusterrank=\"local\";
  ranksep=\"3.0\";
  nodesep=\"0.5\";
  node[shape=box, fontsize=14, fillcolor=darkolivegreen1, style=filled];
" > $OUT;

for class in $BLACKLIST; do
  echo "  $class [fillcolor=gray];" >> $OUT;
done

function getcolor {
  echo "$@" | sha1sum | sed 's/\(......\).*/\1/'
}

echo $ZOZNAM | $CLUSTER_PY >> $OUT;
for file in $ZOZNAM; do
  CLASS=`basename $file | sed 's/\.php//'`
  VYSKYTY=`grep -l "[^a-zA-Z]$CLASS[^a-zA-Z]" $ZOZNAM`;
  for vyskyt in $VYSKYTY; do
    CLASS2=`basename $vyskyt | sed 's/\.php//'`
    OK="true"
    for blacklisted in $BLACKLIST; do
      [ $CLASS != $blacklisted ] || OK="false"
    done
    [ $CLASS != $CLASS2 ] || OK="false";
    if [ $OK == "true" ] ; then
      col=`getcolor $CLASS`
      echo "  $CLASS2 -> $CLASS [color=\"#$col\"];" >> $OUT
    fi
  done
done
echo "}" >> $OUT
dot $OUT -Tpng > ${OUT%%.dot}.png
