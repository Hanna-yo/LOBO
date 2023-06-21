<?php
function chg_member_vscode($nVal)
{
	global $pdo;

	$nVscode = $nVal;
	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';

	$aSQL_Array = array(
		'vscode'	=> (int) $nVscode,
	);

	$sSQL = 'UPDATE	member_cookie
		SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
		WHERE	ssid = :ssid
		AND 	member_id = 0';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
function get_member_vscode()
{
	global $pdo;

	$nVscode = 0;
	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';
	$sSQL = 'SELECT vscode
		 FROM	member_cookie
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
function chk_member_login($aVal)
{
	global $pdo;

	$state = 0;
	$cset = 0;
	$txt = '';
	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';
	/*判斷管理者身份是否正確*/
	$sSQL = 'SELECT cid, cname0, cemail0, sys_member_code
		 FROM	member
		 WHERE	sys_member_code = :sys_member_code
		 AND 	cpassword = :cpassword
		 AND 	online = 1
		 ORDER BY cid DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':sys_member_code',	$aVal['account'],	PDO::PARAM_STR);
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
			 FROM	member_cookie
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


			$sSQL = 'DELETE	FROM member_cookie WHERE member_id = :member_id';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':member_id', $aRow['cid'], PDO::PARAM_INT);
			sql_query($Result);

			$aSQL_Array = array(
				'ip'		=> (string) user_ip,
				'caccount'	=> (string) $aRow['sys_member_code'],
				'cpassword'	=> (string) '',
				'cset'		=> (int) $cset,
				'txt'		=> (string) $txt,
				'wtime'		=> (int) now_time,
			);

			$sSQL = 'INSERT INTO member_log ' . sql_build_array('INSERT', $aSQL_Array );
			$Result = $pdo->prepare($sSQL);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		}

		$cset = 1;
		$txt = '登入成功 ';

		$aSQL_Array = array(
			'member_id'	=> (int) $aRow['cid'],
			'account'	=> (string) $aRow['sys_member_code'],
		);

		$sSQL = 'UPDATE	member_cookie
			SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
			WHERE	ssid = :ssid
			AND 	member_id = 0';
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

	$sSQL = 'INSERT INTO member_log ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);

	#寫入記錄
	return $state;
}
function adm_login_member($aVal)
{
	global $pdo;

	$s = 0;
	$sAdm_Ssid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';

	if ($sAdm_Ssid == '')
	{
		$s = 1;
	}

	$sSQL = 'SELECT ssid,
			manager_id,
			account
		 FROM 	adm_verification
		 WHERE 	ssid = :ssid
		 ORDER BY ssid DESC '. sql_limit(0, 1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sAdm_Ssid, PDO::PARAM_INT);
	sql_query($Result);
	$aAdm = $Result->fetch(PDO::FETCH_ASSOC);

	if (empty($aAdm))
	{
		$s = 1;
	}

	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : md5(adm_key.now_time);

	if ($s == 0)
	{
		$sSQL = 'SELECT cid,
				sys_member_code
			 FROM 	member
			 WHERE 	cid = :member_id
			 ORDER BY cid DESC '. sql_limit(0, 1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':member_id', $aVal['id'], PDO::PARAM_INT);
		sql_query($Result);
		$aMem = $Result->fetch(PDO::FETCH_ASSOC);

		$sSQL = 'SELECT ssid,
				member_id
			 FROM 	member_cookie
			 WHERE 	member_id = :member_id
			 ORDER BY createtime DESC '. sql_limit(0, 1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':member_id', $aMem['cid'], PDO::PARAM_INT);
		sql_query($Result);
		$aRow = $Result->fetch(PDO::FETCH_ASSOC);
		if (empty($aRow))
		{
			$aSQL_Array = array(
				'member_id'	=> (int) $aMem['cid'],
				'account'	=> (string) $aMem['sys_member_code'],
			);

			$sSQL = 'UPDATE	member_cookie
				SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
				WHERE	ssid = :ssid';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		}
		else
		{
			$sSQL = 'DELETE	FROM member_cookie WHERE member_id = :member_id';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':member_id', $aMem['cid'], PDO::PARAM_INT);
			sql_query($Result);

			$aSQL_Array = array(
				'member_id'	=> (int) $aMem['cid'],
				'account'	=> (string) $aMem['sys_member_code'],
			);

			$sSQL = 'UPDATE	member_cookie
				SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
				WHERE	ssid = :ssid';
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
			sql_build_value($Result, $aSQL_Array);
			sql_query($Result);
		}

		$txt = '后台管理者「'. $aAdm['account'] .'」登入会员「'.$aMem['sys_member_code'].'」帐号';
		#登入記錄
		$aSQL_Array = array(
			'ip'		=> (string) user_ip,
			'caccount'	=> (string) $aAdm['account'],
			'cpassword'	=> (string) '',
			'cset'		=> (int) 1,
			'txt'		=> (string) $txt,
			'wtime'		=> (int) now_time,
		);
		$sSQL = 'INSERT INTO member_log ' . sql_build_array('INSERT', $aSQL_Array );
		$Result = $pdo->prepare($sSQL);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);
	}

	return $s;
}
function chk_member_logout()
{
	global $pdo;

	$state = 0;
	$cset = 3;
	$txt = '登出';
	/*建立登出記錄*/
	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';
	$sSQL = 'SELECT account
		 FROM	member_cookie
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
		$sSQL = 'INSERT INTO member_log ' . sql_build_array('INSERT', $aSQL_Array );
		$Result = $pdo->prepare($sSQL);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);

		$sSQL = 'DELETE	FROM member_cookie WHERE ssid = :ssid';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
		sql_query($Result);

		setcookie('m_user_c', '', cookie_close, '/');
	}
	else
	{
		$state = 1;
	}
	return $state;
}
?>