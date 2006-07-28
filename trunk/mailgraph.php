<?php
/**
$Id$

* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or (at your
* option) any later version.
**/

$rrdpath = "/usr/local/bin/rrdtool";
$rrd = "/opt1/www/noc.ipfw.org/mrtg/mailgraph.rrd";
$rrd_virus = "/opt1/www/noc.ipfw.org/mrtg/mailgraph_virus.rrd";
$host = "mail.ipfw.org";
$sversion = "mailgraph.php 1.12p2";

$wtitle = "Mail Statistics for ". $host;
$xpoints = 540;
$points_per_sample = 3;
$ypoints = 160;
$ypoints_err = 96;

if (isset($_GET['period'])) {
  switch ($_GET['period']) {
	case 'week':
		$range = 3600 * 24 * 7;
		$title = "Last week";
		break;

	case 'month':
		$range = 3600 * 24 *  30;
		$title = "Last month";
		break;

	case 'year':
		$range = 3600 * 24 * 365 * 2;
		$title = "Last 2 years";
		break;

	case 'day':
	default:
		$range = 3600 * 24 * 2;
		$title = "Last 2 days";
  }
  $step = $range*$points_per_sample/$xpoints;
}

if (isset($_GET['picture'])) {
    header("content-type: image/png");

    $rrdcommand =
	"$rrdpath graph - " .
    "--imgformat PNG " .
    "--interlaced " .
    "--title '$title' " .
    "--width $xpoints " .
    "--start -$range " .
    "--vertical-label 'msgs/min' " .
    "--lower-limit 0 " .
    "--units-exponent 0 " .
	"--lazy " .
    "--color SHADEA#ffffff " .
    "--color SHADEB#ffffff " .
    "--color BACK#ffffff ";

	if ($_GET['picture'] == 1) {
        $rrdcommand .=
        "--height $ypoints " .
        "DEF:sent=$rrd:sent:AVERAGE " .
        "DEF:msent=$rrd:sent:MAX " .
        "CDEF:rsent=sent,60,* " .
        "CDEF:rmsent=msent,60,* " .
        "CDEF:dsent=sent,UN,0,sent,IF,$step,* " .
        "CDEF:ssent=PREV,UN,dsent,PREV,IF,dsent,+ " .
        "AREA:rsent#000099:'Sent    ' " .
        "GPRINT:ssent:MAX:'total\: %8.0lf msgs' " .
        "GPRINT:rsent:AVERAGE:'avg\: %5.2lf msgs/min' " .
        "GPRINT:rmsent:MAX:'max\: %4.0lf msgs/min\l' " .
    
        "DEF:recv=$rrd:recv:AVERAGE " .
        "DEF:mrecv=$rrd:recv:MAX " .
        "CDEF:rrecv=recv,60,* " .
        "CDEF:rmrecv=mrecv,60,* " .
        "CDEF:drecv=recv,UN,0,recv,IF,$step,* " .
        "CDEF:srecv=PREV,UN,drecv,PREV,IF,drecv,+ " .
        "LINE1.5:rrecv#009900:Received " .
        "GPRINT:srecv:MAX:'total\: %8.0lf msgs' " .
        "GPRINT:rrecv:AVERAGE:'avg\: %5.2lf msgs/min' " .
        "GPRINT:rmrecv:MAX:'max\: %4.0lf msgs/min\l' " .

        "DEF:rejected=$rrd:rejected:AVERAGE " .
        "DEF:mrejected=$rrd:rejected:MAX " .
        "CDEF:rrejected=rejected,60,* " .
        "CDEF:drejected=rejected,UN,0,rejected,IF,$step,* " .
        "CDEF:srejected=PREV,UN,drejected,PREV,IF,drejected,+ " .
        "CDEF:rmrejected=mrejected,60,* " .
        "LINE1.5:rrejected#AA0000:Rejected " .
        "GPRINT:srejected:MAX:'total\: %8.0lf msgs' " .
        "GPRINT:rrejected:AVERAGE:'avg\: %5.2lf msgs/min' " .
        "GPRINT:rmrejected:MAX:'max\: %4.0lf msgs/min\l' " .
    
        "COMMENT:' \s' " .
        "COMMENT:'Last updated\: " . date ("F d, Y H\\\:i\\\:s", filemtime($rrd)) . "\c'";
        passthru($rrdcommand);
    } elseif ($_GET['picture'] == 2) {
    $rrdcommand .=
    "--height $ypoints_err " .
    "DEF:bounced=$rrd:bounced:AVERAGE " .
    "DEF:mbounced=$rrd:bounced:MAX " .
    "CDEF:rbounced=bounced,60,* " .
    "CDEF:dbounced=bounced,UN,0,bounced,IF,$step,* " .
    "CDEF:sbounced=PREV,UN,dbounced,PREV,IF,dbounced,+ " .
    "CDEF:rmbounced=mbounced,60,* " .
    "AREA:rbounced#000000:'Bounced ' " .
    "GPRINT:sbounced:MAX:'total\: %8.0lf msgs' " .
    "GPRINT:rbounced:AVERAGE:'avg\: %5.2lf msgs/min' " .
    "GPRINT:rmbounced:MAX:'max\: %4.0lf msgs/min\l' " .

    "DEF:virus=$rrd_virus:virus:AVERAGE " .
    "DEF:mvirus=$rrd_virus:virus:MAX " .
    "CDEF:rvirus=virus,60,* " .
    "CDEF:dvirus=virus,UN,0,virus,IF,$step,* " .
    "CDEF:svirus=PREV,UN,dvirus,PREV,IF,dvirus,+ " .
    "CDEF:rmvirus=mvirus,60,* " .
    "STACK:rvirus#DDBB00:'Viruses ' " .
    "GPRINT:svirus:MAX:'total\: %8.0lf msgs' " .
    "GPRINT:rvirus:AVERAGE:'avg\: %5.2lf msgs/min' " .
    "GPRINT:rmvirus:MAX:'max\: %4.0lf msgs/min\l' " .

    "DEF:spam=$rrd_virus:spam:AVERAGE " .
    "DEF:mspam=$rrd_virus:spam:MAX " .
    "CDEF:rspam=spam,60,* " .
    "CDEF:dspam=spam,UN,0,spam,IF,$step,* " .
    "CDEF:sspam=PREV,UN,dspam,PREV,IF,dspam,+ " .
    "CDEF:rmspam=mspam,60,* " .
    "STACK:rspam#999999:'Spam    ' " .
    "GPRINT:sspam:MAX:'total\: %8.0lf msgs' " .
    "GPRINT:rspam:AVERAGE:'avg\: %5.2lf msgs/min' " .
    "GPRINT:rmspam:MAX:'max\: %4.0lf msgs/min\l' " .

    "COMMENT:' \s' " .
    "COMMENT:'Last updated\: " . date ("F d, Y H\\\\:i\\\\:s", filemtime($rrd)) . "\c'";
    passthru($rrdcommand);
    }
} else {
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<title>$wtitle</title>\n";
    echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"300\">\n";
    echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";

    echo "<LINK REL=\"stylesheet\" href=\"mailgraph.css\" type=\"text/css\">\n";

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>$wtitle</h1>\n";

	echo "<h2>Day Graphs</h2>\n";
    echo "<img width=\"637\" height=\"287\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=day&amp;picture=1\" alt=\"Daily\"><BR><BR>\n";
    echo "<img width=\"637\" height=\"223\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=day&amp;picture=2\" alt=\"Daily\"><BR>\n";
    
	echo "<h2>Week Graphs</h2>\n";
    echo "<img width=\"637\" height=\"287\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=week&amp;picture=1\" alt=\"Weekly\"><BR><BR>\n";
    echo "<img width=\"637\" height=\"223\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=week&amp;picture=2\" alt=\"Daily\"><BR>\n";
    
	echo "<h2>Month Graphs</h2>\n";
    echo "<img width=\"637\" height=\"287\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=month&amp;picture=1\" alt=\"Monthy\"><BR><BR>\n";
    echo "<img width=\"637\" height=\"223\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=month&amp;picture=2\" alt=\"Daily\"><BR>\n";
    
	echo "<h2>Year Graphs</h2>\n";
    echo "<img width=\"637\" height=\"287\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=year&amp;picture=1\" alt=\"Yearly\"><BR><BR>\n";
    echo "<img width=\"637\" height=\"223\" src=\"" . $_SERVER['SCRIPT_NAME'] ."?&amp;period=year&amp;picture=2\" alt=\"Daily\"><BR><BR>\n";
    
    echo "<hr>\n";
    echo "<div class=\"version\">\n";
    echo "<a href=\"http://people.ee.ethz.ch/~oetiker/webtools/rrdtool/\"><img alt=\"RRDtool\" border=\"0\" width=\"120\" height=\"34\" src=\"http://people.ee.ethz.ch/~oetiker/webtools/rrdtool/.pics/rrdtool.gif\"></a>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$sversion by <a href=\"mailto:webbie <webbie@ipfw.org>\">webbie</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
}
?>
