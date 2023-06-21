<?php require_once(dirname(dirname(dirname(dirname(dirname(__file__)))))."/XG/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(dirname(__file__)))))."/XG/inc/config/function.php");?>
<?php
$sWebToken	= filter_input_str('wt',	INPUT_POST);
$sWebGuid	= filter_input_str('gy',	INPUT_POST);
$sGDataName	= filter_input_str('sl',	INPUT_POST);
$nG_IID	= filter_input_str('giid',	INPUT_POST);
$nPlatform	= filter_input_int('cd',	INPUT_POST);
$nGid		= filter_input_int('gd',	INPUT_POST);
$nPage	= filter_input_int('nd',	INPUT_POST);
$nCount	= filter_input_int('tot',	INPUT_POST);
$nOne		= filter_input_int('ns',	INPUT_POST, 0);
$bOk		= $nG_IID == 'false' ? false : true;
$sRes		= '';
$nOnePage	= 15;
$nLen 	= strlen($nCount);

if($nOne == 1)
{
	#抓玩家保留局
	$sSQL = 'SELECT 	room_num
		   FROM 	game_rooms
		   WHERE 	game_id = :game_id 
		   AND	ctype = 3
		   AND	l_group_str = :l_group_str
		   ORDER BY room_num ASC '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':game_id', 	$nGid, PDO::PARAM_INT);
	$Result->bindValue(':l_group_str', 	$nG_IID, PDO::PARAM_STR);
	sql_query($Result);
	$aNowUser = $Result->fetch(PDO::FETCH_ASSOC);
	if($aNowUser)
	{
		$nMod = $aNowUser['room_num'] % $nOnePage > 0 ? 1 : 0;
		$nPage = intval(($aNowUser['room_num'] / $nOnePage) + $nMod);
	}
}

#顯示機台頁面
$nStart = ($nPage - 1) * $nOnePage;

#抓房間
$sSQL = 'SELECT 	room_id,
			room_num,
			l_group_str,
			ctype
	   FROM 	game_rooms
	   WHERE 	game_id = :game_id 
	   ORDER BY room_num ASC '.sql_limit($nStart,$nOnePage);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':game_id', 	$nGid, PDO::PARAM_INT);
sql_query($Result);
while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
{
	$aRoom[$aRow['room_num']] = $aRow;
}

if(!empty($aRoom))
{
	$i = 0;
	$nCount = count($aRoom);
	$sClass = '';
	foreach ($aRoom as $a => $s)
	{
		$i++;

		switch($s['ctype'])
		{
			case 0:
				#空機台						
				$nCtype = 1;
				$sGUrl = ($bOk == true ? INTERNAL_URL.$sGDataName.'/?WebToken='.$sWebToken.'&gkey='.$sWebGuid.'&acode='.$nPlatform.'&nid='.$s['room_id'] : '#');
				break;

			case 3:
				#保留局
				if($s['l_group_str'] == $nG_IID)
				{
					#自己保留
					$sGUrl = ($bOk == true ? INTERNAL_URL.$sGDataName.'/?WebToken='.$sWebToken.'&gkey='.$sWebGuid.'&acode='.$nPlatform.'&nid='.$s['room_id'] : '#');
					$nCtype = 4;
				}
				else
				{
					#別人保留
					$nCtype = 3;
					$sGUrl = '#';
				}
				break;

			default:
				$nCtype = 2;
				$sGUrl = '#';
				break;
		}
		

		$sRes .= ($i == 1 || $i % 5 == 1 ? '<tr>' : '');
		$sRes .= '<td>';
		$sRes .= '<a class="gamea" href="'.$sGUrl.'">';
		$sRes .= '<span class="mach_txt">'.add_zero($a,$nLen).'</span>';
		$sRes .= '<img class="game " src="../inc_all_game/img/hall/machine/desk'.$nCtype.'.png" />';
		$sRes .= '</a>';
		$sRes .= '</td>';
		$sRes .= ($i % 5 == 0 || $i == $nCount ? '</tr>' : '');
	}

	$aArray = array(
		'sres' => $sRes,
		'page' => $nPage,
	);
	echo json_encode($aArray);
}
unset($pdo);
exit;
?>