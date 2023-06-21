
<?php ini_set('error_log', dirname(__FILE__).'/error_log.txt'); ?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_web.php");?>
<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/function_tidy.php");?>
<?php
$nPlatform		= filter_input_int('acode',		INPUT_GET, 0);
$sWebToken		= filter_input_str('WebToken',	INPUT_GET, '');
$nStu			= filter_input_int('stu',		INPUT_GET, 0);
$sLang 		= filter_input_str('sLang',		INPUT_GET, '');
$nDemo 		= filter_input_int('nDemo',		INPUT_GET, 0);
$nLeboVersion 	= filter_input_int('nLeboVersion',	INPUT_REQUEST, 0);

//$sStr		= "您的连接有误，请关闭视窗，<br />重新开启连接。";
$sStr		= "Something error, Plese Reopen Game。";
$sErrorClass= '';
$sG_Name	= '';
$sErrUrl	= '';
$sCondition	= '';
$dG_Credit	= 0;
$nG_IID	= 0;
$bOk		= false;
$nRePage 	= 1;
$gif_icon_folder = '/cn';//大廳gif icon所在資聊夾，預設為cn

// 如果有存cookie且站別相同就吃cookie的
$sTempLang = $_COOKIE['sLang'];
$aTempLang = explode('_',$sTempLang);
// print_r($aTempLang);
if(isset($aTempLang[1]) && $aTempLang[1] == $nPlatform && $aTempLang[0] != '')
{
	$sLang = $aTempLang[0];
}


// 寫入cookie
setcookie('sLang',$sLang.'_'.$nPlatform, time()+14400);

switch($sLang)
{
	case "vn":
		$gif_icon_folder = '/vn';
		break;
	case "tw":
		$gif_icon_folder = '/tw';
		break;
	case "en":
		$gif_icon_folder = '/en';
		break;
}

if(isset($_COOKIE['pntest']))
{
	$aReqData = array(
		'game_id' 	=> (int) 249,
		'username' 	=> (string) $sG_Name,
		'back_url' 	=> (string) (GEN_HALL_URL.'?WebToken='.$sGtoken.'&acode='.$nAgentCode.'&t='.time()),
		'quality' 	=> 'MD',
		'lang' 	=> $sTidyLang,
	);

	$aResData = json_decode(TidySend('DemoLink',$aReqData),true);

	print_r('<pre>');
	print_r($aResData);
	print_r('</pre>');
	exit;
}

if(isset($_GET['nLeboVersion']) && $_GET['nLeboVersion'] == 2)
{
	if($sLang == 'tw')
	{
		$sCondition = ',name';
	}
	else if($sLang == 'cn')
	{
		$sCondition = ',cn_name';
	}
	else if($sLang == 'en')
	{
		$sCondition = ',en_name';
	}
	else if($sLang == 'vn')
	{
		$sCondition = ',vn_name';
	}
}
#查詢所有上架遊戲
$sSQL = 'SELECT 	id,
			CQ9Gid,
			web_enable
			'.$sCondition.'
	   FROM 	game_version
	   WHERE 	web_enable < 4
	   AND	id != 255
	   ORDER BY web_enable,id';
$Result = $pdo->prepare($sSQL);
sql_query($Result);
$sRoomSql = '';
while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
{
	if($aRow['id'] == 1248 && ($nPlatform == 202 || $nPlatform == 10 || $nPlatform == 11 || $nPlatform == 12 || $nPlatform == 204))
	{
		continue;
	}

	$aGame[$aRow['id']] = $aRow;
	if(isset($_GET['nLeboVersion']) && $_GET['nLeboVersion'] == 2)
	{
		if($sLang == 'tw')
		{
			$aGame[$aRow['id']]['sGameName'] = $aRow['name'];
		}
		else if($sLang == 'cn')
		{
			$aGame[$aRow['id']]['sGameName'] = $aRow['cn_name'];
		}
		else if($sLang == 'en')
		{
			$aGame[$aRow['id']]['sGameName'] = $aRow['en_name'];
		}
		else if($sLang == 'vn')
		{
			$aGame[$aRow['id']]['sGameName'] = $aRow['vn_name'];
		}
	}
	$sRoomSql .= ($sRoomSql == '' ? '(' : ',' ).$aRow['id'];

}
$sRoomSql = $sRoomSql.')';

#查詢各遊戲的最小點數
$sSQL = 'SELECT	game_id,
			min_point
	   FROM	game_rooms
	   WHERE	game_id
	   IN '.$sRoomSql.'
	   GROUP BY  `game_id`
	   ORDER BY  `min_point` ASC ';
$Result = $pdo->prepare($sSQL);
sql_query($Result);
while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
{
	$aGame[$aRow['game_id']]['min_point'] = $aRow['min_point'] / 100;
}

if($sWebToken != '')
{
	#外部
	$sGameToken = $sWebToken;

	#抓返回網址
	$sSQL = 'SELECT 	aurl,
						At_Enable,
						agent_code,
						nFormal,
						nCurrency
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
		$nFormal = $aAgent['nFormal'];
		$nCurrency = $aAgent['nCurrency'];


		#判斷對接商權限
		if($nAt_Enable == 1)
		{
			#判斷 WebToken是否存在
			$sSQL = 'SELECT 	l.G_IID,
						l.G_Name,
						l.G_status,
						l.third_party,
						l.webG_Uid,
						l.G_Credit,
						l.G_Wallet,
						g.gstatus,
						g.in_time_out,
						g.in_time,
						g.createtime,
						g.cid
				   FROM 	game_token g,
						l_group l
				   WHERE 	g.platform = :platform
				   AND	g.gtoken = :gtoken
				   AND	g.nDemo = :nDemo
				   AND	l.G_IID = g.G_IID '.sql_limit(0,1);
			$Result = $pdo->prepare($sSQL);
			$Result->bindValue(':platform', 	$nPlatform, PDO::PARAM_INT);
			$Result->bindValue(':nDemo', 		$nDemo, PDO::PARAM_INT);
			$Result->bindValue(':gtoken', 	$sGameToken, PDO::PARAM_STR);
			sql_query($Result);
			$iGCount = $Result->rowCount();
			if($iGCount > 0)
			{
				$aRow = $Result->fetch(PDO::FETCH_ASSOC);

				$dG_Credit = Decimal_number(($aRow['G_Credit'] + $aRow['G_Wallet']) / BasicExchange);
				$nG_IID = $aRow['G_IID'];
				$sG_Name = substr($aRow['G_Name'], 0, $nCodeLen);
				$sWebGuid = $aRow['webG_Uid'];
				$nGstatus = $aRow['gstatus'];
				$nCreatetime = $aRow['createtime'];
				$nG_status = $aRow['G_status'];
				$nThird_party = $aRow['third_party'];
				$nIn_time = $aRow['in_time'];
				$nIn_time_out = $aRow['in_time_out'];
				$nCid = $aRow['cid'];
				$sA = md5(md5('check_user_status').$nCreatetime);

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
					//$sStr = '您的连接已失效。';	#Token 失效
					$sStr = 'Invalid Token';	#Token 失效
				}


				if($bOk == true)
				{
					if($nG_status > 0 && ($nG_status < 301 || $nG_status > 399))
					{

						if($sLang == 'vn')
						{
							$sStr = 'Hệ thống, tài khoản game của bạn chưa thanh toán, vui lòng chờ.';
						}
						else
						{
							$sStr = '您的游戏尚未结算，请稍后。';
						}

                                    $sErrorClass = 'errorBetNotSettle';
						$dG_Credit = 0;

						$nRePage = 0;
					}

					#tidy api 判斷是否已建立帳號
					$aReqData = array(
						'username' 	=> $aRow['G_Name'],
					);
					$aResData = json_decode(TidySend('UserInfo',$aReqData),true);
					if(isset($aResData['check']) && $aResData['check'] == 1)
					{
						if($nThird_party != 0)
						{
							#存在,強制用戶離線
							$aReqData = array(
								'username' 	=> $aRow['G_Name'],
							);
							$aResData = json_decode(TidySend('KickUser',$aReqData),true);
							if(isset($aResData['result']) && $aResData['result'] == 'success')
							{
								#查詢帳戶額度
								$aReqData = array(
									'username' 	=> $aRow['G_Name'],
								);
								$aResData = json_decode(TidySend('UserBalance',$aReqData),true);
								if(isset($aResData['user']['balance']) && $aResData['user']['balance'] > 0)
								{
									#拉回額度
									$dBalance = $aResData['user']['balance'] * BasicExchange;
									$nRes = Tidy_amount_in($nG_IID,$dBalance,$nThird_party,$sGameToken,$nPlatform,$nCurrency);

									if($nRes == 0)
									{
										error_log("index 228 : 查無會員 G_IID = {$nG_IID}");
									}

									if($nRes == 1)
									{
										#重取當前餘額
										$sSQL = 'SELECT 	G_Wallet
											   FROM 	l_group
											   WHERE 	G_IID = :G_IID '.sql_limit(0,1);
										$Result = $pdo->prepare($sSQL);
										$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
										sql_query($Result);
										$aRow = $Result->fetch(PDO::FETCH_ASSOC);
										$dG_Credit = Decimal_number($aRow['G_Wallet'] / BasicExchange);

										#更新玩家狀態
										$aSQL_Array = array(
											'third_party'	=> 0,
										);
										$sSQL = 'UPDATE	l_group
											   SET 	' . sql_build_array('UPDATE', $aSQL_Array ).'
											   WHERE 	G_IID = :G_IID';
										$Result = $pdo->prepare($sSQL);
										$Result->bindValue(':G_IID', $nG_IID, PDO::PARAM_INT);
										sql_build_value($Result, $aSQL_Array);
										sql_query($Result);
									}
								}
							}
						}
					}
				}
			}
			else
			{
				$sStr = 'Invalid Token';	#Token 失效
				//$sStr = '您的连接已失效。';	#Token 失效
			}
		}
		else
		{
			$sStr = 'Permission denied';
			//$sStr = '您无权限进行游戏。';
		}
	}
	else
	{
		$sStr = 'Permission denied';
		//$sStr = '您无权限进行游戏。';
	}
}
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, minimal-ui" />
	<title>LEBO</title>
	<link rel="Shortcut Icon" type="image/x-icon" href="../inc_all_game/img/hall/logo.png"/>
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/ResetCss.min.css">
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/hall.css?t=<?php echo time();?>">
	<?php
	if(isset($_GET['nLeboVersion']) && $_GET['nLeboVersion'] == 2)
	{
	?>
		<link rel="stylesheet" href="../inc_all_game/css/2021/pc.css?t=<?php echo now_time; ?>">
		<link rel="stylesheet" href="../inc_all_game/css/2021/mobile.css?t=<?php echo now_time; ?>" media="screen and (max-width:1179px)">
		<link rel="stylesheet" href="../inc_all_game/css/2021/module.css?t=<?php echo now_time;?>">
	<?php
	}
	?>
	<script type="text/javascript" src="../inc_all_game/js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="../inc_all_game/js/hall.js?t=<?php echo time();?>"></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		var oGameMin = {}, nGameTrue = <?php echo $nRePage;?>;
		<?php
		if(!empty($aGame))
		{
			foreach($aGame as $s => $z)
			{
				if(isset($z['min_point']) && $z['web_enable'] == 1)
				{
					echo 'oGameMin['.$s.'] = '.$z['min_point'].';';
				}
			}
		}
		?>

		$('.gamea').click(function()
		{
			if(nGameTrue == 1)
			{
				let gid = $(this).attr('data-gid');
				let surl = $(this).attr('data-href');
				let user_point = parseFloat($('.point').text().substring(1));
				let userAgent = navigator.userAgent;

				if(parseInt(gid) > 1000)
				{
					$('.loading-round').show();
					$('#ua').val(userAgent);
					$('#gid').val(gid);
					$('#chg_solt').submit();
				}
				else if(oGameMin[gid])
				{
					if(surl != '' && surl != '#')
					{
						let minP = oGameMin[gid];

						if(user_point < minP)
						{
							//alert('您的点数少于 '+minP+' 无法进入此游戏。');
							alert('need point:'+minP);
						}
						else
						{
							$('.loading-round').show();
							location.href = surl;
						}
					}
				}
				else
				{
					location.href = surl;
				}
			}
		});

		// 2021/12/06 PD
		$('.JqGameBtn').click(function()
		{
			if(nGameTrue == 1)
			{
				let gid = $(this).attr('data-gid');
				let surl = $(this).attr('data-href');
				let user_point = parseFloat($('.JqPoint').text().substring(1));
				let userAgent = navigator.userAgent;

				if(parseInt(gid) > 1000)
				{
					$('.loading-round').show();
					$('#ua').val(userAgent);
					$('#gid').val(gid);
					$('#chg_solt').submit();
				}
				else if(oGameMin[gid])
				{
					if(surl != '' && surl != '#')
					{
						let minP = oGameMin[gid];

						if(user_point < minP)
						{
							//alert('您的点数少于 '+minP+' 无法进入此游戏。');
							alert('need point:'+minP);
						}
						else
						{
							$('.loading-round').show();
							location.href = surl;
						}
					}
				}
				else
				{
					location.href = surl;
				}
			}
		});

            if($('.inx_sys').hasClass('errorBetNotSettle'))
            {
                  setTimeout(function()
                  {
                        $('.inx_sys').hide();
                  },
                  3000);
            }

		<?php
		if($nRePage == 0)
		{
		?>
		function check_status()
		{
			$.ajax(
			{
				url: '../inc_all_game/ajax/ajax_check_status.php',
				type: 'POST',
				async: true,
				cache: false,
				dataType: 'json',
				contentType: 'application/x-www-form-urlencoded',
				data:
				{
					a: '<?php echo $sA;?>',
					acode: '<?php echo $nPlatform;?>',
					token: '<?php echo $sWebToken;?>'
				},
				error: function(e)
				{

				},
				success: function(e)
				{
					if(e.res && e.res == 1)
					{
						$('.inx_sys').hide();
						nGameTrue = 1;
					}
					else
					{
						setTimeout(function(){ check_status() }, 2000);
					}
				}
			});
		}

		setTimeout(function(){ check_status() }, 1000);
		<?php
		}
		?>
	});
	</script>
</head>
<body class="inx_body">
<input id="loadTF" type="hidden" value="true"/>
<!-- <div class="loadingLogoDiv">
	<img class="loadingLogo" src="../inc_all_game/img/hall/logo.png" />
	<p class="loginWord">Loading...</p>
</div> -->
<!-- <div class="loadingOneDiv">
	<img class="ling loadingOne" src="../inc_all_game/img/hall/loading/loading.gif"/> -->
	<!--<img class="ling loadingOneWord" src="../inc_all_game/img/hall/loading/loading_word.png"/>-->
	<!-- <img class="ling loadingOneNFrame" src="../inc_all_game/img/hall/loading/none_frame.png"/>
	<img class="ling loadingOneRFrame" src="../inc_all_game/img/hall/loading/run_frame.png"/>
	<div class="loadingOneLightDiv">
		<div><img class="ling loadingOneLight" src="../inc_all_game/img/hall/loading/light.png"/></div></div>
	<img class="ling loadingOneOFrame" src="../inc_all_game/img/hall/loading/outer_frame.png"/>
</div> -->
<?php
if($bOk == false || $nRePage == 0)
{
?>
<div class="inx_sys <?php echo $sErrorClass; ?>">
	<p class="f1">System</p>
	<p><?php echo $sStr;?></p>
</div>
<?php
}
?>
<span class="loading-round"></span>
<?php
# 2021/12/07 PD Kn說要改版給客人看
if(isset($_GET['nLeboVersion']) && $_GET['nLeboVersion'] == 2)
{
?>
	<input id="ayn" type="hidden" value="<?php echo $bOk == true ? $nG_IID : 'false';?>" />
	<?php
	if($bOk == true)
	{
		$sGameUrl = 'change_solt_game.php';
		if($nDemo == 1)
		{
			$sGameUrl = 'change_demo.php';
		}
	?>
	<form id="chg_solt" action="<?php echo $sGameUrl;?>?t=<?php echo now_time.'&nLeboVersion=2';?>" method="POST">
		<input type="hidden" name="gtoken" value="<?php echo $sWebToken;?>">
		<input type="hidden" name="gid" id="gid">
		<input type="hidden" name="user_agent" id="ua">
		<input type="hidden" name="acode" value="<?php echo $nPlatform;?>">
	</form>
	<?php
	}
	?>
	<div class="indexBox">
		<div class="indexBg DevicePc BG" style="background-image: url('../inc_all_game/images/bg1.png?t=<?php echo now_time; ?>');"></div>
		<div class="indexBg DeviceMobile BG" style="background-image: url('../inc_all_game/images/bgPh2.png?t=<?php echo now_time; ?>');"></div>
		<div class="indexHeaderBox">
			<div class="indexHeaderLogo">
				<img src="../inc_all_game/images/logo.png?t=<?php echo now_time; ?>" alt="">
			</div>
			<div class="indexHeaderInfBox">
				<div class="indexHeaderInfInner">
					<div class="indexHeaderInf">
						<div class="indexHeaderInfIcon">
							<img src="../inc_all_game/images/infAccount.png?t=<?php echo now_time; ?>" alt="">
						</div>
						<div class="indexHeaderInfVal">
							<div class="indexHeaderInfValTxt"><?php echo $sG_Name;?></div>
						</div>
					</div>
					<div class="indexHeaderInf">
						<div class="indexHeaderInfIcon">
							<img src="../inc_all_game/images/infBalance.png?t=<?php echo now_time; ?>" alt="">
						</div>
						<div class="indexHeaderInfVal">
							<div class="indexHeaderInfValTxt">
							<?php
							if($nDemo == 0)
							{
								echo '$<span class="JqPoint">'.Decimal_number($dG_Credit/100).'</span>';
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<a href="<?php echo $sErrUrl;?>" class="indexHeaderBtnBack">
				<img src="../inc_all_game/images/back.png?t=<?php echo now_time; ?>" alt="">
			</a>
		</div>
		<div class="indexGameBox">
			<?php
			if(!empty($aGame))
			{
				foreach ($aGame as $LPnId => $LPaGame)
				{
					$sClass = '';
					$sText = '';
					switch($LPaGame['web_enable'])
					{
						case 2:
							#維護中
							$sText = 'In maintain';
							$sClass = 'maintain';
							break;
						case 3:
							#敬請期待
							$sText = 'Coming soon';
							$sClass = 'coming';
							break;
					}

					if($LPaGame['id'] > 100 && $LPaGame['id'] < 200)
					{
						$sGUrl = ($bOk == true && $LPaGame['web_enable'] == 1 ? 'machine.php?WebToken='.$sWebToken.'&acode='.$nPlatform.'&gid='.$LPaGame['id'].'&t='.time() : '#');
					}
					else if($LPaGame['id'] > 1000)
					{
						$sGUrl = '';
					}				
					else
					{
						$sGUrl = ($bOk == true && $LPaGame['web_enable'] == 1 ? INTERNAL_URL.$LPaGame['CQ9Gid'].'/?WebToken='.$sWebToken.'&gkey='.$sWebGuid.'&acode='.$nPlatform.'&sLang='.$sLang.'&t='.time() : '#');
					}
			?>
				<div class="indexGameBtn JqGameBtn" data-gid="<?php echo $LPnId; ?>" data-href="<?php echo $sGUrl; ?>">
					<div class="indexGameBtnImgBox">
						<?php
						if($LPaGame['web_enable'] == 3)
						{
							# 透明圖
						?>
							<div class="indexGameBtnImg DevicePc <?php echo $sClass; ?>">
								<img src="../inc_all_game/images/game/default/pcD.png?t=<?php echo now_time; ?>" alt="">
							</div>
							<div class="indexGameBtnImg DeviceMobile <?php echo $sClass; ?>">
								<img src="../inc_all_game/images/game/default/mobile/mobileD.png?t=<?php echo now_time; ?>" alt="">
							</div>
						<?php
						}
						else
						{
						?>
							<div class="indexGameBtnImg DevicePc <?php echo $sClass; ?>">
								<img src="../inc_all_game/images/game/default/<?php echo $LPnId; ?>.png?t=<?php echo now_time; ?>" alt="">
							</div>
							<div class="indexGameBtnImg DeviceMobile <?php echo $sClass; ?>">
								<img src="../inc_all_game/images/game/default/mobile/<?php echo $LPnId; ?>.png?t=<?php echo now_time; ?>" alt="">
							</div>
						<?php
						}
						?>
						<?php
						if($LPaGame['web_enable'] != 1)
						{
						?>
							<div class="indexGameBtnImgTxt"><?php echo $sText; ?></div>
						<?php
						}
						?>					
					</div>
					<?php
					if($LPaGame['sGameName'] != '')
					{
					?>
						<div class="indexGameBtnTxt"><?php echo $LPaGame['sGameName']; ?></div>
					<?php
					}
					?>					
				</div>
			<?php
				}
			}
			?>
		</div>
	</div>
<?php
}
else
{
?>
<article class="inx_art">
	<header class="header">
		<div class="frame-div">
			<img class="frame1" src="../inc_all_game/img/hall/frame1.png" />
			<div class="frame-name-div">
				<p class="name"><?php echo $sG_Name;?></p>
				<?php
					if($nDemo == 0)
					{
				?>
						<p class="point">$<?php echo Decimal_number($dG_Credit/100);?></p>
				<?php
					}
				?>

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
	<section class="inx_sec">
		<input id="ayn" type="hidden" value="<?php echo $bOk == true ? $nG_IID : 'false';?>" />
		<?php
		if($bOk == true)
		{
			$sGameUrl = 'change_solt_game.php';
			if($nDemo == 1)
			{
				$sGameUrl = 'change_demo.php';
			}
		?>
		<form id="chg_solt" action="<?php echo $sGameUrl;?>?t=<?php echo time();?>" method="POST">
			<input type="hidden" name="gtoken" value="<?php echo $sWebToken;?>">
			<input type="hidden" name="gid" id="gid">
			<input type="hidden" name="user_agent" id="ua">
			<input type="hidden" name="acode" value="<?php echo $nPlatform;?>">
		</form>
		<?php
		}
		?>
		<table class="inx_table">
			<?php
			$nCols = 4; #一列數量
			if(!empty($aGame))
			{
				$i = 0;
				$nCount = count($aGame);
				$sClass = '';
				$sWeb_img = '';
				foreach ($aGame as $a => $s)
				{
					$i++;

					switch($s['web_enable'])
					{
						case 2:
							#維護中
							$sWeb_img = '<img class="game_maintain" src="../inc_all_game/img/hall/maintain.png"/>';
							$sClass = 'bright';
							break;
						case 3:
							#敬請期待
							$sWeb_img = '<img class="game_stop" src="../inc_all_game/img/hall/game_stop.png"/>';
							$sClass = 'bright';
							break;
					}

					if($s['id'] > 100 && $s['id'] < 200)
						$sGUrl = ($bOk == true && $s['web_enable'] == 1 ? 'machine.php?WebToken='.$sWebToken.'&acode='.$nPlatform.'&gid='.$s['id'].'&t='.time() : '#');
					else if($s['id'] > 1000)
						$sGUrl = '';
					else
						$sGUrl = ($bOk == true && $s['web_enable'] == 1 ? INTERNAL_URL.$s['CQ9Gid'].'/?WebToken='.$sWebToken.'&gkey='.$sWebGuid.'&acode='.$nPlatform.'&sLang='.$sLang.'&t='.time() : '#');
					$sClass = ($s['web_enable'] != 1 ? 'bright' : '');

					echo ($i == 1 || $i % $nCols == 1 ? '<tr>' : '');
					echo '<td style="width:calc(100%/'.$nCols.');">';
					echo '<a class="gamea" data-gid="'.$a.'" data-href="'.$sGUrl.'">';
					echo '<img class="game game'.$s['id'].' '.$sClass.'" src="../inc_all_game/img/hall/game'.$s['id'].$gif_icon_folder.'/inx_game.png" />';
					echo $sWeb_img;
					echo '</a>';
					echo '</td>';
					echo ($i % $nCols == 0 || $i == $nCount ? '</tr>' : '');
				}
			}
			?>
		</table>
	</section>
</article>
<?php
}
?>
</body>
</html>