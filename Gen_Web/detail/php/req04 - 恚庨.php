<?php require_once('../php/function/req_fun04.php');?>
<article>
<h1>游戏名称：<?php echo $sGameName;?></h1>
<table class="accTab">
	<tbody>
	<tr>
		<td class="accBor"><p><span>单号</span><?php echo $sRoundid;?></p></td>
		<td class="accBor"><span>输赢</span><?php echo ($iWtot < 0 ? '<span class="sumNegTot">'. ($iWtot / BasicExchange) .'</span>' : ($iWtot > 0 ? '<span class="sumAddTot">'. '+'.($iWtot / BasicExchange) .'</span>' : ($iWtot / BasicExchange)));?></td>
	</tr>
	<tr>
		<td <?php echo (!empty($sPcc) ? '' : 'colspan="2"')?>>
			<p <?php echo (!empty($sPcc) ? '' : 'class="nbor"')?>>
				<span>玩家帐号</span><?php echo $sAcc;?>
			</p>
		</td>
		<?php 
			if(!empty($sPcc))
			{
				echo '<td><span>代理帐号</span>'.$sPcc.'</td>';
			}
		?>
	</tr>
	</tbody>
</table>
<h2>游戏结果</h2>
<?php
if(!empty($aRecord))
{
	foreach($aRecord as $a => $s)
	{
		$dBet = $s['adata']['Bet'] / BasicExchange;	#玩家總下注額
		$sUid = $s['adata']['UID'];			#當前玩家UID
		$sBanker = $s['adata']['Banker'];		#莊家UID
		$sBB = $s['adata']['USER_BB'];			#小盲注座位號
		$sSB = $s['adata']['USER_SB'];			#大盲注座位號
		$sBoard1 = $s['adata']['Board1'];		#公用牌1
		$sBoard2 = $s['adata']['Board2'];		#公用牌2
		$sBoard3 = $s['adata']['Board3'];		#公用牌3
		$sBoard4 = $s['adata']['Board4'];		#公用牌4
		$sBoard5 = $s['adata']['Board5'];		#公用牌5		
		$aAllType = $s['adata']['Type'];		#全牌型
		$aAllHDataTwo = $s['adata']['UserHoldemData'];	#全手牌
		$aAllWin = $s['adata']['WinUser'];		#全Win
		$aAllWinAmount = $s['adata']['WinAmountsPot'];	#全贏金額
		$aAllOldAmount = $s['adata']['Amounts'];	#全原金額
		$aAllAction = $s['adata']['Action'];		#全Action
		$aAllHead = $s['adata']['UserHead'];		#全頭像
		$aOPot = $s['adata']['Pot'];			#彩池
		$aPot = array();
		foreach($aOPot as $a => $z)
		{
			$aPot[substr($a,-1,1)] = $z / BasicExchange;
		}

		$nMaxCardType = 20;
		for($i = 1;$i < 9; $i++)
		{
			if($s['adata']['seat'.$i] != 'Null')
			{
				#全局最大牌型
				if($nMaxCardType > $aAllType[$i])
					$nMaxCardType = $aAllType[$i];

				#各座位
				$aAllHData = explode(',', $aAllHDataTwo[$i]);
				$aSet[$i]['UID'] = $s['adata']['seat'.$i];						#UID
				$aSet[$i]['type'] = $aAllType[$i];							#牌型
				$aSet[$i]['head'] = $aAllHead[$i];							#頭像
				$aSet[$i]['HData1'] = $aAllHData[0];							#手牌1
				$aSet[$i]['HData2'] = $aAllHData[1];							#手牌2
				$aSet[$i]['Win'] = $aAllWin[$i];							#Win
				$aSet[$i]['WinAmount'] = Decimal_number($aAllWinAmount[$i] / BasicExchange);		#贏金額
				$aSet[$i]['OldAmount'] = Decimal_number($aAllOldAmount[$i] / BasicExchange);		#原金額
				$aSet[$i]['Action'] = (isset($aAllAction[$i]) && $aAllAction[$i] == 'FOLD' ? 1 : 0);	#Action				
				$aSet[$i]['SetBanker'] = ($aSet[$i]['UID'] == $sBanker ? 1 : 0);			#莊家
				$aSet[$i]['SetBB'] = ($i == $sBB ? 1 : 0);						#小盲
				$aSet[$i]['SetSB'] = ($i == $sSB ? 1 : 0);						#大盲
				$aSet[$i]['SetNow'] = 0;

				if($aSet[$i]['UID'] == $sUid)
				{
					#當前玩家
					$bTotWinAmount = ($aAllWinAmount[$i] / BasicExchange) - $dBet;	#贏金額
					$aSet[$i]['SetNow'] = 1;
					$nPlayerCardType = $s['aPlayer']['Type'];
				}
			}
		}
		
		$iWTime = strtotime($s['writeTime']);
		date_default_timezone_set('America/Puerto_Rico'); #定時區
		$sWtime = date('Y-m-d\TH:i:sP',$iWTime);
		date_default_timezone_set('Asia/Taipei');
?>
<div class="bgColor">
<p style="text-align: center; padding-top: 5px;">#<?php echo $s['gamecnts'];?></p>
<p class="topCount">时间：<?php echo $sWtime;?></p>
<div class="container">
	<table>
	<tr>
	<td>
	<div class="bgDiv">
		<img src="../img/gid04/bg.png"/>
		<?php
		#彩池5~8
		if(count($aPot) > 4)
		{
			echo '<div class="PotTop">';			
			foreach($aPot as $p => $z)
			{
				switch($p)
				{
					case '5':
						PotHtml($p,$z);
						break;
					case '6':
						PotHtml($p,$z);
						break;
					case '7':
						PotHtml($p,$z);
						break;
					case '8':
						PotHtml($p,$z);
						break;
				}
			}
			echo '</div>';
		}
		?>
		<div class="CardImg">
			<img src="../img/gid04/<?php echo $sBoard1;?>.png"/>
			<img src="../img/gid04/<?php echo $sBoard2;?>.png"/>
			<img src="../img/gid04/<?php echo $sBoard3;?>.png"/>
			<img src="../img/gid04/<?php echo $sBoard4;?>.png"/>
			<img src="../img/gid04/<?php echo $sBoard5;?>.png"/>
		</div>
		<div class="PotBottom">
			<?php
			#彩池1~4
			foreach($aPot as $p => $z)
			{
				switch($p)
				{
					case '1':
						PotHtml($p,$z);
						break;
					case '2':
						PotHtml($p,$z);
						break;
					case '3':
						PotHtml($p,$z);
						break;
					case '4':
						PotHtml($p,$z);
						break;
				}
			}
			?>
		</div>
		<?php
		foreach($aSet as $h => $z)
		{
			SetHtml($h,$z);
		}
		?>
	</div>
	</td>
	<td class="PlayerText">
		<div class="box box2">
			<div class="contentText">
				<p>本局最大牌型：<?php echo TypeText($nMaxCardType);?></p>
				<p>玩家牌型：<?php echo TypeText($nPlayerCardType);?></p><br/>
				<?php
				foreach($s['aPlayer']['pots_point'] as $ss => $zz)
				{
					echo '<p>彩池'.$ss.'下注：'.($zz / BasicExchange).'</p>';
				}
				?>
				<p>总下注额：<span class="gold"><?php echo $dBet;?></span></p><br/>
				<?php
				foreach($s['aPlayer']['pots_point'] as $ss => $zz)
				{
					$dWinAmountPot = isset($s['aPlayer']['WinAmountsPot'][$ss]) ? $s['aPlayer']['WinAmountsPot'][$ss] : 0;
					echo '<p>彩池'.$ss.'赢分：'.($dWinAmountPot / BasicExchange).'(尚未抽水)</p>';
				}

				$sColor = $bTotWinAmount < 0 ? 'red' : 'green';
				$bSysAmount = $s['aPlayer']['SysAmounts'] / BasicExchange;
				$bUserTot = $bTotWinAmount < 0 ? ($bTotWinAmount + $dBet + $bSysAmount) : ($bTotWinAmount + $bSysAmount);
				$bTotWinAmount = $bTotWinAmount < 0 ? $bTotWinAmount : '+'.$bTotWinAmount;
				?>
				<p>总赢分(扣除下注、尚未抽水)：<span class="gold"><?php echo $bUserTot;?></span></p><br/>
				<p>系统总抽水：<span class="gold"><?php echo $bSysAmount;?></span></p>
				<p>实得金额(输赢)：<span class="<?php echo $sColor;?>"><?php echo $bTotWinAmount;?></span></p>
			</div>
		</div>
	</td>
	</tr>
	</table>
</div>
	<div class="PlayerTextB">
		<p>本局最大牌型：<?php echo TypeText($nMaxCardType);?></p>
		<p>玩家牌型：<?php echo TypeText($nPlayerCardType);?></p><br/>
		<?php
		foreach($s['aPlayer']['pots_point'] as $ss => $zz)
		{
			echo '<p>彩池'.$ss.'下注：'.($zz / BasicExchange).'</p>';
		}
		?>
		<p>总下注额：<span class="gold"><?php echo $dBet;?></span></p><br/>
		<?php
		foreach($s['aPlayer']['pots_point'] as $ss => $zz)
		{
			$dWinAmountPot = isset($s['aPlayer']['WinAmountsPot'][$ss]) ? $s['aPlayer']['WinAmountsPot'][$ss] : 0;
			echo '<p>彩池'.$ss.'赢分：'.($dWinAmountPot / BasicExchange).'(尚未抽水)</p>';
		}
		?>
		<p>总赢分(扣除下注、尚未抽水)：<span class="gold"><?php echo $bUserTot;?></span></p><br/>
		<p>系统总抽水：<span class="gold"><?php echo $bSysAmount;?></span></p>
		<p>实得金额(输赢)：<span class="<?php echo $sColor;?>"><?php echo $bTotWinAmount;?></span></p>
	</div>
</div>
<?php
		$iTotCount--;
	}
}
?>
</article>
</body>
</html>