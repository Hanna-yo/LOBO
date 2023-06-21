<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/fun.class.websocket.php");?>
<?php
$sLhref		= filter_input_str('lhref',	  INPUT_POST,'');
$sLhref = 'https://api.tocq988.com/XG_Web/game/AQ01/?token=8b791835ed5f3caefd5e62ca9d1e01e6#';
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
$sGa 			= '';
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
	else if(stripos($s,'ST') === 0 && stripos($s,'.') === false)
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
		
			#查詢 token
			$nCstatus = -1;
			$pdo->beginTransaction();
			$sSQL = 'SELECT	cq9_token,
						cstatus,
						createtime
				   FROM	cq9_token
				   WHERE	G_IID = :G_IID 
				   FOR UPDATE ';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
			sql_query($Result);
			$nCountToken = $Result->rowCount();			
			if($nCountToken > 0)
			{
				$aToken = $Result->fetch(PDO::FETCH_ASSOC);

				#該 GIID存在
				$nCstatus = $aToken['cstatus'];
				$nCreatetime = $aToken['createtime'];
				$sCq9_token = $aToken['cq9_token'];

				#更新為新的token
				$sGameToken = substr($sWebGuid,0,16);
				$aSQL_Array = array(
					'cq9_token'		=> (string) $sGameToken,
					'cstatus'		=> (int) 0,
					'gid'			=> (int) $nGid,
					'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
					'createtime'	=> (int) now_time,
				);	
				$sSQL = 'UPDATE	cq9_token
					   SET	' . sql_build_array('UPDATE', $aSQL_Array ).'
					   WHERE	G_IID = :G_IID';
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
			}
			$pdo->commit();

			if($nCstatus == 1 || ($nCstatus == 0 && (now_time - $nCreatetime)))
			{
				# token 更換、超過時間
				#登出 cq9 Token
				$aData = array(
					'gametoken'	=> (string) $sCq9_token,
				);
				$sJsonReq = json_encode($aData);

				$aSQL_Array = array(
					'GIID'		=> (int) $nG_IID,
					'gid'			=> (int) $nGid,
					'gametoken'		=> (string) $sCq9_token,
					'json_req'		=> (string) $sJsonReq,
					'tStatus'		=> (int) 3,
					'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
				);
				$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
			}

			if($nCountToken == 0)
			{
				#新增 Token 資料-只CQ9Token-	
				$sGameToken = substr($sWebGuid,0,16);
				$aSQL_Array = array(
					'G_IID'		=> (int) $nG_IID,
					'cq9_token'		=> (string) $sGameToken,
					'gid'			=> (int) $nGid,
					'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
					'createtime'	=> (int) now_time,
				);	
				$sSQL = 'INSERT INTO cq9_token ' . sql_build_array('INSERT', $aSQL_Array );
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
			}
				
			#取得 User資料成功
			$aRes = array(
				'res'		=> (int) $aGameAjaxResCode['success'],
				'tid'		=> (int) 0,
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

	if((($nWeb_enable == 1 && $nEnable == 1) || $nStatus == 99) && $nGid > 0)
	{
		$sGameToken = $sToken;
		$nPlatform = CQ9At_Code;
		$aAuth['gametoken'] = $sGameToken;
		#$aResult = CQ9_fun('auth',$aAuth);	#取 token基本資料
		$aResult = json_decode('{"data":{"account":"Ryan","balance":0,"betlevel":1,"cobrand":{"createat":"2018-10-11T00:17:52.438-04:00","images":[],"ownerid":"591ed7e09fc27a0001eaa900","parentid":"591ed7e09fc27a0001eaa900","permissions":{"basic":true,"custom":false},"updateat":"2019-03-18T21:56:21.204-04:00"},"currency":"CNY","gamecode":"AQ01","gamehall":"cq9","gameplat":"web","gametech":"html5","gametype":"table","id":"5be4fd36df703e00010d479d","ownerid":"591ed7e09fc27a0001eaa900","parentid":"5b986f430b581000019e08e3","webid":1},"status":{"code":"0","message":"Success","datetime":"2019-06-26T02:54:45-04:00","traceCode":"1RlPG2jHdZ9"}}',true);
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
						$dBalance = $s;
						break;
					case 'currency':
						#玩家幣別
						$sCurrency = $s;
						break;
					case 'gamecode':
						#遊戲代號
						$sGamecode = $s;
						break;
					case 'parentid':
						#代理id
						$sParentid = $s;
						break;
					case 'ownerid':
						#上層代理id
						$sOwnerid = $s;
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
			$sSQL = 'SELECT 	cid,
						currency
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
				$dCurrency = $aCurrency['currency'];
				$dBalance = $dBalance * BasicExchange / $dCurrency;
				$sEnabled = 'Y';
			}
			else
			{				
				$iCurrency = 0;
				$dBalance = 0;
				$sEnabled = 'N';
			}

			#判斷該玩家是否已存在
			$sSQL = 'SELECT 	G_IID,
						G_status,
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
				$nG_status = 0;
				$sG_Date = date("Y-m-d H:i:s");
				$aSQL_Array = array(
					'G_Name'		=> (string) $sG_Name,
					'G_NickName'	=> (string) $sAcc,
					'G_Passwd'		=> (string) CQ9PW,
					'G_Date'		=> (string) $sG_Date,
					'platform'		=> (int) CQ9At_Code,
					'G_Enabled'		=> (string) $sEnabled,
					'currencyID'	=> (int) $iCurrency,
					'parentid'		=> (string) $sParentid,
					'ownerid'		=> (string) $sOwnerid,
					'O_Wallet'		=> (double) $dBalance,
				);	
				$sSQL = 'INSERT INTO l_group ' . sql_build_array('INSERT', $aSQL_Array );
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
				$nG_IID = $pdo->lastInsertId(); #GIID
			}
			else
			{
				#取得會員GIID
				$aGIID = $Result->fetch(PDO::FETCH_ASSOC);
				$nG_IID = $aGIID['G_IID'];
				$iNCurrency = $aGIID['currencyID'];
				$nG_status = $aGIID['G_status'];
				
				#修改玩家登入狀態
				$aSQL_Array = array(
					'currencyID'	=> (int) $iCurrency,
					'G_Enabled'		=> (string) $sEnabled,
					'G_Passwd'		=> (string) CQ9PW,
					'parentid'		=> (string) $sParentid,
					'ownerid'		=> (string) $sOwnerid,
					'O_Wallet'		=> (double) $dBalance,
				);	
				$sSQL = 'UPDATE 	l_group 
					   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) . ' 
					   WHERE 	G_IID = :G_IID ';
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				$Result->bindValue(':G_IID', 	$nG_IID, PDO::PARAM_INT);
				sql_query($Result);
			}

			if($iCurrency == 0)
			{
				#登出 cq9 Token
				$aData = array(
					'gametoken'	=> (string) $sGameToken,
				);
				$sJsonReq = json_encode($aData);
				
				#向CQ9發送logout
				$aResult = CQ9_fun('logout',$aData,true,30);
				$sJsonRes = json_encode($aResult);
				$iCode = isset($aResult['status']['code']) ? $aResult['status']['code'] : -99;
				if($iCode == 0)
				{
					if(LogPermission > 1)
					{
						#建立 logout 紀錄-成功-
						$aSQL_Array = array(
							'GIID'		=> (int) $nG_IID,
							'gid'			=> (int) $nGid,
							'gametoken'		=> (string) $sGameToken,
							'json_req'		=> (string) $sJsonReq,
							'json_res'		=> (string) $sJsonRes,
							'tStatus'		=> (int) 1,
							'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
						);
						$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
						$Result = $pdo->prepare($sSQL);
						sql_build_value($Result, $aSQL_Array);
						sql_query($Result);
					}
				}
				else
				{
					#建立 logout紀錄-失敗-
					$aSQL_Array = array(
						'GIID'		=> (int) $nG_IID,
						'gid'			=> (int) $nGid,
						'gametoken'		=> (string) $sGameToken,
						'json_req'		=> (string) $sJsonReq,
						'json_res'		=> (string) $sJsonRes,
						'tStatus'		=> (int) 2,
						'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
					);
					$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
					$Result = $pdo->prepare($sSQL);
					sql_build_value($Result, $aSQL_Array);
					sql_query($Result);
				}
				
				$aRes = array(
					'res'		=> (int) $aGameAjaxResCode['currency_error'],
					'surl'	=> '',
				);

				$pdo = null;
				echo json_encode($aRes);
				exit;
			}

			#查詢 token
			$bAuth = false;
			$nCstatus = -1;
			$pdo->beginTransaction();
			$sSQL = 'SELECT	cq9_token,
						cstatus,
						gid,
						createtime
				   FROM	cq9_token
				   WHERE	G_IID = :G_IID 
				   FOR UPDATE ';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
			sql_query($Result);
			$nCountToken = $Result->rowCount();
			if($nCountToken > 0)
			{
				$aToken = $Result->fetch(PDO::FETCH_ASSOC);

				#該 GIID存在
				$nToken_gid = $aToken['gid'];
				$nCstatus = $aToken['cstatus'];
				$nCreatetime = $aToken['createtime'];
				$sCq9_token = $aToken['cq9_token'];
				
				if($nG_status == 0 || ($nG_status > 0 && $nG_status == $nGid) || ($nG_status > 300 && ($nG_status - 300) == $nGid))
				{
					#更新為新的token
					$aSQL_Array = array(
						'cq9_token'		=> (string) $sGameToken,
						'cq9_id'		=> (string) $sCQ9Gid,
						'auth_json'		=> (string) $sJRes,
						'cstatus'		=> (int) ($nCstatus == 1 ? 1 : 0),
						'gid'			=> (int) $nGid,
						'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
						'createtime'	=> (int) now_time,
					);	
					$sSQL = 'UPDATE	cq9_token
						   SET	' . sql_build_array('UPDATE', $aSQL_Array ).'
						   WHERE	G_IID = :G_IID';
					$Result = $pdo->prepare($sSQL);
					$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
					sql_build_value($Result, $aSQL_Array);
					sql_query($Result);
					
					$bAuth = true;
				}
				else
				{
					#登出 cq9 Token
					$aData = array(
						'gametoken'	=> (string) $sGameToken,
					);
					$sJsonReq = json_encode($aData);
					
					#向CQ9發送logout
					$aResult = CQ9_fun('logout',$aData,true,30);
					$sJsonRes = json_encode($aResult);
					$iCode = isset($aResult['status']['code']) ? $aResult['status']['code'] : -99;
					if($iCode == 0)
					{
						if(LogPermission > 1)
						{
							#建立 logout 紀錄-成功-
							$aSQL_Array = array(
								'GIID'		=> (int) $nG_IID,
								'gid'			=> (int) $nGid,
								'gametoken'		=> (string) $sGameToken,
								'json_req'		=> (string) $sJsonReq,
								'json_res'		=> (string) $sJsonRes,
								'tStatus'		=> (int) 1,
								'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
							);
							$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
							$Result = $pdo->prepare($sSQL);
							sql_build_value($Result, $aSQL_Array);
							sql_query($Result);
						}
					}
					else
					{
						#建立 logout紀錄-失敗-
						$aSQL_Array = array(
							'GIID'		=> (int) $nG_IID,
							'gid'			=> (int) $nGid,
							'gametoken'		=> (string) $sGameToken,
							'json_req'		=> (string) $sJsonReq,
							'json_res'		=> (string) $sJsonRes,
							'tStatus'		=> (int) 2,
							'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
						);
						$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
						$Result = $pdo->prepare($sSQL);
						sql_build_value($Result, $aSQL_Array);
						sql_query($Result);
					}
				
					$aRes = array(
						'res'		=> (int) $aGameAjaxResCode['game_running'],
						'surl'	=> '',
					);
				}
			}
			$pdo->commit();

			#if($nCstatus != 3 && $bAuth == true)
			#{
			#	#登出 cq9 Token
			#	$aData = array(
			#		'gametoken'	=> (string) $sCq9_token,
			#	);
			#	$sJsonReq = json_encode($aData);
			#	
			#	#向CQ9發送logout
			#	$aResult = CQ9_fun('logout',$aData,true,30);
			#	$sJsonRes = json_encode($aResult);
			#	$iCode = isset($aResult['status']['code']) ? $aResult['status']['code'] : -99;
			#	if($iCode == 0)
			#	{
			#		if(LogPermission > 1)
			#		{
			#			#建立 logout 紀錄-成功-
			#			$aSQL_Array = array(
			#				'GIID'		=> (int) $nG_IID,
			#				'gid'			=> (int) $nToken_gid,
			#				'gametoken'		=> (string) $sCq9_token,
			#				'json_req'		=> (string) $sJsonReq,
			#				'json_res'		=> (string) $sJsonRes,
			#				'tStatus'		=> (int) 1,
			#				'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
			#			);
			#			$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
			#			$Result = $pdo->prepare($sSQL);
			#			sql_build_value($Result, $aSQL_Array);
			#			sql_query($Result);
			#		}
			#	}
			#	else
			#	{
			#		#建立 logout紀錄-失敗-
			#		$aSQL_Array = array(
			#			'GIID'		=> (int) $nG_IID,
			#			'gid'			=> (int) $nToken_gid,
			#			'gametoken'		=> (string) $sCq9_token,
			#			'json_req'		=> (string) $sJsonReq,
			#			'json_res'		=> (string) $sJsonRes,
			#			'tStatus'		=> (int) 2,
			#			'createdate'	=> (string) date('Y-m-d H:i:s',now_time),
			#		);
			#		$sSQL = 'INSERT INTO cq9_logout ' . sql_build_array('INSERT', $aSQL_Array );
			#		$Result = $pdo->prepare($sSQL);
			#		sql_build_value($Result, $aSQL_Array);
			#		sql_query($Result);
			#	}
			#}

			if($nCountToken == 0)
			{
				#新增 Token 資料-只CQ9Token-
				$aSQL_Array = array(
					'G_IID'		=> (int) $nG_IID,
					'cq9_id'		=> (string) $sCQ9Gid,
					'auth_json'		=> (string) $sJRes,
					'cq9_token'		=> (string) $sGameToken,
					'gid'			=> (int) $nGid,
					'createdate'	=> (string) Date('Y-m-d H:i:s', now_time),
					'createtime'	=> (int) now_time,
				);	
				$sSQL = 'INSERT INTO cq9_token ' . sql_build_array('INSERT', $aSQL_Array );
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				sql_query($Result);
				
				$bAuth = true;
			}
			
			if($bAuth == true)
			{
				#修改玩家登入狀態
				$aSQL_Array = array(
					'G_SubUser'		=> (string) 'Y',
				);	
				$sSQL = 'UPDATE 	l_group 
					   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) . ' 
					   WHERE 	G_IID = :G_IID ';
				$Result = $pdo->prepare($sSQL);
				sql_build_value($Result, $aSQL_Array);
				$Result->bindValue(':G_IID', 	$nG_IID, PDO::PARAM_INT);
				sql_query($Result);
				
				#取得 User資料成功
				$aRes = array(
					'res'		=> (int) $aGameAjaxResCode['success'],
					'tid'		=> (int) 0,
					'gid'		=> (int) $nGid,
					'acc'		=> (string) $sG_Name,
					'pw'		=> (string) $sG_Passwd,
					'surl'	=> '',
				);
				
				$nG_status > 300 ? $aRes['aut'] = 2 : '';
			}
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

$pdo = null;
echo json_encode($aRes);
exit;
?>