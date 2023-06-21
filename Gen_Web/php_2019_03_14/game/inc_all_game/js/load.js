$(document).ready(function()
{	
	var CheckAuth = new Event('ListenerCheckAuth');	//取得權限
	
	//回上頁
	if (window.history && window.history.pushState) 
	{
		$(window).on('popstate', function () 
		{
			window.history.pushState('forward', null, '#');
			window.history.forward(1);
			alert('请点选"回大厅"');
		});
	}	
	window.history.pushState('forward', null, '#'); //在IE中必须得有这两行
	window.history.forward(1);

	var checkToken = function()
	{
		$.ajax(
		{
			url: '../inc_all_game/ajax/ajax_check.php',
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			contentType: 'application/x-www-form-urlencoded',
			data: 
			{
				lhref: location.href
			},
			error: function(e)
			{
				$('#game_runtime').val(4);
			},
			success: function(e)
			{
				$('#game_runtime').val(e.res);
				console.log('ok');
				if(e.res == 1)
				{
					$('#g_id').val(e.acc);
					$('#g_pw').val(e.pw);
					$('#g_url').val(e.surl);
					$('#tokensno').val(e.tid);
				}
				else if(e.surl)
				{
					$('#g_url').val(e.surl);
				}
				document.dispatchEvent(CheckAuth); //回傳權限狀態
			}
		});
	};

	//監聽載入完成
	document.addEventListener('ListenerLoadingStart',function (e)
	{
		checkToken();
	});
});

function get_id()
{
	//取得帳號
	v = $("#g_id").val();
	return v;
}

function get_pw()
{
	//取得密碼
	v = $("#g_pw").val();
	return v;
}

function get_url()
{
	//取得導回網址
	v = $("#g_url").val();
	return v;
}

function get_server()
{
	//取得serverIP
	v = '103.35.207.162:6380';
	return v;
}

function get_ssl()
{
	//取得連線方式
	v = 'ws';
	return v;
}

function get_tokensno()
{
	//取得 CQ9 Token sno
	v = $("#tokensno").val();
	return v;
}

function get_open_note()
{
	//判斷是否顯示"全屏請使用safari",1=顯示
	v = $("#g_open_note").val();
	return v;
}

function get_game_runtime()
{
	//是否可進行遊戲,1=可進行
	v = $("#game_runtime").val();
	return v;
}