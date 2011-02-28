#!/usr/bin/perl -w

# $Id: DIR.pl,v 1.3 2004/02/06 15:18:13 mtsouk Exp mtsouk $
#
# Please note that this is alpha code
#
# Command line arguments
# program_name.pl directory

use strict;

my $directory="";
my $COMMAND="";
my %DIRECTORIES=();

die <<Thanatos unless @ARGV;
usage:
   $0 directory
Thanatos

if ( @ARGV != 1 )
{
   die <<Thanatos
      usage info:
         Please use exactly 1 argument!
Thanatos
}

# Get the file name
($directory) = @ARGV;
$COMMAND = "cat $directory/report/documentation/li_*.html | grep 'new WebFXTreeItem(' ";
$COMMAND = "$COMMAND | grep -E 'classe|file .*Test' "; #| grep -v -e '---' ";
$COMMAND = "$COMMAND | sed \"s/.*, '//\" | sed \"s/');//\" | grep -E '[a-z]' "; #| sed 's/[^\\/]*\\///'";
$COMMAND = "$COMMAND | sed 's/\\.html//' | sed 's/__/\\//' | ";

open (INPUT, "$COMMAND")
   || die "Cannot run the ".$COMMAND.": $!\n";

#
# The reason for putting OUTPUT in front of the
# directory name is that we
# can have . as directory name
#
my $OUTPUT="$directory/report/package_structure.dot";
#$OUTPUT =~ s/\//-/g;
open (OUTPUT, "> $OUTPUT")
   || die "Cannot create output file $OUTPUT: $!\n";

print OUTPUT <<START;
digraph G
{
#  ratio = auto;
#   rotate=90;
#   nodesep=.05;
   node[height=.05, fontsize=8, fillcolor=darkolivegreen1];
START

# Make nodes for the command line argument directory
#my @split = split /\//, $directory;
my $key="";
#my $prev=undef;
#for $key (@split)
#{
#    my $KEY=$key;
#    $key =~ s/[^[a-zA-Z0-9]/_/g;
#    $key = $prev."_".$key;
#    $prev = $key;
#    print OUTPUT "\t".$prev;
#    print OUTPUT " [shape=box, label=\"$KEY\", style=\"filled\"];";
#    print OUTPUT "\n";
#}

my $lastpart = "";
while (<INPUT>)
{
   chomp;
   my $orig=$_;

   # Get the right label
   my @split = split /\//, $_;
   $lastpart = pop @split;

   $_ =~ s/\//_/g;
   #
   # The _ is accepted as a valid node character
   # . , + - are not accepted
   #
   $_ =~ s/[^a-zA-Z0-9]/_/g;
   my @split = split /_/, $_;
   print OUTPUT "\t_".$_;
   print OUTPUT " [label=\"$lastpart\",style=\"filled\"];";
   print OUTPUT "\n";
   $DIRECTORIES{$orig}=0;
}

my $subdir="";
my %TEMP=();
foreach $key ( reverse sort keys %DIRECTORIES )
{
   print "KEY: $key\n";
   my @split = split /\//, $key;
   my $prev = undef;

   my $tmp = 0;

   for $subdir (@split)
   {
      $subdir =~ s/[^a-zA-Z0-9_]/_/g;
      my $count = ($prev =~ tr/_//);
      my $color = "0.5,0.5,".(1 - $count*0.05);
      if (defined($prev)) {
        print OUTPUT "$prev [shape=box, fillcolor=\"$color\"];\n";
      }
      my $next = $prev."_".$subdir;
      $tmp = $tmp + 1;
      # print "NEXT: $next\n";
      if ( !defined($prev) )
      {
         $prev = $next;
         next;
      }
      my $val = "$prev->$next;\n";
      # print "VAL: $val\n";
      if ( !defined( $TEMP{$val} ))
      {
        my $len = 3 + 3 * (scalar(@split) - $tmp);
         print OUTPUT "$prev->$next [len=$len];\n";
    #     print OUTPUT "$prev [style='filled'];\n";
      }
      $prev .= "_".$subdir;
      $TEMP{$val}=1;
   };
}

close(INPUT)
   || die "Cannot close input file: $!\n";


print OUTPUT <<END;
}
END

close (OUTPUT)
   || die "Cannot close file: $!\n";

exit 0;
