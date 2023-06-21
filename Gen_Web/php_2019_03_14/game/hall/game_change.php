<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_web.php");?>
<?php
$sA		= filter_input_str('a',		INPUT_GET, '', 32);
$nTime	= filter_input_int('nt',	INPUT_GET);
$iGid		= filter_input_int('gid',	INPUT_GET);
$sAccount	= filter_input_str('acc',	INPUT_GET, '', 16);
$nOk		= 1;

if ($sA <> '')
{
	$sChk = md5(adm_key.$nTime);
	if($sA == $sChk)
	{
		#更新 WebGuid
		$sWebGuid = substr(md5(md5($sAccount.now_time).adm_key),0,32);
		$aSQL_Array = array(
			'webG_Uid'		=> (string) $sWebGuid,
		);	
		$sSQL = 'UPDATE 	l_group 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) . ' 
			   WHERE 	G_Name = :G_Name ';
		$Result = $pdo->prepare($sSQL);
		sql_build_value($Result, $aSQL_Array);
		$Result->bindValue(':G_Name', 	$sAccount, PDO::PARAM_STR);
		sql_query($Result);

		#查詢遊戲代碼
		$sSQL = 'SELECT 	CQ9Gid
			   FROM 	game_version
			   WHERE 	id = :id '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':id', 	$iGid, 	PDO::PARAM_INT);
		sql_query($Result);
		$nCount = $Result->rowCount();
		if($nCount > 0)
		{
			$aRow = $Result->fetch(PDO::FETCH_ASSOC);
			if($iGid == 1)
			{
				$sUrl = 'http://180.210.204.108:8080/Gen_Web/game/AQ03_TEST?gtoken='.$sWebGuid.'&status=99';
			}
			else
			{
				$sUrl = 'http://180.210.204.108:8080/Gen_Web/game/'.$aRow['CQ9Gid'].'?gtoken='.$sWebGuid.'&status=99';
			}			
			$nOk++;
		}
	}
}

if($nOk == 1)
{
	$sUrl = 'http://180.210.204.108:8080/New_Web/game/hall/';
}
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-with, initial-scale=1">
	<title>大厅</title>
	<link rel="stylesheet" type="text/css" href="css/ResetCss.min.css">
	<link rel="stylesheet" type="text/css" href="css/hall.css">
	<script type="text/javascript">
		<?php
		echo ($nOk == 1 ? 'alert("无此游戏。");' : '');
		?>
		location.href="<?php echo $sUrl;?>";
	</script>
</head>
</html>