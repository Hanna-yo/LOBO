<?php
function Decimal_Detail_number($dNum)
{
	#回傳格式化數值(最多到兩位)
	
	$nFloor = floor($dNum * 100);
	$sNum = number_format(($nFloor / 100),2);
	
	return $sNum;
}
?>