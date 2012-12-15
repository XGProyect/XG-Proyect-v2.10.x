<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2012
 */

function unset_vars ( $prefix )
{
	$vars = array_keys ( $GLOBALS );

	for( $n = 0, $i = 0;  $i < count($vars);  $i ++ )
	{
		if ( strpos ( $vars[$i] , $prefix ) === 0 )
		{
			unset ( $GLOBALS[$vars[$i]] );

			$n ++;
		}
	}

	return  $n;
}

function read_config ( $config_name = '' , $all = FALSE )
{
	$configs		= new xml ( 'config.xml' );

	if ( $all )
	{
		return $configs->get_configs ();
	}
	else
	{
		return $configs->get_config ( $config_name );
	}

}

function update_config ( $config_name, $config_value )
{
	$configs		= new xml ( 'config.xml' );

	$configs->write_config ( $config_name , $config_value );
}

function is_email($email)
{
	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

function message ($mes, $dest = "", $time = "3", $topnav = FALSE, $menu = TRUE)
{
	$parse['mes']   = $mes;

	$page .= parsetemplate(gettemplate('general/message_body'), $parse);

	if (!defined('IN_ADMIN'))
	{
		display ($page, $topnav, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"$time;URL=$dest\">" : ""), FALSE, $menu);
	}
	else
	{
		display ($page, $topnav, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"$time;URL=$dest\">" : ""), TRUE, FALSE);
	}

}

function display ($page, $topnav = TRUE, $metatags = '', $AdminPage = FALSE, $menu = TRUE)
{
	global $link, $debug, $user, $planetrow;

	if (!$AdminPage)
		$DisplayPage  = StdUserHeader($metatags);
	else
		$DisplayPage  = AdminUserHeader($metatags);

	if ($topnav)
	{
		include_once(XGP_ROOT . 'includes/functions/ShowTopNavigationBar.php');
		$DisplayPage .= ShowTopNavigationBar( $user, $planetrow );
	}

	if ($menu && !$AdminPage)
	{
		include_once(XGP_ROOT . 'includes/functions/ShowLeftMenu.php');
		$DisplayPage .= ShowLeftMenu ($user);
	}

	$DisplayPage .= "\n<center>\n". $page ."\n</center>\n";

	if(!defined('LOGIN') && $_GET['page'] != 'galaxy')
		$DisplayPage .= parsetemplate(gettemplate('general/footer'), $parse);

	if ($link)
	{
		mysql_close($link);
	}

	if ( $user['authlevel'] == 3 && read_config ( 'debug' ) == 1 && !$AdminPage )
	{
		// Convertir a objeto dom
		$DisplayPage = str_get_html($DisplayPage);

		// Modificar div#content
		$content = $DisplayPage->find("div#content", 0);

		// Contenido debug
		$content->innertext .= $debug->echo_log();
	}

	echo $DisplayPage;

	if ( $user['authlevel'] == 3 && read_config ( 'debug' ) == 1 && $AdminPage)
	{

		echo "<center>";
		echo $debug->echo_log();
		echo "</center>";
	}

	die();
}

function StdUserHeader ($metatags = '')
{
	$parse['-title-'] 	.= read_config ( 'game_name' );
	$parse['-favi-']	.= "<link rel=\"shortcut icon\" href=\"./favicon.ico\">\n";
	$parse['-meta-']	.= "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\">\n";
	$parse['-meta-']	.= "<meta name=\"generator\" content=\"XG Proyect " . VERSION . "\" />\n";

	if(!defined('LOGIN'))
	{
		$parse['-style-']  	.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/css/default.css\">\n";
		$parse['-style-']  	.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/css/formate.css\">\n";
		$parse['-style-'] 	.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"". DPATH ."formate.css\" />\n";
		$parse['-meta-']	.= "<script type=\"text/javascript\" src=\"js/overlib-min.js\"></script>\n";
	}
	else
	{
		$parse['-style-']  	.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/css/styles.css\">\n";
	}

	$parse['-meta-']	.= ($metatags) ? $metatags : "";

	return parsetemplate(gettemplate('general/simple_header'), $parse);
}

function AdminUserHeader ($metatags = '')
{
	if (!defined('IN_ADMIN'))
	{
		$parse['-title-'] 	.= 	"XG Proyect - Install";
	}
	else
	{
		$parse['-title-'] 	.= 	read_config ( 'game_name' ) . " - Admin CP";
	}


	$parse['-favi-']	.= 	"<link rel=\"shortcut icon\" href=\"./../favicon.ico\">\n";
	$parse['-style-']	.=	"<link rel=\"stylesheet\" type=\"text/css\" href=\"./../styles/css/admin.css\">\n";
	$parse['-meta-']	.= 	"<script type=\"text/javascript\" src=\"./../js/overlib-min.js\"></script>\n";
	$parse['-meta-'] 	.= ($metatags) ? $metatags : "";

	return parsetemplate(gettemplate('adm/simple_header'), $parse);
}

function CalculateMaxPlanetFields (&$planet)
{
	global $resource;
	return $planet["field_max"] + ($planet[ $resource[33] ] * FIELDS_BY_TERRAFORMER);
}

function GetTargetDistance ($OrigGalaxy, $DestGalaxy, $OrigSystem, $DestSystem, $OrigPlanet, $DestPlanet)
{
	$distance = 0;

	if (($OrigGalaxy - $DestGalaxy) != 0)
		$distance = abs($OrigGalaxy - $DestGalaxy) * 20000;
	elseif (($OrigSystem - $DestSystem) != 0)
		$distance = abs($OrigSystem - $DestSystem) * 5 * 19 + 2700;
	elseif (($OrigPlanet - $DestPlanet) != 0)
		$distance = abs($OrigPlanet - $DestPlanet) * 5 + 1000;
	else
		$distance = 5;

	return $distance;
}

function GetMissionDuration ($GameSpeed, $MaxFleetSpeed, $Distance, $SpeedFactor)
{
	$Duration = 0;
	$Duration = round(((35000 / $GameSpeed * sqrt($Distance * 10 / $MaxFleetSpeed) + 10) / $SpeedFactor));
	return $Duration;
}

function GetGameSpeedFactor ()
{
	return read_config ( 'fleet_speed' ) / 2500;
}

function GetFleetMaxSpeed ($FleetArray, $Fleet, $Player)
{
	global $reslist, $pricelist;

	if ($Fleet != 0)
		$FleetArray[$Fleet] =  1;

	foreach ($FleetArray as $Ship => $Count)
	{
		if ($Ship == 202)
		{
			if ($Player['impulse_motor_tech'] >= 5)
				$speedalls[$Ship] = $pricelist[$Ship]['speed2'] + (($pricelist[$Ship]['speed'] * $Player['impulse_motor_tech']) * 0.2);
			else
				$speedalls[$Ship] = $pricelist[$Ship]['speed']  + (($pricelist[$Ship]['speed'] * $Player['combustion_tech']) * 0.1);
		}
		if ($Ship == 203 or $Ship == 204 or $Ship == 209 or $Ship == 210)
			$speedalls[$Ship] = $pricelist[$Ship]['speed'] + (($pricelist[$Ship]['speed'] * $Player['combustion_tech']) * 0.1);

		if ($Ship == 205 or $Ship == 206 or $Ship == 208)
			$speedalls[$Ship] = $pricelist[$Ship]['speed'] + (($pricelist[$Ship]['speed'] * $Player['impulse_motor_tech']) * 0.2);

		if ($Ship == 211)
		{
			if ($Player['hyperspace_motor_tech'] >= 8)
				$speedalls[$Ship] = $pricelist[$Ship]['speed2'] + (($pricelist[$Ship]['speed'] * $Player['hyperspace_motor_tech']) * 0.3);
			else
				$speedalls[$Ship] = $pricelist[$Ship]['speed']  + (($pricelist[$Ship]['speed'] * $Player['impulse_motor_tech']) * 0.2);
		}

		if ($Ship == 207 or $Ship == 213 or $Ship == 214 or $Ship == 215)
			$speedalls[$Ship] = $pricelist[$Ship]['speed'] + (($pricelist[$Ship]['speed'] * $Player['hyperspace_motor_tech']) * 0.3);
	}

	if ($Fleet != 0)
	{
		$ShipSpeed = $speedalls[$Ship];
		$speedalls = $ShipSpeed;
	}

	return $speedalls;
}

function GetShipConsumption ( $Ship, $Player )
{
	global $pricelist;

	if ($Player['impulse_motor_tech'] >= 5)
		$Consumption  = $pricelist[$Ship]['consumption2'];
	else
		$Consumption  = $pricelist[$Ship]['consumption'];

	return $Consumption;
}

function GetFleetConsumption ($FleetArray, $SpeedFactor, $MissionDuration, $MissionDistance, $FleetMaxSpeed, $Player)
{
	$consumption = 0;
	$basicConsumption = 0;

	foreach ($FleetArray as $Ship => $Count)
	{
		if ($Ship > 0)
		{
			$ShipSpeed         = GetFleetMaxSpeed ( "", $Ship, $Player );
			$ShipConsumption   = GetShipConsumption ( $Ship, $Player );
			$spd               = 35000 / ($MissionDuration * $SpeedFactor - 10) * sqrt( $MissionDistance * 10 / $ShipSpeed );
			$basicConsumption  = $ShipConsumption * $Count;
			$consumption      += $basicConsumption * $MissionDistance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
		}
	}

	$consumption = round($consumption) + 1;

	return $consumption;
}

function pretty_time ($seconds)
{
	$day = floor($seconds / (24 * 3600));
	$hs = floor($seconds / 3600 % 24);
	$ms = floor($seconds / 60 % 60);
	$sr = floor($seconds / 1 % 60);

	if ($hs < 10) { $hh = "0" . $hs; } else { $hh = $hs; }
	if ($ms < 10) { $mm = "0" . $ms; } else { $mm = $ms; }
	if ($sr < 10) { $ss = "0" . $sr; } else { $ss = $sr; }

	$time = '';
	if ($day != 0) { $time .= $day . 'd '; }
	if ($hs  != 0) { $time .= $hh . 'h ';  } else { $time .= '00h '; }
	if ($ms  != 0) { $time .= $mm . 'm ';  } else { $time .= '00m '; }
	$time .= $ss . 's';

	return $time;
}

function pretty_time_hour ($seconds)
{
	$min = floor($seconds / 60 % 60);
	$time = '';
	if ($min != 0) { $time .= $min . 'min '; }
	return $time;
}

function ShowBuildTime($time)
{
	global $lang;
	return "<br>". $lang['fgf_time'] . pretty_time($time);
}

function get_max_fleets ( $computer_tech , $amiral_level )
{
	return ( 1 + $computer_tech + ( $amiral_level * AMIRAL ) );
}

function parsetemplate ( $template , $array )
{
	return preg_replace ( '#\{([a-z0-9\-_]*?)\}#Ssie' , '( ( isset($array[\'\1\']) ) ? $array[\'\1\'] : \'\' );' , $template );
}

function gettemplate ( $templatename )
{
	return @file_get_contents ( XGP_ROOT . TEMPLATE_DIR . '/' . $templatename . '.php' );
}

function includeLang ( $filename )
{
	global $lang;

	include ( XGP_ROOT . "language/" . DEFAULT_LANG ."/". $filename . '.php' );
}

function GetStartAdressLink ( $FleetRow, $FleetType )
{
	$Link  = "<a href=\"game.php?page=galaxy&mode=3&galaxy=".$FleetRow['fleet_start_galaxy']."&system=".$FleetRow['fleet_start_system']."\" ". $FleetType ." >";
	$Link .= "[".$FleetRow['fleet_start_galaxy'].":".$FleetRow['fleet_start_system'].":".$FleetRow['fleet_start_planet']."]</a>";
	return $Link;
}

function GetTargetAdressLink ( $FleetRow, $FleetType )
{
	$Link  = "<a href=\"game.php?page=galaxy&mode=3&galaxy=".$FleetRow['fleet_end_galaxy']."&system=".$FleetRow['fleet_end_system']."\" ". $FleetType ." >";
	$Link .= "[".$FleetRow['fleet_end_galaxy'].":".$FleetRow['fleet_end_system'].":".$FleetRow['fleet_end_planet']."]</a>";
	return $Link;
}

function BuildPlanetAdressLink ( $CurrentPlanet )
{
	$Link  = "<a href=\"game.php?page=galaxy&mode=3&galaxy=".$CurrentPlanet['galaxy']."&system=".$CurrentPlanet['system']."\">";
	$Link .= "[".$CurrentPlanet['galaxy'].":".$CurrentPlanet['system'].":".$CurrentPlanet['planet']."]</a>";
	return $Link;
}

function doquery($query, $table, $fetch = FALSE)
{
	global $link, $debug;

	require ( XGP_ROOT . 'config.php' );

	if(!$link)
	{
		$link = mysql_connect	(
									$dbsettings["server"],
									$dbsettings["user"],
									$dbsettings["pass"]
								) or $debug->error ( mysql_error() . "<br />$query" , "SQL Error" );

		mysql_select_db ( $dbsettings["name"] ) or $debug->error ( mysql_error() . "<br />$query" , "SQL Error" );

		echo mysql_error();
	}

	$sql 		= str_replace ( "{{table}}" , $dbsettings["prefix"] . $table , $query );
	$sqlquery 	= mysql_query ( $sql ) or $debug->error ( mysql_error() . "<br />$sql<br />" , "SQL Error" );

	unset ( $dbsettings );

	global $numqueries,$debug;
	$numqueries++;

	$debug->add ( "<tr><th>Query $numqueries: </th><th>$query</th><th>$table</th><th>$fetch</th></tr>");

	if ( $fetch )
	{
		return mysql_fetch_array ( $sqlquery );
	}
	else
	{
		return $sqlquery;
	}

}

function colorNumber($n, $s = '')
{
	if ($n > 0)
		if ($s != '')
			$s = colorGreen($s);
		else
			$s = colorGreen($n);
	elseif ($n < 0)
		if ($s != '')
			$s = colorRed($s);
		else
			$s = colorRed($n);
	else
		if ($s != '')
			$s = $s;
		else
			$s = $n;

	return $s;
}

function colorRed($n)
{
	return '<font color="#ff0000">' . $n . '</font>';
}

function colorGreen($n)
{
	return '<font color="#00ff00">' . $n . '</font>';
}

function pretty_number($n, $floor = TRUE)
{
	if ($floor)
		$n = floor($n);

	return number_format($n, 0, ",", ".");
}

function shortly_number($number)
{
	// MAS DEL TRILLON
	if ($number >= 1000000000000000000000000)
		return pretty_number(($number/1000000000000000000))."&nbsp;<font color=lime>T+</font>";

	// TRILLON
	elseif ($number >= 1000000000000000000 && $number < 1000000000000000000000000)
		return pretty_number(($number/1000000000000000000))."&nbsp;<font color=lime>T</font>";

	// BILLON
	elseif ($number >= 1000000000000 && $number < 1000000000000000000)
		return pretty_number(($number/1000000000000))."&nbsp;<font color=lime>B</font>";

	// MILLON
	elseif ($number >= 1000000 && $number < 1000000000000)
		return pretty_number(($number/1000000))."&nbsp;<font color=lime>M</font>";

	// MIL
	elseif ($number >= 1000 && $number < 1000000)
		return pretty_number(($number/1000))."&nbsp;<font color=lime>K</font>";

	// NUMERO SIN DEFINIR
	else
		return pretty_number($number);
}

function floattostring($Numeric, $Pro = 0, $Output = FALSE)
{
	return ($Output) ? str_replace(",",".", sprintf("%.".$Pro."f", $Numeric)) : sprintf("%.".$Pro."f", $Numeric);
}

function roundUp($value, $precision = 0)
{
	if ( $precision == 0 )
	{
		$precisionFactor = 1;
	}

	else
	{
		$precisionFactor = pow( 10, $precision );
	}

	return ceil( $value * $precisionFactor )/$precisionFactor;
}

?>