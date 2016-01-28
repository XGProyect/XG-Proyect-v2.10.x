<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2016
 */

define('INSIDE'  , TRUE);
define('INSTALL' , FALSE);
define('IN_ADMIN', TRUE);
define('XGP_ROOT', './../');

include(XGP_ROOT . 'global.php');
include('AdminFunctions/Autorization.php');

if ($EditUsers != 1) die(message ($lang['404_page']));

$parse = $lang;

$getOrder   = isset($_GET['order']) ? $_GET['order'] : null;
$getOrder2  = isset($_GET['order2']) ? $_GET['order2'] : null;
$getView    = isset($_GET['view']) ? $_GET['view'] : null;

if ($getOrder == 'id')
	$ORDER	=	"id";
else
	$ORDER	=	"username";


$ListWHERE  = '';
$WHEREBANA  = '';

if ($user['authlevel'] != 3)
	$ListWHERE = "WHERE `authlevel` < '".($user['authlevel'])."'";


if ($getView == 'bana' && $user['authlevel'] != 3)
	$WHEREBANA	=	"AND `bana` = 1";
elseif ($getView == 'bana' && $user['authlevel'] == 3)
	$WHEREBANA	=	"WHERE `bana` = 1";

$UserList		=	doquery("SELECT `username`, `id`, `bana` FROM {{table}} ".$ListWHERE." ".$WHEREBANA." ORDER BY ".$ORDER." ASC", "users");

$Users          = 0;
$parse['List']  = '';
while ($a	=	mysql_fetch_array($UserList))
{
	if ($a['bana']	==	'1')
		$SuspendedNow	=	$lang['bo_characters_suus'];
	else
		$SuspendedNow	=	"";

	$parse['List']	.=	'<option value="'.$a['username'].'">'.$a['username'].'&nbsp;&nbsp;(ID:&nbsp;'.$a['id'].')'.$SuspendedNow.'</option>';
	$Users++;
}


if ($getOrder2 == 'id')
	$ORDER2	=	"id";
else
	$ORDER2	=	"username";

$Banneds            = 0;
$parse['ListBan']   = '';
$UserListBan        = doquery("SELECT `username`, `id` FROM {{table}} WHERE `bana` = '1' ORDER BY ".$ORDER2." ASC", "users");
while ($b = mysql_fetch_array($UserListBan))
{
    $parse['ListBan']	.=	'<option value="'.$b['username'].'">'.$b['username'].'&nbsp;&nbsp;(ID:&nbsp;'.$b['id'].')</option>';
    $Banneds++;
}

$parse['userss']	=	"<font color=lime>".$Users."</font>";
$parse['banneds']	=	"<font color=lime>".$Banneds."</font>";


mysql_free_result($UserList);
mysql_free_result($UserListBan);

if(isset($_GET['panel']))
{
	$QueryUserBan			=	doquery("SELECT * FROM {{table}} WHERE `who` = '".$_GET['ban_name']."'", "banned", TRUE);
	$QueryUserBanVacation	=	doquery("SELECT urlaubs_modus FROM {{table}} WHERE `username` = '".$_GET['ban_name']."'", "users", TRUE);

	if (!$QueryUserBan)
	{
		$parse['title']			=	$lang['bo_bbb_title_1'];
		$parse['changedate']	=	$lang['bo_bbb_title_2'];
	}
	else
	{
		$parse['title']			=	$lang['bo_bbb_title_3'];
		$parse['changedate']	=	$lang['bo_bbb_title_6'];
		$parse['changedate_advert']	=	"<td class=c width=5%><img src=\"../styles/images/Adm/i.gif\" onMouseOver='return overlib(\"".$lang['bo_bbb_title_4']."\",
			CENTER, OFFSETX, -80, OFFSETY, -65, WIDTH, 250);' onMouseOut='return nd();'></td>";

		$parse['reas']			=	$QueryUserBan['theme'];
		$parse['timesus']		=
			"<tr>
				<th>".$lang['bo_bbb_title_5']."</th>
				<th height=25 colspan=2>".date("d-m-Y H:i:s", $QueryUserBan['longer'])."</th>
			</tr>";
	}


	if ($QueryUserBanVacation['urlaubs_modus'] == 1)
		$parse['vacation']	=	'checked	=	"checked"';
	else
		$parse['vacation']	=	'';

	$parse['name']			=	isset($_GET['ban_name']) ? $_GET['ban_name'] : '';
        $name                           =       isset($_GET['ban_name']) ? $_GET['ban_name'] : '';


	if (isset($_POST['bannow']) && $_POST['bannow'])
	{
		if(!is_numeric($_POST['days']) || !is_numeric($_POST['hour']) || !is_numeric($_POST['mins']) || !is_numeric($_POST['secs']))
			return display( parsetemplate(gettemplate("adm/BanOptionsResultBody"), $parse), FALSE, '', TRUE, FALSE);

		$name              = isset($_POST['ban_name']) ? $_POST['ban_name'] : '';
		$reas              = isset($_POST['why']) ? $_POST['why'] : '';
		$days              = isset($_POST['days']) ? $_POST['days'] : '';
		$hour              = isset($_POST['hour']) ? $_POST['hour'] : '';
		$mins              = isset($_POST['mins']) ? $_POST['mins'] : '';
		$secs              = isset($_POST['secs']) ? $_POST['secs'] : '';
		$admin             = $user['username'];
		$mail              = $user['email'];
		$Now               = time();
		$BanTime           = $days * 86400;
		$BanTime          += $hour * 3600;
		$BanTime          += $mins * 60;
		$BanTime          += $secs;
		if ($QueryUserBan['longer'] > time())
			$BanTime          += ($QueryUserBan['longer'] - time());

		if (($BanTime + $Now) < time())
			$BannedUntil       = $Now;
		else
			$BannedUntil       = $Now + $BanTime;


		if ($QueryUserBan)
		{
			$QryInsertBan      = "UPDATE {{table}} SET ";
			$QryInsertBan     .= "`who` = '". $name ."', ";
			$QryInsertBan     .= "`theme` = '". $reas ."', ";
			$QryInsertBan     .= "`who2` = '". $name ."', ";
			$QryInsertBan     .= "`time` = '". $Now ."', ";
			$QryInsertBan     .= "`longer` = '". $BannedUntil ."', ";
			$QryInsertBan     .= "`author` = '". $admin ."', ";
			$QryInsertBan     .= "`email` = '". $mail ."' ";
			$QryInsertBan     .= "WHERE `who2` = '".$name."';";
			doquery( $QryInsertBan, 'banned');
		}
		else
		{
			$QryInsertBan      = "INSERT INTO {{table}} SET ";
			$QryInsertBan     .= "`who` = \"". $name ."\", ";
			$QryInsertBan     .= "`theme` = '". $reas ."', ";
			$QryInsertBan     .= "`who2` = '". $name ."', ";
			$QryInsertBan     .= "`time` = '". $Now ."', ";
			$QryInsertBan     .= "`longer` = '". $BannedUntil ."', ";
			$QryInsertBan     .= "`author` = '". $admin ."', ";
			$QryInsertBan     .= "`email` = '". $mail ."';";
			doquery( $QryInsertBan, 'banned');
		}

		$QryUpdateUser     = "UPDATE {{table}} SET ";
		$QryUpdateUser    .= "`bana` = '1', ";
		$QryUpdateUser    .= "`banaday` = '". $BannedUntil ."', ";

		if(isset($_POST['vacat']))
		{
			$QryUpdateUser    .= "`urlaubs_modus` = '1'";
			$ASD	=	1;
		}
		else
		{
			$QryUpdateUser    .= "`urlaubs_modus` = '0'";
			$ASD	=	0;
		}

		$QryUpdateUser    .= "WHERE ";
		$QryUpdateUser    .= "`username` = '". $name ."';";
		doquery( $QryUpdateUser, 'users');

		$PunishThePlanets     = "UPDATE {{table}} SET ";
		$PunishThePlanets    .= "`metal_mine_porcent` = '0', ";
		$PunishThePlanets    .= "`crystal_mine_porcent` = '0', ";
		$PunishThePlanets    .= "`deuterium_sintetizer_porcent` = '0'";
		$PunishThePlanets    .= "WHERE ";
		$PunishThePlanets    .= "`id_owner` = '". $GetUserData['id'] ."';";
		doquery( $PunishThePlanets, 'planets');



		$Log	.=	"\n".$lang['log_suspended_title']."\n";
		$Log	.=	$lang['log_the_user'].$user['username']." ".$lang['log_suspended_1'].$name.$lang['log_suspended_2']."\n";
		$Log	.=	$lang['log_reason'].$reas."\n";
		$Log	.=	$lang['log_time'].date("d-m-Y H:i:s", time())."\n";
		$Log	.=	$lang['log_longer'].date("d-m-Y H:i:s", $BannedUntil)."\n";
		$Log	.=	$lang['log_vacations'].$lang['log_viewmod'][$ASD]."\n";

		LogFunction($Log, "GeneralLog", $LogCanWork);

		header ( 'location:BanPage.php?panel=ban_name&ban_name='.$_GET['ban_name'].'&succes=yes' );
	}
	if (isset($_GET['succes']) && $_GET['succes'] == 'yes')
		$parse['display']	=	"<tr><th colspan=\"2\"><font color=lime>". $lang['bo_the_player'] . $name . $lang['bo_banned'] ."</font></th></tr>";
	display( parsetemplate(gettemplate("adm/BanOptionsResultBody"), $parse), FALSE, '', TRUE, FALSE);
}
elseif($_POST && $_POST['unban_name'])
{
	$name = $_POST['unban_name'];
	doquery("DELETE FROM {{table}} WHERE who = '".$name."'", 'banned');
	doquery("UPDATE {{table}} SET bana = '0', banaday = '0' WHERE username = '".$name."'", "users");



	$Log	.=	"\n".$lang['log_suspended_title']."\n";
	$Log	.=	$lang['log_the_user'].$user['username']." ".$lang['log_suspended_3'].$name."\n";

	LogFunction($Log, "GeneralLog", $LogCanWork);

	header ( 'location:BanPage.php?succes2=yes&u=' . $name );
}
	if (isset($_GET['succes2']) && $_GET['succes2'] == 'yes')
		$parse['display2']	=	"<tr><th colspan=\"2\"><font color=lime>". $lang['bo_the_player2'] . (isset($_GET['u'])?$_GET['u']:'') . $lang['bo_unbanned'] ."</font></th></tr>";



display( parsetemplate(gettemplate("adm/BanOptions"), $parse), FALSE, '', TRUE, FALSE);
?>