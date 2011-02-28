SCRIPT=`dirname $0`/phpdoc/makedoc.sh;
SCRIPT_GRAPH_CLASS=`dirname $0`/phpdoc/class_graph.sh;
SCRIPT_GRAPH_CLASS_DEPENDENCY=`dirname $0`/phpdoc/dependency_graph.sh;
SCRIPT_GRAPH_DIR=`dirname $0`/phpdoc/directory_graph.sh;
SCRIPT_GRAPH_PACKAGE=`dirname $0`/phpdoc/package_graph.sh;

BASE_PATH=`dirname $0`/..
echo "Generating documentation"
$SCRIPT $BASE_PATH "Example";

echo "Generating class graph"
$SCRIPT_GRAPH_CLASS $BASE_PATH/src $BASE_PATH/report/

echo "Generating class dependency graph"
$SCRIPT_GRAPH_CLASS_DEPENDENCY $BASE_PATH

echo "Generating dir graph"
$SCRIPT_GRAPH_DIR $BASE_PATH

echo "Generating package graph"
$SCRIPT_GRAPH_PACKAGE $BASE_PATH
