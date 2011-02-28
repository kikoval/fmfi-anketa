SCRIPT=`dirname $0`/directory_graph.pl

$SCRIPT $1 2> /dev/null 1>/dev/null
FILEBASE="$1/report/directory_structure"
neato "$FILEBASE.dot" -Tpng > "$FILEBASE.png"

