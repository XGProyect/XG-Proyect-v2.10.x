<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2012
 */

if(!defined('INSIDE')){ die(header("location:../../"));}

class ShowPhalanxPage
{
	function __construct ( $CurrentUser , $CurrentPlanet )
	{
		global $lang;

		include_once(XGP_ROOT . 'includes/functions/InsertJavaScriptChronoApplet.php');
		include_once(XGP_ROOT . 'includes/classes/class.FlyingFleetsTable.php');
		include_once(XGP_ROOT . 'includes/classes/class.GalaxyRows.php');

		$FlyingFleetsTable 	= new FlyingFleetsTable();
		$GalaxyRows 		= new GalaxyRows();

		$parse	= $lang;

		$radar_menzil_min = $CurrentPlanet['system'] - $GalaxyRows->GetPhalanxRange ( $CurrentPlanet['phalanx'] );
		$radar_menzil_max = $CurrentPlanet['system'] + $GalaxyRows->GetPhalanxRange ( $CurrentPlanet['phalanx'] );

		if ( $radar_menzil_min < 1 )
			$radar_menzil_min = 1;

		if ( $radar_menzil_max > MAX_SYSTEM_IN_GALAXY )
			$radar_menzil_max = MAX_SYSTEM_IN_GALAXY;


		$DoScan=TRUE;

		if (intval ( $_GET["system"] ) < $radar_menzil_min or intval ( $_GET["system"] ) > $radar_menzil_max or intval ( $_GET["galaxy"] ) != $CurrentPlanet['galaxy'])
		{
			$DoScan = FALSE;
		}

		if ($CurrentPlanet['planet_type'] == 3 && $DoScan)
		{
			$parse['phl_pl_galaxy']    = $CurrentPlanet['galaxy'];
			$parse['phl_pl_system']    = $CurrentPlanet['system'];
			$parse['phl_pl_place']     = $CurrentPlanet['planet'];
			$parse['phl_pl_name']      = $CurrentUser['username'];

			if ($CurrentPlanet['deuterium'] > 10000)
			{
				doquery ("UPDATE {{table}} SET `deuterium` = `deuterium` - '10000' WHERE `id` = '". $CurrentUser['current_planet'] ."';", 'planets');
				$parse['phl_er_deuter'] = "";
				$DoScan                 = TRUE;
			}
			else
			{
				$parse['phl_er_deuter'] = $lang['px_no_deuterium'];
				$DoScan                 = FALSE;
			}

			if ($DoScan == TRUE)
			{
				$Galaxy  = intval($_GET["galaxy"]);
				$System  = intval($_GET["system"]);
				$Planet  = intval($_GET["planet"]);
				$PlType  = intval($_GET["planettype"]);

				if ( $PlType == 1 )
				{

				}
				else
				{
					die(header("Location: game.php?page=galaxy"));
				}

				$TargetInfo = doquery("SELECT * FROM {{table}} WHERE `galaxy` = '". $Galaxy ."' AND `system` = '". $System ."' AND `planet` = '". $Planet ."' AND `planet_type` = '". $PlType ."';", 'planets', TRUE);
				$TargetName = $TargetInfo['name'];

				$QryLookFleets  = "SELECT * ";
				$QryLookFleets .= "FROM {{table}} ";
				$QryLookFleets .= "WHERE ( ( ";
				$QryLookFleets .= "`fleet_start_galaxy` = '". $Galaxy ."' AND ";
				$QryLookFleets .= "`fleet_start_system` = '". $System ."' AND ";
				$QryLookFleets .= "`fleet_start_planet` = '". $Planet ."' AND ";
				$QryLookFleets .= "`fleet_start_type` = '". $PlType ."' ";
				$QryLookFleets .= ") OR ( ";
				$QryLookFleets .= "`fleet_end_galaxy` = '". $Galaxy ."' AND ";
				$QryLookFleets .= "`fleet_end_system` = '". $System ."' AND ";
				$QryLookFleets .= "`fleet_end_planet` = '". $Planet ."' AND ";
				$QryLookFleets .= "`fleet_end_type` = '". $PlType ."' ";
				$QryLookFleets .= ") ) ";
				$QryLookFleets .= "ORDER BY `fleet_start_time`;";

				$FleetToTarget  = doquery( $QryLookFleets, 'fleets' );

				if (mysql_num_rows($FleetToTarget) <> 0 )
				{
					while ($FleetRow = mysql_fetch_array($FleetToTarget))
					{
						$Record++;

						$StartTime   = $FleetRow['fleet_start_time'];
						$StayTime    = $FleetRow['fleet_end_stay'];
						$EndTime     = $FleetRow['fleet_end_time'];

						if ($FleetRow['fleet_owner'] == $TargetInfo['id_owner'])
							$FleetType = TRUE;
						else
							$FleetType = FALSE;

						$FleetRow['fleet_resource_metal']     = 0;
						$FleetRow['fleet_resource_crystal']   = 0;
						$FleetRow['fleet_resource_deuterium'] = 0;

						$Label = "fs";
						if ($StartTime > time())
							$fpage[$StartTime] = $FlyingFleetsTable->BuildFleetEventTable ( $FleetRow, 0, $FleetType, $Label, $Record );

						if ($FleetRow['fleet_mission'] <> 4)
						{
							$Label = "ft";
							if ($StayTime > time())
								$fpage[$StayTime] = $FlyingFleetsTable->BuildFleetEventTable ( $FleetRow, 1, $FleetType, $Label, $Record );

							if ($FleetType == TRUE)
							{
								$Label = "fe";
								if ($EndTime > time())
									$fpage[$EndTime]  = $FlyingFleetsTable->BuildFleetEventTable ( $FleetRow, 2, $FleetType, $Label, $Record );
							}
						}
					}
				}

				if (count($fpage) > 0)
				{
					ksort($fpage);
					foreach ($fpage as $FleetTime => $FleetContent)
					{
						$Fleets .= $FleetContent ."\n";
					}
				}
			}

			$parse['phl_fleets_table'] = $Fleets;
		}
		else
		{
			header("location:game.php?page=overview");
		}

		return display(parsetemplate(gettemplate('galaxy/phalanx_body'), $parse), FALSE, '', FALSE, FALSE);
	}
}
?>