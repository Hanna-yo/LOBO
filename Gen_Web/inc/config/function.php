<?php
function web_base()
{
	global $pdo;

	$aRe_Val = array();

	$sSQL = 'SELECT cid,
			ctitle0,
			proxy_per,
			smtp
		 FROM	adm_web_base
		 WHERE	1 = 1
		 ORDER BY cid DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$nCount = $Result->rowCount();

	if ($nCount == 1)
	{
		$aRe_Val = $aRow;
	}
	else
	{
		$aRe_Val = false;
	}
	return $aRe_Val;

}
function bulid_cookie()
{
	global $pdo;

	$a_user_c = md5(adm_key.now_time);
	setcookie("a_user_c", $a_user_c, now_time+3600, '/');

	$aSQL_Array = array(
		'ssid'		=> (string) $a_user_c,
		'createtime'	=> (int) now_time,
	);

	$sSQL = 'INSERT INTO adm_verification ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
function member_bulid_cookie()
{
	global $pdo;

	$m_user_c = md5(adm_key.now_time);
	setcookie("m_user_c", $m_user_c, now_time+3600, '/');

	$aSQL_Array = array(
		'ssid'		=> (string) $m_user_c,
		'createtime'	=> (int) now_time,
	);

	$sSQL = 'INSERT INTO member_cookie ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
function is_adm_login()
{
	global $pdo;

	$state = 0;
	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';

	$sSQL = 'SELECT manager_id
		 FROM	adm_verification
		 WHERE	ssid = :ssid
		 AND 	createtime >= '. cookie_max_time .'
		 ORDER BY manager_id DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid',	$sSsid,	PDO::PARAM_STR);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$nCount = $Result->rowCount();
	if ( $nCount == 1 )
	{
		if ($aRow['manager_id'] == 0)
		{
			$state = 1;
		}
		else
		{
			#目前登入中
			$state = 0;
		}
	}
	else
	{
		$state = 2;
		setcookie('a_user_c', '', cookie_close, '/');
		bulid_cookie();
	}

	#清除過期cookie
	$sSQL = 'DELETE	FROM adm_verification WHERE createtime < '.cookie_max_time;
	$Result = $pdo->prepare($sSQL);
	sql_query($Result);

	return $state;
}
function is_member_login()
{
	global $pdo;

	$state = 0;
	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';

	$sSQL = 'SELECT member_id
		 FROM	member_cookie
		 WHERE	ssid = :ssid
		 AND 	createtime >= '. cookie_max_time .'
		 ORDER BY member_id DESC ' . sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid',		$sSsid,	PDO::PARAM_STR);
	sql_query($Result);
	$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	$nCount = $Result->rowCount();
	if ( $nCount == 1 )
	{
		if ($aRow['member_id'] == 0)
		{
			$state = 1;
		}
		else
		{
			#目前登入中
			$state = 0;
		}
	}
	else
	{
		$state = 2;
		setcookie('m_user_c', '', cookie_close, '/');
		member_bulid_cookie();
	}

	#清除過期cookie
	$sSQL = 'DELETE	FROM member_cookie WHERE createtime < '.cookie_max_time;
	$Result = $pdo->prepare($sSQL);
	sql_query($Result);

	return $state;
}
function chg_adm_time()
{
	global $pdo;

	$sSsid = isset($_COOKIE['a_user_c']) ? $_COOKIE['a_user_c'] : '';
	setcookie('a_user_c', $_COOKIE['a_user_c'], now_time + 3600, '/');
	$aSQL_Array = array(
		'createtime'	=> (int) now_time,
	);

	$sSQL = 'UPDATE	adm_verification
		SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
		WHERE	ssid = :ssid';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
function chg_member_time()
{
	global $pdo;

	$sSsid = isset($_COOKIE['m_user_c']) ? $_COOKIE['m_user_c'] : '';
	setcookie('m_user_c', $_COOKIE['m_user_c'], now_time + 3600, '/');
	$aSQL_Array = array(
		'createtime'	=> (int) now_time,
	);

	$sSQL = 'UPDATE	member_cookie
		SET	' . sql_build_array('UPDATE', $aSQL_Array) . '
		WHERE	ssid = :ssid';
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':ssid', $sSsid, PDO::PARAM_STR);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
#導頁
function redirect($dataInfo){
	$js=null;
	$params="";
	if(is_array($dataInfo['params'])){
		foreach($dataInfo['params'] as $key	=>	$val){
			$params .= "&".$key."=".$val;
		}
	}
	if($dataInfo['alert']==0)
	{
		$js = "<script language='javascript' type='text/javascript'>".
					"location.href='".$dataInfo['URL']."?t=".time().$params."'".
					"</script>";
	}
	elseif($dataInfo['alert']==2)
	{
		$js = "<script language='javascript' type='text/javascript'>".
					"alert('".$dataInfo['msg']."');".					
					"</script>";
	}
	else
	{
		$js = "<script language='javascript' type='text/javascript'>".
					"alert('".$dataInfo['msg']."');".
					"location.href='".$dataInfo['URL']."?t=".time().$params."'".
					"</script>";
	}
	echo $js;
}
#檔案上傳
function uploadfile($v){
	$state = 0;
	if ($v['file']["name"] != "") {
		#$filesize = $_FILES["main_image"]["size"];
		$filetype = explode(".",$v['file']["name"]);
		$filename = $filetype[0];
		$filetype = $filetype[1];
		$filesize = $v['file']['size'];
		if ($v['ctrl_type']!=''){
			$ctrl_type = explode(',',$v['ctrl_type']);
			foreach($ctrl_type as $key => $val){
				if ($val==$filetype){
					$state = 1;
				}
			}
		}
		if ($state == 1){
			$filename = rand(0,9999).date('md'.(date('H')+8).'isY').".".$filetype;
			#$filename = $filename.".".$filetype;
			$fpath=explode("/",$v['path']);
			$tmp=$fpath[0];
			for($i=1;$i < count($fpath);$i++){
				$tmp.="/".$fpath[$i];
				if(!is_dir($tmp)){
					mkdir($tmp,0777);
				}
			}
			if($v['file']["tmp_name"] != null){
				if (copy($v['file']["tmp_name"],$tmp."/".$filename)){
					$finfo[0]=0;
					$finfo[1]=$filename;
					$finfo[2]=$filesize;
				}
				else{
					$finfo[0]=1;
					$finfo[1]=$v['file']["name"];
					$finfo[2]=$filesize;
				}

			}
			else{
				$finfo[0]=2;
				$finfo[1]="查無檔案";
				$finfo[2]=0;
			}
		}else{
			$finfo[0]=3;
			$finfo[1]="檔案類型錯誤";
			$finfo[2]=0;
		}
	}
	return $finfo;
}
#$sVal : 字串 $nLen : 總長度
function add_zero($sVal, $nLen)
{
	$sRe_Val = '';
	$nTemp_len = strlen($sVal);
	$nGet_zero = 0;
	if ( $nTemp_len < $nLen )
	{
		$nGet_zero = $nLen - $nTemp_len;
		for( $i=1; $i <= $nGet_zero ; $i++ )
		{
			$sRe_Val .= '0';
		}
	}
	$sRe_Val .= $sVal;
	return $sRe_Val;
}
function rand_get_out($v)
{
	$re = rand(0, 9);
	if ($re == $v)
	{
		$re = 0;
	}
	return $re;
}
#分頁
#函數listpag(每頁顯示筆數,頁數,總筆數,對應參數)
function page_list($v){
	$aLang = array(
		'no1'		=> '第一页',
		'along10'	=> '前10页',
		'next10'	=> '下10页',
		'last'		=> '最末页',
	);
	$aRe_Val = array(
		'html'		=> '',
		'first'		=> '',
		'end'		=> '',
		'along10'	=> '',
		'next10'	=> '',
		'all_page'	=> '',
	);
	$aTemp = array(
		'style'		=> (int) isset($v['style']) ? $v['style'] : 1,
		'class'		=> (string) isset($v['class']) ? 'class=\''. $v['class'] .'\'' : '',
		'rowcount'	=> (int) isset($v['all_count']) ? $v['all_count'] : 0,
		'size'		=> (int) isset($v['pagesize']) ? $v['pagesize'] : 0,
		'totle'		=> (int) 0,
		'no'		=> (int) isset($v['pageno']) ? $v['pageno'] : 0,
		'start'		=> (int) 1,
		'end'		=> (int) 0
	);

	#計算總共有多少頁
	if ( ($aTemp['rowcount'] % $aTemp['size']) > 0 )
	{
		$aTemp['totle'] = ceil($aTemp['rowcount'] / $aTemp['size']);
	}
	else
	{
		$aTemp['totle'] = ($aTemp['rowcount'] / $aTemp['size']);
	}
	#計算本頁的起始頁
	if ( strlen($aTemp['no']) == 1)
	{
		$aTemp['start'] = 1;
	}
	else
	{
		$aTemp['start'] = substr($aTemp['no'],0,(strlen($aTemp['no'])-1)) * 10;
	}

	#計算本頁的結束頁
	if ($aTemp['totle'] > 10){
		if (strlen($aTemp['no']) == 1)
		{
			$aTemp['end'] = strlen($aTemp['no']) * 9;
		}
		else
		{
			$aTemp['end'] = (substr($aTemp['no'],0,(strlen($aTemp['no'])-1)) + 1) * 10 - 1;
		}
		if ($aTemp['end'] > $aTemp['totle']){
			$aTemp['end'] = $aTemp['totle'];
		}
	}else{
		$aTemp['end'] = $aTemp['totle'];
	}

	#串連導頁參數
	$sTemp = '';
	if (!empty($v['var']))
	{
		foreach($v['var'] as $k => $v)
		{
			$sTemp .= '&'. $k .'='. $v;
		}
	}

	$aLink = array(
		'first'		=> '?pageno=1'. $sTemp,
		'along10'	=> '?pageno='. ((($aTemp['no']-10) == 0) ? 1 : ($aTemp['no']-10)) . $sTemp,
		'next10'	=> '?pageno='. ($aTemp['end']+1) . $sTemp,
		'end'		=> '?pageno='. $aTemp['totle'] . $sTemp,
	);

	if ($aTemp['style'] == 2)
	{
		$aRe_Val['html'] =  '<select '.$aTemp['class'].' onchange="location.href=\'\'+this.value">';
		$aRe_Val['html'] .=  '<option value=\''.$aLink['first'].'\'>'. $aLang['no1'] .'</option>';

		if ($aTemp['no'] >= 10){
			$aRe_Val['html'] .= '<option value=\''. $aLink['along10'] .'\'>'. $aLang['along10'] .'</option>';
		}

		for ($i=$aTemp['start'] ; $i <= $aTemp['end'] ; $i++){
			$selected = '';
			if ($aTemp['no']==$i)
			{
				$selected = 'selected';
			}
			$aRe_Val['html'] .= '<option value=\''.'?pageno='.$i.$sTemp.'\' '. $selected .'>Page '.$i.'.</option>';
		}
		if (($aTemp['totle'] > 10) && ($aTemp['end'] <> $aTemp['totle'])){
			$aRe_Val['html'] .= '<option value=\''. $aLink['next10'] .'\'>'. $aLang['next10'] .'</option>';
		}
		$aRe_Val['html'] .= '<option value=\''.$aLink['end'].'\'>'. $aLang['last'] .'</option>';
		$aRe_Val['html'] .= '</select>';
	}
	else
	{
		$aRe_Val['html'] = '<li '.$aTemp['class'].'><a href=\''.$aLink['first'].'\'>'. $aLang['no1'] .'</a></li>';

		if ($aTemp['no'] >= 10){
			$aRe_Val['html'] .= '<li '.$aTemp['class'].'><a href=\''.$aLink['along10'].'\'>'. $aLang['along10'] .'</a></li>';
		}

		for ($i=$aTemp['start'] ; $i <= $aTemp['end'] ; $i++){
			$selected = '';
			if ($aTemp['no']==$i)
			{
				$selected = 'selected';
			}
			$aRe_Val['html'] .= '<li '.$aTemp['class'].'><a href=\''.'?pageno='.$i.$sTemp.'\'>Page '.$i.'.</a></li>';
		}
		if (($aTemp['totle'] > 10) && ($aTemp['end'] <> $aTemp['totle'])){
			$aRe_Val['html'] .= '<li '.$aTemp['class'].'><a href=\''.$aLink['next10'].'\'>'. $aLang['next10'] .'</a></li>';
		}

		$aRe_Val['html'] .= '<li '.$aTemp['class'].'><a href=\''.$aLink['end'].'\'>'. $aLang['last'] .'</a></li>';
	}

	$aRe_Val['first']	= $aLink['first'];
	$aRe_Val['end']		= $aLink['end'];
	$aRe_Val['along10']	= ($aTemp['no'] >= 10) ? $aLink['along10'] : '';
	$aRe_Val['next10']	= (($aTemp['totle'] > 10) && ($aTemp['end'] <> $aTemp['totle'])) ? $aLink['next10'] : '';
	$aRe_Val['all_page']	= $aTemp['totle'];

	return $aRe_Val;
}
/*發送信件*/
#'title'
#'smtp'
#'from'
#'to_who'
#'tmp_file'
#'html'
function send_email($send_info){
	$title = $send_info['title'] ;
	$title = "=?UTF-8?B?" . base64_encode($title) . "?=";
	ini_set("SMTP",$send_info['smtp']);
	ini_set("smtp_port",25);
	ini_set("sendmail_from",$send_info['from']);
	$sHeaders = "Content-type: text/html; charset=UTF-8\r\n" ."From: Customer Service<".$send_info['from'].">\r\n";
	$message1 = '';
	if ($send_info['tmp_file'] != '')
	{
		$message = fopen($send_info['tmp_file'],"r");
		if ($message!=0){
			$message1 = fread($message,filesize($send_info['tmp_file']));
		}else{
			$message1 = 'sorry ...';
		}
	}
	else
	{
		$message1 = $send_info['html'];
	}

	$is_Success=mail($send_info['to_who'],$title,$message1,$sHeaders);
	return $is_Success;
}
function send_email_mailer($aVal){
	$mail = new PHPMailer();                        // 建立新物件

	$mail->IsSMTP();                                // 設定使用SMTP方式寄信
	$mail->SMTPAuth = true;                         // 設定SMTP需要驗證

	$mail->SMTPSecure = "ssl";                      // Gmail的SMTP主機需要使用SSL連線
	$mail->Host = $aVal['smtp'];			// Gmail的SMTP主機
	$mail->Port = $aVal['port'];			// Gmail的SMTP主機的port為465
	$mail->CharSet = "utf-8";                       // 設定郵件編碼
	$mail->Encoding = "base64";
	$mail->WordWrap = 50;                           // 每50個字元自動斷行

	$mail->Username = $aVal['from'];		// 設定驗證帳號
	$mail->Password = "testmail";			// 設定驗證密碼

	$mail->From = $aVal['from'];			// 設定寄件者信箱
	$mail->FromName = $aVal['from_tit'];		// 設定寄件者姓名

	$mail->Subject = $aVal['title'];		// 設定郵件標題

	$mail->IsHTML(true);                            // 設定郵件內容為HTML

	$mail->AddAddress($aVal['to_who'], $aVal['to_who']);	// 收件者郵件及名稱
	$mail->Body = $aVal['html'];			// AddAddress(receiverMail, receiverName)'

	$reVal = $mail->Send();				// 郵件寄出
}
function send_email_web($aVal)
{
	global $pdo;

	$aSQL_Array = array(
		'member_id'	=> (int) $aVal['member_id'],
		'ctitle0'	=> (string) $aVal['from_tit'],
		'ccontent0'	=> (string) $aVal['html'],
		'createtime'	=> (int) now_time,
		'online'	=> (int) 1,
		'forever'	=> (int) 1,
	);

	$sSQL = 'INSERT INTO member_msg ' . sql_build_array('INSERT', $aSQL_Array );
	$Result = $pdo->prepare($sSQL);
	sql_build_value($Result, $aSQL_Array);
	sql_query($Result);
}
?>