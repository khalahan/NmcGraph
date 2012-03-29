<?php

function load_nmc_data($MyData, $date = "2011-06-09", $type="daily", &$minscale, &$maxscale, $interval="1-hour" )
{
//namecoin_2011-06-08.csv  namecoin_2011-06-09.csv
//array(5) { [0]=> string(3) "195"
//           [1]=> string(3) "buy"
//           [2]=> string(19) "10.0000000000000000"
//           [3]=> string(22) "0.00723001000000000000"
//           [4]=> string(26) "2011-06-09 00:27:19.698726" } 
//array(5) { [0]=> string(3) "199" [1]=> string(4) "sell" [2]=> string(18) "3.0000000000000000" [3]=> string(22) "0.00690000000000000000" [4]=> string(26) "2011-06-09 01:12:29.363403" }

//echo $type;
//exit;
//printf ("avant load");
$filename = "../data/$type/namecoin_$date.csv";
//printf("filename : $filename <br />\n"); exit;
if ( file_exists($filename ) )
{ $csvfile=fopen ($filename,"r") or die("can't open file"); }
else {echo "wrong date : ".$date; exit;}
//echo $type;exit;
/*
if ( $type == "daily" )   loadfile   ($MyData, $csvfile, $date, $minscale, $maxscale, $interval);
if ( $type == "weekly" )  loadfile   ($MyData, $csvfile, $date, $minscale, $maxscale, $interval);
if ( $type == "monthly" ) loadfile   ($MyData, $csvfile, $date, $minscale, $maxscale, $interval);
if ( $type == "yearly" )  loadfile   ($MyData, $csvfile, $date, $minscale, $maxscale, $interval);
*/
//printf("$MyData, $csvfile, $date, $minscale, $maxscale, $interval");
loadfile   ($MyData, $csvfile, $date, $minscale, $maxscale, $interval);
fclose($csvfile);
//var_dump($MyData);exit;
//printf ("apres load");
//printf("minscale: $minscale maxscale: $maxscale <br />\n");


//printf("minscale: $minscale maxscale: $maxscale <br />\n");

//var_dump($rawdata);

/*
if ( $type == "hourly" )
{
 $MyData->addPoints(array("01","02","03","04","05", "06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24"),"Time");
}
*/
}

function loadfile(&$MyData, &$csvfile, $date, &$minscale, &$maxscale, $interval)
{
 $maxline = 100;
 $openlist = array(); $closelist= array(); $minlist= array(); $maxlist= array();$abcissalist= array();
 $rawline="";$tradeday="";$tradehour="";$open=0; $close=0;$min=0;$max=0;
 $currentperiod="";$maxperiod="";$firsthour="";$firstday="";

 $firsthour="01";
 $firstday="01";
 $firsmonth="01";
 $firstweek="0";

 if ($interval == "1-day" )
 {
  $firstperiod= $currentperiod = $firstday;
  $month=substr($date,5,2);
  $year =substr($date,0,4);
  $maxperiod= cal_days_in_month (CAL_GREGORIAN, $month, $year  );
 }
 if ( $interval == "1-hour" )
 {
  $firstperiod= $currentperiod = $firsthour;
  $maxperiod="24";
 }
 if($interval == "1-month" )
 {
  $firstperiod= $currentperiod = $firsmonth;
  $maxperiod= "12";
 }
 if($interval == "1-week" )
 {
   $firstperiod= $currentperiod = $firstweek;
   $year =substr($date,0,4);
   $maxperiod= date("W", mktime(0,0,0,12,28,$year));
   //echo $maxperiod;exit;
 }

 $newday=1;

 //load first line
 $rawline= fgetcsv( $csvfile, $maxline );
 $tradedate = $rawline[4];
 $tradevalue = round($rawline[3],4 );
 $tradehour = substr($tradedate, 11,2);
 $tradeday = substr($tradedate, 8,2);
 $trademonth    = substr($tradedate, 5,2);
 $tradeyear     = substr($tradedate,0,4);
 $dateforeweek  = "$tradeday-$trademonth-$tradeyear";
 $timestampweek = strtotime("$tradeday-$trademonth-$tradeyear");
 $tradeweek     = date("W",$timestampweek);
 if ( $tradeweek == 52 )  $tradeweek=0;
 $open=$tradevalue; $close=$tradevalue;
 $min=$tradevalue; $cmax=$tradevalue;
 //$min=$tradevalue;$max=$tradevalue;
 $lastperiod = $currentperiod -1;

 //printf("date : $date interval : $interval firsthour : $firsthour firstday: $firstday <br />\n");


 do
 {
  $tradevalue = round($rawline[3],4 );
  $tradedate = $rawline[4];
  $tradehour = substr($tradedate, 11,2);
  $tradeday = substr($tradedate, 8,2);
  //printf("$tradedate");exit;
  $trademonth    = substr($tradedate, 5,2);
  $tradeyear     = substr($tradedate,0,4);
  $dateforeweek  = "$tradeday-$trademonth-$tradeyear";
  $timestampweek = strtotime("$tradeday-$trademonth-$tradeyear");
  $tradeweek     = date("W",$timestampweek);
 if ( $tradeweek == 52 )  $tradeweek=0;
 
  if ( $interval == "1-hour" ) $currentperiod = $tradehour;
  if ( $interval == "1-day"  ) $currentperiod = $tradeday; 
  if ( $interval == "1-month"  ) $currentperiod = $trademonth; 
  if ( $interval == "1-week"  ) $currentperiod = $tradeweek;

  if ( $min == 0  ) $min = $tradevalue; 
 //$currentperiod =
 
  //printf(" currentperiod : $currentperiod tradedate : $tradedate tradevalue : $tradevalue tradehour : $tradehour tradeday : $tradeday lastperiod : $lastperiod currentperiod : $currentperiod dwwek: $dateforeweek tweek: $tradeweek<br />\n");
  //special case, first interval is empty
  $emptyintervals = $currentperiod - $lastperiod ;
  if ( $emptyintervals > 1 )
  {
   
   //printf("current : $currentperiod last : $lastperiod  empty intervals : $emptyintervals <br />\n");// exit;
   //printf(" empty intervals : $emptyintervals<br />\n");
   $i=1;
   //filling data with empty intervals
   while( $i < $emptyintervals )
   {
   //printf("push empty<br />\n");
   $openlist[]=null; $closelist[]=null;$minlist[]=null;$maxlist[]=null;$i++;
   $abcissalist[]=$lastperiod;
   $lastperiod +=1;
   }
  }
//  else
//  {
   //updating vars
   if ( $min == 0 ) $min = $tradevalue;

   if ( $tradevalue < $min ) $min = $tradevalue;
   if ( $tradevalue > $max ) $max = $tradevalue;
   $close = $tradevalue;
   if ( $currentperiod != $lastperiod  && $currentperiod <= $maxperiod )
   { 
    //printf("WRITING $lastperiod : open : $open close : $close min : $min max: $max <br />\n");
    //interval ended, storing data
    //printf("push array<br />\n");
    $openlist[]=round($open,4);
    $closelist[]=round($close,4);
    $minlist[]=round($min,4);
    $maxlist[]=round($max,4);
    $abcissalist[]=$currentperiod;
     $open=$tradevalue; $min=$tradevalue; $max=$tradevalue; $close=$tradevalue;
   }
//  }
  $lastperiod = $currentperiod;
  //updating graph scale
  if ( $minscale == null ) $minscale = $min;
  else if ( $min < $minscale )  $minscale=$min ;
  if ( $maxscale == null ) $maxscale = $max;
  else if ( $max > $maxscale )  $maxscale=$max ;
 }while ( $rawline= fgetcsv( $csvfile, $maxline ));

 if ( $currentperiod <= $maxperiod  && $lastperiod >0)
 {
  //no more lines, still one interval to write
  //printf("WRITING $lastperiod : open : $open close : $close min : $min max: $max <br />\n");
  $openlist[]=round($open,4);
  $closelist[]=round($close,4);
  $minlist[]=round($min,4);
  $maxlist[]=round($max,4);
  $abcissalist[]=$currentperiod;
 }
 //what if there are still empty intervals to reach max interval ? 
 //printf("current : $currentperiod max : $maxperiod ");exit;
 if ( $currentperiod < $maxperiod  && $currentperiod > 0 )
 {
  //echo "more intervals"; exit;
  $emptyintervals = $maxperiod - $currentperiod ;
  $i=1;
  //filling data with empty intervals
  while( $i < $emptyintervals )
  { //printf("push empty<br />\n");
   $openlist[]=null; $closelist[]=null;$minlist[]=null;$maxlist[]=null;$i++;
   $abcissalist[]=$currentperiod;
   $currentperiod +=1;
  }
 }
//no more lines in file
 $MyData->addPoints($openlist,"Open");
 $MyData->addPoints($closelist,"Close");
 $MyData->addPoints($minlist,"Min");
 $MyData->addPoints($maxlist,"Max");
 $MyData->addPoints($abcissalist,"Weeks");

 
 if ( $minscale >0 ) $minscale *=0.95;
 else $minscale = 0;
 $maxscale *=1.05;
 $minscale = round($minscale, 4, PHP_ROUND_HALF_DOWN );
 $maxscale = round($maxscale, 4, PHP_ROUND_HALF_UP );

//exit;
}

?>