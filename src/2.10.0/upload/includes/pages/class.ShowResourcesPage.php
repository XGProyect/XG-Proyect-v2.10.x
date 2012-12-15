<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2012
 */

if(!defined('INSIDE')){ die(header("location:../../"));}

class ShowResourcesPage
{
	function __construct ( $CurrentUser , $CurrentPlanet )
	{
		global $lang, $ProdGrid, $resource, $reslist;

		$parse 							= 	$lang;
		$game_metal_basic_income		=	read_config ( 'metal_basic_income' );
		$game_crystal_basic_income		=	read_config ( 'crystal_basic_income' );
		$game_deuterium_basic_income	=	read_config ( 'deuterium_basic_income' );
		$game_energy_basic_income		=	read_config ( 'energy_basic_income' );
		$game_resource_multiplier		=	read_config ( 'resource_multiplier' );

		if ($CurrentPlanet['planet_type'] == 3)
		{
			$game_metal_basic_income    	= 0;
			$game_crystal_basic_income   	= 0;
			$game_deuterium_basic_income 	= 0;
		}

		$ValidList['percent'] = array (  0,  10,  20,  30,  40,  50,  60,  70,  80,  90, 100 );
		$SubQry               = "";

		if ($_POST)
		{
			foreach($_POST as $Field => $Value)
			{
				$FieldName = $Field."_porcent";
				if ( isset( $CurrentPlanet[ $FieldName ] ) )
				{
					if ( ! in_array( $Value, $ValidList['percent']) )
					{
						header("Location: game.php?page=ressources");
						exit;
					}

					$Value                        = $Value / 10;
					$CurrentPlanet[ $FieldName ]  = $Value;
					$SubQry                      .= ", `".$FieldName."` = '".$Value."'";
				}
			}
		}

		$parse['production_level'] = 100;
		if       ($CurrentPlanet['energy_max'] == 0 && $CurrentPlanet['energy_used'] > 0)
		{
			$post_porcent = 0;
		}
		elseif ($CurrentPlanet['energy_max'] >  0 && ($CurrentPlanet['energy_used'] + $CurrentPlanet['energy_max']) < 0 )
		{
			$post_porcent = floor(($CurrentPlanet['energy_max']) / ($CurrentPlanet['energy_used']*-1) * 100);
		}
		else
		{
			$post_porcent = 100;
		}

		if ($post_porcent > 100)
		{
			$post_porcent = 100;
		}

		$CurrentPlanet['metal_max']		=	(BASE_STORAGE_SIZE + 50000 * (roundUp(pow(1.6,$CurrentPlanet[ $resource[22] ])) -1));
		$CurrentPlanet['crystal_max']	=	(BASE_STORAGE_SIZE + 50000 * (roundUp(pow(1.6,$CurrentPlanet[ $resource[23] ])) -1));
		$CurrentPlanet['deuterium_max']	=	(BASE_STORAGE_SIZE + 50000 * (roundUp(pow(1.6,$CurrentPlanet[ $resource[24] ])) -1));

		$parse['resource_row']               = "";
		$CurrentPlanet['metal_perhour']      = 0;
		$CurrentPlanet['crystal_perhour']    = 0;
		$CurrentPlanet['deuterium_perhour']  = 0;
		$CurrentPlanet['energy_max']         = 0;
		$CurrentPlanet['energy_used']        = 0;
		$BuildTemp                           = $CurrentPlanet[ 'temp_max' ];
		$ResourcesRowTPL=gettemplate('resources/resources_row');
		foreach($reslist['prod'] as $ProdID)
		{
			if ($CurrentPlanet[$resource[$ProdID]] > 0 && isset($ProdGrid[$ProdID]))
			{
				$BuildLevelFactor                    = $CurrentPlanet[ $resource[$ProdID]."_porcent" ];
				$BuildLevel                          = $CurrentPlanet[ $resource[$ProdID] ];
				$metal     = floor( eval ( $ProdGrid[$ProdID]['formule']['metal']     ) * ( $game_resource_multiplier ) * ( 1 + ( $CurrentUser['rpg_geologue']  * GEOLOGUE ) ) );
				$crystal   = floor( eval ( $ProdGrid[$ProdID]['formule']['crystal']   ) * ( $game_resource_multiplier ) * ( 1 + ( $CurrentUser['rpg_geologue']  * GEOLOGUE ) ) );
				$deuterium = floor( eval ( $ProdGrid[$ProdID]['formule']['deuterium'] ) * ( $game_resource_multiplier ) * ( 1 + ( $CurrentUser['rpg_geologue']  * GEOLOGUE ) ) );

				if( $ProdID >= 4 )
				{
					$energy = floor( eval ( $ProdGrid[$ProdID]['formule']['energy']    ) * ( $game_resource_multiplier ) * ( 1 + ( $CurrentUser['rpg_ingenieur'] * ENGINEER_ENERGY ) ) );
				}
				else $energy = floor( eval ( $ProdGrid[$ProdID]['formule']['energy']    ) * ( $game_resource_multiplier ) );
				if ($energy > 0)
				{
					$CurrentPlanet['energy_max']    += $energy;
				}
				else
				{
					$CurrentPlanet['energy_used']   += $energy;
				}

				$CurrentPlanet['metal_perhour']     += $metal;
				$CurrentPlanet['crystal_perhour']   += $crystal;
				$CurrentPlanet['deuterium_perhour'] += $deuterium;

				$metal                               = $metal     * 0.01 * $post_porcent;
				$crystal                             = $crystal   * 0.01 * $post_porcent;
				$deuterium                           = $deuterium * 0.01 * $post_porcent;
				$energy                              = $energy    * 0.01 * $post_porcent;
				$Field                               = $resource[$ProdID] ."_porcent";
				$CurrRow                             = array();
				$CurrRow['name']                     = $resource[$ProdID];
				$CurrRow['porcent']                  = $CurrentPlanet[$Field];

				for ($Option = 10; $Option >= 0; $Option--)
				{
					$OptValue = $Option * 10;

					if ($Option == $CurrRow['porcent'])
					{
						$OptSelected    = " selected=selected";
					}
					else
					{
						$OptSelected    = "";
					}
					$CurrRow['option'] .= "<option value=\"".$OptValue."\"".$OptSelected.">".$OptValue."%</option>";
				}

				$CurrRow['type']                     = $lang['tech'][$ProdID];
				$CurrRow['level']                    = ($ProdID > 200) ? $lang['rs_amount'] : $lang['rs_lvl'];
				$CurrRow['level_type']               = $CurrentPlanet[ $resource[$ProdID] ];
				$CurrRow['metal_type']               = pretty_number ( $metal     );
				$CurrRow['crystal_type']             = pretty_number ( $crystal   );
				$CurrRow['deuterium_type']           = pretty_number ( $deuterium );
				$CurrRow['energy_type']              = pretty_number ( $energy    );
				$CurrRow['metal_type']               = colorNumber ( $CurrRow['metal_type']     );
				$CurrRow['crystal_type']             = colorNumber ( $CurrRow['crystal_type']   );
				$CurrRow['deuterium_type']           = colorNumber ( $CurrRow['deuterium_type'] );
				$CurrRow['energy_type']              = colorNumber ( $CurrRow['energy_type']    );
				$parse['resource_row']              .= parsetemplate ($ResourcesRowTPL , $CurrRow );
			}
		}

		$parse['Production_of_resources_in_the_planet'] = str_replace('%s', $CurrentPlanet['name'], $lang['rs_production_on_planet']);

		if ($CurrentPlanet['energy_max'] == 0 && $CurrentPlanet['energy_used'] > 0)
		{
			$parse['production_level'] = 0;
		}
		elseif ($CurrentPlanet['energy_max']  > 0 && abs($CurrentPlanet['energy_used']) > $CurrentPlanet['energy_max'])
		{
			$parse['production_level'] = floor(($CurrentPlanet['energy_max']) / ($CurrentPlanet['energy_used']*-1) * 100);
		}
		elseif ($CurrentPlanet['energy_max'] == 0 && abs($CurrentPlanet['energy_used']) > $CurrentPlanet['energy_max'])
		{
			$parse['production_level'] = 0;
		}
		else
		{
			$parse['production_level'] = 100;
		}

		if ($parse['production_level'] > 100)
		{
			$parse['production_level'] = 100;
		}

		$parse['metal_basic_income']     = $game_metal_basic_income;
		$parse['crystal_basic_income']   = $game_crystal_basic_income;
		$parse['deuterium_basic_income'] = $game_deuterium_basic_income;
		$parse['energy_basic_income']    = $game_energy_basic_income;

		if ($CurrentPlanet['metal_max'] < $CurrentPlanet['metal'])
		{
			$parse['metal_max']         = "<font color=\"#ff0000\">";
		}
		else
		{
			$parse['metal_max']         = "<font color=\"#00ff00\">";
		}
		$parse['metal_max']            .= pretty_number($CurrentPlanet['metal_max'] / 1000) ."k</font>";

		if ($CurrentPlanet['crystal_max'] < $CurrentPlanet['crystal'])
		{
			$parse['crystal_max']       = "<font color=\"#ff0000\">";
		}
		else
		{
			$parse['crystal_max']       = "<font color=\"#00ff00\">";
		}
		$parse['crystal_max']          .= pretty_number($CurrentPlanet['crystal_max'] / 1000) ."k</font>";

		if ($CurrentPlanet['deuterium_max'] < $CurrentPlanet['deuterium'])
		{
			$parse['deuterium_max']     = "<font color=\"#ff0000\">";
		}
		else
		{
			$parse['deuterium_max']     = "<font color=\"#00ff00\">";
		}
		$parse['deuterium_max']        .= pretty_number($CurrentPlanet['deuterium_max'] / 1000) ."k</font>";

		$parse['metal_total']           = colorNumber( pretty_number( floor( ( ($CurrentPlanet['metal_perhour']     * 0.01 * $parse['production_level'] ) + $parse['metal_basic_income']))));
		$parse['crystal_total']         = colorNumber( pretty_number( floor( ( ($CurrentPlanet['crystal_perhour']   * 0.01 * $parse['production_level'] ) + $parse['crystal_basic_income']))));
		$parse['deuterium_total']       = colorNumber( pretty_number( floor( ( ($CurrentPlanet['deuterium_perhour'] * 0.01 * $parse['production_level'] ) + $parse['deuterium_basic_income']))));
		$parse['energy_total']          = colorNumber( pretty_number( floor( ( $CurrentPlanet['energy_max'] + $parse['energy_basic_income']    ) + $CurrentPlanet['energy_used'] ) ) );

		$parse['daily_metal']           = floor($CurrentPlanet['metal_perhour']     * 24      * 0.01 * $parse['production_level']  + $parse['metal_basic_income']      * 24      );
		$parse['weekly_metal']          = floor($CurrentPlanet['metal_perhour']     * 24 * 7  * 0.01 * $parse['production_level']  + $parse['metal_basic_income']      * 24 * 7  );

		$parse['daily_crystal']         = floor($CurrentPlanet['crystal_perhour']   * 24      * 0.01 * $parse['production_level']  + $parse['crystal_basic_income']    * 24      );
		$parse['weekly_crystal']        = floor($CurrentPlanet['crystal_perhour']   * 24 * 7  * 0.01 * $parse['production_level']  + $parse['crystal_basic_income']    * 24 * 7  );

		$parse['daily_deuterium']       = floor($CurrentPlanet['deuterium_perhour'] * 24      * 0.01 * $parse['production_level']  + $parse['deuterium_basic_income']  * 24      );
		$parse['weekly_deuterium']      = floor($CurrentPlanet['deuterium_perhour'] * 24 * 7  * 0.01 * $parse['production_level']  + $parse['deuterium_basic_income']  * 24 * 7  );

		$parse['daily_metal']           = colorNumber(pretty_number($parse['daily_metal']));
		$parse['weekly_metal']          = colorNumber(pretty_number($parse['weekly_metal']));

		$parse['daily_crystal']         = colorNumber(pretty_number($parse['daily_crystal']));
		$parse['weekly_crystal']        = colorNumber(pretty_number($parse['weekly_crystal']));

		$parse['daily_deuterium']       = colorNumber(pretty_number($parse['daily_deuterium']));
		$parse['weekly_deuterium']      = colorNumber(pretty_number($parse['weekly_deuterium']));


		$QryUpdatePlanet  = "UPDATE {{table}} SET ";
		$QryUpdatePlanet .= "`id` = '". $CurrentPlanet['id'] ."' ";
		$QryUpdatePlanet .= $SubQry;
		$QryUpdatePlanet .= "WHERE ";
		$QryUpdatePlanet .= "`id` = '". $CurrentPlanet['id'] ."';";
		doquery( $QryUpdatePlanet, 'planets');

		return display(parsetemplate( gettemplate('resources/resources'), $parse));
	}
}
?>