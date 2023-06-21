<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_tidy.php");?>
<?php
$sGtoken		= filter_input_str('gtoken',	  INPUT_POST);
$nGid			= filter_input_int('gid',	  INPUT_POST,0);
$nAgentCode		= filter_input_int('acode',	  INPUT_POST,0);
$sUserAgent		= filter_input_str('user_agent',	  INPUT_POST);
$sLang 		= filter_input_str('sLang', 	INPUT_REQUEST);

$nUA 			= (strpos($sUserAgent, 'iPhone') || strpos($sUserAgent, 'iPad') || strpos($sUserAgent, 'mobile') || strpos($sUserAgent,'Android')) ? 1 : -1;
$aRes 		= array();
$aRes['res'] 	= 0;
$aRes['slink']	= GEN_HALL_URL.'?WebToken='.$sGtoken.'&acode='.$nAgentCode.'&t='.time();

// print_r('<pre>');
// print_r($_POST);
// print_r('</pre>');

if(isset($_COOKIE['sLang']))
{
	$sLang = str_replace('_'.$nAgentCode,'',$_COOKIE['sLang']);
}
$sBackUrl = '';
$sSQL = 'SELECT 	aurl,
			nFormal,
			nCurrency
	   FROM 	agent
	   WHERE 	At_Code = :At_Code LIMIT 1';
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':At_Code', 	$nAgentCode, PDO::PARAM_INT);
sql_query($Result);
$aAgent = $Result->fetch(PDO::FETCH_ASSOC);
if($aAgent !== false)
{
	$nCurrency = $aAgent['nCurrency'];
	$nFormal = $aAgent['nFormal'];
	$sBackUrl = $aAgent['aurl'];

	// error_log($nCurrency);
}


#查詢該遊戲
$sSQL = 'SELECT 	id,
			web_enable
	   FROM 	game_version
	   WHERE 	id = :id '.sql_limit(0,1);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':id', 	$nGid, PDO::PARAM_INT);
sql_query($Result);
$aRow = $Result->fetch(PDO::FETCH_ASSOC);
if($aRow)
{
	$aRes['res'] = $aRow['web_enable'] != 1 ? 2 : 0;

	#判斷 WebToken是否存在
	$sSQL = 'SELECT 	l.G_IID,
				l.G_Name,
				l.webG_Uid,
				l.G_Wallet,
				l.G_status,
				l.third_party,
				g.gstatus,
				g.in_time_out,
				g.in_time,
				g.cid
		   FROM 	game_token g,
				l_group l
		   WHERE 	g.platform = :platform
		   AND	g.gtoken = :gtoken
		   AND	l.G_IID = g.G_IID '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':platform', 	$nAgentCode, PDO::PARAM_INT);
	$Result->bindValue(':gtoken', 	$sGtoken, PDO::PARAM_STR);
	sql_query($Result);
	$iGCount = $Result->rowCount();
	if($iGCount > 0)
	{
		$aRow = $Result->fetch(PDO::FETCH_ASSOC);
		$sG_Name = $aRow['G_Name'];
		$nG_IID = $aRow['G_IID'];
		$dG_Wallet = $aRow['G_Wallet'];
		$nThird_party = $aRow['third_party'];
		$nGstatus = $aRow['gstatus'];
		$nG_status = $aRow['G_status'];
		$nIn_time = $aRow['in_time'];

		if($nG_status != 0 || $nThird_party != 0)
		{
			$aRes['res'] = 5;
		}
		else if($nGstatus == 2 && (time() - $nIn_time) <= 86400)
		{
			$nGame = $nGid - 1000;
			$aReqData = array(
				'game_id' 	=> (int) $nGame,
				'username' 	=> (string) $sG_Name,
				'back_url' 	=> (string) (GEN_HALL_URL.'?WebToken='.$sGtoken.'&acode='.$nAgentCode.'&nDemo=1&t='.time()),
				'quality' 	=> 'MD',
				'lang' 	=> $sTidyLang,
			);

			$aResData = json_decode(TidySend('DemoLink',$aReqData),true);

			if(isset($aResData['link']) && $aResData['link'] != '')
			{
				$aRes['res'] = 1;
				$aRes['slink'] = $aResData['link'];
			}
		}
		else
			$aRes['res'] = 4;
	}
	else
		$aRes['res'] = 4;
}

echo '<script>';
switch ($aRes['res'])
{
	case '0':
	case '4':
		echo 'alert("error");';
		break;
}
echo 'location.href = "'.$aRes['slink'].'";';
echo '</script>';
exit;
?>