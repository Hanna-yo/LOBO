<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php
$sA			= filter_input_str('a',	  	INPUT_POST);
$nAgentCode		= filter_input_int('acode',	INPUT_POST , 0);
$sGtoken		= filter_input_str('token',	INPUT_POST);
$aRes			= array();

if($sA != '' && $nAgentCode != 0)
{
	#判斷 WebToken是否存在
	$sSQL = 'SELECT 	createtime,
				gstatus,
				in_time_out,
				in_time,
				G_IID
		   FROM 	game_token
		   WHERE 	platform = :platform
		   AND	gtoken = :gtoken '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':platform', 	$nAgentCode, PDO::PARAM_INT);
	$Result->bindValue(':gtoken', 	$sGtoken, PDO::PARAM_STR);
	sql_query($Result);
	$iGCount = $Result->rowCount();
	if($iGCount > 0)
	{
		$aRow = $Result->fetch(PDO::FETCH_ASSOC);		
		$nCreatetime = $aRow['createtime'];
		$nIn_time = $aRow['in_time'];
		$nGstatus = $aRow['gstatus'];
		$nG_IID = $aRow['G_IID'];
		$sKey = md5(md5('check_user_status').$nCreatetime);

		if($sA == $sKey)
		{
			if($nGstatus == 2 && (time() - $nIn_time) <= 86400)
			{
				# 查詢會員資料
				$sSQL = 'SELECT 	G_status,
							third_party
					   FROM 	l_group
					   WHERE 	G_IID = :G_IID '.sql_limit(0,1);
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':G_IID', 	$nG_IID, PDO::PARAM_INT);
				sql_query($Result);
				$aRow = $Result->fetch(PDO::FETCH_ASSOC);
				$nG_status = $aRow['G_status'];
				$nThird_party = $aRow['third_party'];

				if($nG_status == 0 && $nThird_party == 0)
				{
					$aRes['res'] = 1;
				}
				else
				{
					$aRes['res'] = 3;
				}
			}
			else
			{
				$aRes['res'] = 2;
			}
			echo json_encode($aRes);
		}
	}	
}

$pdo = null;
exit;
?>