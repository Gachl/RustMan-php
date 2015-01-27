#!/bin/bash

curdir=`pwd`
cd /path/to/log/dir/
for file in chat_log*;
do
	sed -e 's/^\(..:..:..\)\t\(.*\)\t\(.*\)$/[\1] <\2> \3/' $file > pisg/fmt3_$file
	sed -e 's/\[IRC\] //' pisg/fmt3_$file > pisg/fmt2_$file
	sed -e 's/\[color #......\]//g' pisg/fmt2_$file > pisg/fmt_$file
	rm pisg/fmt3_$file
	rm pisg/fmt2_$file
done
cd /path/to/pisg.cfg/dir/
pisg -cf pisg.cfg
cd /path/to/pisg/output/dir/
for file in fmt*;
do
	rm $file
done
cd $curdir
