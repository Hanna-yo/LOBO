<!-- main -->
<?php require_once('../php/function/req_fun03.php');?>
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
<h2>游戏结果</h2>
<?php 
$sImgUrl = '../img/gid03/'; #圖片URL
if(!empty($aRecord))
{
	foreach($aRecord as $a => $s)
	{
		$iCmax = isset($s['adata']['cmax']) ? $s['adata']['cmax'] / BasicExchange : 100;
		$iCmin = isset($s['adata']['cmin']) ? $s['adata']['cmin'] / BasicExchange : 1;
		$sCmaxCss = (mb_strlen($iCmax,'utf-8') == 3 ? 'maxBet2' : 'maxBet');
		$iTBet1 = num_except($s['adata']['TotalBetArea1']);
		$iTBet2 = num_except($s['adata']['TotalBetArea2']);
		$iTBet3 = num_except($s['adata']['TotalBetArea3']);
		$iTBet4 = num_except($s['adata']['TotalBetArea4']);
		$iBet1 = num_except($s['adata']['Bet1']);
		$iBet2 = num_except($s['adata']['Bet2']);
		$iBet3 = num_except($s['adata']['Bet3']);
		$iBet4 = num_except($s['adata']['Bet4']);
		$iTBt = $iBet1 + $iBet2 + $iBet3 + $iBet4;
		$iWL1 = num_except($s['adata']['WinLost1']);
		$iWL2 = num_except($s['adata']['WinLost2']);
		$iWL3 = num_except($s['adata']['WinLost3']);
		$iWL4 = num_except($s['adata']['WinLost4']);
		$iWLt = $iWL1 + $iWL2 + $iWL3 + $iWL4;
		$sWL1 = num_color($s['adata']['WinLost1']);
		$sWL2 = num_color($s['adata']['WinLost2']);
		$sWL3 = num_color($s['adata']['WinLost3']);
		$sWL4 = num_color($s['adata']['WinLost4']);
		$sWLt = num_color($iWLt);
		$swl1 = num_wl($s['adata']['WinLoseWithBank1']);
		$swl2 = num_wl($s['adata']['WinLoseWithBank2']);
		$swl3 = num_wl($s['adata']['WinLoseWithBank3']);
		$swl4 = num_wl($s['adata']['WinLoseWithBank4']);
		$iNNtype1 = 'n'.$s['adata']['NiuNiuType1'];
		$iNNtype2 = 'n'.$s['adata']['NiuNiuType2'];
		$iNNtype3 = 'n'.$s['adata']['NiuNiuType3'];
		$iNNtype4 = 'n'.$s['adata']['NiuNiuType4'];
		$iNNtype5 = 'n'.$s['adata']['NiuNiuType5'];
		$sCard1 = cord_name($s['adata']['card1']);
		$sCard2 = cord_name($s['adata']['card2']);
		$sCard3 = cord_name($s['adata']['card3']);
		$sCard4 = cord_name($s['adata']['card4']);
		$sCard5 = cord_name($s['adata']['card5']);
		$sCard6 = cord_name($s['adata']['card6']);
		$sCard7 = cord_name($s['adata']['card7']);
		$sCard8 = cord_name($s['adata']['card8']);
		$sCard9 = cord_name($s['adata']['card9']);
		$sCard10 = cord_name($s['adata']['card10']);
		$sCard11 = cord_name($s['adata']['card11']);
		$sCard12 = cord_name($s['adata']['card12']);
		$sCard13 = cord_name($s['adata']['card13']);
		$sCard14 = cord_name($s['adata']['card14']);
		$sCard15 = cord_name($s['adata']['card15']);
		$sCard16 = cord_name($s['adata']['card16']);
		$sCard17 = cord_name($s['adata']['card17']);
		$sCard18 = cord_name($s['adata']['card18']);
		$sCard19 = cord_name($s['adata']['card19']);
		$sCard20 = cord_name($s['adata']['card20']);
		$sCard21 = cord_name($s['adata']['card21']);
		$sCard22 = cord_name($s['adata']['card22']);
		$sCard23 = cord_name($s['adata']['card23']);
		$sCard24 = cord_name($s['adata']['card24']);
		$sCard25 = cord_name($s['adata']['card25']);
		$sBet1   = strlen($iBet1) == 3 ? 'ibetThree' : (strlen($iBet1) == 2 ? 'ibetTwo' : '');
		$sBet2   = strlen($iBet2) == 3 ? 'ibetThree' : (strlen($iBet2) == 2 ? 'ibetTwo' : '');
		$sBet3   = strlen($iBet3) == 3 ? 'ibetThree' : (strlen($iBet3) == 2 ? 'ibetTwo' : '');
		$sBet4   = strlen($iBet4) == 3 ? 'ibetThree' : (strlen($iBet4) == 2 ? 'ibetTwo' : '');
		$dGamePoint = $s['adata']['GamePoint'] / BasicExchange;
		$nChipInx = isset($s['adata']['betValueIdx']) ? $s['adata']['betValueIdx'] : 0;
		
		#時間
		$iWTime = strtotime($s['writeTime']);
		date_default_timezone_set('America/Puerto_Rico'); #定時區
		$sWtime = date('Y-m-d\TH:i:sP',$iWTime);
		date_default_timezone_set('Asia/Taipei');

		#籌碼
		$aChip = array();
		for($nI = 2;$nI < 6; $nI++)
		{
			$nR = RAND(1,7);
			if($nI == 2)
			{
				$aChip[$nI] = $nR;
			}
			else
			{
				foreach($aChip as $c => $p)
				{
					if($p == $nR)
					{
						$nI--;
						break;
					}
					else
					{
						$aChip[$nI] = $nR;
					}
				}
			}			
		}
?>
<script type="text/javascript">
	$(document).ready(function()
		{
			var sMemPoint = '<?php echo $dGamePoint;?>';
			var sMaxBet = '<?php echo $iCmax;?>';
			var sMinBet = '<?php echo $iCmin;?>';
			var aTotArea = ['<?php echo $iTBet1;?>','<?php echo $iTBet2;?>','<?php echo $iTBet3;?>','<?php echo $iTBet4;?>'];
			var aBetArea = ['<?php echo $iBet1;?>','<?php echo $iBet2;?>','<?php echo $iBet3;?>','<?php echo $iBet4;?>'];
			var aWinLoseAreaNum = ['<?php echo $iWL1;?>','<?php echo $iWL2;?>','<?php echo $iWL3;?>','<?php echo $iWL4;?>'];
			var oCardImg = oChipImg = oNiuImg = oWinLoseImg = oAllCardImg = {};
			var oCardArea1 = [<?php echo $sCard1;?>,<?php echo $sCard2;?>,<?php echo $sCard3;?>,<?php echo $sCard4;?>,<?php echo $sCard5;?>];
			var oCardArea2 = [<?php echo $sCard6;?>,<?php echo $sCard7;?>,<?php echo $sCard8;?>,<?php echo $sCard9;?>,<?php echo $sCard10;?>];
			var oCardArea3 = [<?php echo $sCard11;?>,<?php echo $sCard12;?>,<?php echo $sCard13;?>,<?php echo $sCard14;?>,<?php echo $sCard15;?>];
			var oCardArea4 = [<?php echo $sCard16;?>,<?php echo $sCard17;?>,<?php echo $sCard18;?>,<?php echo $sCard19;?>,<?php echo $sCard20;?>];
			var oCardArea5 = [<?php echo $sCard21;?>,<?php echo $sCard22;?>,<?php echo $sCard23;?>,<?php echo $sCard24;?>,<?php echo $sCard25;?>];
			var aWinLoseArea = ['<?php echo $swl1;?>','<?php echo $swl2;?>','<?php echo $swl3;?>','<?php echo $swl4;?>'];
			var aChip = [[1,5,10,100],[10,50,100,1000],[100,500,1000,5000],[500,1000,2000,5000]];

			var aNiu = {};
			aNiu['area1'] = '<?php echo $iNNtype1;?>';
			aNiu['area2'] = '<?php echo $iNNtype2;?>';
			aNiu['area3'] = '<?php echo $iNNtype3;?>';
			aNiu['area4'] = '<?php echo $iNNtype4;?>';
			aNiu['area5'] = '<?php echo $iNNtype5;?>';			

			var aArrImg = new Array();
			aArrImg.push('../img/gid03/btn01.png');
			aArrImg.push('../img/gid03/btn02.png');
			aArrImg.push('../img/gid03/btn04_b.png');
			aArrImg.push('../img/gid03/btn05_b.png');
			aArrImg.push('../img/gid03/point_word.png');
			aArrImg.push('../img/gid03/number.png');
			aArrImg.push('../img/gid03/maxmin.png');

			var aChipImg = new Array();			
			$.each(aChip[<?php echo $nChipInx;?>],function(k,v)
			{
				aChipImg.push('../img/gid03/'+v+'.png');
			});

			var aNiuniuImg = new Array();
			$.each(aNiu,function(k,v)
			{
				aNiuniuImg.push('../img/gid03/'+v+'.png');
			});

			var aWinLoseImg = new Array();
			aWinLoseImg.push('../img/gid03/ww.png');
			aWinLoseImg.push('../img/gid03/ll.png');

			var aBetImg = new Array();
			aBetImg.push('../img/gid03/totnum.png');
			aBetImg.push('../img/gid03/betnum.png');

			var aCardImg = new Array();
			$.each(oCardArea1, function(k,v)
			{
				oAllCardImg[v] = true;
			});
			$.each(oCardArea2, function(k,v)
			{
				oAllCardImg[v] = true;
			});
			$.each(oCardArea3, function(k,v)
			{
				oAllCardImg[v] = true;
			});
			$.each(oCardArea4, function(k,v)
			{
				oAllCardImg[v] = true;
			});
			$.each(oCardArea5, function(k,v)
			{
				oAllCardImg[v] = true;
			});
			
			$.each(oAllCardImg, function(k,v)
			{
				aCardImg.push('../img/gid03/'+k+'.png');
			});

			var domCanvas = document.getElementById('canvas');
			var ctx = domCanvas.getContext('2d');
			var img_bg = new Image();
			img_bg.onload = function()
			{
				domCanvas.width = img_bg.width;
				domCanvas.height = img_bg.height;
				ctx.drawImage(img_bg,0,0);
				OrtherLoad(aArrImg);
				CardLoad(aCardImg);
				ChipLoad(aChipImg);
			};
			img_bg.src = '../img/gid03/bg<?php echo $nChipInx;?>.png';

			$('#canvas').width(100+'%');

			function OrtherLoad(aArrImg,inx)
			{
				inx = inx || 0;
			
				if(inx < aArrImg.length)
				{
					var img = new Image();
					img.onload = function()
					{
						switch(inx)
						{
							case 0:
								var x = 800;
								var y = 75;
								var w = img.width;
								var h = img.height;								
								ctx.drawImage(img,x,y);
								//規則
								break;
							case 1:
								var x = 845;
								var y = 75;
								ctx.drawImage(img,x,y);
								//音樂
								break;
							case 2:
								var x = 780;
								var y = 450;
								var w = img.width;
								var h = img.height;
								ctx.drawImage(img,0,0,w,h,x,y,(w - 5),(h - 5));
								break;
							case 3:
								var x = 780;
								var y = 493;
								var w = img.width;
								var h = img.height;
								ctx.drawImage(img,0,0,w,h,x,y,(w - 5),(h - 5));
								break;
							case 4:
								var x = 140;
								var y = 450;
								ctx.drawImage(img,x,y);
								//point_word
								break;
							case 5:
								var aPoint = sMemPoint.split("");

								$.each(aPoint, function(k,v)
								{
									DrawPoint(img,v,k);
								});
								break;
							case 6:
								var aMaxBet = sMaxBet.split("");
								var aMinBet = sMinBet.split("");

								$.each(aMaxBet, function(k,v)
								{
									DrawMaxMin(img,v,k);
								});

								$.each(aMinBet, function(k,v)
								{
									DrawMaxMin(img,v,k,1);
								});
								break;							
						}
						OrtherLoad(aArrImg,inx+1);
					}
					img.src = aArrImg[inx];
				}
			}

			function CardLoad(aArr,inx)
			{
				inx = inx || 0;
			
				if(inx < aArr.length)
				{
					var img = new Image();
					img.onload = function()
					{
						var src = img.src.split('/');
						src = src[src.length - 1].split('.');
						
						oCardImg[src[0]] = img;

						CardLoad(aArr,inx+1);
					}
					img.src = aArr[inx];
				}
				else
				{
					DrawCard(oCardArea1,370,17,39);
					DrawCard(oCardArea2,25,323,36);
					DrawCard(oCardArea3,262,323,36);
					DrawCard(oCardArea4,499,323,36);
					DrawCard(oCardArea5,737,323,36);
					NiuniuLoad(aNiuniuImg);
				}
			}

			function ChipLoad(aArr,inx)
			{
				inx = inx || 0;
			
				if(inx < aArr.length)
				{
					var img = new Image();
					img.onload = function()
					{
						var iW = iH = 115;
						var dW = dH = 97;
						var y = 445;
						oChipImg[inx] = img;

						switch(inx)
						{
							case 0:
								var x = 300;								
								ctx.drawImage(img,0,0,iW,iH,x,y,dW,dH);
								break;
							case 1:
								var x = 413;
								ctx.drawImage(img,0,0,iW,iH,x,y,dW,dH);
								break;
							case 2:
								var x = 526;
								ctx.drawImage(img,0,0,iW,iH,x,y,dW,dH);
								break;
							case 3:
								var x = 639;
								ctx.drawImage(img,0,0,iW,iH,x,y,dW,dH);
								break;
						}
						
						ChipLoad(aArr,inx+1);
					}
					img.src = aArr[inx];
				}
				else
				{
					//ctx.drawImage(oChipImg[0],0,0,117,117,750,255,50,50);
					for(var i = 0; i < 10; i++)
					{
						var imgN = RandNumber(3,0);
						if(aTotArea[0] > 0)
							DrawRand(oChipImg[imgN],165,35,255,190);
						
						imgN = RandNumber(3,0);
						if(aTotArea[1] > 0)
							DrawRand(oChipImg[imgN],405,275,255,190);
						
						imgN = RandNumber(3,0);
						if(aTotArea[2] > 0)
							DrawRand(oChipImg[imgN],640,515,255,190);
						
						imgN = RandNumber(3,0);
						if(aTotArea[2] > 0)
							DrawRand(oChipImg[imgN],880,750,255,190);
					}

					WinLoseLoad(aWinLoseImg);
				}
			}

			function NiuniuLoad(aArr,inx)
			{
				inx = inx || 0;
				if(inx < aArr.length)
				{
					var img = new Image();
					img.onload = function()
					{
						var src = img.src.split('/');
						src = src[src.length - 1].split('.');
						
						oNiuImg[src[0]] = img;
						
						NiuniuLoad(aArr,inx+1);
					}
					img.src = aArr[inx];
				}
				else
				{
					DrawNiu(aNiu['area1'],380,25);
					DrawNiu(aNiu['area2'],35,325);
					DrawNiu(aNiu['area3'],273,325);
					DrawNiu(aNiu['area4'],507,325);
					DrawNiu(aNiu['area5'],747,325);
				}
			}

			function WinLoseLoad(aArr,inx)
			{
				inx = inx || 0;
				if(inx < aArr.length)
				{
					var img = new Image();
					img.onload = function()
					{
						var src = img.src.split('/');
						src = src[src.length - 1].split('.');
						
						oWinLoseImg[src[0]] = img;

						WinLoseLoad(aArr,inx+1);
					}
					img.src = aArr[inx];
				}
				else
				{
					$.each(aWinLoseArea, function(k,v)
					{
						var w = 200;
						var h = 90;
						var y = 195;

						if(v == 'ww')
						{
							w = 150;
							h = 140;
							y = 180;
						}

						switch(k)
						{
							case 0:
								if(v == 'ww')
									DrawWinLose(oWinLoseImg[v],51,y,w,h);
								else
									DrawWinLose(oWinLoseImg[v],25,y,w,h);
								break;
							case 1:
								if(v == 'ww')
									DrawWinLose(oWinLoseImg[v],289,y,w,h);
								else
									DrawWinLose(oWinLoseImg[v],263,y,w,h);
								break;
							case 2:
								if(v == 'ww')
									DrawWinLose(oWinLoseImg[v],525,y,w,h);
								else
									DrawWinLose(oWinLoseImg[v],502,y,w,h);
								break;
							case 3:
								if(v == 'ww')
									DrawWinLose(oWinLoseImg[v],763,y,w,h);
								else
									DrawWinLose(oWinLoseImg[v],740,y,w,h);
								break;
						}
					});

					BetNumLoad(aBetImg);
				}
			}

			function BetNumLoad(aArr,inx)
			{
				inx = inx || 0;
				if(inx < aArr.length)
				{
					var img = new Image();
					img.onload = function()
					{
						switch(inx)
						{
							case 0:								
								$.each(aTotArea, function(k,v)
								{
									if(v > 0)
									{
										var aTotAreaOne = v.split("");
										var x;

										switch(k)
										{
											case 0:
												x = 39;
												break;
											case 1:
												x = 275;
												break;
											case 2:
												x = 513;
												break;
											case 3:
												x = 750;
												break;
										}
										DrawBet(img,aTotAreaOne,x,155);
									}
								});
								break;
							case 1:								
								$.each(aBetArea, function(k,v)
								{
									if(v > 0)
									{
										var aBetAreaOne = v.split("");
										var x;

										switch(k)
										{
											case 0:
												x = 39;
												break;
											case 1:
												x = 275;
												break;
											case 2:
												x = 513;
												break;
											case 3:
												x = 750;
												break;
										}
										DrawBet(img,aBetAreaOne,x,303);
									}
								});
								break;
						}
						
						BetNumLoad(aArr,inx+1);
					}
					img.src = aArr[inx];
				}
				else
				{
					$.each(aWinLoseAreaNum, function(k,v)
					{
						if(v != 0)
						{
							if(v > 0)
								v = '+'+v;						
							switch(k)
							{
								case 0:
									JudegWLNum(v, 113);
									break;
								case 1:
									JudegWLNum(v, 348);
									break;
								case 2:
									JudegWLNum(v, 590);
									break;
								case 3:
									JudegWLNum(v, 825);
									break;
							}
						}
					});
				}
			}

			function DrawWinLoseNum(str, x, y)
			{
				var col = '#FE0000';
				if(parseInt(str) > -1)
					col = '#24FF00';

				ctx.font = '34px Sans-serif';
				ctx.strokeStyle = 'black';
				ctx.lineWidth = 5;
				ctx.strokeText(str, x, y);
				ctx.fillStyle = col;
				ctx.fillText(str, x, y);
			}

			function JudegWLNum(str,x)
			{
				var y = 300;
				x = x - ((str.length - 2) * 10);
				DrawWinLoseNum(str, x, y);
			}

			function DrawPoint(oimg,ss,inx)
			{
				var x = 83;
				var y = 498;
				x += (12*inx);

				if(!isNaN(ss))
				{
					if(ss < 8)
					{
						xx = 18*ss;
						yy = 0;
					}
					else
					{
						xx = 18*(ss - 8);
						yy = 25;
					}
					ctx.drawImage(oimg,xx,yy,17,25,x,y,11,18);
				}
				else if(ss == '.')
				{
					xx = 18*3;
					yy = 25;
					ctx.drawImage(oimg,xx,yy,10,24,x,y,8,18);
				}
			}

			function DrawMaxMin(oimg,ss,inx,y = 0)
			{
				var x;
				if(y == 0)
					y = 60;
				else
					y = 95;

				if(!isNaN(ss))
				{
					if(ss < 6)
					{
						x = 235;
						x += (15.3*inx);
						xx = 19*ss;
						yy = 0;
						ctx.drawImage(oimg,xx,yy,17,25,x,y,15,24);
					}
					else
					{
						x = 235;
						x += (15*inx);
						xx = 19*(ss - 6);
						yy = 25;
						ctx.drawImage(oimg,xx,yy,17,25,x,y,16,24);
					}
				}
			}

			function DrawBet(oimg,aA,nSP,h)
			{
				var dOddNum = ((12-aA.length) % 2) != 0 ? 7.5 : 0;
				var nPosition = nSP + (parseInt((12-aA.length) / 2) * 15) + dOddNum;
				
				$.each(aA, function(k,v)
				{
					var nPos = nPosition + (k*15);
					var nNumPos;

					if(v < 8)
					{
						nNumPos = 14*v+1;
						ctx.drawImage(oimg,nNumPos,0,14,20,nPos,h,15,23);
					}
					else
					{
						nNumPos = 14*(v-8)+1;
						ctx.drawImage(oimg,nNumPos,20,14,20,nPos,h,15,23);
					}
				});
			}

			function DrawCard(oC,x,yPo,iv)
			{
				$.each(oC, function(k,v)
				{
					var xPo = k * iv + x;

					ctx.drawImage(oCardImg[v],0,0,77,108,xPo,yPo,65,95);
				});
			}

			function DrawRand(oImg,xMax,xMin,yMax,yMin)
			{
				var randX = RandNumber(xMax,xMin);
				var randY = RandNumber(yMax,yMin);
				ctx.drawImage(oImg,0,0,117,117,randX,randY,50,50);
			}

			function DrawNiu(cN,x,y)
			{
				var Nimg = oNiuImg[cN];
				if(Nimg.width == 181)
				{
					x+=13;
					y-=15;
				}

				ctx.drawImage(Nimg,0,0,Nimg.width,Nimg.height,x,y,(Nimg.width - 15),(Nimg.height - 15));
			}

			function DrawWinLose(oImg,x,y,w,h)
			{
				ctx.drawImage(oImg,0,0,oImg.width,oImg.height,x,y,w,h);
			}

			function RandNumber(nmax,nmin)
			{
				return Math.floor(Math.random()*(nmax - nmin + 1))+nmin;;
			}
		});
</script>
<div class="bgColor">
<p class="topRound">#<?php echo $s['gamecnts'];?></p>
<p class="topCount">时间：<?php echo $sWtime;?></p>
<div class="container">
<div class="imgDiv">
<table class="gameTab">
	<tbody>
	<tr>
		<td class="relImg">
			<div class="floatDiv">
				<canvas id="canvas"></canvas>
			</div>
		</td>
		<td class="padH">
			<div class="betB">
				<p>下注金额：</p>
				<p>第一区：<span><?php echo $iBet1;?></span></p>
				<p>第二区：<span><?php echo $iBet2;?></span></p>
				<p>第三区：<span><?php echo $iBet3;?></span></p>
				<p>第四区：<span><?php echo $iBet4;?></span></p>
				<p>总金额：<span><?php echo $iTBt;?></span></p>
			</div>
			<div class="wlB">
				<p>输赢：</p>
				<p>第一区：<span class="<?php echo $sWL1;?>"><?php echo ($iWL1 > 0 ? '+' : '').$iWL1;?></span></p>
				<p>第二区：<span class="<?php echo $sWL2;?>"><?php echo ($iWL2 > 0 ? '+' : '').$iWL2;?></span></p>
				<p>第三区：<span class="<?php echo $sWL3;?>"><?php echo ($iWL3 > 0 ? '+' : '').$iWL3;?></span></p>
				<p>第四区：<span class="<?php echo $sWL4;?>"><?php echo ($iWL4 > 0 ? '+' : '').$iWL4;?></span></p>
				<p>总输赢：<span class="<?php echo $sWLt;?>"><?php echo ($iWLt > 0 ? '+' : '').$iWLt;?></span></p>
			</div>
		</td>
	</tr>
	</tbody>
</table>
</div>

<div class="padS">
	<div class="betB">
		<p>下注金额：</p>
		<p>第一区：<span><?php echo $iBet1;?></span></p>
		<p>第二区：<span><?php echo $iBet2;?></span></p>
		<p>第三区：<span><?php echo $iBet3;?></span></p>
		<p>第四区：<span><?php echo $iBet4;?></span></p>
		<p>总金额：<span><?php echo $iTBt;?></span></p>
	</div>
	<div class="wlB">
		<p>输赢：</p>
		<p>第一区：<span class="<?php echo $sWL1;?>"><?php echo $iWL1;?></span></p>
		<p>第二区：<span class="<?php echo $sWL2;?>"><?php echo $iWL2;?></span></p>
		<p>第三区：<span class="<?php echo $sWL3;?>"><?php echo $iWL3;?></span></p>
		<p>第四区：<span class="<?php echo $sWL4;?>"><?php echo $iWL4;?></span></p>
		<p>总输赢：<span class="<?php echo $sWLt;?>"><?php echo $iWLt;?></span></p>
	</div>
</div>
</div>
</div>
<?php
		$iTotCount--;
	}
}
?>
</article>