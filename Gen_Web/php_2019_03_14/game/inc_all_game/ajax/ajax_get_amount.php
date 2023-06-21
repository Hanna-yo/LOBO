<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/config.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function.php");?>
<?php require_once(dirname(dirname(dirname(dirname(__file__))))."/inc/config/function_web.php");?>
<?php
$nGIID		= filter_input_int('giid',	  INPUT_POST,0);

#取得該GIID當前點數
$sSQL = 'SELECT	G_Credit
	   FROM 	l_group
	   WHERE 	G_IID = :G_IID '.sql_limit(0,1);
$Result = $pdo->prepare($sSQL);
$Result->bindValue(':G_IID', 	$nGIID, PDO::PARAM_INT);
sql_query($Result);
$aRow = $Result->fetch(PDO::FETCH_ASSOC);
$dG_Credit = Decimal_number($aRow['G_Credit'] / BasicExchange);

$aRes['res'] = 1;
$aRes['amount'] = '$'.$dG_Credit;
$pdo = null;
echo json_encode($aRes);
exit;
?>