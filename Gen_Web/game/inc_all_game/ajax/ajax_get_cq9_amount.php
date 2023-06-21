<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php
$sGname		= filter_input_str('gname',	  INPUT_POST);

#取得該 GIID當前點數
$sSQL = 'SELECT	G_status,
			currencyID
	   FROM 	l_group
	   WHERE 	G_Name = :G_Name '.sql_limit(0,1);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':G_Name', $sGname, PDO::PARAM_STR);
sql_query($Result);
$aRow = $Result->fetch(PDO::FETCH_ASSOC);
$nG_status = isset($aRow['G_status']) ? $aRow['G_status'] : 0;
$nCurrencyID = isset($aRow['currencyID']) ? $aRow['currencyID'] : 0;
if($nG_status == 255)
{
	$aResult = CQ9_fun_get($aCQ9Url['Balance'].substr($sGname,0,-4));
	if(isset($aResult['status']['code']) && $aResult['status']['code'] == 0)
	{
		#幣別
		$sSQL = 'SELECT	currency
			   FROM	currency
			   WHERE	cid = :cid '.sql_limit(0,1);
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':cid', 	$nCurrencyID, 	PDO::PARAM_INT);
		sql_query($Result);
		$aCurrency = $Result->fetch(PDO::FETCH_ASSOC);
		$nCurrency = isset($aCurrency['currency']) ? $aCurrency['currency'] : 1;
		$dO_Balance = $aResult['data']['balance'] * BasicExchange / $nCurrency;

		#更新玩家餘額
		$aSQL_Array = array(
			'O_Wallet'		=> $dO_Balance,
		);	
		$sSQL = 'UPDATE 	l_group 
			   SET 	' . sql_build_array('UPDATE', $aSQL_Array ) . ' 
			   WHERE 	G_Name = :G_Name';
		$Result = $pdo->prepare($sSQL);
		$Result->bindValue(':G_Name', 	$sGname, 	PDO::PARAM_STR);
		sql_build_value($Result, $aSQL_Array);
		sql_query($Result);
	}
}

$pdo = null;
echo 1;
exit;
?>