<!-- main -->
<?php require_once('../php/function/req_fun01.php');?>
<?php
define('ImgDir','../img/gid01/'); #圖片路徑
?>
<article>
	<!-- header -->
	<h1>游戏名称：<?php echo $sGameName;?></h1>
	<table class="accTab">
		<tbody>
		<tr>
			<td class="accBor"><p><span>单号</span><?php echo $sRoundid;?></p></td>
			<td class="accBor">
				<span>输赢</span><?php echo ($iWtot < 0 ? '<span class="sumNegTot">'. ($iWtot / BasicExchange) .'</span>' : ($iWtot > 0 ? '<span class="sumAddTot">'. '+'.($iWtot / BasicExchange) .'</span>' : ($iWtot / BasicExchange)));?></td>
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
	<!-- header End -->
	<!-- body -->
	<h2>游戏结果</h2>
	<?php
	if(!empty($aRecord))
	{
		foreach($aRecord as $a => $s)
		{
			$iCmax = $s['adata']['cmax'] / BasicExchange;	#最大押注
			$iCmin = $s['adata']['cmin'] / BasicExchange;	#最小押注
			$iGamePoint = $s['adata']['GamePoint'] / BasicExchange;
			#牌
			$sTabLCar1 = $s['adata']['PlayerCard3'] > 0 ? '<img class="TabLCar1" src="'.ImgDir.$s['adata']['PlayerCard3'].'.png" />' : '';
			$sTabLCar2 = $s['adata']['PlayerCard2'];
			$sTabLCar3 = $s['adata']['PlayerCard1'];
			$sTabRCar1 = $s['adata']['BankerCard3'] > 0 ? '<img class="TabRCar1" src="'.ImgDir.$s['adata']['BankerCard3'].'.png" />' : '';
			$sTabRCar2 = $s['adata']['BankerCard1'];
			$sTabRCar3 = $s['adata']['BankerCard2'];
			#各區總下注
			$iTotBet1 = $s['adata']['TotalBetArea1'] / BasicExchange;
			$iTotBet2 = $s['adata']['TotalBetArea4'] / BasicExchange;
			$iTotBet3 = $s['adata']['TotalBetArea3'] / BasicExchange;
			$iTotBet4 = $s['adata']['TotalBetArea5'] / BasicExchange;
			$iTotBet5 = $s['adata']['TotalBetArea2'] / BasicExchange;
			#結果
			$iResType = $s['adata']['ResultType']; #莊閒和
			$sResType = ($iResType == 0 ? 'w2' : ($iResType == 1 ? 'w1' : 'w3')); #莊閒和
			$sResClass = ($iResType == 2 ? 'Tie' : 'PlayBank'); #是否為和
			#各區玩家下注
			$iBet1 = $s['adata']['Bet1'] / BasicExchange; #閒
			$iBet2 = $s['adata']['Bet4'] / BasicExchange; #閒對
			$iBet3 = $s['adata']['Bet3'] / BasicExchange; #和
			$iBet4 = $s['adata']['Bet5'] / BasicExchange; #莊對
			$iBet5 = $s['adata']['Bet2'] / BasicExchange; #莊
			$iPTotBet = $iBet1 + $iBet2 + $iBet3 + $iBet4 + $iBet5;	#玩家總下注
			#各區玩家輸贏(若為0表示輸掉下注額)
			$iWL1 = $s['adata']['WinLost1'] / BasicExchange;
			$iWL2 = $s['adata']['WinLost4'] / BasicExchange;
			$iWL3 = $s['adata']['WinLost3'] / BasicExchange;
			$iWL4 = $s['adata']['WinLost5'] / BasicExchange;
			$iWL5 = $s['adata']['WinLost2'] / BasicExchange;
			$iWL1 = $iWL1 == 0 ? $iBet1 * (-1) : $iWL1;
			$iWL2 = $iWL2 == 0 ? $iBet2 * (-1) : $iWL2;
			$iWL3 = $iWL3 == 0 ? $iBet3 * (-1) : $iWL3;
			$iWL4 = $iWL4 == 0 ? $iBet4 * (-1) : $iWL4;
			$iWL5 = $iWL5 == 0 ? $iBet5 * (-1) : $iWL5;
			if($iResType == 2)
				$iWL1 = $iWL5 = 0;
			$iPTotWL = $iWL1 + $iWL2 + $iWL3 + $iWL4 + $iWL5;	#玩家總輸贏
			#玩家輸贏顏色				
			$sWL1 = $iWL1 >= 0 ? 'colGreen' : '';
			$sWL2 = $iWL2 >= 0 ? 'colGreen' : '';
			$sWL3 = $iWL3 >= 0 ? 'colGreen' : '';
			$sWL4 = $iWL4 >= 0 ? 'colGreen' : '';
			$sWL5 = $iWL5 >= 0 ? 'colGreen' : '';
			$sPTotWL = $iPTotWL >= 0 ? 'colGreen' : '';	#玩家總輸贏顏色
			#玩家輸贏+-
			$iWL1 > 0 ? $iWL1 = '+'.$iWL1 : ($iWL1 < 0 ? $iWL1 = $iWL1 : '');
			$iWL2 > 0 ? $iWL2 = '+'.$iWL2 : ($iWL2 < 0 ? $iWL2 = $iWL2 : '');
			$iWL3 > 0 ? $iWL3 = '+'.$iWL3 : ($iWL3 < 0 ? $iWL3 = $iWL3 : '');
			$iWL4 > 0 ? $iWL4 = '+'.$iWL4 : ($iWL4 < 0 ? $iWL4 = $iWL4 : '');
			$iWL5 > 0 ? $iWL5 = '+'.$iWL5 : ($iWL5 < 0 ? $iWL5 = $iWL5 : '');
			$iPTotWL > 0 ? $iPTotWL = '+'.$iPTotWL : ($iPTotWL < 0 ? $iPTotWL = $iPTotWL : ''); #玩家總輸贏+-
			#牌數值
			$iPlayerS = $s['adata']['PlayerScore'];	#閒
			$iBankerS = $s['adata']['BankerScore']; #莊			
			#莊對閒對
			$iPair1 = $s['adata']['PlayerPair'];
			$iPair2 = $s['adata']['BankerPair'];
			$sPair = '';
			if($iPair1 == 1 && $iPair2 == 1)
			{
				$sPair = '<img class="WinTxt Pair1" src="'.ImgDir.'w22.png" />';
				$sPair .= '<img class="WinTxt Pair2" src="'.ImgDir.'w11.png" />';
			}
			else if($iPair1 == 1 || $iPair2 == 1)
			{
				$nPair = $iPair1 == 1 ? 'w11' : 'w22';
				$sPair = '<img class="WinTxt Pair1" src="'.ImgDir.$nPair.'.png" />';
			}
			#莊閒和次數總計
			$iPlayerCnts = $s['adata']['PlayerCnts'];
			$iBankerCnts = $s['adata']['BankerCnts'];
			$iTieCnts = $s['adata']['TieCnts'];
			#籌碼
			$sChip = '';
			$sChip .= $iTotBet1 > 0 ? '<img class="chip PlayerChip" src="'.ImgDir.'chip'.rand(10,15).'.png" />' : '';
			$sChip .= $iTotBet2 > 0 ? '<img class="chip PlayerPairChip" src="'.ImgDir.'chip'.rand(4,9).'.png" />' : '';
			$sChip .= $iTotBet3 > 0 ? '<img class="chip TieChip" src="'.ImgDir.'chip'.rand(1,3).'.png" />' : '';
			$sChip .= $iTotBet4 > 0 ? '<img class="chip BankerPairChip" src="'.ImgDir.'chip'.rand(4,9).'.png" />' : '';
			$sChip .= $iTotBet5 > 0 ? '<img class="chip BankerChip" src="'.ImgDir.'chip'.rand(10,15).'.png" />' : '';
			#Win
			$sWin = ($iResType == 0 ? 'PlayerWin' : ($iResType == 1 ? 'BankerWin' : 'TieWin'));
			
			$iWTime = strtotime($s['writeTime']);
			date_default_timezone_set('America/Puerto_Rico'); #定時區
			$sWtime = date('Y-m-d\TH:i:sP',$iWTime);
			date_default_timezone_set('Asia/Taipei');
	?>
	<div class="BGDiv">
		<p style="text-align: center; padding-top: 5px;">#<?php echo $s['gamecnts'];?></p>
		<p class="topCount">时间：<?php echo $sWtime;?></p>
		<!-- img & text -->
		<div class="container">
			<table>
			<tbody>
			<tr>
				<td>
				<div class="ImgDiv">
					<div class="Tab">
						<img class="BgImg" src="<?php echo ImgDir;?>bg1.png" />
						<img class="WinTxt <?php echo $sResClass;?>" src="<?php echo ImgDir.$sResType;?>.png" />
						<?php echo $sPair;?>
						<div class="MaxMin">
							<div class="Min BetNumColor"><?php echo $iCmin;?></div>
							<div class="Max BetNumColor"><?php echo $iCmax;?></div>
						</div>
						<div class="TabLCar">
							<div class="PlayerScore PlayerColor"><?php echo $iPlayerS;?></div>
							<?php echo $sTabLCar1;?>
							<img class="TabLCar2" src="<?php echo ImgDir.$sTabLCar2;?>.png" />
							<img class="TabLCar3" src="<?php echo ImgDir.$sTabLCar3;?>.png" />
						</div>
						<div class="TabRCar">
							<div class="BankerScore BankerColor"><?php echo $iBankerS;?></div>
							<?php echo $sTabRCar1;?>
							<img class="TabRCar2" src="<?php echo ImgDir.$sTabRCar2;?>.png" />
							<img class="TabRCar3" src="<?php echo ImgDir.$sTabRCar3;?>.png" />
						</div>
						<span class="Tot1 PlayerColor2"><?php echo $iTotBet1;?></span>
						<span class="Tot2 PlayerColor2"><?php echo $iTotBet2;?></span>
						<span class="Tot3 TieColor2"><?php echo $iTotBet3;?></span>
						<span class="Tot4 BankerColor2"><?php echo $iTotBet4;?></span>
						<span class="Tot5 BankerColor2"><?php echo $iTotBet5;?></span>
						<span class="BetNumColor bet1"><?php echo $iBet1;?></span>
						<span class="BetNumColor bet2"><?php echo $iBet2;?></span>
						<span class="BetNumColor bet3"><?php echo $iBet3;?></span>
						<span class="BetNumColor bet4"><?php echo $iBet4;?></span>
						<span class="BetNumColor bet5"><?php echo $iBet5;?></span>
						<img class="WinImg <?php echo $sWin;?>" src="<?php echo ImgDir;?>win.png" />
						<?php echo $sChip;?>
						<div class="nowNum BetNumColor"><?php echo $iGamePoint;?></div>
						<span class="PlayerCnt PlayerColor"><?php echo $iPlayerCnts;?></span>
						<span class="BankerCnt BankerColor"><?php echo $iBankerCnts;?></span>
						<span class="TieCnt TieColor"><?php echo $iTieCnts;?></span>
					</div>
				</div>
				</td>

				<td class="padH">
					<div class="betB">
						<h3>下注金额</h3>
						<p>闲：<span><?php echo $iBet1;?></span></p>
						<p>闲对：<span><?php echo $iBet2;?></span></p>
						<p>和：<span><?php echo $iBet3;?></span></p>
						<p>庄对：<span><?php echo $iBet4;?></span></p>
						<p>庄：<span><?php echo $iBet5;?></span></p>
						<p>总下注额：<span><?php echo $iPTotBet;?></span></p>
					</div>
					<div class="wlB">
						<h3>输赢</h3>
						<p>闲：<span class="<?php echo $sWL1;?>"><?php echo $iWL1;?></span></p>
						<p>闲对：<span class="<?php echo $sWL2;?>"><?php echo $iWL2;?></span></p>
						<p>和：<span class="<?php echo $sWL3;?>"><?php echo $iWL3;?></span></p>
						<p>庄对：<span class="<?php echo $sWL4;?>"><?php echo $iWL4;?></span></p>
						<p>庄：<span class="<?php echo $sWL5;?>"><?php echo $iWL5;?></span></p>
						<p>总输赢：<span class="<?php echo $sPTotWL;?>"><?php echo $iPTotWL;?></span></p>
					</div>
				</td>
			</tr>
			</tbody>
			</table>
		</div>
		<div class="padH2">
			<div class="betB">
				<h3>下注金额</h3>
				<p>闲：<span><?php echo $iBet1;?></span></p>
				<p>闲对：<span><?php echo $iBet2;?></span></p>
				<p>和：<span><?php echo $iBet3;?></span></p>
				<p>庄对：<span><?php echo $iBet4;?></span></p>
				<p>庄：<span><?php echo $iBet5;?></span></p>
				<p>总下注额：<span><?php echo $iPTotBet;?></span></p>
			</div>
			<div class="wlB">
				<h3>输赢</h3>
				<p>闲：<span class="<?php echo $sWL1;?>"><?php echo $iWL1;?></span></p>
				<p>闲对：<span class="<?php echo $sWL2;?>"><?php echo $iWL2;?></span></p>
				<p>和：<span class="<?php echo $sWL3;?>"><?php echo $iWL3;?></span></p>
				<p>庄对：<span class="<?php echo $sWL4;?>"><?php echo $iWL4;?></span></p>
				<p>庄：<span class="<?php echo $sWL5;?>"><?php echo $iWL5;?></span></p>
				<p>总输赢：<span class="<?php echo $sPTotWL;?>"><?php echo $iPTotWL;?></span></p>
			</div>
		</div>
		<!-- img & text End -->
		<!-- Road -->
		<?php
		#路
		$aBallRoad = explode(',',$s['adata']['BallRoad']);
		?>
		<div class="divRoad">
			<p></p>
			<div class="Roadtab1">
			<div class="tabW1">
				<table>
				<tbody>
					<?php
					#珠路
					$sStr = Zhulu($aBallRoad);
					echo $sStr;
					?>
				</tbody>
				</table>

				<table>
				<tbody>
					<?php
					#大路
					$sStr = BigRoad($aBallRoad);
					echo $sStr;
					?>
				</tbody>
				</table>
			</div>
			</div>

			<div class="Roadtab2">
			<div class="tabW2">
				<table>
				<tbody>
					<?php
					#預備畫下三路的大路
					$aBigRoad = BigRoad($aBallRoad,1);
					
					#大眼仔
					$sStr = ThreeRoad($aBigRoad,1);
					echo $sStr;
					?>
				</tbody>
				</table>

				<table style="margin-right: -5px">
				<tbody>
					<?php
					#小路
					$sStr = ThreeRoad($aBigRoad,2);
					echo $sStr;
					?>
				</tbody>
				</table>

				<table>
				<tbody>
					<?php
					#甲由路
					$sStr = ThreeRoad($aBigRoad,3);
					echo $sStr;
					?>
				</tbody>
				</table>
			</div>
			</div>
		</div>
		<!-- Road End -->
	</div>
	<?php
		}
	}
	?>
	<!-- body End -->
</article>
<!-- main End -->