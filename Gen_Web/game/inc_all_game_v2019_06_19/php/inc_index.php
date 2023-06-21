<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php
$sWebGuid	= filter_input_str('gtoken',	  INPUT_GET, ''); #內部
$stoken  	= filter_input_str('token', 	  INPUT_GET, '');	#CQ9
$sWtoken 	= filter_input_str('WebToken',  INPUT_GET, ''); #其它
$sWkey 	= filter_input_str('gkey',	  INPUT_GET, ''); #其它
$nPlatform	= filter_input_int('acode',	  INPUT_GET, 0);
$nStatus	= filter_input_int('stu',	  INPUT_GET, 0);
$sGa		= filter_input_str('ga',	  INPUT_GET, '');
$sGameToken = '';
$nGid		= 0;
$bOk		= false;
$sStr		= 'Error!';
$sBackUrl	= '';
$nUrl_strpos = stripos($_SERVER['REQUEST_URI'],'AQ');	#對外
$nUrl_strpos = ($nUrl_strpos == false ? stripos($_SERVER['REQUEST_URI'],'ST') : $nUrl_strpos);	#對外

if($sWebGuid != '' && $nPlatform == 1 && $nStatus == 99)
{
	#內部
	$sBackUrl = INTERNAL_URL;
	$bOk = true;
}
else if($stoken != '')
{
	#CQ9
	$nPlatform = CQ9At_Code;
	$sGameToken = $stoken;

	#查詢該對接方狀態
	$sSQL = 'SELECT	aurl
		   FROM 	agent
		   WHERE 	At_Code = :At_Code
		   AND	At_Enable = 1';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':At_Code', $nPlatform, PDO::PARAM_INT);
	sql_query($Result);
	$iCont = $Result->rowCount();
	if($iCont > 0)
	{
		#判斷此網址為哪個遊戲
		if($nUrl_strpos > 0)
		{
			#外部
			$sGame_name = substr($_SERVER['REQUEST_URI'],$nUrl_strpos,4);
			
			if($nStatus != 99)
			{
				$sSQL = 'SELECT	id,
							web_enable,
							enable
					FROM 	game_version
					WHERE 	CQ9Gid = :CQ9Gid '.sql_limit(0,1);
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':CQ9Gid', $sGame_name, PDO::PARAM_STR);
				sql_query($Result);
				$aRow = $Result->fetch(PDO::FETCH_ASSOC);
				$nGid = $aRow['id'];
				$nWeb_enable = $aRow['web_enable'];
				$nEnable = $aRow['enable'];
		
				#判斷該遊戲是否維護中
				$nWeb_enable == 1 && $nEnable == 1 ? $bOk = true : $sStr = '维护中';
			}
			else
			{
				$bOk = true;
			}
		}
	}
	else
	{
		$sStr = '无权限进行游玩';
	}
}
else if($sWtoken != '')
{
	#其它
	$sGameToken = $sWtoken;

	#查詢該對接方狀態
	$sSQL = 'SELECT	aurl
		   FROM 	agent
		   WHERE 	At_Code = :At_Code
		   AND	At_Enable = 1';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':At_Code', $nPlatform, PDO::PARAM_INT);
	sql_query($Result);
	$iCont = $Result->rowCount();
	if($iCont > 0)
	{
		$aAgent = $Result->fetch(PDO::FETCH_ASSOC);
		if($nPlatform != 9)
		{
			$sBackUrl = ($sGa != '' ? $aAgent['aurl'] : GEN_HALL_URL.'?WebToken='.$sWtoken.'&acode='.$nPlatform.'&t='.time());
		}		

		#判斷 WebToken是否存在
		$sSQL = 'SELECT 	in_time
			   FROM 	game_token
			   WHERE 	platform = :platform
			   AND	gtoken = :gtoken
			   AND	gstatus = 2 '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':platform', 	$nPlatform, PDO::PARAM_INT);
		$Result->bindValue(':gtoken', 	$sGameToken, PDO::PARAM_STR);
		sql_query($Result);
		$iGCount = $Result->rowCount();
		if($iGCount > 0)
		{
			$aRow = $Result->fetch(PDO::FETCH_ASSOC);
			$nIn_time = $aRow['in_time'];

			if((time() - $nIn_time) <= 86400)
			{
				#24小時效用
				#判斷此網址為哪個遊戲
				#外部
				$sGame_name = substr($_SERVER['REQUEST_URI'],$nUrl_strpos,4);
				
				if($nStatus != 99)
				{
					$sSQL = 'SELECT	id,
								web_enable,
								enable
						FROM 	game_version
						WHERE 	CQ9Gid = :CQ9Gid '.sql_limit(0,1);
					$Result = $pdo->prepare($sSQL);
					$Result->bindValue(':CQ9Gid', $sGame_name, PDO::PARAM_STR);
					sql_query($Result);
					$aRow = $Result->fetch(PDO::FETCH_ASSOC);
					$nGid = $aRow['id'];
					$nWeb_enable = $aRow['web_enable'];
					$nEnable = $aRow['enable'];
		
					#判斷該遊戲是否維護中
					$nWeb_enable == 1 && $nEnable == 1 ? $bOk = true : $sStr = '维护中';
				}
				else
				{
					$bOk = true;
				}
			}
			else
			{
				$sStr = '您的连接已失效。';
			}
		}
		else
		{
			$sStr = '您的连接已失效。';
		}		
	}
	else
	{
		$sStr = '无权限进行游玩';
	}
}

if($bOk == false)
{
	require_once('err_index.php');
	exit;
}
?>