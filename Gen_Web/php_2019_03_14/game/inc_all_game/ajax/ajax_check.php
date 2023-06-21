<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/fun.class.websocket.php");?>
<?php
$sLhref		= filter_input_str('lhref',	  INPUT_POST,'');
$aRes['res'] 	= $aGameAjaxResCode['api_no_response'];
$aRes['tid'] 	= 0;
$nGid 	 	= 0;
$nPlatform		= 0;
$sWebGuid		= '';
$sToken		= '';
$sWtoken		= '';
$sWkey		= '';
$sLanguage		= '';
$sGame_name  	= '';
$nStatus		= 0;

if($sLhref == '')
{
	echo json_encode($aRes);
	exit;	
}
else
{
	$aLhref = explode('?', $sLhref);
	if(count($aLhref) != 2)
	{
		echo json_encode($aRes);
		exit;
	}
	else
	{
		$sUrl_game_name = $aLhref[0];
		$sUrl_parameter = $aLhref[1];
	}	
}

/*
$aRes['res'] = 99;
echo json_encode($aRes);
exit;
*/

$aUrl_game_name = explode('/', $sUrl_game_name);
foreach($aUrl_game_name as $a => $s)
{
	if(stripos($s,'AQ') === 0 && stripos($s,'.') === false)
	{
		$sGame_name = $s;
	}
}

$aUrl_parameter = explode('&', $sUrl_parameter);
$nEndCount = count($aUrl_parameter) - 1;
if(stripos($aUrl_parameter[$nEndCount],'#') > 0)
	$aUrl_parameter[$nEndCount] = substr($aUrl_parameter[$nEndCount],0,-1);
foreach($aUrl_parameter as $a => $s)
{
	$aParameter = explode('=', $s);
	switch($aParameter[0])
	{
		case 'gtoken':
			#內部
			$sWebGuid = (string) $aParameter[1];
			break;

		case 'token':
			#CQ9
			$sToken = (string) $aParameter[1];
			break;

		case 'WebToken':
			#對接方 token
			$sWtoken = (string) $aParameter[1];
			break;
		
		case 'gkey':
			#對接方 game webGuid
			$sWkey = (string) $aParameter[1];
			break;

		case 'acode':
			#對接方代碼
			$nPlatform = (int) $aParameter[1];
			break;

		case 'stu':
			$nStatus = (int) $aParameter[1];
			break;

		case 'language':
			$sLanguage = (string) $aParameter[1];
			break;
	}
}

if($sWebGuid != '' && $nPlatform == 1 && $nStatus == 99)
{
	#內測
	#查詢遊戲ID
	if($sGame_name == '')
	{
		#local-server
		$sSQL = 'SELECT	id,
					logDB
			   FROM 	game_version
			   WHERE 	enable = 1
			   AND	id != 255 ';
		$Result = $pdo->prepare($sSQL);
		sql_query($Result);
		while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
		{
			$aGame_name[$aRow['id']] = $aRow['logDB'];
			if(stripos($sUrl_game_name,$aRow['logDB']) > 0)
			{
				$nGid = $aRow['id'];
			}
		}
	}
	else
	{
		#外部
		$sSQL = 'SELECT	id
			   FROM 	game_version
			   WHERE 	CQ9Gid = :CQ9Gid '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':CQ9Gid', $sGame_name, PDO::PARAM_STR);
		sql_query($Result);
		$aRow = $Result->fetch(PDO::FETCH_ASSOC);
		$nGid = $aRow['id'];
	}

	if($nGid > 0)
	{
		#查詢 WebGuid 是否正確
		$sSQL = 'SELECT	G_IID,
					G_Name,
					G_Passwd
			   FROM	l_group
			   WHERE	webG_Uid = :webG_Uid
			   AND	platform = :platform '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':webG_Uid', $sWebGuid, PDO::PARAM_STR);
		$Result->bindValue(':platform', $nPlatform, PDO::PARAM_INT);
		sql_query($Result);
		$aUser = $Result->fetch(PDO::FETCH_ASSOC);
		if($aUser)
		{
			$sG_Name = $aUser['G_Name'];
			$nG_IID  = $aUser['G_IID'];
			$sG_Passwd = $aUser['G_Passwd'];
		
			#更新玩家狀態
			$aSQL_Array = array(
				'G_Enabled'	=> (string) 'Y',
				'G_SubUser'	=> (string) 'Y',
			);
			$sSQL = 'UPDATE	l_group
				   SET	'.sql_build_array('UPDATE', $aSQL_Array).'
				   WHERE	G_IID = :G_IID';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		
			#新增 Token 資料-只CQ9Token-	
			$sGameToken = substr($sWebGuid,0,16);
			$aSQL_Array = array(
				'G_IID'		=> (int) $nG_IID,
				'cq9_token'		=> (string) $sGameToken,
				'gid'			=> (int) $nGid,
				'cstatus'		=> (int) 5,
				'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),				
			);	
			$sSQL = 'INSERT INTO cq9_token ' . sql_build_array('INSERT', $aSQL_Array );
			$Result = $pdo->prepare($sSQL);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
			$nTokenID = $pdo->lastInsertId(); #TokenID
		
			#傳送WebSocket
			$aSocket = array(
				'gid' 	=> $nGid,
				'giid' 	=> $nG_IID,
				'cid' 	=> $nTokenID,
			);
			set_websocket($aWebSocket['authToken'],$aSocket);
		
			#取得 User資料成功
			$aRes = array(
				'res'		=> (int) $aGameAjaxResCode['success'],
				'tid'		=> (int) $nTokenID,
				'gid'		=> (int) $nGid,
				'acc'		=> (string) $sG_Name,
				'pw'		=> (string) $sG_Passwd,
				'surl'	=> (string) INTERNAL_URL,
			);
		}
	}
}
else if($sToken != '')
{
	#CQ9
	$sG_Passwd = CQ9PW;
	#查詢遊戲ID
	if($sGame_name != '')
	{
		#外部
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
	}

	if($nWeb_enable == 1 && $nEnable == 1 && $nGid > 0)
	{
		$sGameToken = $sToken;
		$nPlatform = CQ9At_Code;
		$aAuth['gametoken'] = $sGameToken;
		$aResult = CQ9_fun('auth',$aAuth);	#取 token基本資料
		$sJRes = json_encode($aResult);
		
		if(LogPermission > 1)
		{
			#auth log
			$aSQL_Array = array(
				'gametoken'		=> (string) $sGameToken,
				'res_json'		=> (string) json_encode($aResult),
				'createtime'	=> (int) now_time,
				'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
			);	
			$sSQL = 'INSERT INTO cq9_auth ' . sql_build_array('INSERT', $aSQL_Array );
			$Result = $pdo->prepare($sSQL);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		}
		
		if(isset($aResult['status']['code']) && $aResult['status']['code'] == 0)
		{
			#取對接商資料
			$sSQL = 'SELECT 	agent_code
				   FROM 	agent
				   WHERE 	At_Code = :At_Code '.sql_limit(0,1);
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':At_Code', 	$nPlatform, 	PDO::PARAM_INT);
			sql_query($Result);
			$aAgent_code = $Result->fetch(PDO::FETCH_ASSOC);
			$sAgent_code = $aAgent_code['agent_code'];

			#取得玩家資料成功
			foreach($aResult['data'] as $a => $s)
			{
				switch($a)
				{
					case 'id':
						#玩家ID
						$sG_Name = $s.'@'.$sAgent_code;
						$sCQ9Gid = $s;
						break;
					case 'account':
						#玩家帳號
						$sAcc = $s;
						break;
					case 'balance':
						#玩家餘額
						$sBalance = $s;
						break;
					case 'currency':
						#玩家幣別
						$sCurrency = $s;
						break;
					case 'gamecode':
						#遊戲代號
						$sGamecode = $s;
						break;
					case 'gametype':
						#遊戲類別
						$sGametype = $s;
						break;
					default:
						break;
				}
			}
			
			#取得幣別
			$sSQL = 'SELECT 	cid
				   FROM 	currency
				   WHERE 	code = :code
				   AND	enabled = 1 '.sql_limit(0,1);
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':code', 	$sCurrency, 	PDO::PARAM_STR);
			sql_query($Result);
			$iCurrCont = $Result->rowCount();
			if($iCurrCont > 0)
			{
				$aCurrency = $Result->fetch(PDO::FETCH_ASSOC);
				$iCurrency = $aCurrency['cid'];			
				$sEnabled = 'Y';
			}
			else
			{				
				$iCurrency = 0;
				$sEnabled = 'N';
			}

			#判斷該玩家是否已存在
			$sSQL = 'SELECT 	G_IID,
						currencyID
				   FROM 	l_group
				   WHERE 	G_Name = :G_Name '.sql_limit(0,1);
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':G_Name', 	$sG_Name, 	PDO::PARAM_STR);
			sql_query($Result);
			$iCount = $Result->rowCount();
			if($iCount < 1)
			{
				#不存在則新增帳號
				$sG_Date = date("Y-m-d H:i:s");
				$aSQL_Array = array(
					'G_Name'		=> (string) $sG_Name,
					'G_NickName'	=> (string) $sAcc,
					'G_Passwd'		=> (string) CQ9PW,
					'G_Date'		=> (string) $sG_Date,
					'platform'		=> (int) CQ9At_Code,
					'G_SubUser'		=> (string) 'Y',
					'G_Enabled'		=> (string) $sEnabled,
					'currencyID'	=> (int) $iCurrency,
				);	
				$sSQL = 'INSERT INTO l_group ' . sql_build_array('INSERT', $aSQL_Array );
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
				$nGIID = $pdo->lastInsertId(); #GIID
			}
			else
			{
				#取得會員GIID
				$aGIID = $Result->fetch(PDO::FETCH_ASSOC);
				$nGIID = $aGIID['G_IID'];
				$iNCurrency = $aGIID['currencyID'];

				#帶入幣別不同
				$iCurrency = ($iCurrency != 0 ? $iCurrency : $iNCurrency);
				
				#修改玩家登入狀態
				$aSQL_Array = array(
					'currencyID'	=> (int) $iCurrency,
					'G_SubUser'		=> (string) 'Y',
					'G_Enabled'		=> (string) $sEnabled,
					'G_Passwd'		=> (string) CQ9PW,
				);	
				$sSQL = 'UPDATE 	l_group 
					   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) . ' 
					   WHERE 	G_IID = :G_IID ';
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				$Result->bindValue(':G_IID', 	$nGIID, PDO::PARAM_INT);
				sql_query($Result);
			}

			#新增CQ9Token資料		
			$aSQL_Array = array(
				'G_IID'		=> (int) $nGIID,
				'cq9_id'		=> (string) $sCQ9Gid,
				'cq9_token'		=> (string) $sGameToken,
				'auth_json'		=> (string) $sJRes,
				'gid'			=> (int) $nGid,
				'cstatus'		=> (int) 5,
				'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
				'createtime'	=> (int) now_time,
			);	
			$sSQL = 'INSERT INTO cq9_token ' . sql_build_array('INSERT', $aSQL_Array );
			$Result = $pdo->prepare($sSQL);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
			$nTokenID = $pdo->lastInsertId(); #TokenID

			#傳送WebSocket
			$aSocket = array(
				'gid' 	=> $nGid,
				'giid' 	=> $nGIID,
				'cid' 	=> $nTokenID,
			);
			set_websocket($aWebSocket['authToken'],$aSocket);

			#取得 User資料成功
			$aRes = array(
				'res'		=> (int) $aGameAjaxResCode['success'],
				'tid'		=> (int) $nTokenID,
				'gid'		=> (int) $nGid,
				'acc'		=> (string) $sG_Name,
				'pw'		=> (string) $sG_Passwd,
				'surl'	=> '',
			);
		}
		else if(isset($aResult['status']['code']) && ($aResult['status']['code'] == 4 || $aResult['status']['code'] == 9))
		{
			$aRes['res'] = $aGameAjaxResCode['token_invalid'];	#Token 失效
		}
		else
		{
			$aRes['res'] = $aGameAjaxResCode['api_no_response'];	#API 無回應
		}
	}
	else
	{
		$aRes['res'] = $aGameAjaxResCode['maintain'];	#維護中
	}	
}
else if($sWtoken != '')
{
	#對接商
	$sGameToken = $sWtoken;
	$sErrUrl = '';

	#抓回傳網址
	$sSQL = 'SELECT 	aurl,
		   		At_Enable
		   FROM 	agent
		   WHERE 	At_Code = :At_Code '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':At_Code', 	$nPlatform, PDO::PARAM_INT);
	sql_query($Result);
	$iAgentCount = $Result->rowCount();
	if($iAgentCount > 0)
	{
		$aAgent = $Result->fetch(PDO::FETCH_ASSOC);
		if($aAgent['At_Enable'] == 1)
		{
			$sErrUrl = $aAgent['aurl'];

			#外部
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

			if($nWeb_enable == 1 && $nEnable == 1 && $nGid > 0)
			{
				#判斷 WebToken是否存在
				$sSQL = 'SELECT 	l.G_IID,
							l.G_Name,
							l.webG_Uid,
							l.G_Passwd,
							g.in_time,
							g.cid
					   FROM 	game_token g,
							l_group l
					   WHERE 	g.platform = :platform
					   AND	g.gtoken = :gtoken
					   AND	l.G_IID = g.G_IID
					   AND	g.gstatus = 2 '.sql_limit(0,1);
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':platform', 	$nPlatform, PDO::PARAM_INT);
				$Result->bindValue(':gtoken', 	$sGameToken, PDO::PARAM_STR);
				sql_query($Result);
				$iGCount = $Result->rowCount();

				if($iGCount > 0)
				{
					$aRow = $Result->fetch(PDO::FETCH_ASSOC);
					$sG_Name = $aRow['G_Name'];
					$sWebGuid = $aRow['webG_Uid'];
					$sG_Passwd = $aRow['G_Passwd'];
					$nG_IID = $aRow['G_IID'];

					if((time() - $aRow['in_time']) <= 86400)
					{
						if($sWebGuid == $sWkey)
						{
							$aSQL_Array = array(
								'G_SubUser'		=> (string) 'Y',
							);	
							$sSQL = 'UPDATE 	l_group 
								   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) .'
								   WHERE 	G_IID = :G_IID';
							$Result = $pdo->prepare($sSQL);
							$Result->bindValue(':G_IID', 	$nG_IID, PDO::PARAM_INT);
							sql_build_value($Result, $aSQL_Array);
							sql_query($Result);
							
							#取得 User資料成功
							$aRes = array(
								'res'		=> (int) $aGameAjaxResCode['success'],
								'tid'		=> (int) 0,
								'gid'		=> (int) $nGid,
								'acc'		=> (string) $sG_Name,
								'pw'		=> (string) $sG_Passwd,
							);
						}
						else
						{
							$aRes['res'] = $aGameAjaxResCode['key_invalid'];	#遊戲 key 失效
						}
					}
					else
					{
						$aRes['res'] = $aGameAjaxResCode['token_invalid'];	#Token 失效
					}
				}
				else
				{
					$aRes['res'] = $aGameAjaxResCode['token_invalid'];	#Token 失效
				}
			}
			else
			{
				$aRes['res'] = $aGameAjaxResCode['maintain'];	#維護中
			}
		}
		else
		{
			$sErrUrl = $aAgent['aurl'];
			$aRes['res'] = $aGameAjaxResCode['no_permission'];	#無權限進行遊戲
		}

		$aRes['surl'] = $sErrUrl;
	}
	else
	{
		$aRes['res'] = $aGameAjaxResCode['no_permission'];	#無權限進行遊戲
	}
}

$pdo = null;
echo json_encode($aRes);
exit;
?>