#!/bin/sh
rrdtool create $1 --start `date +%s` DS:temperature:GAUGE:600:U:U RRA:MIN:0.5:12:1440 RRA:MAX:0.5:12:1440 RRA:AVERAGE:0.5:1:1440
