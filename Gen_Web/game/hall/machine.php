<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_web.php");?>
<?php
$sWebToken	= filter_input_str('WebToken',	INPUT_GET, '');
$nPlatform	= filter_input_int('acode',		INPUT_GET, 0);
$nGid		= filter_input_int('gid',		INPUT_GET, 0);
$nStu		= filter_input_int('stu',		INPUT_GET, 0);
$sStr		= "您的连接有误，请关闭视窗，<br />重新开启连接。";
$sG_Name	= '';
$sErrUrl	= 'index.php?WebToken='.$sWebToken.'&acode='.$nPlatform.'&gid='.$nGid;
$dG_Credit	= 0;
$nG_IID	= 0;
$bOk		= false;

#查詢相應遊戲
$sSQL = 'SELECT 	CQ9Gid,
			web_enable
	   FROM 	game_version
	   WHERE 	id = :id '.sql_limit(0,1);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':id', 	$nGid, PDO::PARAM_INT);
sql_query($Result);
$aGame = $Result->fetch(PDO::FETCH_ASSOC);
$sGDataName = $aGame['CQ9Gid'];

if($aGame['web_enable'] == 1 || $nStu == 999)
{
	#運行中
	if($sWebToken != '')
	{
		#外部
		$sGameToken = $sWebToken;
	
		#抓返回網址
		$sSQL = 'SELECT 	aurl,
			   		At_Enable,
			   		agent_code
			   FROM 	agent
			   WHERE 	At_Code = :At_Code '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':At_Code', 	$nPlatform, PDO::PARAM_INT);
		sql_query($Result);
		$iAgentCount = $Result->rowCount();

		$sStr = '您无权限进行游戏。';
		if($iAgentCount > 0)
		{
			$aAgent = $Result->fetch(PDO::FETCH_ASSOC);
			$sBackErrUrl = $aAgent['aurl'];
			$nAt_Enable = $aAgent['At_Enable'];
			$nCodeLen = (strlen($aAgent['agent_code'])+1) * -1;
	
			#判斷對接商權限
			if($nAt_Enable == 1)
			{
				#判斷 WebToken是否存在
				$sSQL = 'SELECT 	l.G_IID,
							l.G_Name,
							l.webG_Uid,
							l.G_Credit,
							g.gstatus,
							g.in_time_out,
							g.in_time,
							g.cid
					   FROM 	game_token g,
							l_group l
					   WHERE 	g.platform = :platform
					   AND	g.gtoken = :gtoken
					   AND	l.G_IID = g.G_IID '.sql_limit(0,1);
				$Result = $pdo->prepare($sSQL);
				$Result->bindValue(':platform', 	$nPlatform, PDO::PARAM_INT);
				$Result->bindValue(':gtoken', 	$sGameToken, PDO::PARAM_STR);
				sql_query($Result);
				$iGCount = $Result->rowCount();

				$sStr = '您的连接已失效。';	#Token 失效
				if($iGCount > 0)
				{
					$aRow = $Result->fetch(PDO::FETCH_ASSOC);
	
					$dG_Credit = Decimal_number($aRow['G_Credit'] / BasicExchange);
					$nG_IID = $aRow['G_IID'];
					$sG_Name = substr($aRow['G_Name'], 0, $nCodeLen);
					$sWebGuid = $aRow['webG_Uid'];
					$nGstatus = $aRow['gstatus'];
					$nIn_time = $aRow['in_time'];
					$nIn_time_out = $aRow['in_time_out'];
					$nCid = $aRow['cid'];
	
					if($nGstatus == 2 && (time() - $nIn_time) <= 86400)
					{
						#24小時效用
						#抓房間
						$sSQL = 'SELECT 	room_id
							   FROM 	game_rooms
							   WHERE 	game_id = :game_id';
						$Result = $pdo->prepare($sSQL);
						$Result->bindValue(':game_id', 	$nGid, PDO::PARAM_INT);
						sql_query($Result);
						$nCount = $Result->rowCount();

						$bOk = true;
						$sStr = '';
					}
				}
			}
		}
	}
}
else if($aGame['web_enable'] == 2)
{
	#維護中
	$sStr = '此游戏维护中';
}
else
{
	#不可遊玩
	$sStr = '此游戏尚未开放';
}
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, minimal-ui" />
	<title>乐博</title>
	<link rel="Shortcut Icon" type="image/x-icon" href="../inc_all_game/img/hall/logo.png"/>
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/ResetCss.min.css">
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/hall.css?t=<?php echo time();?>">
	<script type="text/javascript" src="../inc_all_game/js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="../inc_all_game/js/machine.js?t=<?php echo time();?>"></script>
</head>
<body class="inx_body">
<article class="inx_art mach_art">
	<?php
	if($bOk == false)
	{
	?>
	<div class="inx_sys">
		<p class="f1">系统</p>
		<p><?php echo $sStr;?></p>
	</div>
	<?php
	}
	?>
	<header class="header">
		<div class="frame-div">
			<img class="frame1" src="../inc_all_game/img/hall/frame1.png" />
			<div class="frame-name-div">
				<p class="name"><?php echo $sG_Name;?></p>
				<p class="point">$<?php echo Decimal_number($dG_Credit);?></p>
				<img class="frame_point" src="../inc_all_game/img/hall/frame_point.png" />
			</div>
			<img class="frame-circle" src="../inc_all_game/img/hall/fcircle.png" />
			<img class="frame-img" src="../inc_all_game/img/hall/fimg1.png" />
		</div>			
		<img class="frame-back" src="../inc_all_game/img/hall/frame2.png" />
		<a class="frame-back-img" href="<?php echo $sErrUrl;?>">
			<img class="back" src="../inc_all_game/img/hall/back.png" />
		</a>
	</header>
	<section class="inx_sec mach_sec">
		<input id="ayn" type="hidden" value="<?php echo $bOk == true ? $nG_IID : 'false';?>" />
		<input id="wt" type="hidden" value="<?php echo $sWebToken;?>" />
		<input id="cd" type="hidden" value="<?php echo $nPlatform;?>" />
		<input id="gd" type="hidden" value="<?php echo $nGid;?>" />
		<input id="nd" type="hidden" value="1" />
		<input id="sl" type="hidden" value="<?php echo $bOk == true ? $sGDataName : 'false';?>" />
		<input id="gy" type="hidden" value="<?php echo $bOk == true ? $sWebGuid : 'false';?>" />
		<input id="tot" type="hidden" value="<?php echo $bOk == true ? $nCount : 'false';?>" />
		<img class="btn-left" src="../inc_all_game/img/hall/machine/btn_left.png"/>
		<img class="btn-right" src="../inc_all_game/img/hall/machine/btn_right.png"/>
		<table class="inx_table mach_table"></table>
	</section>
</article>
</body>
</html>