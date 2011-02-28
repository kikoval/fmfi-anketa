#!/usr/bin/python

import sys;

root_dict = {}
x = sys.stdin.read()
for file in x.rstrip().split(' '):
  path_components = file.split('/');
  current_dict = root_dict;
  for path_segment in path_components:
    path_segment = path_segment.replace(".", "_").replace("-","_");
    if path_segment not in current_dict:
      current_dict[path_segment]={}
    current_dict = current_dict[path_segment]


def print_dict(level, dictionary, path):
  for key in dictionary.keys():
    if dictionary[key].keys():
      print "".rjust(level*4), "subgraph", "cluster"+path+"_"+key, "{"
      print "".rjust(level*4), "  color=blue"
      print "".rjust(level*4), "  label=\"",key,"\""
      print_dict(level+1, dictionary[key], path+"_"+key)
      print "".rjust(level*4), "}"
    else:
      print "".rjust(level*4), key.replace("_php","")
print_dict(0, root_dict, "");
