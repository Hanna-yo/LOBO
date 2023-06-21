<?php
$sCode = isset($_POST['code']) ? (string) $_POST['code'] : -1;

if($sCode == -1)
{
	$sUrl = 'http://180.210.204.108:8080/XG_Web/detail/in_dlook.php?t='.time();
}
else
{
	$ch = curl_init();//啟用curl
	$ch = curl_init('http://api.cqgame.games/dev/peace/detailtoken?roundid=AQ'.$sCode.'&gamecode=AQ01');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	$aR = json_decode($result,true);
	if($aR['status']['code'] == 0)
	{
		$sDetailtoken = $aR['data']['detailtoken'];
		$sUrl = 'http://180.210.204.108:8080/XG_Web/detail/DAQ/?token='.$sDetailtoken.'&language=zh-cn&t='.time();
	}
	else
	{
		$sUrl = 'http://180.210.204.108:8080/XG_Web/detail/in_dlook.php?t='.time();
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset='UTF-8' />
	<script type="text/javascript">
        	location.href = "<?php echo $sUrl;?>";
    	</script>
</head>
</html>