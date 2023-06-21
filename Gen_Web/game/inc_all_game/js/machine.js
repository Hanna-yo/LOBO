$(document).ready(function()
{
	var nUser = 4,resizeTime,getMachineTime;
	var userAgent = navigator.userAgent;
	var inxDOM = $('.inx_art');
	var machTabDOM = $('.mach_table');
	var btnLDOM = $('.btn-left');
	var btnRDOM = $('.btn-right');
	var ndRDOM = $('#nd');
	var get_ayn = $('#ayn').val();
	var get_wt = $('#wt').val();
	var get_cd = $('#cd').val();
	var get_gd = $('#gd').val();	
	var get_sl = $('#sl').val();
	var get_gy = $('#gy').val();
	var get_tot = parseInt($('#tot').val());
	var nStart = 1;

	get_machine(1);
	
	if(/iPhone/i.test(userAgent) || (/mobile/i.test(userAgent) && /Android/i.test(userAgent)))
		nUser = 1; //手機
	else if(/iPad/i.test(userAgent))
		nUser = 2; //iPad平板
	else if(/Android/i.test(userAgent))
		nUser = 3; //Android平板

	resize_device();

	//click btn
	btnLDOM.on('click',function()
	{
		click_get_machine(-1);
	});
	btnRDOM.on('click',function()
	{
		click_get_machine(1);
	});
	function click_get_machine(ss)
	{
		nStart++;
		var get_nd = parseInt(ndRDOM.val()) + ss;
		ndRDOM.val(get_nd);
		get_machine(1);
	}

	//get_machine
	function get_machine(ss = 0)
	{
		var get_nd = parseInt(ndRDOM.val());
		var asy = true; //非同步

		if(ss > 0)
			asy = false; //同步

		$.ajax(
		{
			url: '../inc_all_game/ajax/ajax_get_machine.php',
			type: 'POST',
			async: asy,
			cache: false,
			dataType: 'json',
			contentType: 'application/x-www-form-urlencoded',
			data: 
			{
				giid: get_ayn,
				wt: get_wt,
				cd: get_cd,
				gy: get_gy,
				sl: get_sl,
				gd: get_gd,
				nd: get_nd,
				tot: get_tot,
				ns: nStart
			},
			error: function(e)
			{
				
			},
			success: function(e)
			{
				machTabDOM.html(e.sres);
				machine_txt();

				//當前頁
				ndRDOM.val(e.page);

				//左按鈕
				if(parseInt(e.page) == 1)
					btnLDOM.css('display','none');
				else
					btnLDOM.css('display','block');

				//右按鈕
				var nM = get_tot % 15 > 0 ? 1 : 0;
				if(parseInt(e.page) == (parseInt(get_tot / 15) + nM))
					btnRDOM.css('display','none');
				else
				btnRDOM.css('display','block');

				//clearTimeout(getMachineTime);
				//getMachineTime = setTimeout(function(){ get_machine() }, 6000);
			}
		});
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

	function resize_device()
	{
		var nowOri = window.orientation;
		var client_width = document.documentElement.clientWidth;
		var client_height = document.documentElement.clientHeight;
		var iWh = client_height;
		var iWw = client_width;

		if(nUser == 1 || nUser == 2)
		{
			if(nowOri != 90 && nowOri != -90)
			{
				//非橫
				iWh = client_width;
				iWw = client_height;
				device_art(iWw,iWh,1);
			}
			else
				device_art(iWw,iWh);
		}
		else if(nUser == 3)
		{			
			if(nowOri != 0 && nowOri != 180)
			{
				//非橫			
				iWh = client_width;
				iWw = client_height;
				device_art(iWw,iWh,1);
			}
			else	
				device_art(iWw,iWh);
		}

		//SYS
		var dH = parseInt($('.inx_sys').height());
		var dW = parseInt($('.inx_sys').width()) + 20;
		$('.inx_sys').css({'top': ((iWh * 0.5) - (dH / 2)) + 'px','left': ((iWw * 0.5) - (dW / 2)) + 'px'});

		machine_txt();
	}

	function device_art(iWw,iWh,nn = 0)
	{
		if(nn > 0)
			inxDOM.css({"top":((iWw - iWh) / 2),"left":(0 - (iWw - iWh) / 2),"width":iWw,"height":iWh,"transform":"rotate(90deg)"}); //直
		else
			inxDOM.css({"top":0,"left":0,"width":"100%","height":"100%","transform":"none"}); //橫
	}

	function machine_txt()
	{
		//機台文字
		$('.mach_txt').each(function()
		{
			var dW = $(this).width();
			var gameW = $('.gamea').width();
			var game_left = (gameW / 2 - dW / 2);
			$(this).css('left',game_left + 'px');
		});
	}

	if(get_ayn != 'false')
	{

		setInterval(function(){ get_amount(get_ayn); }, 3000);
	}

	$(window).on('resize', function(e)
	{
		clearTimeout(resizeTime);
		resizeTime = setTimeout(function(){ resize_device() }, 100);
	});
});