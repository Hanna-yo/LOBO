<?php
#與 CQ9對應的 API網址
$aCQ9Url = array(
	'auth'		=> DomainName.'/gamepool/cq9/player/auth',			#玩家基本資訊
	'rollout'		=> DomainName.'/gamepool/cq9/table/rollout',			#取玩家餘額
	'rollin'		=> DomainName.'/gamepool/cq9/table/rollin',			#轉回玩家餘額
	'logout'		=> DomainName.'/gamepool/cq9/player/logout',			#登出
	'gtoken'		=> DomainName.'/gamepool/cq9/game/generateroundcache',	#回復 gametoken 效用(時間)
	'gtokenid'		=> DomainName.'/gamepool/cq9/game/generateoneroundcache',	#回復 gametoken 效用(roundid)
	'detailtoken'	=> DomainName.'/gamepool/cq9/game/detailtoken',			#驗證細單Token
	'RoundCheck'	=> DomainName.'/gamepool/cq9/game/roundcheck?',			#查詢未完成注單
	'RoundDetail'	=> DomainName.'/gamepool/cq9/game/rounddetail?',		#查詢未完成注單內容
	'GetOrder'		=> DomainName.'/gamepool/cq9/game/getorder?indexid=',		#查詢注單內容
	'CurrencyList'	=> DomainName.'/gamepool/cq9/game/currency',			#幣別列表
	'OrderCredit'	=> DomainName.'/gamepool/cq9/order/credit',			#注單補款
	'OrderDebit'	=> DomainName.'/gamepool/cq9/order/debit',			#注單補款2
	'Balance'		=> DomainName.'/gamepool/cq9/player/balance?id=',		#查詢餘額
);

#回應 Photon 錯誤碼
$aResServerCode = array(
	'Success'			=> 1,	#成功
	'NoPost'			=> 2,	#無POST
	'NoJson'			=> 3,	#非json
	'NoData'			=> 4,	#資料不完整
	'NoCQ9Data'			=> 5,	#無對應的CQ9資料
	'InvalidToken'		=> 6,	#CQ9 token 失效
	'CQ9GamingAPI'		=> 7,	#CQ9 API 無回應
	'NoAcc'			=> 8,	#無此帳號
	'ErrorStatus'		=> 9,	#錯誤單大於20筆
	'Insufficientbalance'	=> 10,	#餘額不足
	'NoRollInCode'		=> 11,	#無 Roll_Out
);
#數值除100
function num_except($iN)
{
	return $iN / 100;
}
#輸贏顏色
function num_color($iN)
{
	$str = ($iN >= 0 ? 'colGreen' : '');
	return $str;
}
#輸贏
function num_wl($iN)
{
	$str = ($iN == 0 ? 'll' : 'ww');
	return $str;
}
#牌圖片名
function cord_name($iN)
{
	if($iN > 256 && $iN < 512)
	{
		#梅花
		$s = $iN % 256;
		$s = '1'.add_zero($s,2);
	}
	elseif($iN > 512 && $iN < 768)
	{
		#方塊
		$s = $iN % 512;
		$s = '2'.add_zero($s,2);
	}
	elseif($iN > 768 && $iN < 1024)
	{
		#紅心
		$s = $iN % 768;
		$s = '3'.add_zero($s,2);
	}
	else
	{
		#黑桃
		$s = $iN % 1024;
		$s = '4'.add_zero($s,2);
	}
	return $s;
}

function is_json($sStr)
{
	#判斷是否為JSON
	# true = 是json
	return is_array(json_decode($sStr,true));
}

function CQ9_fun($sUrl,$aArray,$bY = true,$iT = 30,$iHead = 1)
{
	#呼叫 CQ9 API	
	# sUrl = 要傳的網址,可傳 $aCQ9Url內,對應網址字串
	# aArray = 要傳的資料
	# bY = 預設走 CQ9 API 對應網址,false 則走傳進來的 sUrl
	# iT = 預設 timeout 為 30s
	# iHead = 預設 1 傳 CQ9 授權

	global $aCQ9Url;
	
	$sSendUrl = ($bY == false ? $sUrl : $aCQ9Url[$sUrl]);
	$ch = curl_init();#啟用curl
	$ch = curl_init($sSendUrl);#url
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $iT);	# timeout 30s
	if($iHead == 1)
	{
		#授權(整合站)
		#Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjQ2MDc5MDI2IiwiaWF0IjoxNTM1OTY0NjU0LCJpc3MiOiJDeXByZXNzIiwic3ViIjoiR1NUb2tlbiJ9.A7TYUMzEAeFtg2mJC0x_5LjXJ5Crn-K30BiZAI6K8co
		#授權(正式站)
		#Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjcwNzc0NjY5NyIsImlhdCI6MTU0MTQ5MDg3MywiaXNzIjoiQ3lwcmVzcyIsInN1YiI6IkdTVG9rZW4ifQ.ujx5HTHGeW9OiPzgV-CQV1BJ8MqFCFmb4qhMgsCPrkQ
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjQ2MDc5MDI2IiwiaWF0IjoxNTM1OTY0NjU0LCJpc3MiOiJDeXByZXNzIiwic3ViIjoiR1NUb2tlbiJ9.A7TYUMzEAeFtg2mJC0x_5LjXJ5Crn-K30BiZAI6K8co','Content-Type:application/x-www-form-urlencoded'));#授權(整合站)
	}	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aArray));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);

	if(is_json($result))
	{
		$aResult = json_decode($result,true);
	}
	else if(curl_errno($ch))
	{
		$aResult = 'curl_Error:'.curl_error($ch);
	}
	else if(curl_errno($ch) == 28)
	{
		$aResult = 28;
	}
	else
	{
		$aResult = $result;
	}

	curl_close($ch);
	
	return $aResult;
}

function CQ9_fun_get($sUrl)
{
	#呼叫 CQ9 API GET
	# sUrl = 要傳的網址
	global $aCQ9Url;	
	
	$ch = curl_init();#啟用curl
	$ch = curl_init($sUrl);#url
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	#授權(整合站)
	#Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjQ2MDc5MDI2IiwiaWF0IjoxNTM1OTY0NjU0LCJpc3MiOiJDeXByZXNzIiwic3ViIjoiR1NUb2tlbiJ9.A7TYUMzEAeFtg2mJC0x_5LjXJ5Crn-K30BiZAI6K8co
	#授權(正式站)
	#Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjcwNzc0NjY5NyIsImlhdCI6MTU0MTQ5MDg3MywiaXNzIjoiQ3lwcmVzcyIsInN1YiI6IkdTVG9rZW4ifQ.ujx5HTHGeW9OiPzgV-CQV1BJ8MqFCFmb4qhMgsCPrkQ
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjQ2MDc5MDI2IiwiaWF0IjoxNTM1OTY0NjU0LCJpc3MiOiJDeXByZXNzIiwic3ViIjoiR1NUb2tlbiJ9.A7TYUMzEAeFtg2mJC0x_5LjXJ5Crn-K30BiZAI6K8co','Content-Type:application/x-www-form-urlencoded'));#授權(整合站)
	$result = curl_exec($ch);

	if(is_json($result))
	{
		$aResult = json_decode($result,true);
	}
	elseif(curl_errno($ch))
	{
		$aResult = curl_error($ch);
	}
	else
	{
		$aResult = $result;
	}

	curl_close($ch);
	
	return $aResult;
}

function get_CQ9_time($iTime = 0)
{
	#取得CQ9相應時區時間
	#預設為當前時間,有 iTime 則轉換指定時間
	#時區 UTC-4
	#格式 RFC3339	

	##啟用curl
	#global $aCQ9Url;	
	#$ch = curl_init();
	#$ch = curl_init($aCQ9Url['CurrencyList']); #幣值網址
	#curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	#curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	#curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJnYW1laGFsbCI6ImNxOSIsInRlYW0iOiJBUSIsImp0aSI6IjQ2MDc5MDI2IiwiaWF0IjoxNTM1OTY0NjU0LCJpc3MiOiJDeXByZXNzIiwic3ViIjoiR1NUb2tlbiJ9.A7TYUMzEAeFtg2mJC0x_5LjXJ5Crn-K30BiZAI6K8co','Content-Type:application/x-www-form-urlencoded'));#授權(整合站)
	#curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	#$result = curl_exec($ch);
	#echo $result;
	#curl_close($ch);

	if($iTime == 0)
	{
		#當前時間轉換
		date_default_timezone_set('America/Puerto_Rico');
		$iT = Decimal_number(microtime(true));		
		$sTime = Date('Y-m-d H:i:s',$iT);
		$aTime = explode('.', $iT);
		$sMicroTime = '.'.$aTime[(count($aTime) - 1)];
		$rTime = substr_replace(date('Y-m-d\TH:i:sP',$iT),$sMicroTime,19,0);
		date_default_timezone_set('Asia/Taipei');
	}
	else
	{
		#指定時間轉換
		date_default_timezone_set('America/Puerto_Rico');
		$rTime = date('Y-m-d\TH:i:sP',$iTime);	#時間差
		date_default_timezone_set('Asia/Taipei');
	}
	
	return $rTime;
}

function Decimal_number($dNum,$i = 0)
{
	#回傳格式化數值(預設最少小數兩位)
	if(stripos($dNum,'e'))
	{
		$nFnum = substr($dNum,-1);
		$dNum = sprintf('%.'.$nFnum.'f',$dNum);
	}
	$aArr = explode('.',$dNum);
	$iNum = isset($aArr[1]) ? strlen($aArr[1]) : 0;	
	$iR = ($i != 0 ? $i : ($iNum > 2 ? $iNum : 2));
	$sNum = str_replace(",", "", number_format($dNum,$iR));
	
	return $sNum;
}

function number_format_two($num)
{
	#數值格式，取到小數點第二位
	$tnum = str_replace(",", "", number_format($num,2));
	return $tnum;
}

function AddKey($len)
{
	#產生對接密鑰-API
	$sCher = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9';
	$aChar = explode(',',$sCher);
	$sStr = '';

	for($i = 0; $i < $len; $i++)
	{
		shuffle($aChar);
		$sStr .= $aChar[rand(0, (count($aChar)-1))];
	}

	return $sStr;
}

function AddToken($sAcc,$iAgentID,$iGid)
{
	#產生 WebToken-API
	$sStr = md5(md5(md5($sAcc).$iAgentID.now_time).$iGid.AddKey(30));
	return $sStr;
}

function data_incomplete()
{
	#判斷資料是否為空-API
	global $aErrorResult;
	global $pdo;

	$Result = array(
		"Result" => $aErrorResult['DataIncomplete'],
	);
	$Result = base64_encode(json_encode($Result));
	$pdo = null;
	echo $Result;
	exit;
}

function result_error($iError)
{
	#傳出錯誤結果-API
	global $pdo;

	$Result = array(
		"Result" => $iError,
	);
	$Result = base64_encode(json_encode($Result));
	$pdo = null;
	echo $Result;
	exit;
}

function account_format($sStr,$sBack)
{
	#判斷帳號格式-API
	if(strpos($sStr,'@'))
	{
		$aArray = explode("@",$sStr);
		$iC = count($aArray);
		$sR = ($aArray[($iC-1)] == $sBack ? true : false);
		return $sR;
	}
	else
	{
		return false;
	}
}

function check_client_ip($nPlatform,$sType)
{
	#判斷是否為合法IP
	global $pdo;
	global $aErrorResult;
	$bOk = false;
	
	#取得對接IP
	if(!empty($_SERVER['HTTP_CLIENT_IP']))
	{
		$sIp = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$sIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif(!empty($_SERVER['REMOTE_ADDR']))
	{
		$sIp = $_SERVER['REMOTE_ADDR'];
	}
	else
	{
		$sIp = '';
	}

	#查詢相應的白名單IP
	$sSQL = 'SELECT 	whitelist
		   FROM 	agent_ip
		   WHERE 	platform = :platform
		   AND	ip_enable = 1';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':platform', 	$nPlatform, PDO::PARAM_INT);
	sql_query($Result);
	$iCont = $Result->rowCount();
	if($iCont > 0)
	{
		while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
		{
			if($aRow['whitelist'] == $sIp)
			{
				$bOk = true;
			}
		}
	}	

	if($bOk == false && IPLogPermission > 1)
	{
		#errIP
		$aSQL_Array = array(
			'err_ip'		=> (string) $sIp,
			'platform'		=> (int) $nPlatform,
			'api_type'		=> (string) $sType,
			'createdate'	=> (string) Date('Y-m-d H:i:s'),
		);	
		$sSQL = 'INSERT INTO agent_ip_err ' . sql_build_array('INSERT', $aSQL_Array );
		$Result = $pdo->prepare($sSQL);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		result_error($aErrorResult['NoIp']);
	}
}

function API_IN($sStr_64)
{
	#API 資料傳入 解碼
	global $pdo;
	global $aErrorResult;

	#無參數
	if(empty($sStr_64))
	{
		result_error($aErrorResult['NullParameter']);
	}

	$sStr_json = base64_decode($sStr_64);	#參數解碼base64

	#判斷是否為json
	if(is_json($sStr_json))
	{		
		$aArray = json_decode($sStr_json,true);	#參數解碼json
	}
	else
	{
		result_error($aErrorResult['NoJson']);
	}

	#判斷有無對接方代碼
	if(empty($aArray['AgentCode']))
	{
		result_error($aErrorResult['NullAgentCode']);
	}
	#判斷有無對接類型
	elseif(empty($aArray['ActType']))
	{
		result_error($aErrorResult['NullActType']);
	}
	#判斷有無資料
	elseif(empty($aArray['Params']))
	{
		result_error($aErrorResult['NullParams']);
	}
	#判斷有無驗證碼
	elseif(empty($aArray['Sign']))
	{
		result_error($aErrorResult['NullSign']);
	}
	#判斷資料是否為json
	elseif(!is_json($aArray['Params']))
	{	
		#非json	
		result_error($aErrorResult['NoJson']);
	}
	else
	{
		$aParams = json_decode($aArray['Params'],true);	#資料解碼json

		#查詢是否有此對接方代碼
		$sSQL = 'SELECT 	At_Code,
					agent_code,
					encrypt,
					At_Enable
			   FROM	agent
			   WHERE 	At_Code = :At_Code ' . sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':At_Code', $aArray['AgentCode'], PDO::PARAM_INT);
		sql_query($Result);
		$iCount = $Result->rowCount();
		$aARow = $Result->fetch(PDO::FETCH_ASSOC);

		#對接方代碼有誤
		if($iCount < 1)
		{
			result_error($aErrorResult['AgentCodeError']);
		}
		#對接方權限不足
		else if($aArray['ActType'] != 'GetRecord' && $aArray['ActType'] != 'RecordModify' && $aARow['At_Enable'] != 1)
		{
			result_error($aErrorResult['InsufficientPermissions']);
		}
		else
		{
			$aARow['encrypt'] <> '' ? $sKey = $aARow['encrypt'] : $sKey = '';
			$sSignKey = md5(md5($aArray['Params']).$sKey);

			if($aArray['Sign'] == $sSignKey)
			{
				$aData = array(
					'Result'		=> '20',
					'ActType'		=> $aArray['ActType'],
					'At_Code'		=> $aARow['At_Code'],
					'agent_code'	=> $aARow['agent_code'],
					'Params'		=> $aParams,
				);
			}
			else
			{
				#驗證失敗
				result_error($aErrorResult['SignError']);
			}			
		}
	}
	return $aData;
}
?>