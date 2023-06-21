<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_tidy.php");?>
<?php
$sGtoken		= filter_input_str('gtoken',	  		INPUT_POST);
$nGid			= filter_input_int('gid',	  		INPUT_POST,0);
$nAgentCode		= filter_input_int('acode',	  		INPUT_POST,0);
$sUserAgent		= filter_input_str('user_agent',	  	INPUT_POST);
$sLang 		= filter_input_str('sLang', 			INPUT_REQUEST);
$nLeboVersion 	= filter_input_int('nLeboVersion',		INPUT_REQUEST, 0);

$nUA 			= (strpos($sUserAgent, 'iPhone') || strpos($sUserAgent, 'iPad') || strpos($sUserAgent, 'mobile') || strpos($sUserAgent,'Android')) ? 1 : -1;
$aRes 		= array();
$aRes['res'] 	= 0;
$aRes['slink']	= GEN_HALL_URL.'?WebToken='.$sGtoken.'&acode='.$nAgentCode.'&t='.time();

if($nLeboVersion == 2)
{
	echo $aRes['slink'] .= '&nLeboVersion=2';
}

// print_r('<pre>');
// print_r($_POST);
// print_r('</pre>');

// if(isset($_COOKIE['sLang']))
// {
// 	$sLang = str_replace('_'.$nAgentCode,'',$_COOKIE['sLang']);
// }

// 如果有存cookie且站別相同就吃cookie的
$sTempLang = $_COOKIE['sLang'];
$aTempLang = explode('_',$sTempLang);
// print_r($aTempLang);
if(isset($aTempLang[1]) && $aTempLang[1] == $nAgentCode && $aTempLang[0] != '')
{
	$sLang = $aTempLang[0];
}

// 寫入cookie
if($sLang != '')
{
	setcookie('sLang',$sLang.'_'.$nPlatform, time()+14400);
}

// echo $sLang;exit;

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
			if($dG_Wallet > 0)
			{
				#tidy api 轉出額度

				$nRes = Tidy_amount_out($nG_IID,$dG_Wallet,$nGid,$sGtoken,$nAgentCode,$nCurrency);
				if($nRes == 1)
				{
					#更新玩家狀態
					$aSQL_Array = array(
						'third_party'	=> $nGid,
					);
					$sSQL = 'UPDATE	l_group
						   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).'
						   WHERE 	G_IID = :G_IID';
					$Result = $pdo->prepare($sSQL);
					$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
					sql_build_value($Result, $aSQL_Array);
					sql_query($Result);

					// 語系 (預設中文)
					$sTidyLang = 'zh';
					switch($sLang)
					{
						case "tw":
							$sTidyLang = 'zh-tw';
							break;
						case "vn":
							$sTidyLang = 'vi';
							break;
						case "en":
							$sTidyLang = 'en';
							break;
					}

					#tidy api 取得遊戲連結
					$nTidyGid = $nGid - 1000;
					$aReqData = array(
						'game_id' 	=> (int) $nTidyGid,
						'username' 	=> (string) $sG_Name,
						'back_url' 	=> (string) (GEN_HALL_URL.'?WebToken='.$sGtoken.'&acode='.$nAgentCode.'&t='.time()).'&sLang='.$sLang,
						'quality' 	=> 'MD',
						'lang' 	=> $sTidyLang,
					);
					if($nAgentCode >= 10 && $nAgentCode < 200)
					{
						$aName = explode('@',$sG_Name);
						$aReqData['invite_code'] = $aName[1].$nAgentCode;
						if($nGid == 1248)
						{
							$aReqData['back_url'] = $sBackUrl;
						}
					}

					if(($nAgentCode == 202 || $nAgentCode == 204 ) && $nGid == 1248)
					{
						$aReqData['back_url'] = $sBackUrl;
					}

					# 2021/12/07 PD Kn說要改版給客人看
					if($nLeboVersion == 2)
					{
						$aReqData['back_url'] .= '&nLeboVersion=2';
					}

					$nUA == 1 ? $aReqData['user_agent'] = $sUserAgent : '';
					$sResData = json_decode(TidySend('GameLink',$aReqData),true);

					// print_r('<pre>');
					// print_r($aReqData);
					// print_r($sResData);
					// print_r('</pre>');
					// exit;
					if(isset($sResData['link']))
					{
						$aRes['res'] = 1;
						$aRes['slink'] = $sResData['link'];
					}
					else
					{
						#新建對接錯誤訊息
						ApiErrorTidy($sG_Name,$aReqData,$sResData);

						#查詢Tidy帳戶額度
						$aReqData = array(
							'username' 	=> $sG_Name,
						);
						$aResData = json_decode(TidySend('UserBalance',$aReqData),true);
						if(isset($aResData['user']['balance']) && $aResData['user']['balance'] > 0)
						{
							#拉回額度
							$dBalance = $aResData['user']['balance'] * BasicExchange;
							$nRes = Tidy_amount_in($nG_IID,$dBalance,$nGid,$sGtoken,$nAgentCode,$nCurrency);
							if($nRes == 1)
							{
								#更新玩家狀態
								$aSQL_Array = array(
									'third_party'	=> 0,
								);
								$sSQL = 'UPDATE	l_group
									   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).'
									   WHERE 	G_IID = :G_IID';
								$Result = $pdo->prepare($sSQL);
								$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
								sql_build_value($Result, $aSQL_Array);
								sql_query($Result);
							}
						}
						$aRes['res'] = 4;
					}
				}
				else
					$aRes['res'] = 4;
			}
			else
				$aRes['res'] = 6;
		}
		else
			$aRes['res'] = 3;
	}
	else
		$aRes['res'] = 3;
}

echo '<script>';
switch ($aRes['res'])
{
	case '0':
	case '4':
		echo 'alert("error");';
		break;
	case '2':
		echo 'alert("此遊戲尚未開放");';
		break;
	case '3':
		echo 'alert("连结失效");';
		// exit;
		break;
	case '5':
		// 捕魚
		if($nThird_party == 1248)
		{
			echo 'alert("遊戲結算中，請稍後再進入");';
			echo 'location.href = "'.$sBackUrl.'";';
			echo '</script>';
			exit;

		}
		else
		{
			echo 'alert("尚有游戏未结算");';
		}
		break;
	case '6':
		echo 'alert("馀额不足");';
		break;
}
echo 'location.href = "'.$aRes['slink'].'";';
echo '</script>';
exit;
?>