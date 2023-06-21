<?php
function chg_adm_vscode($nVal)
{
	global $pdo;

	$nVscode = $nVal;
	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';

	$aSQL_Array = array(
		'vscode'	=> (int) $nVscode,
	);

	$sSQL = 'UPDATE	adm_verification
		SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
		WHERE	ssid = :ssid
		AND 	manager_id = 0';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
function get_adm_vscode()
{
	global $pdo;

	$nVscode = 0;
	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';
	$sSQL = 'SELECT vscode
		 FROM	adm_verification
		 WHERE	ssid = :ssid
		 AND 	createtime >= '. cookie_max_time .'
		 ORDER BY createtime DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	if (empty($aRow))
	{
		$nVscode = '';
	}
	else
	{
		$nVscode = isset($aRow['vscode']) ? $aRow['vscode'] : '';
	}

	return $nVscode;
}
function chk_adm_login($aVal)
{
	global $pdo;

	$state = 0;
	$cset = 0;
	$txt = '';
	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';
	/*判斷管理者身份是否正確*/
	$sSQL = 'SELECT cid, cname1
		 FROM	adm_manager_base
		 WHERE	cname1 = :cname1
		 AND 	cpassword = :cpassword
		 AND 	online = 1
		 ORDER BY cid DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':cname1',		$aVal['account'],	PDO::PARAM_STR);
	$Result->bindValue(':cpassword',	$aVal['password'],	PDO::PARAM_STR);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$nCount = $Result->rowCount();
	if ( $nCount == 0 )
	{#登入失敗
		$state = 1;
		$txt = '登入失敗';
	}

	if ( $state == 0 )
	{
		#登入成功
		$nCount = 0;

		$sSQL = 'SELECT account
			 FROM	adm_verification
			 WHERE	ssid = :ssid
			 ORDER BY account DESC ' . sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':ssid',		$sSsid,	PDO::PARAM_STR);
		sql_query($Result);
		$nCount = $Result->rowCount();
		if ( $nCount > 0)
		{
			$cset = 3;
			$txt = '系統強制登出過期帳號';


			$sSQL = 'DELETE	FROM adm_verification WHERE manager_id = :manager_id';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':manager_id', $aRow['cid'], PDO::PARAM_INT);
			sql_query($Result);

			$aSQL_Array = array(
				'ip'		=> (string) user_ip,
				'caccount'	=> (string) $aRow['cname1'],
				'cpassword'	=> (string) '',
				'cset'		=> (int) $cset,
				'txt'		=> (string) $txt,
				'wtime'		=> (int) now_time,
			);

			$sSQL = 'INSERT INTO adm_log ' . sql_build_array('INSERT', $aSQL_Array );
			$Result = $pdo->prepare($sSQL);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		}

		$cset = 1;
		$txt = '登入成功 ';

		$aSQL_Array = array(
			'manager_id'	=> (int) $aRow['cid'],
			'account'	=> (string) $aRow['cname1'],
		);

		$sSQL = 'UPDATE	adm_verification
			SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
			WHERE	ssid = :ssid
			AND 	manager_id = 0';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);
	}
	#登入記錄
	$aSQL_Array = array(
		'ip'		=> (string) user_ip,
		'caccount'	=> (string) $aVal['account'],
		'cpassword'	=> (string) $aVal['password'],
		'cset'		=> (int) $cset,
		'txt'		=> (string) $txt,
		'wtime'		=> (int) now_time,
	);

	$sSQL = 'INSERT INTO adm_log ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);

	#寫入記錄
	return $state;
}
function chk_adm_logout()
{
	global $pdo;

	$state = 0;
	$cset = 3;
	$txt = '登出';
	/*建立登出記錄*/
	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';
	$sSQL = 'SELECT account
		 FROM	adm_verification
		 WHERE	ssid = :ssid
		 ORDER BY account DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid',		$sSsid,	PDO::PARAM_STR);
	sql_query($Result);
	$nCount = $Result->rowCount();
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	if ($nCount == 1)
	{
		$aSQL_Array = array(
			'ip'		=> (string) user_ip,
			'caccount'	=> (string) $aRow['account'],
			'cpassword'	=> (string) '',
			'cset'		=> (int) $cset,
			'txt'		=> (string) $txt,
			'wtime'		=> (int) now_time,
		);
		$sSQL = 'INSERT INTO adm_log ' . sql_build_array('INSERT', $aSQL_Array );
		$Result = $pdo->prepare($sSQL);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		$sSQL = 'DELETE	FROM adm_verification WHERE ssid = :ssid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
		sql_query($Result);

		setcookie('a_user_c', '', cookie_close, '/');
	}
	else
	{
		$state = 1;
	}
	return $state;
}
?>