<?php

// configuration
$ip="192.168.10.132"; //remote ebus
$rrdtempfile='/tmp/ecotec-temperatures.rrd';
$rrdparamfile='/tmp/ecotec-params.rrd';
$counterlog='/tmp/vaillant-prenergy.log';
$ecoteclog='/tmp/vaillant-ecotec.log';


// get register using ebusctl
function getregister( $reg )
{
  global $ip;
  exec("/usr/bin/ebusctl -s $ip read -m 30 $reg", $output, $retcode);

  if($retcode==0 && substr($output[0],0,3)!="ERR" && substr($output[0],0,3)!="err" ) return($output[0]);
    else return("error");
}

$flame=getregister("flame");

// temperatures
if( $flame!="error" )
{
  list($flowtemp, $flowtempstatus) = split(";", getregister("FlowTemp") );
  list($storagetemp, $storagetempstatus) = split(";", getregister("StorageTemp") );
  $flowtempdesired = getregister("FlowTempDesired");
  list($returntemp, $rt2, $returntempstatus) = split(";", getregister("ReturnTemp") );
  list($outsidetemp, $outsidetempstatus) = split(";", getregister("OutdoorstempSensor") );
  exec("/usr/bin/rrdtool update $rrdtempfile ".time().":$flowtemp:$flowtempdesired:$returntemp:$storagetemp:$outsidetemp", $eoutput, $ecode);
  if( $ecode>0 ) { echo "RRD update problem ($rrdtempfile)\n"; print_r($eoutput); }
}

// other data
if( $flame!="error" )
{
  list($waterpressure, $waterpressurestatus) = split(";", getregister("WaterPressure") );
  $modulationtempdesired=getregister("ModulationTempDesired");
  $partloadhckw=getregister("PartloadHcKW");
  $pumppower=getregister("PumpPower");
  $remainingboilerblocktime=getregister("RemainingBoilerblocktime");
  $positionvalveset=getregister("PositionValveSet");

  exec("echo ".date("Y-m-d H:i")."\;PositionValveSet=$positionvalveset\;FlowTemp=".$flowtemp."\;FlowTempDesired=$flowtempdesired\;ModulationTempDesired=".$modulationtempdesired."\;PartloadHcKW=".$partloadhckw."\;PumpPower=".$pumppower."\;RemainingBoilerblocktime=".$remainingboilerblocktime."\;Flame=".$flame." >> $ecoteclog");

  $power=0;
  if($flame=="on") $power=$modulationtempdesired;
  exec("/usr/bin/rrdtool update $rrdparamfile ".time().":$power:$pumppower:$remainingboilerblocktime:$positionvalveset:$waterpressure", $eoutput, $ecode);
  if( $ecode>0 ) { echo "RRD update problem ($rrdparamfile)\n"; print_r($eoutput); }
  
  //energy counters
  if( date("i")=="00" )
  {
    $prenergysumhc1=getregister("PrEnergySumHc1");
    $prenergycounterhc1=getregister("PrEnergyCountHc1");
    $prenergysumhwc1=getregister("PrEnergySumHwc1");
    $prenergycounterhwc1=getregister("PrEnergyCountHwc1");
    exec("echo ".date("Y-m-d H")."\;".$prenergysumhc1."\;".$prenergysumhwc1."\;".$prenergycounterhc1."\;".$prenergycounterhwc1." >> $counterlog");
  }
}

?>
