$(document).ready(function()
{
	var nStage = 0,nUser = 3,nImgOn = 0,nImgTime = 0,ImgSetTime,resizeTime,resizeOk = 0,loadlogo_h = 0,loadlogo_w = 0,loadingWord_w = 0,loadingF_w = 0,loadingF_h = 0,nLoad_ok = 0,nLlogo_num = 0,nLOne_num = 0,nDot = 0;
	var userAgent = navigator.userAgent;
	ImgSetTime = setInterval(function(){ nImgTime++; },1000);
	if(/iPhone/i.test(userAgent) || /iPad/i.test(userAgent) || (/mobile/i.test(userAgent) && /Android/i.test(userAgent)))
	{
		$('.inx_sec').css('overflowY','auto');
		nUser = 1; //手機
	}
	else if(/Android/i.test(userAgent))
	{
		$('.inx_sec').css('overflowY','auto');
		nUser = 2; //Android平板
	}

	var loadYN = $('#loadTF').val();
	var inxDOM = $('.inx_art');
	var inxLogin = $('.loadingLogoDiv');
	var inxLoginOne = $('.loadingOneDiv');
	var inxFrame1 = $('.loadingOneOFrame');
	var inxFrame2 = $('.loadingOneRFrame');
	var inxFrame3 = $('.loadingOneNFrame');

	var get_ayn = $('#ayn').val();

	var nAll_len = $('img').length;
	var nLoad_len = $('.ling').length + 1;

	// 預載圖
	var aArrImg = new Array();
	aArrImg.push('../inc_all_game/img/hall/logo.png'); // logo SE
	aArrImg.push('../inc_all_game/img/hall/loading/loading.gif'); // loading S
	aArrImg.push('../inc_all_game/img/hall/loading/light.png');
	aArrImg.push('../inc_all_game/img/hall/loading/none_frame.png');
	aArrImg.push('../inc_all_game/img/hall/loading/outer_frame.png');
	aArrImg.push('../inc_all_game/img/hall/loading/run_frame.png');
	aArrImg.push('../inc_all_game/img/hall/loading/loading_word.png'); // loading E
	aArrImg.push('../inc_all_game/img/hall/inx_bg.jpg'); // index S
	aArrImg.push('../inc_all_game/img/hall/game1/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game2/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game3/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game4/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game101/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game102/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game103/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/game104/inx_game.gif');
	aArrImg.push('../inc_all_game/img/hall/back.png');
	aArrImg.push('../inc_all_game/img/hall/fcircle.png');
	aArrImg.push('../inc_all_game/img/hall/fimg1.png');
	aArrImg.push('../inc_all_game/img/hall/frame1.png');
	aArrImg.push('../inc_all_game/img/hall/frame2.png');
	aArrImg.push('../inc_all_game/img/hall/game_stop.png');
	aArrImg.push('../inc_all_game/img/hall/maintain.png'); // index E
	aArrImg.push('../inc_all_game/img/hall/machine/btn_left.png'); // machine S
	aArrImg.push('../inc_all_game/img/hall/machine/btn_right.png');
	aArrImg.push('../inc_all_game/img/hall/machine/desk1.png');
	aArrImg.push('../inc_all_game/img/hall/machine/desk2.png');
	aArrImg.push('../inc_all_game/img/hall/machine/desk3.png');
	aArrImg.push('../inc_all_game/img/hall/machine/desk4.png');
	aArrImg.push('../inc_all_game/img/hall/machine/dot_b.png');
	aArrImg.push('../inc_all_game/img/hall/machine/dot_w.png'); // machine E

	function preload(aArrImg,inx)
	{
		inx = inx || 0;

		if(inx < aArrImg.length)
		{
			var img = new Image();
			img.onload = function()
			{
				//console.log('inx:'+inx)
				if(inx > 6 && inx < aArrImg.length)
					if(nImgTime > 0)
						nImgOn++;

				if(inx == 0)
				{
					// Logo loading OK
					Logo_Loading_Ok();
				}
				else if(inx == 6)
				{
					if(nImgTime < 2)
					{
						setTimeout(function(){ LoadingOne_Loading_Ok() }, 1000);
						setTimeout(function(){ LoadingOne_Complete() }, 1200);
					}
					else
						LoadingOne_Loading_Ok();
					// Loading loading Ok

				}
				else if(inx == (aArrImg.length - 1))
				{
					// index loading Ok
					if(nImgTime > 2)
						setTimeout(function(){ Index_Loading_Ok() }, 500);
				}
				preload(aArrImg,inx+1);
			}
			img.src = aArrImg[inx];
		}
	}
	// preload(aArrImg);
	Index_Loading_Ok();

	function LoadingOne_Complete(n)
	{
		n = n || 0;

		if(n == (aArrImg.length - 7))
		{
			Index_Loading_Ok();
		}
		else
		{
			nImgOn++;
			setTimeout(function(){ LoadingOne_Complete(n+1) }, 100);
		}
	}

	function Logo_Loading_Ok()
	{
		$('.loadingLogo').css('display','block');
		$('.loginWord').css('display','block');
		loadlogo_h = $('.loadingLogo').height();
		loadlogo_w = $('.loadingLogo').width();
		loadlogo_h = loadlogo_h == 0 ? 152 : loadlogo_h;
		loadlogo_w = loadlogo_w == 0 ? 490 : loadlogo_w;
		resize_device();
		loading_setTime();
		Logo_Link_Wait();
	}

	//Loading Logo
	function Logo_Link_Wait(n)
	{
		n = n || 0;
		if(nStage == 0)
		{
			var sDot = 'Connecting... ';
			n++;

			for(var i = 0; i < n; i++)
			{
				sDot += '.';
			}

			if(n == 3)
				n = 0;

			$('.loginWord').text(sDot);
			setTimeout(function(){ Logo_Link_Wait(n) }, 500);
		}
	}

	function LoadingOne_Loading_Ok()
	{
		nStage++;
		$('.loadingLogoDiv').css('display','none');
		$('.loadingOneDiv').css('display','block');
		resize_device();
		loadingWord_w = $('.loadingOneWord').width();
		loadingF_w = $('.loadingOneOFrame').width();
		loadingF_h = $('.loadingOneOFrame').height();

		LoadingOne_Load(1);
	}

	function LoadingOne_Load(t,n = 0)
	{
		t = t || 0;
		var kk = false;
		if(nStage == 1)
		{
			if(n == 1)
				resize_device();

			var lightW = parseInt($('.loadingOneLight').width()) / 2;
			loadingF_w = $('.loadingOneOFrame').width();
			var len = loadingF_w / (aArrImg.length - 7);

			if(t < 7)
			{
				// 0
				len = len * 0;
				$('.loadingOneLight').hide();
				kk = true;
			}
			else if(t < 10 && nImgOn < 4)
			{
				len = len * 3;
				$('.loadingOneLight').show();
				kk = true;
			}
			else if(nImgOn > 3)
			{
				len = len * nImgOn;
				$('.loadingOneLight').show();
				kk = true;
			}

			if(kk == true)
			{
				if(len > loadingF_w * 0.35)
					$('.loadingOneRFrame').css('border-radius',0);

				$('.loadingOneLight').css('left',(len - lightW)+'px');
				$('.loadingOneRFrame').width(len+'px');
				if(nImgOn == (aArrImg.length - 7))
					$('.loadingOneLight').hide();
			}

			setTimeout(function(){ LoadingOne_Load(t+1) }, 100);
		}
	}

	function Index_Loading_Ok()
	{
		nStage++;
		nLoad_ok = 1;

		$('.loadingOneLight').css('display','none');
		$('.loadingOneDiv').css('display','none');
		$('.inx_art').css('display','block');
		$('.inx_sys').css('display','block');
		clearInterval(ImgSetTime);
		resize_device();
	}

	function loading_setTime()
	{
		if(nStage < 2 && resizeOk == 0)
		{
			resize_device();
			setTimeout(function(){ loading_setTime() }, 200);
		}
	}

	//game word
	function Img_Word()
	{
		var n = 2;

		if($('img').hasClass('game_maintain'))
		{
			var gameW = $('.gamea').width();
			var gameH = $('.game').height();
			var game_maintainW = $('.game_maintain').width();
			var game_maintainH = $('.game_maintain').height();
			var game_maintain_left = (gameW / 2 - game_maintainW / 2) - 2;
			var game_maintain_top = gameH / 2 - game_maintainH / 2;
			$('.game_maintain').each(function()
			{
				$(this).css('left',game_maintain_left + 'px');
				$(this).css('top',game_maintain_top + 'px');
			});

			n--;
		}
		else
			n--;

		if($('img').hasClass('game_stop'))
		{
			var gameW = $('.gamea').width();
			var gameH = $('.game').height();
			var game_stopW = $('.game_stop').width();
			var game_stopH = $('.game_stop').height();
			var game_stop_left = gameW / 2 - game_stopW / 2;
			var game_stop_top = (gameH / 2 - game_stopH / 2) + 5;
			$('.game_stop').each(function()
			{
				$(this).css('left',game_stop_left + 'px');
				$(this).css('top',game_stop_top + 'px');
			});

			n--;
		}
		else
			n--;

		if(n != 0)
			setTimeout(function(){ Img_Word() }, 200);
	}

	//get_amount
	function get_amount(giid)
	{
		$.ajax(
		{
			url: '../inc_all_game/ajax/ajax_get_amount.php',
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			contentType: 'application/x-www-form-urlencoded',
			data:
			{
				giid: giid
			},
			error: function(e)
			{

			},
			success: function(e)
			{
				if(e.res == 1)
				{
					$('.point').text(e.amount);
				}
			}
		});
	}

	if(get_ayn != 'false')
		setInterval(function(){ get_amount(get_ayn); }, 3000);

	function resize_device(ss = 0)
	{
		if(ss > 0 && nStage > 0)
			resizeOk = 1;
		var nowOri = window.orientation;
		var client_width = document.documentElement.clientWidth;
		var client_height = document.documentElement.clientHeight;
		var iWh = client_height;
		var iWw = client_width;

		if(nUser == 1)
		{
			if(nowOri != 90 && nowOri != -90)
			{
				//非橫
				iWh = client_width;
				iWw = client_height;
				device_system(iWh,iWw,1);

				if(nStage == 0)
					device_logo(iWw,iWh,1);
				else if(nStage == 1)
					device_loading(iWw,iWh,1);
				else
					device_art(iWw,iWh,1);
			}
			else
			{
				device_system(iWw,iWh);

				if(nStage == 0)
					device_logo(iWw,iWh);
				else if(nStage == 1)
					device_loading(iWw,iWh);
				else
					device_art(iWw,iWh);
			}
		}
		else if(nUser == 2)
		{
			if(nowOri != 0 && nowOri != 180)
			{
				//非橫
				iWh = client_width;
				iWw = client_height;
				device_system(iWh,iWw,1);

				if(nStage == 0)
					device_logo(iWw,iWh,1);
				else if(nStage == 1)
					device_loading(iWw,iWh,1);
				else
					device_art(iWw,iWh,1);
			}
			else
			{
				device_system(iWw,iWh);

				if(nStage == 0)
					device_logo(iWw,iWh);
				else if(nStage == 1)
					device_loading(iWw,iWh);
				else
					device_art(iWw,iWh);
			}
		}
		else
		{
			iWh = client_height;
			iWw = client_width;
			device_system(iWw,iWh);

			if(nStage == 0)
			{
				$('.loadingLogo').css('left',(iWw / 2 - loadlogo_w / 2));
				$('.loadingLogo').css('top',(iWh / 2 - loadlogo_h / 2));
			}
			else if(nStage == 1)
			{
				var loadWord_iWw = $('.loadingOneWord').width();
				$('.loadingOne').height(iWh);
				$('.loadingOneWord').css({'left':(iWw / 2 - loadWord_iWw / 2 + 10),'bottom':'85px'});
				$('.loadingOneOFrame').css('left',(iWw / 2 - loadingF_w / 2));
				$('.loadingOneRFrame').css('left',(iWw / 2 - loadingF_w / 2));
				$('.loadingOneLightDiv').css('left',(iWw / 2 - loadingF_w / 2));
				$('.loadingOneNFrame').css('left',(iWw / 2 - loadingF_w / 2));
			}
		}

		if(nStage == 2)
		{
			if($('img').hasClass('game_maintain') == true || $('img').hasClass('game_stop') == true)
				Img_Word();
		}
	}

	function device_system(iWw,iWh,nn = 0)
	{
		//SYS
		var dH = parseInt($('.inx_sys').height());
		var dW = parseInt($('.inx_sys').width()) + 20;
		$('.inx_sys').css({'top': ((iWh * 0.5) - (dH / 2)) + 'px','left': ((iWw * 0.5) - (dW / 2)) + 'px'});

		if(nn > 0)
			$('.inx_sys').css('transform','rotate(90deg)');
		else
			$('.inx_sys').css('transform','none');
	}

	function device_logo(iWw,iWh,nn = 0)
	{
		var load_iWw = loadlogo_w;
		var load_iHh = loadlogo_h;

		if(nn > 0)
			inxLogin.css({"top":((iWw - iWh) / 2),"left":(0 - (iWw - iWh) / 2),"width":iWw,"height":iWh,"transform":"rotate(90deg)"}); //直
		else
			inxLogin.css({"top":0,"left":0,"width":"100%","height":"100%","transform":"none"}); //橫

		if(load_iWw >= (iWw - 10))
			load_iWw = load_iWw - (load_iWw - iWw + 10);

		$('.loadingLogo').css({"left":(iWw / 2 - load_iWw / 2),"top":(iWh / 2 - load_iHh / 2),"width":load_iWw});
	}

	function device_loading(iWw,iWh,nn = 0)
	{
		var loadWord_iWw = loadingWord_w;
		var loadBar_iWw = loadingF_w;
		var loadBar_iHh = loadingF_h;

		if(nn > 0)
			inxLoginOne.css({"top":((iWw - iWh) / 2),"left":(0 - (iWw - iWh) / 2),"width":(iWw+1),"height":iWh,"transform":"rotate(90deg)"}); //直
		else
			inxLoginOne.css({"top":0,"left":0,"width":"100%","height":"100%","transform":"none"}); //橫

		$('.loadingOne').height(iWh);
		loadWord_iWw = 170;
		$('.loadingOneWord').css({'left':(iWw / 2 - loadWord_iWw / 2 + 10),'width':loadWord_iWw});
		loadBar_iWw = iWw * 0.7;
		loadBar_iHh = 20;
		if(nLoad_ok == 1)
			$('.loadingOneRFrame').width(loadBar_iWw);
		$('.loadingOneOFrame').css({'left':(iWw / 2 - loadBar_iWw / 2)+'px','width':loadBar_iWw+'px','height':loadBar_iHh+'px'});
		$('.loadingOneRFrame').css({'left':(iWw / 2 - loadBar_iWw / 2 + 1)+'px','height':loadBar_iHh+'px'});
		$('.loadingOneLightDiv').css({'left':(iWw / 2 - loadBar_iWw / 2 + 1)+'px','height':loadBar_iHh+'px'});
		$('.loadingOneNFrame').css({'left':(iWw / 2 - loadBar_iWw / 2)+'px','width':loadBar_iWw+'px','height':loadBar_iHh+'px'});
	}

	function device_art(iWw,iWh,nn = 0)
	{
		if(nn > 0)
		{
			inxDOM.css({"top":((iWw - iWh) / 2),"left":(0 - (iWw - iWh) / 2),"width":(iWw+1),"height":iWh,"transform":"rotate(90deg)"}); //直
			var padd = (iWw - parseInt($('.inx_table').width())) / 2;
			padd = padd == 0 ? 100 : padd;
			$('.inx_sec').css('padding','0 '+padd+'px');
			let inx_secH = iWh - parseInt($('header').height());
			$('.inx_sec').height(inx_secH);
		}
		else
		{
			inxDOM.css({"top":0,"left":0,"width":"100%","height":"100%","transform":"none"}); //橫
			let inx_secH = iWh - parseInt($('header').height());
			$('.inx_sec').height(inx_secH);
		}
	}

	$(window).on('resize', function(e)
	{
		clearTimeout(resizeTime);
		resizeTime = setTimeout(function(){ resize_device(1) }, 100);
	});
});