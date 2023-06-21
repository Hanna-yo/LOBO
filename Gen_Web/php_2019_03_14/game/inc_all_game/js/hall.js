$(document).ready(function()
{		
	var userAgent = navigator.userAgent;
	var client_width,client_height;
	var inxDOM = $('.inx_art');
	var get_ayn = $('#ayn').val();

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

	resize_device();

	function resize_device()
	{
		var nowOri = window.orientation;
		var iWh,iWw;

		if(/iPhone/i.test(userAgent) || /iPad/i.test(userAgent) || (/mobile/i.test(userAgent) && /Android/i.test(userAgent)))
		{
			if(nowOri != 90 && nowOri != -90)
			{
				//非橫
				client_width = document.documentElement.clientWidth;
				client_height = document.documentElement.clientHeight;
				iWh = client_width;
				iWw = client_height;
				
				inxDOM.width(client_height);
				inxDOM.height(client_width);
				inxDOM.css('top',(client_height - client_width) / 2);
				inxDOM.css('left',0 - (client_height - client_width) / 2);
				inxDOM.css('transform',"rotate(90deg)");
			}
			else
			{
				client_width = document.documentElement.clientWidth;
				client_height = document.documentElement.clientHeight;
				iWh = client_height;
				iWw = client_width;
				
				inxDOM.width(100 + '%');
				inxDOM.height(100 + '%');
				inxDOM.css('top',0);
				inxDOM.css('left',0);
				inxDOM.css('transform',"none");
			}
		}
		else if(/Android/i.test(userAgent))
		{
			if(nowOri != 0 && nowOri != 180)
			{
				//非橫
				client_width = document.documentElement.clientWidth;
				client_height = document.documentElement.clientHeight;
				iWh = client_width;
				iWw = client_height;
									
				inxDOM.width(client_height);
				inxDOM.height(client_width);
				inxDOM.css('top',(client_height - client_width) / 2);
				inxDOM.css('left',0 - (client_height - client_width) / 2);
				inxDOM.css('transform',"rotate(90deg)");
			}
			else
			{
				client_width = document.documentElement.clientWidth;
				client_height = document.documentElement.clientHeight;
				iWh = client_height;
				iWw = client_width;
				
				inxDOM.width(100 + '%');
				inxDOM.height(100 + '%');
				inxDOM.css('top',0);
				inxDOM.css('left',0);
				inxDOM.css('transform',"none");
			}
		}
		
		if(!/mobile/i.test(userAgent))
		{
			iWh = document.documentElement.clientHeight;
			iWw = document.documentElement.clientWidth;
		}

		//word
		var gameW = $('.game').parent().width();
		var game_stopW = $('.game_stop').width();
		var game_stop_left = gameW / 2 - game_stopW / 2;
		$('.game_stop').each(function()
		{
			$(this).css('left',game_stop_left + 'px');
		});
		
		//SYS
		var dH = parseInt($('.inx_sys').height());
		var dW = parseInt($('.inx_sys').width()) + 20;
		$('.inx_sys').css('top', ((iWh * 0.5) - (dH / 2)) + 'px');
		$('.inx_sys').css('left', ((iWw * 0.5) - (dW / 2)) + 'px');
	}

	$(window).resize(function()
	{
		resize_device();
	});
});