SCRIPT=`dirname $0`/package_graph.pl
$SCRIPT $1 2> /dev/null 1>/dev/null
FILEBASE="$1/report/package_structure"
neato "$FILEBASE.dot" -Tpng > "$FILEBASE.png"

