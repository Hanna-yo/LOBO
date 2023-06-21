<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php
$nGIID		= filter_input_int('giid',	  INPUT_POST,0);

#取得該GIID當前點數
$sSQL = 'SELECT	G_status,
			G_Credit,
			G_Wallet
	   FROM 	l_group
	   WHERE 	G_IID = :G_IID '.sql_limit(0,1);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':G_IID', 	$nGIID, PDO::PARAM_INT);
sql_query($Result);
$aRow = $Result->fetch(PDO::FETCH_ASSOC);
$nG_status = $aRow['G_status'];
// $dG_Credit = Decimal_number(($aRow['G_Credit'] + $aRow['G_Wallet']) / BasicExchange);
$dG_Credit = Decimal_number(($aRow['G_Credit'] + $aRow['G_Wallet']) / 100);
if($nG_status > 0 && ($nG_status < 301 || $nG_status > 399))
{
	$aRes['res'] = 2;
}
else
{
	$aRes['res'] = 1;
	$aRes['amount'] = '$'.$dG_Credit;
}

$pdo = null;
echo json_encode($aRes);
exit;
?>