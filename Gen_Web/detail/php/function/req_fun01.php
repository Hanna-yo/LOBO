<?php
function Zhulu($aBallRoad)
{
	#珠路
	$iRowNum = 6;	
	$iRow = 0;
	$iCol = 0;
	$sStr = '';
	$aRoad = array();
	foreach($aBallRoad as $a => $z)
	{
		if($z <> 0)
		{
			$iRow = ($iRow == $iRowNum ? 0 : $iRow);
			switch($z)
			{
				case 1:
					$iPng = 10;#莊贏
					break;
				case 11:
					$iPng = 11;#莊贏莊對
					break;
				case 21:
					$iPng = 12;#莊贏閒對
					break;
				case 31:
					$iPng = 13;#莊贏莊對閒對
					break;
				case 41:
					$iPng = 20;#閒贏
					break;
				case 51:
					$iPng = 21;#閒贏莊對
					break;
				case 61:
					$iPng = 22;#閒贏閒對
					break;
				case 71:
					$iPng = 23;#閒贏莊對閒對
					break;
				case 81:
					$iPng = 30;#和
					break;
				case 91:
					$iPng = 31;#和莊對
					break;
				case 101:
					$iPng = 32;#和閒對
					break;
				case 111:
					$iPng = 33;#和莊對閒對
					break;
			}
			$aRoad[$iCol][$iRow] = $iPng;
			if(($a + 1) % $iRowNum == 0)
				$iCol++;
			$iRow++;
		}
	}
	
	for($j = 0; $j < $iRowNum; $j++)
	{
		$sStr .= '<tr>';
		for($i = 0; $i <= $iCol; $i++)
		{
			$sStr .= (isset($aRoad[$i][$j]) ? '<td><img src="'.ImgDir.$aRoad[$i][$j].'.png"></td>' : '<td></td>');
		}
		$sStr .= '</tr>';
	}
	return $sStr;
}

function BigRoad($aBallRoad,$iType=0)
{
	# iType = 0,畫大路
	# iType > 0,預備畫下三路的大路
	$iRow = 0;
	$iCol = 0;
	$sRes = '';
	$iRowNum = 6;
	$iLongCol = 0;
	$iLongRow = 0;
	$aBigRoad = array();
	$aCount = array();
	foreach($aBallRoad as $a => $z)
	{
		if($z <> 0)
		{
			switch($z)
			{
				case 1:
				case 11:
				case 21:
				case 31:
					$iPng = 14;#莊
					break;
				case 41:
				case 51:
				case 61:
				case 71:
					$iPng = 24;#閒
					break;
				default:
					$iPng = 34;#和
					break;
			}

			if($iType == 0)
			{
				if($iLongCol != 0 || $iLongRow != 0)
				{
					#長連開
					if($aBigRoad[$iLongCol - 1][$iLongRow] == $iPng)
					{
						$aBigRoad[$iLongCol][$iLongRow] = $iPng;
						$iLongCol++;
						continue;
					}
					else if($iPng == 34)
					{
						#和
						isset($aCount[$iLongCol - 1][$iLongRow]) ? $aCount[$iLongCol - 1][$iLongRow]++ : $aCount[$iLongCol - 1][$iLongRow] = 1;
						continue;
					}
					else
					{
						$aBigRoad[$iCol][$iRow] = $iPng;
						$iRow++;
						$iLongCol = 0;
						$iLongRow = 0;
						continue;
					}
				}
			}
			
			if($iRow == 0)
			{
				#第一個
				if($iPng == 34)
					isset($aCount[$iCol][$iRow]) ? $aCount[$iCol][$iRow]++ : $aCount[$iCol][$iRow] = 1;
				else
				{
					$aBigRoad[$iCol][$iRow] = $iPng;
					$iRow++;
				}
			}
			else if($iPng == 34)
			{
				#和
				isset($aCount[$iCol][$iRow - 1]) ? $aCount[$iCol][$iRow - 1]++ : $aCount[$iCol][$iRow - 1] = 1;
			}
			else if($aBigRoad[$iCol][$iRow - 1] == $iPng)
			{
				#連開
				if($iType == 0)
				{
					if(isset($aBigRoad[$iCol][$iRow]) || $iRow == 6)
					{
						$iCol++;
						$aBigRoad[$iCol][$iRow - 1] = $iPng;								
						$iLongRow = $iRow - 1;
						$iLongCol = $iCol + 1;		
						$iRow = 0;
					}
					else
					{
						$aBigRoad[$iCol][$iRow] = $iPng;
						$iRow++;
					}
				}
				else
				{
					$aBigRoad[$iCol][$iRow] = $iPng;
					$iRow++;
				}
			}
			else if($aBigRoad[$iCol][$iRow - 1] != $iPng)
			{
				$iRow = 0;
				$iCol++;
				$aBigRoad[$iCol][$iRow] = $iPng;
				$iRow++;
			}
		}
	}

	if($iType == 0)
	{
		$iTotCol = COUNT($aBigRoad);
		for($j = 0; $j < $iRowNum; $j++)
		{
			$sRes .= '<tr>';
			for($i = 0; $i < $iTotCol; $i++)
			{
				$sStr = '';
				if(isset($aCount[$i][$j]))
				{
					$aBigRoad[$i][$j] = ($aBigRoad[$i][$j] == 14 ? '15' : ($aBigRoad[$i][$j] == 24 ? '25' : '34'));
					$sStr = (strlen($aCount[$i][$j]) > 1 ? 'twoCount' : '');
				}
				$sRes .= '<td>';
				$sRes .= (isset($aBigRoad[$i][$j]) ? '<img src="'.ImgDir.$aBigRoad[$i][$j].'.png">' : '');
				$sRes .= (isset($aCount[$i][$j]) ? '<span class="'.$sStr.'">'.$aCount[$i][$j].'</span>' : '');
				$sRes .= '</td>';
			}
			$sRes .= '</tr>';
		}
		return $sRes;
	}
	else
	{
		$aRoad = $aBigRoad;
		return $aRoad;
	}
}

function ThreeRoad($aBigRoad,$iType)
{	
	# iType = 1,大眼仔(第二列,第二行)  16紅 26藍
	# iType = 2,小路(第三列,第二行)  17紅 27藍
	# iType = 3,甲由路(第四列,第二行)  18紅 28藍

	#有对写红，齐脚跳写红，突脚连写红
	#无对写蓝，突脚跳写蓝

	switch($iType)
	{
		case 1:
			$iStartCol = 1;#起始點
			$iStartRow = 1;#起始點
			$iJPG1 = 16;
			$iJPG2 = 26;
			break;
		case 2:
			$iStartCol = 2;#起始點
			$iStartRow = 1;#起始點
			$iJPG1 = 17;
			$iJPG2 = 27;
			break;
		case 3:
			$iStartCol = 3;#起始點
			$iStartRow = 1;#起始點
			$iJPG1 = 18;
			$iJPG2 = 28;
			break;
	}

	$iCol = 0;
	$iRow = 0;
	$iPng = 0;
	$sStr = '';
	$iLongCol = 0;
	$iLongRow = 0;
	$iRowNum = 6;
	$aRoad = array();
	foreach($aBigRoad as $a => $z)
	{
		foreach($z as $s => $v)
		{
			if($a > $iStartCol || ($a == $iStartCol && $s != 0))
			{
				if($s == 0)
				{
					#齊腳跳 ,突腳跳 
					$iPng = COUNT($aBigRoad[$a - ($iStartCol + 1)]) == COUNT($aBigRoad[$a - 1]) ? $iJPG1 : $iJPG2;
				}
				else
				{
					#突腳連 
					if($s > 1 && !isset($aBigRoad[$a - $iStartCol][$s - 1]) && !isset($aBigRoad[$a - $iStartCol][$s]))
					{
						$iPng = $iJPG1;
					}
					else
					{
						#有對 ,沒對 
						$iPng = isset($aBigRoad[$a - $iStartCol][$s]) ? $iJPG1 : $iJPG2;
					}
				}
	
				if($iLongCol != 0 && $iLongRow != 0)
				{
					#長同顏色				
					if($aRoad[$iLongCol - 1][$iLongRow] == $iPng)
					{
						$aRoad[$iLongCol][$iLongRow] = $iPng;
						$iLongCol++;
					}
					else
					{
						$aRoad[$iCol][$iRow] = $iPng;
						$iRow++;
						$iLongCol = 0;
						$iLongRow = 0;
					}
				}
				else if($iRow == 0)
				{
					#第一個
					$aRoad[$iCol][$iRow] = $iPng;
					$iRow++;
				}
				else if($aRoad[$iCol][$iRow - 1] == $iPng)
				{
					#同顏色
					if(isset($aRoad[$iCol][$iRow]) || $iRow == 6)
					{
						$iCol++;
						$aRoad[$iCol][$iRow - 1] = $iPng;
						$iLongRow = $iRow - 1;
						$iLongCol = $iCol + 1;
						$iRow = 0;
					}
					else
					{
						$aRoad[$iCol][$iRow] = $iPng;
						$iRow++;
					}
				}
				else if($aRoad[$iCol][$iRow - 1] != $iPng)
				{
					#顏色不同
					$iRow = 0;
					$iCol++;
					$aRoad[$iCol][$iRow] = $iPng;
					$iRow++;
				}
			}
		}
	}

	$iTotCol = COUNT($aRoad);
	for($j = 0; $j < $iRowNum; $j++)
	{
		$sStr .= '<tr>';
		for($i = 0; $i < $iTotCol; $i++)
		{
			$sStr .= '<td>';
			$sStr .= (isset($aRoad[$i][$j]) ? '<img src="'.ImgDir.$aRoad[$i][$j].'.png">' : '');
			$sStr .= '</td>';
		}
		$sStr .= '</tr>';
	}
	return $sStr;
}
?>