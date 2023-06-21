<?php
function JWTencode($aData, $alg = 'HS256')
{
	$aHeader = array('alg' => $alg,'typ' => 'JWT');
	$sKey = 'd29b287b0c0fd7a0d3fcc79f35cd981b';	
	$aData['iat'] = (int) microtime(1);
	$jwt = urlsafeB64Encode(json_encode($aHeader)) . '.' . urlsafeB64Encode(json_encode($aData));
	return $jwt . '.' . urlsafeB64Encode(signature($jwt, $sKey, $alg));
}

function signature($input, $key, $alg)
{
	return hash_hmac('SHA256', $input, $key,true);
}

function urlsafeB64Encode($input)
{
	return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

function TidySend($sUrlName,$aData)
{
	$aTidyUrl = array(
		'UserInfo' 		=> array('/api/user/outside/info','GET'), 	#查詢帳號資訊
		'CashDeposit' 	=> array('/api/cash/outside/deposit','POST'),	#存款並創建帳戶
		'CashWithdraw' 	=> array('/api/cash/outside/withdraw','POST'),	#提款
		'CashCheck' 	=> array('/api/cash_entry/outside/check','GET'),#交易明細查詢
		'UserBalance'	=> array('/api/user/outside/balance','GET'),	#查詢帳戶額度
		'GameList'		=> array('/api/game/outside/list','GET'),		#取得遊戲列表
		'GameLink'		=> array('/api/game/outside/link','POST'),	#取得遊戲連結
		'WagersList'	=> array('/api/wagers/outside/list','GET'),	#取得下注紀錄
		'ReportList'	=> array('/api/report/outside/list','GET'),	#取得代理商報表
		'KickUser'		=> array('/api/user/outside/kick','POST'),	#強制指定用戶離線
		'KickAll'		=> array('/api/user/outside/kick/all','POST'),	#強制所有用戶離線
	);
	$sApiUrl = 'https://api.w942u6.com';
	$aData['client_id'] = '469ec019';
	$sHttpheader = JWTencode($aData);
	$sGet = http_build_query($aData);

	list($sUrl,$sMethod) = $aTidyUrl[$sUrlName];

	$ch = curl_init();#啟用curl
	$ch = curl_init($sApiUrl.$sUrl.'?'.$sGet);#url
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($ch,CURLOPT_TIMEOUT,30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$sHttpheader));#授權(正式站)

	if($sMethod == 'GET')
	{
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HTTPGET, true);
	}
	else
	{
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
	}
	
	$result = curl_exec($ch);

	return $result;
}

function get_Tidy_time($iTime = 0)
{
	$iTime = ($iTime == 0 ? time() : $iTime);
	date_default_timezone_set('UTC');
	$sTime = date('Y-m-d\TH:i:sP',$iTime);
	date_default_timezone_set('Asia/Taipei');
	
	return $sTime;
}

function Tidy_amount_out($nGiid,$dAmount,$nGid,$sGtoken)
{
	/*
	#餘額轉出
	# $dAmount 參數需已乘 100
	# 1 => 成功
	# 2 => API 失敗
	# 3 => 餘額不足
	*/

	global $pdo;

	#查詢該玩家
	$sSQL = 'SELECT	l.G_Name,
				l.G_Wallet,
				c.currency
		   FROM	l_group l,
		   		currency c
		   WHERE	l.G_IID = :G_IID
		   AND	c.cid = l.currencyID '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':G_IID', 	$nGiid, PDO::PARAM_INT);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$dCurrency = $aRow['currency'];
	$sG_Name = $aRow['G_Name'];
	$dG_Wallet = $aRow['G_Wallet'];
	$dN_Wallet = $dG_Wallet - $dAmount;

	if($dG_Wallet < $dAmount)
		return 3;

	#建立交易單(轉出)
	$dG_Amount = $dAmount / BasicExchange * $dCurrency;
	$aReqData = array(
		'username' 	=> $sG_Name,
		'currency' 	=> '156',
		'amount' 	=> $dG_Amount,
	);
	$sReqData = json_encode($aReqData);
	$aSQL_Array = array(
		'G_IID'		=> (int) $nGiid,
		'G_Name'		=> (string) $sG_Name,
		'G_Credit'		=> $dAmount,
		'N_Credit'		=> $dN_Wallet,
		'O_Credit'		=> $dG_Wallet,
		'tinout'		=> (int) 2,
		'tgid'		=> (int) $nGid,
		'tstatus'		=> (int) 1,
		'gtoken'		=> (string) $sGtoken,
		'currency'		=> $dCurrency,
		'json_req'		=> $sReqData,
		'createdate'	=> (string) Date("Y-m-d H:i:s",now_time),
		'createtime'	=> (int) now_time,		
	);
	$sSQL = 'INSERT INTO trf_pay ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
	$iOrderId = $pdo->lastInsertId();

	#Tidy 對接
	$sResData = TidySend('CashDeposit',$aReqData);
	$aResData = json_decode($sResData,true);
	if(isset($aResData['cash_entry']))
	{
		#更新交易單(轉出)
		$aSQL_Array = array(
			'json_res'		=> $sResData,
			'tstatus'		=> 2,
		);
		$sSQL = 'UPDATE 	trf_pay 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	cid = :cid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':cid', $iOrderId, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		#更新玩家餘額
		$aSQL_Array = array(
			'G_Wallet'		=> $dN_Wallet,
			'O_Wallet'		=> $dN_Wallet,
		);
		$sSQL = 'UPDATE	l_group 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	G_IID = :G_IID';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':G_IID', $nGiid, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		return 1;
	}
	else
	{
		#更新交易單(轉出)
		$aSQL_Array = array(
			'json_res'		=> $sResData,
			'tstatus'		=> 2,
		);
		$sSQL = 'UPDATE 	trf_pay 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	cid = :cid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':cid', $iOrderId, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);
		
		return 2;
	}
}

function Tidy_amount_in($nGiid,$dAmount,$nGid,$sGtoken)
{
	/*
	#餘額轉入
	# $dAmount 參數需已乘 100
	# 1 => 成功
	# 2 => API 失敗
	*/

	#餘額轉入
	global $pdo;

	#查詢該玩家
	$sSQL = 'SELECT	l.G_Name,
				l.G_Wallet,
				c.currency
		   FROM	l_group l,
		   		currency c
		   WHERE	l.G_IID = :G_IID
		   AND	c.cid = l.currencyID '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':G_IID', 	$nGiid, PDO::PARAM_INT);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$dCurrency = $aRow['currency'];
	$sG_Name = $aRow['G_Name'];
	$dG_Wallet = $aRow['G_Wallet'];

	#建立交易單(轉入)
	$dN_Wallet = $dG_Wallet + $dAmount;
	$dG_Amount = $dAmount / BasicExchange * $dCurrency;
	$aReqData = array(
		'username' 	=> $sG_Name,
		'currency' 	=> '156',
		'amount' 	=> $dG_Amount,
	);
	$sReqData = json_encode($aReqData);
	$aSQL_Array = array(
		'G_IID'		=> (int) $nGiid,
		'G_Name'		=> (string) $sG_Name,
		'G_Credit'		=> $dAmount,
		'N_Credit'		=> $dN_Wallet,
		'O_Credit'		=> $dG_Wallet,
		'tinout'		=> (int) 1,
		'tgid'		=> (int) $nGid,
		'tstatus'		=> (int) 1,
		'gtoken'		=> (string) $sGtoken,
		'currency'		=> $dCurrency,
		'json_req'		=> $sReqData,
		'createdate'	=> (string) Date("Y-m-d H:i:s",now_time),
		'createtime'	=> (int) now_time,		
	);
	$sSQL = 'INSERT INTO trf_pay ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
	$iOrderId = $pdo->lastInsertId();

	#Tidy 對接
	$sResData = TidySend('CashWithdraw',$aReqData);
	$aResData = json_decode($sResData,true);
	if(isset($aResData['cash_entry']))
	{
		#更新交易單(轉入)
		$aSQL_Array = array(
			'json_res'		=> $sResData,
			'tstatus'		=> 2,
		);
		$sSQL = 'UPDATE 	trf_pay 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	cid = :cid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':cid', $iOrderId, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		#更新玩家餘額
		$aSQL_Array = array(
			'G_Wallet'		=> $dN_Wallet,
			'O_Wallet'		=> $dN_Wallet,
		);
		$sSQL = 'UPDATE	l_group 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	G_IID = :G_IID';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':G_IID', $nGiid, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		return 1;
	}
	else
	{
		#更新交易單(轉入)
		$aSQL_Array = array(
			'json_res'		=> $sResData,
			'tstatus'		=> 2,
		);
		$sSQL = 'UPDATE 	trf_pay 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).' 
			   WHERE 	cid = :cid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':cid', $iOrderId, PDO::PARAM_INT);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		return 2;
	}
}

function ApiErrorTidy($sName,$aReq,$aRes)
{
	/*
	$sG_Name => 帳號
	$aReqData => 傳出參數
	$sResData => 回傳參數
	*/	
	global $pdo;

	#建立對接錯誤訊息
	$aSQL_Array = array(
		'G_Name'		=> (string) $sName,
		'createtime'	=> (string) Date('Y-m-d H:i:s',now_time),
		'req_txt'		=> (string) json_encode($aReq),
		'res_txt'		=> (string) json_encode($aRes),
	);
	$sSQL = 'INSERT INTO tmp_tidy_error ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
?>