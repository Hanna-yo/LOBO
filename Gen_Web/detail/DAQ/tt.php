<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_web.php");?>
<?php
$sDetailtoken = filter_input_str('token', 	INPUT_GET);
$sLanguage = filter_input_str('language', 	INPUT_GET);

$sEndTime = -1;#判斷是否跨月份
$sYMon = false;#判斷是否跨月份
#error_reporting(0);

#該頁常用變數
$page = array(
	'dir'		=> '', #空=根目錄,../=進入子目錄
	's'		=> 0, #常用狀態比對，預設為0, 1為發生狀況
	'act0'		=> '', #該頁主要執行動作
	'chk_act'	=> '', #比對值
	#分頁變數
	'pagesize'	=> 15,
	'all_count'	=> 0,
	'class'		=> '',
	'style'		=> 1, #1 = li 2= selected
	'var'		=> array(), #'a' => 1, 'b' => 2
	'pageno'	=> filter_input_int('pageno', INPUT_GET, 1, 1, 99999),
);


$sDetailtoken = '123';
if(!empty($sDetailtoken))
{
	#CQ9
	#$aArray = array(
	#	"token" => $sDetailtoken,
	#);
	#$aData = CQ9_fun('detailtoken',$aArray);
	$aData['status']['code'] = 0;
	$aData['data']['roundid'] = 'AQ20190118003000100009';
	$aData['data']['account'] = 'test113';
	$aData['data']['paccount'] = '';
	if(isset($aData['status']['code']) && $aData['status']['code'] == 0)
	{
		$sRoundid = $aData['data']['roundid'];
		$sAcc = $aData['data']['account'];
		$sPcc = $aData['data']['paccount'];

		#取得我方 gameid
		$iGid = (int) substr($sRoundid, 10, 3);
		$sSQL = "SELECT cn_name
			 FROM 	game_version
			 WHERE 	id = :id ".sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':id', $iGid, PDO::PARAM_INT);
		sql_query($Result);
		$aGid = $Result->fetch(PDO::FETCH_ASSOC);
		$sGameName = $aGid['cn_name'];
		$sGid = add_zero($iGid,2);

		#請求月份是否符合查帳範圍***尚未完成***
		$sNY = Date('y');
		$sNM = Date('m');
		$sY = substr($sRoundid, 2, 4);
		$sM = substr($sRoundid, 6, 2);
		$sD = substr($sRoundid, 8, 2);
		$iWtot = 0;	

		#查詢資料表
		$sMtcode = substr($sRoundid, 2);
		$sTabName = $sY.'_'.$sM.'_game'.$sGid;		
		switch($sM)
		{
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				if($sD == 31)
				{
					$sEndTime = strtotime($sY.'-'.$sM.'-'.$sD.' 23:45:59');
				}				
				break;
			case 4:
			case 6:
			case 9:
			case 11:
				if($sD == 30)
				{
					$sEndTime = strtotime($sY.'-'.$sM.'-'.$sD.' 23:45:59');
				}
				break;
			default:
				if($sD == 28 || $sD == 29)
				{
					$sEndTime = strtotime($sY.'-'.$sM.'-'.$sD.' 23:45:59');
				}
				break;
		}

		#切換資料庫
		$sSQL = "USE `mt_gamerecord_cq9`;";
		$Result = $pdo->prepare($sSQL);
		sql_query($Result);

		if($sEndTime != -1)
		{
			#查跨月
			if($sM == 12)
			{
				$sTabName2 = ($sY + 1).'_01_game'.$sGid;
			}
			else
			{
				$sTabName2 = $sY.'_'.add_zero($sM + 1,2).'_game'.$sGid;
			}

			#查表2是否存在
			$sSQL = 'SHOW TABLES LIKE "'. $sTabName2 .'"';
			$Result = $pdo->prepare($sSQL);
			sql_query($Result);
			$iExist2 = $Result->rowCount();
			if($iExist2 > 0)
			{
				#是否有跨月資料
				$sSQL = "SELECT COUNT(1) as count
					 FROM 	".$sTabName2."
					 WHERE	mtcode = :mtcode";
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);
				sql_query($Result);
				$aMcount = $Result->fetch(PDO::FETCH_ASSOC);
				if($aMcount['count'] > 0)
				{
					$sYMon = true;
				}
			}
		}

		if($sYMon === true)
		{
			#查總輸贏
			$sSQL = "SELECT SUM(wlpt) as totwlpt
				 FROM 	".$sTabName."
				 WHERE	mtcode = :mtcode
				 UNION
				 SELECT SUM(wlpt) as totwlpt
				 FROM 	".$sTabName2."
				 WHERE	mtcode = :mtcode ";
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);
			sql_query($Result);
			while($aWtot = $Result->fetch(PDO::FETCH_ASSOC))
			{
				$iWtot += ($aWtot['totwlpt'] == '' ? 0 : $aWtot['totwlpt']);
			}

			#查帳
			$sSQL = "SELECT a.sno,
					a.wlpt,
					a.grpidx,
					a.gamecnts,
					a.json_desc,
					a.writeTime,
					b.json_desc as sys
				 FROM 	".$sTabName." a,
					".$sTabName." b
				 WHERE	a.mtcode = :mtcode
				 AND	b.grpuniid = a.grpuniid
				 AND	b.gamecnts = a.gamecnts
				 AND	b.G_Name = 'mt_system'
				 ORDER BY a.writeTime DESC
				 UNION
				 SELECT a.sno,
					a.wlpt,
					a.grpidx,
					a.gamecnts,
					a.json_desc,
					a.writeTime,
					b.json_desc as sys
				 FROM 	".$sTabName2." a,
					".$sTabName2." b
				 WHERE	a.mtcode = :mtcode
				 AND	b.grpuniid = a.grpuniid
				 AND	b.gamecnts = a.gamecnts
				 AND	b.G_Name = 'mt_system'
				 ORDER BY a.writeTime DESC ";
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);
			sql_query($Result);
			$iTotCount = $Result->rowCount();

			#分頁
			$page['all_count']	= $iTotCount;			
			$Result = $pdo->prepare($sSQL.' '.sql_limit((( $page['pageno'] - 1 ) * $page['pagesize'] ),  $page['pagesize'] ));
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);			
			sql_query($Result);
			$iRecord = 0;
			while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
			{
				$aJsonDe = json_decode($aRow['json_desc'],true);
				$aJsonSys = json_decode($aRow['sys'],true);
				$aNarray = array_merge($aJsonDe,$aJsonSys);
				$aRecord[$aRow[$iRecord]]['wlpt'] = $aRow['wlpt'];
				$aRecord[$aRow['sno']]['grpidx'] = $aRow['grpidx'];
				$aRecord[$aRow['sno']]['gamecnts'] = $aRow['gamecnts'];
				$aRecord[$aRow['sno']]['writeTime'] = $aRow['writeTime'];
				$aRecord[$aRow[$iRecord]]['adata'] = $aNarray;
				$aRecord[$aRow['sno']]['aPlayer'] = $aJsonDe;
				$aRecord[$aRow['sno']]['aSys'] = $aJsonSys;
				$iRecord++;
			}

			$pdo = null;
		}
		else
		{
			#查總輸贏
			$sSQL = "SELECT SUM(wlpt) as totwlpt
				 FROM 	".$sTabName."
				 WHERE	mtcode = :mtcode ".sql_limit(0,1);
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);
			sql_query($Result);
			$aWtot = $Result->fetch(PDO::FETCH_ASSOC);
			$iWtot = $aWtot['totwlpt'];

			#查單月
			$sSQL = "SELECT a.sno,
					a.wlpt,
					a.grpidx,
					a.gamecnts,
					a.json_desc,
					a.writeTime,
					b.json_desc as sys
				 FROM 	".$sTabName." a,
					".$sTabName." b
				 WHERE	a.mtcode = :mtcode
				 AND	b.grpuniid = a.grpuniid
				 AND	b.gamecnts = a.gamecnts
				 AND	b.G_Name = 'mt_system'
				 ORDER BY a.writeTime DESC ";
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);
			sql_query($Result);
			$iTotCount = $Result->rowCount();

			#分頁
			$page['all_count']	= $iTotCount;			
			$Result = $pdo->prepare($sSQL.' '.sql_limit((( $page['pageno'] - 1 ) * $page['pagesize'] ),  $page['pagesize'] ));
			$Result->bindValue(':mtcode', $sMtcode, PDO::PARAM_STR);			
			sql_query($Result);
			while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
			{
				$aJsonDe = json_decode($aRow['json_desc'],true);
				$aJsonSys = json_decode($aRow['sys'],true);
				$aNarray = array_merge($aJsonDe,$aJsonSys);
				$aRecord[$aRow['sno']]['wlpt'] = $aRow['wlpt'];
				$aRecord[$aRow['sno']]['grpidx'] = $aRow['grpidx'];
				$aRecord[$aRow['sno']]['writeTime'] = $aRow['writeTime'];
				$aRecord[$aRow['sno']]['gamecnts'] = $aRow['gamecnts'];
				$aRecord[$aRow['sno']]['adata'] = $aNarray;
				$aRecord[$aRow['sno']]['adata'] = $aNarray;
				$aRecord[$aRow['sno']]['aPlayer'] = $aJsonDe;
				$aRecord[$aRow['sno']]['aSys'] = $aJsonSys;
			}

			$pdo = null;
		}
	}
	else
	{
		$pdo = null;
		echo '20010 token 失效';
		exit;
	}	
}
else
{
	$pdo = null;
	echo 'No data!';
	exit;
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CQ9 游戏细单</title>
	<link rel="stylesheet" type="text/css" href="../css/ResetCss.min.css"/>
	<link rel="stylesheet" type="text/css" href="../css/detail<?php echo $sGid;?>.min.css?<?php echo time();?>"/>
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/cq9_detail<?php echo $sGid;?>.min.js"></script>
</head>
<body>
<?php require_once(dirname(dirname(__file__))."/php/req".$sGid.".php");?>
<?php
#分頁顯示
$page['var']['token'] = $sDetailtoken;
$aPage = page_list($page);
if($aPage['all_page'] != 1)
{
?>
<p class="page_txt">
	第<?php echo $page['pageno'];?>页<br />
	共<?php echo $aPage['all_page'];?>页
</p>
<ul class="page">
	<li><a href="<?php echo $aPage['first'];?>">第一页</a></li>
	<?php
	#手機板
	if($page['pageno'] == 1)
	{
		echo '<li class="pn no">上一页</li>';
	}
	else
	{
		$iNo = $page['pageno'] - 1;
		echo '<li class="pn"><a href="?pageno='. $iNo .'&token='. $sDetailtoken .'">上一页</a></li>';
	}

	#pc
	if($aPage['all_page'] <= 5)
	{
		for($i = 1; $i <= $aPage['all_page']; $i++)
		{
			echo '<li class="num"><a href="?pageno='. $i .'&token='. $sDetailtoken .'">'. $i .'</a></li>';
		}
	}
	else
	{
		#開始
		if(($page['pageno'] - 3) <= 0)
		{
			for($i = 1; $i <= 5; $i++)
			{
				echo '<li class="num"><a href="?pageno='. $i .'&token='. $sDetailtoken .'">'. $i .'</a></li>';
			}
			echo '<span class="dot">......</span>';
		}
		#結束
		elseif(($page['pageno'] + 2) >= $aPage['all_page'])
		{
			echo '<span class="dot">......</span>';
			for($i = ($aPage['all_page'] - 4); $i <= $aPage['all_page']; $i++)
			{
				echo '<li class="num"><a href="?pageno='. $i .'&token='. $sDetailtoken .'">'. $i .'</a></li>';
			}
		}
		else
		{
			echo '<span class="dot">...</span>';
			for($i = ($page['pageno'] - 2); $i <= ($page['pageno'] + 2); $i++)
			{
				echo '<li class="num"><a href="?pageno='. $i .'&token='. $sDetailtoken .'">'. $i .'</a></li>';
			}
			echo '<span class="dot">...</span>';
		}
	}

	#手機板
	if($page['pageno'] == $aPage['all_page'])
	{
		echo '<li class="pn no">下一页</li>';
	}
	else
	{
		$iNo = $page['pageno'] + 1;
		echo '<li class="pn"><a href="?pageno='. $iNo .'&token='. $sDetailtoken .'">下一页</a></li>';
	}		
	?>
	<li><a href="<?php echo $aPage['end'];?>">最末页</a></li>
</ul>
<?php
}
?>
</body>
</html>