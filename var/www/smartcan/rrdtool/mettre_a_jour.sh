#!/bin/bash
rrdtool graph $1.png -a PNG -w 400 -h 150 -s N-86400 --title=$1 --vertical-label "Degré celsius" DEF:probe1=$1.rrd:temperature:AVERAGE 'LINE1:probe1#ff0000:Température\j' 'GPRINT:probe1:LAST:Dernière température\: %2.1lf C'
