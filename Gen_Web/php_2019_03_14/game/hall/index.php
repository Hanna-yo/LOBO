<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_web.php");?>
<?php
$nPlatform	= filter_input_int('acode',		INPUT_GET, 0);
$sWebToken	= filter_input_str('WebToken',	INPUT_GET, '');
$sStr		= "您的连接有误，请关闭视窗，<br />重新开启连接。";
$sG_Name	= '';
$sErrUrl	= '';
$dG_Credit	= 0;
$nG_IID	= 0;
$bOk		= false;

#查詢所有上架遊戲
$sSQL = 'SELECT 	id,
			CQ9Gid,
			web_enable
	   FROM 	game_version
	   WHERE 	web_enable < 4
	   AND	id != 255
	   ORDER BY web_enable,id';
$Result = $pdo->prepare($sSQL);
sql_query($Result);
while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
{
	$aGame[$aRow['id']] = $aRow;
}

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
	if($iAgentCount > 0)
	{
		$aAgent = $Result->fetch(PDO::FETCH_ASSOC);
		$sErrUrl = $aAgent['aurl'];
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
					$bOk = true;
				}
				else if($nGstatus == 1 && $nIn_time_out >= now_time)
				{
					#10分鐘效用

					#更新 WebToken狀態,啟用 token
					$aSQL_Array = array(
						'in_time'		=> (int) now_time,
						'gstatus'		=> (int) 2,
					);	
					$sSQL = 'UPDATE 	game_token 
						   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) .'
						   WHERE 	cid = :cid';
					$Result = $pdo->prepare($sSQL);
					$Result->bindValue(':cid', 	$nCid, PDO::PARAM_INT);
					sql_build_value($Result, $aSQL_Array);
					sql_query($Result);
					
					$bOk = true;
				}
				else
				{
					$sStr = '您的连接已失效。';	#Token 失效
				}
			}
			else
			{
				$sStr = '您的连接已失效。';	#Token 失效
			}
		}
		else
		{
			$sStr = '您无权限进行游戏。';
		}
	}
	else
	{
		$sStr = '您无权限进行游戏。';
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-with, initial-scale=1">
	<title>乐博</title>
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/ResetCss.min.css">
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/hall.css?t=<?php echo time();?>">
	<script type="text/javascript" src="../inc_all_game/js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="../inc_all_game/js/hall.js?t=<?php echo time();?>"></script>
</head>
<body class="inx_body">
<article class="inx_art">
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
			<img src="../inc_all_game/img/hall/frame1.png" />
			<div class="frame-name-div">
				<p class="name"><?php echo $sG_Name;?></p>
				<p class="point">$<?php echo Decimal_number($dG_Credit);?></p>
				<img src="../inc_all_game/img/hall/frame_point.png" />
			</div>
			<img class="frame-circle" src="../inc_all_game/img/hall/fcircle.png" />
			<img class="frame-img" src="../inc_all_game/img/hall/fimg1.png" />
		</div>			
		<img class="frame-back" src="../inc_all_game/img/hall/frame2.png" />
		<a class="frame-back-img" href="<?php echo $sErrUrl;?>">
			<img src="../inc_all_game/img/hall/back.png" />
		</a>
	</header>
	<section class="inx_sec">
		<input id="ayn" type="hidden" value="<?php echo $bOk == true ? $nG_IID : 'false';?>" />
		<table class="inx_table">
			<?php
			if(!empty($aGame))
			{
				$i = 0;
				$nCount = count($aGame);
				foreach ($aGame as $a => $s)
				{
					$i++;

					$sGUrl = ($bOk == true && $s['web_enable'] == 1 ? INTERNAL_URL.$s['CQ9Gid'].'/?WebToken='.$sWebToken.'&gkey='.$sWebGuid.'&acode='.$nPlatform : '#');
					$sClass = ($s['web_enable'] != 1 ? 'bright' : '');

					echo ($i == 1 || $i % 4 == 1 ? '<tr>' : '');
					echo '<td>';
					echo '<a href="'.$sGUrl.'">';
					echo '<img class="game '.$sClass.'" src="../inc_all_game/img/hall/game'.$s['id'].'/inx_game.gif" />';
					echo $s['web_enable'] == 3 ? '<img class="game_stop" src="../inc_all_game/img/hall/game_stop.png"/>' : '';
					echo '</a>';
					echo '</td>';
					echo ($i % 4 == 0 || $i == $nCount ? '</tr>' : '');
				}
			}
			?>
		</table>
	</section>
</article>
</body>
</html>