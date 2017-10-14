#!/bin/bash

#Drawing graphs from collected data

TEMPS=/tmp/ecotec-temperatures.rrd
PARAMS=/tmp/ecotec-params.rrd
NOW=`date -u +%s`
PNG=/tmp

cd $PNG

rrdtool graph $PNG/temp_ecotec.png --lazy -a PNG -n TITLE:12 -t 'Vaillant ecoTEC - temperatures' \
-A -r -w 600 -h 250 --vertical-label 'Celsius degrees' --end now -s end-6h --slope-mode \
DEF:c=$TEMPS:outsidetemp:AVERAGE LINE1:c#0000ff:Outside \
'GPRINT:c:LAST:%2.1lfC' \
DEF:o=$TEMPS:storagetemp:AVERAGE LINE1:o#32B1DB:Hot_water \
'GPRINT:o:LAST:%2.1lfC \j' \
DEF:p=$TEMPS:returntemp:AVERAGE LINE1:p#00bf00:return \
'GPRINT:p:LAST:%2.1lfC' \
DEF:z=$TEMPS:flowtempdesired:AVERAGE LINE1:z#ffaa22:Heating_desired \
'GPRINT:z:LAST:%2.1lfC' \
DEF:x=$TEMPS:flowtemp:AVERAGE LINE1:x#ff0000:Flow \
'GPRINT:x:LAST:%2.1lfC \j' ;

rrdtool graph $PNG/param_ecotec.png --lazy -a PNG -n TITLE:12 -t 'Vaillant ecoTEC - params' \
-A -r -w 600 -h 250 --vertical-label 'percents, minutes' --end now -s end-6h --slope-mode \
DEF:c=$PARAMS:power:AVERAGE LINE1:c#ff0000:Power \
'GPRINT:c:LAST:%2.1lf' \
DEF:o=$PARAMS:pumppower:AVERAGE LINE1:o#32B1DB:Pump_power \
'GPRINT:o:LAST:%2.1lf \j' \
DEF:z=$PARAMS:valve:AVERAGE LINE1:z#9900aa:Valve_position \
'GPRINT:z:LAST:%2.1lf' \
DEF:b=$PARAMS:blocktime:AVERAGE LINE1:b#ff9900:Block_time \
'GPRINT:b:LAST:%2.0lfm \j' ;

