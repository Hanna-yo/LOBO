<?php require_once(dirname(dirname(__file__))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(__file__))."/inc/config/function.php");?>
<?php
$iGid = filter_input_int('gid', 	INPUT_POST);
$sAcc = filter_input_str('sAccount', 	INPUT_POST);
$sAcc = strtolower($sAcc);

#查詢所有遊戲
$sSQL = "SELECT id,
		cn_name
	 FROM 	game_version
	 WHERE 	enable = 1
	 AND	id != 255";
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':id', 	$iGid, 	PDO::PARAM_INT);
sql_query($Result);
while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
{
	$aGData[$aRow['id']] = $aRow['cn_name'];
}

if(isset($_POST['a']) && $_POST['a'] == 'sdfcx')
{
	if($iGid <= 0)
	{
		echo '<script text="javascript/text">';
		echo 'alert("請選擇要查看的遊戲帳!");';
		echo 'location.href="http://180.210.204.108:8080/XG_Web/detail/in_dlook.php";';
		echo '</script>';
	}
	else if($sAcc == '')
	{
		echo '<script text="javascript/text">';
		echo 'alert("請輸入帳號!");';
		echo 'location.href="http://180.210.204.108:8080/XG_Web/detail/in_dlook.php";';
		echo '</script>';
	}

	#判斷該玩家是否已存在
	$sSQL = 'SELECT G_Name
		 FROM 	l_group
		 WHERE 	G_NickName = :G_NickName 
		 ORDER BY G_IID DESC '.sql_limit(0,1);
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':G_NickName', 	$sAcc, 	PDO::PARAM_STR);
	sql_query($Result);
	$aAcc = $Result->fetch(PDO::FETCH_ASSOC);
	$sAcc = $aAcc['G_Name'];
	$sGid = (strlen($iGid) == 1 ? '0'.$iGid : $iGid);

	#切換資料庫
	$sSQL = "USE `mt_gamerecord_cq9`;";
	$Result = $pdo->prepare($sSQL);
	sql_query($Result);

	$iM = Date('m');
	$sSQL = "SELECT mtcode
		 FROM 	`2019_".$iM."_game".$sGid."`
		 WHERE 	G_Name = :G_Name
		 GROUP BY  `mtcode`
		 ORDER BY writeTime DESC ";
	$Result = $pdo->prepare($sSQL);
	$Result->bindValue(':G_Name', 	$sAcc, 	PDO::PARAM_STR);
	sql_query($Result);
	while($aRow = $Result->fetch(PDO::FETCH_ASSOC))
	{
		$aData[$aRow['mtcode']] = $aRow['mtcode'];
	}	
}
$pdo = null;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset='UTF-8' />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-with, initial-scale=1">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<style>
		* {
			text-align: center;
			font-size: 20px;
		}
		form {
			width: 100%;
			text-align: center;
			padding-top: 150px;
			padding-bottom: 10px;
		}
		button {
			width: 100px;
			height: 45px;
			font-size: 20px;
		}
		.ma {			
			width: 170px;
			height: 40px;
		}
	</style>
</head>
<body>
	<?php
	if(isset($_POST['a']) && $_POST['a'] == 'sdfcx')
	{
	?>
	<form action='http://180.210.204.108:8080/XG_Web/detail/in_dcode.php?t=<?php echo time();?>' method='POST' id="form1">
		只查詢本月<br />
		請選擇單號<br />
		<select class="ma" name="code">
			<?php
			if(!empty($aData))
			{
				foreach($aData as $a => $s)
				{
					echo '<option value="'.$a.'">'.$s.'</option>';
				}
			}			
			?>
		</select>
		<button id='pt' form="form1" type='submit'>送出</button>
	</form>
	<?php
	}
	else
	{
	?>	
	<form action='http://180.210.204.108:8080/XG_Web/detail/in_dlook.php?t=<?php echo time();?>' method='POST' id="form1">
		查看帳務<br />
		<input name='a' value='sdfcx' type="hidden"/><br/>
		<select class="ma" name="gid">
			<option value="0">請選擇遊戲</option>
			<?php
			if(!empty($aGData))
			{
				foreach($aGData as $a => $s)
				{
					echo '<option value="'.$a.'">'.$s.'</option>';
				}
			}			
			?>
		</select><br/><br/>
		<input class="ma" name='sAccount' value='' type="text" placeholder="請輸入測試帳號"/><br/><br/>		
		<button id='pt' form="form1" type='submit'>送出</button>
	</form>
	<?php
	}
	?>
</body>
</html>