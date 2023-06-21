<?php
define('DetailImgUrl','../img/gid04/');#圖片URL
function PotHtml($iPot,$dNum)
{
	echo '<div>';
	echo '<span class="nPot">'.$iPot.'.</span>';
	echo '<img class="pot'.$iPot.'" src="'.DetailImgUrl.'pot.png"/>';
	echo '<span class="Text'.$iPot.'">'.$dNum.'</span>';
	echo '</div>';
}
function TypeText($iType)
{
	switch($iType)
	{
		case 1:
			$sType = '同花顺';
			break;
		case 2:
			$sType = '四条';
			break;
		case 3:
			$sType = '葫芦';
			break;
		case 4:
			$sType = '同花';
			break;
		case 5:
			$sType = '顺子';
			break;
		case 6:
			$sType = '三条';
			break;
		case 7:
			$sType = '两对';
			break;
		case 8:
			$sType = '一对';
			break;
		case 9:
			$sType = '高牌';
			break;
		default:
			$sType = '皇家同花顺';
			break;
	}
	return $sType;
}
function SetHtml($iSet,$aArray)
{
	switch($iSet)
	{
		case 1:
			$sClassPlayerWin = 'PlayerWinTR';
			$sClassHandCard = 'handCardTR';
			$sClassSetText = 'SetTextTR';
			break;
		case 8:
			$sClassPlayerWin = 'PlayerWinTL';
			$sClassHandCard = 'handCardTL';
			$sClassSetText = 'SetTextTL';
			break;
		case 2:
		case 3:
		case 4:
			$sClassPlayerWin = 'PlayerWinR';
			$sClassHandCard = 'handCardR';
			$sClassSetText = 'SetTextR';
			break;
		default:
			$sClassPlayerWin = 'PlayerWinL';
			$sClassHandCard = 'handCardL';
			$sClassSetText = 'SetTextL';
			break;
	}
	$sClassRes = ($aArray['type'] == 0 ? 'res0' : ($aArray['type'] == 1 ? 'res1' : ''));
	$sSet = DetailImgUrl.'name'.$iSet.($aArray['SetNow'] == 1 ? '_a' : '');
	$sNameFrame = DetailImgUrl.'name_frame'.($aArray['SetNow'] == 1 ? '_a' : '');
	$sHData1 = DetailImgUrl.$aArray['HData1'];
	$sHData2 = DetailImgUrl.$aArray['HData2'];
	$sType = DetailImgUrl.'type'.$aArray['type'];
	$sNameIng = DetailImgUrl.'name_img'.$aArray['head'];

	#座位 Start
	echo '<div class="Set'.$iSet.'">';
	#贏金額
	if($aArray['WinAmount'] != 0)
	{
		echo '<div class="'.$sClassPlayerWin.'">';
		echo '<p>+'.$aArray['WinAmount'].'</p>';
		echo '</div>';
	}
	#贏金額 End
	#座位文字
	if($iSet == 1 || $iSet == 8)
	{
		echo '<div class="'.$sClassSetText.'">';
		echo '<img src="'.$sSet.'.png">';
		echo '<p>￥'.$aArray['OldAmount'].'</p>';
		echo '</div>';
	}
	else
	{
		echo '<img class="'.$sClassSetText.'" src="'.$sSet.'.png">';
		echo '<p class="'.$sClassSetText.'">￥'.$aArray['OldAmount'].'</p>';
	}
	#座位文字 End
	#手牌
	echo '<div class="'.$sClassHandCard.'">';
	echo '<img src="'.$sHData1.'.png"/>';
	echo '<img class="card2" src="'.$sHData2.'.png"/>';
	echo '<img class="result '.$sClassRes.'" src="'.$sType.'.png"/>';
	echo '</div>';
	#手牌 End
	echo ($aArray['Action'] == 1 ? '<img class="giveup" src="'.DetailImgUrl.'word_giveup.png"/>' : '');	#棄牌
	echo '<img class="nameImg" src="'.$sNameIng.'.png"/>';							#頭像
	echo '<img class="nameArea" src="'.$sNameFrame.'.png"/>';						#圖框
	#莊、大小盲
	if($aArray['SetBB'] == 1)
	{
		echo '<img class="SBD" src="'.DetailImgUrl.'B.png"/>';
	}
	else if($aArray['SetSB'] == 1)
	{
		echo '<img class="SBD" src="'.DetailImgUrl.'S.png"/>';
	}
	else if($aArray['SetBanker'] == 1)
	{
		echo '<img class="SBD" src="'.DetailImgUrl.'D.png"/>';
	}
	#莊、大小盲 End
	#贏圖
	echo ($aArray['Win'] == 1 ? '<img class="winImg" src="'.DetailImgUrl.'win.png"/>' : '');
	#贏圖 End
	echo '</div>';
}
?>