<?php
function filter_input_int($sName, $sType, $nDefult = 0, $nMin = 0, $nMax = 0) {
	$nFun_Val = NULL;
	$aInt_Options = array(
		"options"=>
			array(
				"min_range"=> (int) $nMin,
				"max_range"=> (int) $nMax
	));

	if ( $sType === INPUT_REQUEST ) {
		if ( isset($_POST[$sName]) ) {
			$nFun_Val = filter_input(INPUT_POST, $sName, FILTER_VALIDATE_INT);
		} else {
			if ( isset($_GET[$sName]) ) {
				$nFun_Val = filter_input(INPUT_GET, $sName, FILTER_VALIDATE_INT);
			}
		}
	} else {
		$nFun_Val = filter_input($sType, $sName, FILTER_VALIDATE_INT);
	}

	if ( ($nMin <> 0) || ($nMax <> 0) ) {
		$nFun_Val = filter_var($nFun_Val, FILTER_VALIDATE_INT, $aInt_Options);
	}

	$nFun_Val = (($nFun_Val === NULL) || ($nFun_Val === false)) ? $nDefult : $nFun_Val;

	return (int) $nFun_Val;
}

function filter_input_str($sName, $sType, $sDefult = '', $nString_Limit = 0) {
	$sFun_Val = NULL;

	if ( $sType === INPUT_REQUEST ) {
		if ( isset($_POST[$sName]) ) {
			$sFun_Val = filter_input(INPUT_POST, $sName, FILTER_SANITIZE_STRING);
		} else {
			if ( isset($_GET[$sName]) ) {
				$sFun_Val = filter_input(INPUT_GET, $sName, FILTER_SANITIZE_STRING);
			}
		}
	} else {
		$sFun_Val = filter_input($sType, $sName, FILTER_SANITIZE_STRING);
	}

	if ( $nString_Limit > 0 ) {
		if ( mb_strlen($sFun_Val) > $nString_Limit ) {
			mb_internal_encoding('UTF-8');
			$sFun_Val = mb_substr($sFun_Val, 0, $nString_Limit);
		}
	}

	$sFun_Val = ($sFun_Val === NULL) ? $sDefult : $sFun_Val;

	return $sFun_Val;
}
/*
$sTxt = 字串
$nHere =起始位子, 0表示由後往前
$nLen = 隱藏長度
*/
function hidden_txt($sTxt, $nHere, $nLen)
{
	$sRe_val 	= '';
	$nCount 	= mb_strlen($sTxt);
	if ($nHere > 0){
		$nSt 		= 0;
		$nEng 		= $nHere + $nLen;
		$nGet 		= ($nCount - $nLen) - ($nCount - ($nEng-1));
	}else{
		$nSt 		= $nCount;
		$nEng 		= $nHere + $nLen;
	}
	if ($nHere > 0){
		$sRe_val = substr($sTxt, $nSt ,$nGet);
		if ($nEng > $nCount){
			$nLen = $nCount - ($nHere-1);
		}
	}
	for ( $i = 0 ; $i < $nLen ; $i++ )
	{
		$sRe_val .= '*';
	}

	if ($nCount > ($nEng-1))
	{
		if ($nHere == 0){
			$sRe_val = substr($sTxt, 0, ($nCount-$nLen)).$sRe_val;
		}else{
			$sRe_val .= substr($sTxt, ($nEng-1) ,$nCount - ($nEng-1));
		}
	}


	return $sRe_val;
}
?>