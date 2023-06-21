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
		$nGameState = isset($s['adata']['GameState']) ? $s['adata']['GameState'] : -1;		#局狀態
		$dBet = $s['adata']['Bet'] / BasicExchange;	#玩家總下注額
		$dWlpt = $s['wlpt'] / BasicExchange;		#玩家實得金額
		$sUid = $s['adata']['UID'];				#當前玩家UID
		$sBanker = $s['adata']['Banker'];			#莊家UID
		$nTable = $s['adata']['betValueIdx'] + 1;		#場館
		$sBB = $s['adata']['USER_BB'];			#小盲注座位號
		$sSB = $s['adata']['USER_SB'];			#大盲注座位號
		$sBoard1 = $s['adata']['Board1'];			#公用牌1
		$sBoard2 = $s['adata']['Board2'];			#公用牌2
		$sBoard3 = $s['adata']['Board3'];			#公用牌3
		$sBoard4 = $s['adata']['Board4'];			#公用牌4
		$sBoard5 = $s['adata']['Board5'];			#公用牌5		
		$sBoard = '';
		if($nGameState > 0 || $nGameState == -1)
		{
			$sBoard .= $sBoard1.','.$sBoard2.','.$sBoard3;
		}		
		if($nGameState > 1 || $nGameState == -1)
		{
			$sBoard .= ','.$sBoard4;
		}
		if($nGameState > 2 || $nGameState == -1)
		{
			$sBoard .= ','.$sBoard5;
		}

		$aAllType = $s['adata']['Type'];			#全牌型
		$aAllHDataTwo = $s['adata']['UserHoldemData'];	#全手牌
		$aAllWin = $s['adata']['WinUser'];			#全Win
		$aAllWinAmount = $s['adata']['WinAmountsPot'];	#全贏金額
		$aAllOldAmount = $s['adata']['Amounts'];		#全原金額
		$aAllSysRake = $s['adata']['SysAmounts'];		#全抽水
		$aAllAction = $s['adata']['Action'];		#全Action
		$aAllHead = $s['adata']['UserHead'];		#全頭像
		$aOPot = $s['adata']['Pot'];				#彩池
		
		$sPot = '';
		$sSDB = '';
		$aJsSet = array();
		foreach($aOPot as $a => $z)
		{
			$sPot .= ($sPot == '' ? '' : ',').substr($a,-1,1).':'.($z / BasicExchange);
		}

		for($i = 1;$i < 9; $i++)
		{
			if($s['adata']['seat'.$i] != 'Null' && $s['adata']['seat'.$i] == $sUid)
			{
				$nAction = (isset($aAllAction[$i]) && $aAllAction[$i] == 'FOLD') ? 1 : 0;
			}
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
				$aSet[$i]['type'] = $aAllType[$i];								#牌型
				$aSet[$i]['head'] = $aAllHead[$i];								#頭像
				$aSet[$i]['HData1'] = $aAllHData[0];							#手牌1
				$aSet[$i]['HData2'] = $aAllHData[1];							#手牌2
				$aSet[$i]['Win'] = $aAllWin[$i];								#Win
				$aSet[$i]['WinAmount'] = Decimal_number($aAllWinAmount[$i] / BasicExchange);	#贏金額
				$aSet[$i]['OldAmount'] = Decimal_number($aAllOldAmount[$i] / BasicExchange);	#原金額
				$aSet[$i]['Rake'] = Decimal_number($aAllSysRake[$i] / BasicExchange);		#抽水
				$aSet[$i]['Action'] = (isset($aAllAction[$i]) && $aAllAction[$i] == 'FOLD' ? 1 : 0);	#Action				
				$aSet[$i]['SetBanker'] = ($aSet[$i]['UID'] == $sBanker ? 1 : 0);			#莊家
				$aSet[$i]['SetBB'] = ($i == $sBB ? 1 : 0);						#小盲
				$aSet[$i]['SetSB'] = ($i == $sSB ? 1 : 0);						#大盲
				$aSet[$i]['SetNow'] = 0;

				#js SDB
				$sNsdb = ($aSet[$i]['UID'] == $sBanker ? 'D' : ($i == $sSB ? 'S' : ($i == $sBB ? 'B' : 'F')));
				$sSDB .= ($sSDB == '' ? '' : ',').$i.':'.$sNsdb;

				#js 座位
				$aJsSet[$i]['giveup'] = ($aSet[$i]['Action'] == 1 ? 'giveup' : 'F');
				$aJsSet[$i]['card'] = ($nGameState == -1 ? $aAllHData[0].','.$aAllHData[1] : '');
				$aJsSet[$i]['win'] = (($aAllWin[$i] && $nGameState == -1) == 1 ? 'win' : 'F');				
				$aJsSet[$i]['amount'] = $aSet[$i]['OldAmount'];
				$aJsSet[$i]['type'] = (($aSet[$i]['Action'] == 1 || $nAction == 1) ? '' : $aAllType[$i]);
				$aJsSet[$i]['header'] = $aAllHead[$i];
				$aJsSet[$i]['name'] = ($aSet[$i]['UID'] == $sUid ? 9 : $i);
				$aJsSet[$i]['winam'] = $aSet[$i]['WinAmount'];
				$aJsSet[$i]['rake'] = $aSet[$i]['Rake'] * -1;

				if($aSet[$i]['UID'] == $sUid)
				{
					#當前玩家
					$aJsSet[$i]['card'] = $aAllHData[0].','.$aAllHData[1];
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
<script type="text/javascript">
	$(document).ready(function()
	{
		var 	imgtrue = 0,
			oImg = new Object,
			oCard = new Object,
			oName = new Object,
			nField = <?php echo $nTable;?>,
			nCw = 960,
			nCh = 540;
		// var aChip = {1:'2,5,10,20',2:'5,10,20,50',3:'10,20,50,100',4:'20,50,100,200'};
		// var sChip = aChip[nField];
		var sSet = '<?php echo $sSDB;?>';
		var sTableCard = '<?php echo $sBoard;?>';
		var sPot = '<?php echo $sPot;?>';

		var oSet = new Object;

		<?php		
		foreach($aJsSet as $ss => $zz)
		{
			echo 'oSet['.$ss.'] = new Object;';

			foreach($zz as $sss => $zzz)
			{
				echo 'oSet['.$ss.'].'.$sss.' = \''.$zzz.'\';';
			}
		}
		?>

		var domCanvas = document.getElementById('canvas');
		var ctx = domCanvas.getContext('2d');
		domCanvas.width = nCw;
		domCanvas.height = nCh;		

		var aArrImg = new Array();
		aArrImg.push('../img/gid04/table'+nField+'.png');
		aArrImg.push('../img/gid04/bg_pull'+nField+'.png');
		aArrImg.push('../img/gid04/pull'+nField+'.png');
		aArrImg.push('../img/gid04/btn'+nField+'.png');
		// $.each(sChip.split(','), function(i,v)
		// {
		// 	aArrImg.push('../img/gid04/'+v+'.png');
		// });
		aArrImg.push('../img/gid04/btn.png');
		aArrImg.push('../img/gid04/S.png');
		aArrImg.push('../img/gid04/B.png');
		aArrImg.push('../img/gid04/D.png');
		aArrImg.push('../img/gid04/bar_line.png');
		aArrImg.push('../img/gid04/bg_bottom.png');
		aArrImg.push('../img/gid04/bg_chip.png');
		aArrImg.push('../img/gid04/bg_user.png');		
		aArrImg.push('../img/gid04/card1.png');
		aArrImg.push('../img/gid04/card2.png');
		aArrImg.push('../img/gid04/card3.png');
		aArrImg.push('../img/gid04/circle.png');
		aArrImg.push('../img/gid04/dealer.png');
		aArrImg.push('../img/gid04/less.png');
		aArrImg.push('../img/gid04/plus.png');
		aArrImg.push('../img/gid04/pot.png');
		aArrImg.push('../img/gid04/list1.png');
		aArrImg.push('../img/gid04/list2.png');
		aArrImg.push('../img/gid04/list3.png');
		aArrImg.push('../img/gid04/list4.png');
		aArrImg.push('../img/gid04/name_img1.png');
		aArrImg.push('../img/gid04/name_img2.png');
		aArrImg.push('../img/gid04/name_img3.png');
		aArrImg.push('../img/gid04/name_img4.png');
		aArrImg.push('../img/gid04/name_img5.png');
		aArrImg.push('../img/gid04/name_img6.png');
		aArrImg.push('../img/gid04/name_img7.png');
		aArrImg.push('../img/gid04/name_img8.png');
		aArrImg.push('../img/gid04/name_me.png');
		aArrImg.push('../img/gid04/name1.png');
		aArrImg.push('../img/gid04/name2.png');
		aArrImg.push('../img/gid04/name3.png');
		aArrImg.push('../img/gid04/win.png');
		aArrImg.push('../img/gid04/giveup.png');
		aArrImg.push('../img/gid04/type0.png');
		aArrImg.push('../img/gid04/type1.png');
		aArrImg.push('../img/gid04/type2.png');
		aArrImg.push('../img/gid04/type3.png');
		aArrImg.push('../img/gid04/type4.png');
		aArrImg.push('../img/gid04/type5.png');
		aArrImg.push('../img/gid04/type6.png');
		aArrImg.push('../img/gid04/type7.png');
		aArrImg.push('../img/gid04/type8.png');
		aArrImg.push('../img/gid04/type9.png');
		

		$.each(aArrImg, function(k,v)
		{
			IsImgOnload(v);
		});

		function IsImgOnload(u,cb)
		{
			let img = new Image();

			if(cb)
			{
				img = cb;
			}
			else
			{
				img.src = u;
			}

			if(!img.complete)
			{
				img.onload = function()
				{
					IsImgOnload(u,img);
				}
			}
			else
			{
				let aSrc = u.split('/');
				let sImgName = aSrc[aSrc.length - 1].split('.')[0];

				oImg[sImgName] = img;

				imgtrue++;

				if(imgtrue > (aArrImg.length - 1))
				{
					Draw();
				}
			}
		}

		function Draw()
		{
			LoadCard();
			LoadName();

			Draw_table();
			for(let i = 1; i < 5; i++)
			{
				Draw_list(i);
			}

			DrawSetBg();
			ctx.fillStyle = "rgba(0,0,0,.6)";
			ctx.fillRect(0,0,960,540);
			DrawTableCard();
			DrawTablePot();
			DrawSet();
		}

		function LoadCard()
		{
			for(let i = 1; i < 5; i++)
			{
				for(let ii = 1; ii < 14; ii++)
				{
					let n = (i * 100) + ii;
					let x,y,img,nImgX = 80,nImgY = 105;

					oCard[n] = new Object;

					if(n > 100 && n < 114)
					{
						img = 1;
						x = nImgX * ((ii % 6) == 0 ? 5 : (ii % 6) - 1);
						y = nImgY * ((ii % 6) == 0 ? (parseInt(ii / 6) - 1) : parseInt(ii / 6));
					}
					else if(n > 200 && n < 214)
					{
						if(ii < 12)
						{
							img = 1;
							x = nImgX * (ii % 6);
							y = nImgY * parseInt(ii / 6) + (nImgY * i);						
						}
						else
						{
							img = 2;
							x = nImgX * ((ii - 12) % 6);
							y = 0;
						}
					}
					else if(n > 300 && n < 314)
					{
						if(ii < 10)
						{
							img = 2;
							x = nImgX * ((ii + 2) % 6);
							y = nImgY * (parseInt((ii + 2) / 6) + 2);
							
						}
						else
						{
							img = 3;
							x = nImgX * ((ii - 10) % 3);
							y = nImgY * parseInt((ii - 10) / 3);						
						}
					}
					else
					{
						img = 2;
						x = nImgX * ((ii + 1) % 6);
						y = nImgY * parseInt((ii + 1) / 6);
					}

					oCard[n].img = oImg['card'+img];
					oCard[n].x = x;
					oCard[n].y = y;
				}
			}
		}

		function LoadName()
		{
			for(let i = 1;i < 10;i++)
			{
				oName[i] = new Object;

				if(i < 4)
				{
					oName[i].img = oImg['name1'];
					oName[i].y = ((i % 4) - 1) * 34;
				}
				else if(i < 7)
				{
					oName[i].img = oImg['name2'];
					oName[i].y = (((i - 3) % 4) - 1) * 34;
				}
				else if(i == 9)
				{
					oName[i].img = oImg['name_me'];
					oName[i].y = 0;
				}
				else
				{
					oName[i].img = oImg['name3'];
					oName[i].y = ((i - 3) % 4) * 34;
				}
			}
		}

		function Draw_table()
		{
			ctx.drawImage(oImg['table'+nField],0,0,nCw,nCh);
			Draw_Dealer();
			Draw_chip();
			ctx.drawImage(oImg['bg_bottom'],0,402);
			Draw_bar();
			Draw_btn();			
		}

		function DrawTableCard()
		{
			let nCardW = 78,nCardH = 102,nCavW = 70,nCavH = 92,nCavY = 215;
			let nCavX;

			if(sTableCard != '')
			{
				$.each(sTableCard.split(','), function(i,v)
				{
					let o = oCard[v].img;
					let x = oCard[v].x;
					let y = oCard[v].y;

					switch(i)
					{
						case 0:
							nCavX = 290;
							break;
						case 1:
							nCavX = 370;
							break;
						case 2:
							nCavX = 450;
							break;
						case 3:
							nCavX = 530;
							break;
						default:
							nCavX = 610;
							break;
					}
					ctx.drawImage(o,x,y,nCardW,nCardH,nCavX,nCavY,nCavW,nCavH);
				});
			}
		}

		function DrawTablePot()
		{
			ctx.font = '12px Sans-serif';
			ctx.textAlign="center";
			ctx.fillStyle = '#ffffff';

			$.each(sPot.split(','),function (i,v)
			{
				let vv = v.split(':')[1];
				let nPotX = (i % 4) * 110 + 280;
				let nPotY = (i > 3 ? 125 : 270);
				let nPotTxtX = (i % 4) * 110 + 334;
				let nPotTxtY = (i > 3 ? 195 : 340);

				ctx.drawImage(oImg['pot'],nPotX,nPotY);
				ctx.fillText((i+1)+'.', nPotTxtX - 60, nPotTxtY, 40);
				ctx.fillText(vv, nPotTxtX, nPotTxtY, 40);
			});
		}

		function Draw_Dealer()
		{
			let x,y = 170;

			switch(nField)
			{
				case 1:
					x = 188;
					break;
				case 2:
				case 3:
					x = 194;
					break;
				default:
					x = 183;
					break;
			}
			ctx.drawImage(oImg['dealer'],387,-4,x,y);
		}

		function Draw_chip()
		{
			let x,y = 392,w=h=100;
			ctx.drawImage(oImg['bg_chip'],173,405);

			// $.each(sChip.split(','), function(i,v)
			// {
			// 	switch(i)
			// 	{
			// 		case 0:
			// 			x = 230;						
			// 			break;
			// 		case 1:
			// 			x = 320;
			// 			break;
			// 		case 2:
			// 			x = 410;
			// 			break;
			// 		default:
			// 			x = 500;
			// 			break;
			// 	}
			// 	ctx.drawImage(oImg[v],x,y,w,h);
			// });
		}

		function Draw_list(n)
		{
			let o = oImg['list'+n];
			let x,y = 407;

			switch(n)
			{
				case 1:
					x = 895;
					y = 10;
					break;
				case 2:
					x = 273;
					break;
				case 3:
					x = 373;
					break;
				default:
					x = 473;
					break;
			}
			ctx.drawImage(o,x,y);
		}

		function Draw_bar()
		{
			let nY = 470;
			let nLessX = 200;
			let nPlusX = 590;

			ctx.drawImage(oImg['bar_line'],221,485,26,16);
			ctx.drawImage(oImg['pull'+nField],240,nY);

			ctx.drawImage(oImg['bg_pull'+nField],nLessX,nY);
			ctx.drawImage(oImg['less'],nLessX,nY);

			ctx.drawImage(oImg['bg_pull'+nField],nPlusX,nY);
			ctx.drawImage(oImg['plus'],nPlusX,nY);
		}

		function Draw_btn()
		{
			let x1 = 800,x2 = 655,y1 = 435,y2 = 485;

			ctx.drawImage(oImg['btn'],x1,y1);
			ctx.drawImage(oImg['btn'+nField],x2,y1);
			ctx.drawImage(oImg['btn'+nField],x1,y2);
			ctx.drawImage(oImg['btn'+nField],x2,y2);
		}

		function DrawSetBg()
		{
			$.each(sSet.split(','),function(i,v)
			{
				let vv = v.split(':')[0];
				let s_bds = v.split(':')[1];
				let x,y;

				switch(parseInt(vv))
				{
					case 1:
						ctx.drawImage(oImg['bg_user'],710,35);
						ctx.drawImage(oImg['circle'],709,35);
						x = 707;
						y = 34;
						break;
					case 2:
						ctx.drawImage(oImg['bg_user'],820,100);
						ctx.drawImage(oImg['circle'],819,100);
						x = 815;
						y = 100;
						break;
					case 3:
						ctx.drawImage(oImg['bg_user'],820,210);
						ctx.drawImage(oImg['circle'],819,210);
						x = 815;
						y = 210;
						break;
					case 4:
						ctx.drawImage(oImg['bg_user'],800,315);
						ctx.drawImage(oImg['circle'],799,315);
						x = 795;
						y = 315;
						break;
					case 5:
						ctx.drawImage(oImg['bg_user'],15,320);
						ctx.drawImage(oImg['circle'],14,320);
						x = 10;
						y = 320;
						break;
					case 6:
						ctx.drawImage(oImg['bg_user'],10,215);
						ctx.drawImage(oImg['circle'],9,215);
						x = 5;
						y = 215;
						break;
					case 7:
						ctx.drawImage(oImg['bg_user'],10,105);
						ctx.drawImage(oImg['circle'],9,105);
						x = 5;
						y = 105;
						break;
					default:
						ctx.drawImage(oImg['bg_user'],118,29);
						ctx.drawImage(oImg['circle'],117,29);
						x = 113;
						y = 29;
						break;
				}

				if(s_bds != 'F')
					DrawSDB(s_bds,x,y);
			});
		}

		function DrawSDB(o,x,y)
		{
			ctx.drawImage(oImg[o],x,y);
		}

		function DrawSet()
		{
			$.each(sSet.split(','),function(i,v)
			{
				let nTypeX,nTypeY;
				let vv = v.split(':')[0];

				switch(parseInt(vv))
				{
					case 1:
						DrawHeader(vv,723,49);

						nTypeX = (oSet[vv].type == 9 ? 627 : (oSet[vv].type == 1 ? 615 : 610));
						nTypeY = (oSet[vv].type == 0 ? 110 : 120);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,700,30,723,20);
						DrawSetName(vv,727,40);
						break;
					case 2:
						DrawHeader(vv,833,113);

						nTypeX = (oSet[vv].type == 9 ? 755 : 730);
						nTypeY = (oSet[vv].type == 0 ? 110 : 120);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,810,95,830,85);
						DrawSetName(vv,838,103);
						break;
					case 3:
						DrawHeader(vv,833,223);

						nTypeX = (oSet[vv].type == 9 ? 755 : 730);
						nTypeY = (oSet[vv].type == 0 ? 220 : 230);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,810,205,830,195);
						DrawSetName(vv,838,212);
						break;
					case 4:
						DrawHeader(vv,812,328);

						nTypeX = (oSet[vv].type == 9 ? 735 : 710);
						nTypeY = (oSet[vv].type == 0 ? 320 : 335);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,789,310,809,301);
						DrawSetName(vv,817,317);
						break;
					case 5:
						DrawHeader(vv,28,332);

						nTypeX = (oSet[vv].type == 0 ? 155 : 155);
						nTypeY = (oSet[vv].type == 0 ? 320 : 330);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,5,313,27,305);
						DrawSetName(vv,33,322);
						break;
					case 6:
						DrawHeader(vv,22,228);

						nTypeX = (oSet[vv].type == 0 ? 135 : 150);
						nTypeY = (oSet[vv].type == 0 ? 215 : 225);
						DrawSetType(vv,nTypeX,nTypeY);
						
						DrawSetWinGive(vv,-1,210,22,202);
						DrawSetName(vv,26,216);
						break;
					case 7:
						DrawHeader(vv,22,118);

						nTypeX = (oSet[vv].type == 0 ? 135 : 150);
						nTypeY = (oSet[vv].type == 0 ? 105 : 120);
						DrawSetType(vv,nTypeX,nTypeY);
						
						DrawSetWinGive(vv,-1,100,20,90);
						DrawSetName(vv,26,110);
						break;
					default:
						DrawHeader(vv,131,42);

						nTypeX = (oSet[vv].type == 9 ? 260 : 247);
						nTypeY = (oSet[vv].type == 0 ? 100 : 105);
						DrawSetType(vv,nTypeX,nTypeY);

						DrawSetWinGive(vv,107,24,130,15);
						DrawSetName(vv,137,33);
						break;
				}
			});

			$.each(sSet.split(','),function(i,v)
			{
				let nTypeX,nTypeY;
				let vv = v.split(':')[0];

				switch(parseInt(vv))
				{
					case 1:
						DrawSetCard(vv,605,93,653,-22);
						break;
					case 2:
						DrawSetCard(vv,863,150,910,-9);
						break;
					case 3:
						DrawSetCard(vv,853,257,920,98);
						break;
					case 4:
						DrawSetCard(vv,830,361,915,205);
						break;
					case 5:
						DrawSetCard(vv,47,298,130,278);
						break;
					case 6:
						DrawSetCard(vv,52,188,115,169);
						break;
					case 7:
						DrawSetCard(vv,60,78,107,59);
						break;
					default:
						DrawSetCard(vv,243,52,283,2);
						break;
				}
			});

			$.each(sSet.split(','),function(i,v)
			{
				let nTypeX,nTypeY;
				let vv = v.split(':')[0];

				switch(parseInt(vv))
				{
					case 1:
						DrawSetAmount(vv,804,95);
						DrawSetWinRake(vv,745,72);
						break;
					case 2:
						DrawSetAmount(vv,914,160);
						DrawSetWinRake(vv,840,137);
						break;
					case 3:
						DrawSetAmount(vv,914,271);
						DrawSetWinRake(vv,840,250);
						break;
					case 4:
						DrawSetAmount(vv,894,375);
						DrawSetWinRake(vv,825,353);
						break;
					case 5:
						DrawSetAmount(vv,108,380);
						DrawSetWinRake(vv,40,360);
						break;
					case 6:
						DrawSetAmount(vv,103,276);
						DrawSetWinRake(vv,38,255);
						break;
					case 7:
						DrawSetAmount(vv,103,166);
						DrawSetWinRake(vv,38,145);
						break;
					default:
						DrawSetAmount(vv,213,90);
						DrawSetWinRake(vv,212,37);
						break;
				}
			});
		}

		function DrawHeader(n,x,y)
		{
			let s = 'name_img'+oSet[n].header;
			ctx.drawImage(oImg[s],x,y,45,45);
		}

		function DrawSetCard(n,x1,y1,x2,y2)
		{
			if(oSet[n].card != '')
			{
				$.each(oSet[n].card.split(','), function(i,v)
				{
					let r = (5 * Math.PI / 180) * (i == 0 ? -1 : 1);
					let o = oCard[v].img;
					let x = oCard[v].x;
					let y = oCard[v].y;
					let xx = (i == 0 ? x1 : x2);
					let yy = (i == 0 ? y1 : y2);				

					ctx.save();
					ctx.translate(0,0);
					ctx.rotate(r);
					ctx.drawImage(o,x,y,78,102,xx,yy,55,72);
					ctx.restore();
				});
			}
		}

		function DrawSetType(n,x,y)
		{
			let o = 'type' + oSet[n].type;

			if(oSet[n].type != '')
			{
				ctx.drawImage(oImg[o],0,0,119,92,x,y,90,70);
			}
		}

		function DrawSetName(n,x,y)
		{
			let o = oName[oSet[n].name].img;
			let xx = 0;
			let yy = oName[oSet[n].name].y;

			x = (oSet[n].name == 9 ? (x + 3) : (x - 8));
			y = (oSet[n].name == 9 ? y : (y + 2));

			ctx.drawImage(o,xx,yy,83,34,x,y,50,20);
		}

		function DrawSetAmount(n,x,y)
		{
			let nn = oSet[n].amount;
			ctx.font = '14px Sans-serif';
			ctx.textAlign="center";
			ctx.fillStyle = '#ffcc66';
			ctx.fillText(nn, x, y, 52);
		}

		function DrawSetWinGive(n,x1,y1,x2,y2)
		{
			if(oSet[n].win != 'F')
			{
				ctx.drawImage(oImg['win'],0,0,204,198,x1,y1,90,87);
			}

			if(oSet[n].giveup != 'F')
			{
				ctx.drawImage(oImg['giveup'],0,0,67,36,x2,y2,50,27);
			}
		}

		function DrawSetWinRake(n,x,y)
		{
			let nWinAmount,nRake;

			ctx.font = '16px Sans-serif';
			ctx.strokeStyle = '#000000';
			ctx.lineWidth = 4;
			ctx.fillStyle = '#ffcc00';

			if(oSet[n].winam != 0)
			{
				nWinAmount = '+'+oSet[n].winam;;
				ctx.strokeText(nWinAmount , x, y, 75);
				ctx.fillText(nWinAmount , x, y, 75);
			}

			if(oSet[n].rake != 0)
			{
				nRake = oSet[n].rake + '(Rake)';
				ctx.strokeText(nRake , x, (y + 30), 75);
				ctx.fillText(nRake , x, (y + 30), 75);
			}
		}
	});
</script>

<div class="bgColor">
<p style="text-align: center; padding-top: 5px;">#<?php echo $s['gamecnts'];?></p>
<p class="topCount">时间：<?php echo $sWtime;?></p>
<div class="container">
	<table>
	<tr>
	<td>
	<div class="bgDiv">
		<canvas id="canvas"></canvas>
	</div>
	</td>
	<td class="PlayerText">
		<div class="box box2">
			<div class="contentText">
				<?php
				if($nGameState == -1)
				{
					echo '<p>本局最大牌型：'.TypeText($nMaxCardType).'</p>';
					echo '<p>玩家牌型：'.TypeText($nPlayerCardType).'</p><br/>';
				}
				?>
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
					echo '<p>彩池'.$ss.'分得(未抽水)：'.($dWinAmountPot / BasicExchange).'</p>';
				}

				$sColor = $dWlpt < 0 ? 'red' : 'green';
				$bSysAmount = $s['aPlayer']['SysAmounts'] / BasicExchange;
				$bUserTot = $bTotWinAmount < 0 ? ($bTotWinAmount + $dBet) : $bTotWinAmount;
				$bTotWinAmount = $bTotWinAmount < 0 ? $bTotWinAmount : '+'.$bTotWinAmount;
				$dWlpt = $dWlpt < 0 ? $dWlpt : '+'.$dWlpt;
				?>
				<p>总赢额(扣除下注、未抽水)：<span class="gold"><?php echo $bUserTot;?></span></p><br/>
				<p>实得金额(输赢)：<span class="<?php echo $sColor;?>"><?php echo $dWlpt;?></span></p>
				<p>系统总抽水：<?php echo $bSysAmount;?></p>
			</div>
		</div>
	</td>
	</tr>
	</table>
</div>
	<div class="PlayerTextB">
		<?php
		if($nGameState == -1)
		{
			echo '<p>本局最大牌型：'.TypeText($nMaxCardType).'</p>';
			echo '<p>玩家牌型：'.TypeText($nPlayerCardType).'</p><br/>';
		}
		?>		
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
			echo '<p>彩池'.$ss.'分得(未抽水)：'.($dWinAmountPot / BasicExchange).'</p>';
		}

		$bSysAmount = $s['aPlayer']['SysAmounts'] / BasicExchange;
		$bUserTot = $bTotWinAmount < 0 ? ($bTotWinAmount + $dBet) : $bTotWinAmount;
		$bTotWinAmount = $bTotWinAmount < 0 ? $bTotWinAmount : '+'.$bTotWinAmount;
		?>
		<p>总赢额(扣除下注、未抽水)：<span class="gold"><?php echo $bUserTot;?></span></p><br/>
		<p>实得金额(输赢)：<span class="<?php echo $sColor;?>"><?php echo $dWlpt;?></span></p>
		<p>系统总抽水：<?php echo $bSysAmount;?></p>
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