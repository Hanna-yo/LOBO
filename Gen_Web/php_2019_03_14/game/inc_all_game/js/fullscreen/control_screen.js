//單一DOM resize,Ex:$("#c2canvas").resize(function(){});
(function ($, h, c) 
{
        var a = $([]), e = $.resize = $.extend($.resize, {}), i, k = "setTimeout", j = "resize", d = j + "-special-event", b = "delay", f = "throttleWindow";
        e[b] = 250;
        e[f] = true;
        $.event.special[j] = {
            setup: function () {
                if (!e[f] && this[k]) {
                    return false
                }
                var l = $(this);
                a = a.add(l);
                $.data(this, d, {w: l.width(), h: l.height()});
                if (a.length === 1) {
                    g()
                }
            }, teardown: function () {
                if (!e[f] && this[k]) {
                    return false
                }
                var l = $(this);
                a = a.not(l);
                l.removeData(d);
                if (!a.length) {
                    clearTimeout(i)
                }
            }, add: function (l) {
                if (!e[f] && this[k]) {
                    return false
                }
                var n;
 
                function m(s, o, p) {
                    var q = $(this), r = $.data(this, d);
                    r.w = o !== c ? o : q.width();
                    r.h = p !== c ? p : q.height();
                    n.apply(this, arguments)
                }
 
                if ($.isFunction(l)) {
                    n = l;
                    return m
                } else {
                    n = l.handler;
                    l.handler = m
                }
            }
        };
        function g() {
            i = h[k](function () {
                a.each(function () {
                    var n = $(this), m = n.width(), l = n.height(), o = $.data(this, d);
                    if (m !== o.w || l !== o.h) {
                        n.trigger(j, [o.w = m, o.h = l])
                    }
                });
                g()
            }, e[b])
        }
})(jQuery, this);

var nowWidth = window.screen.width;
var startY,endY;
var Loading = 0;//未載入
	
$(document).ready(function()
{
	function start_device()
	{
		//控制
		var nowOri = window.orientation;
		console.log("旋轉角度:"+nowOri);

		if(/iPhone/i.test(userAgent))
		{
			//iPhone
			//判斷轉向
			if(nowOri == 90 || nowOri == -90)
			{
				console.log("橫向");
				//橫向
				//是否為safari
				if(/(?:Version\/)([\w\._]+)/i.test(userAgent))
				{
					//safari
					//是否載入完成
					console.log('Loading'+Loading);
					if(Loading == 0)
						$('#Blackbg').css('display','none'); //未完成
					else
					{
						//完成
						$('#Blackbg').css('display','block');
						$('.TurnImg').css('display','none');
						$('.fullscreenImg').css('display','block');

						var hh = $('#c2canvas').height();
						console.log('hh：'+hh);
						console.log('nowWidth：'+nowWidth);
						//判斷是否全屏
						if(hh < nowWidth)
						{
							//未全屏
							console.log("If1");
							$('.fullscreenImg').css('display','block');
							$('#Blackbg').css('display','block');
							document.dispatchEvent(TouchStop); //禁止點擊
						}
						else
						{
							//全屏
							console.log("If2");
							$('.fullscreenImg').css('display','none');
							$('#Blackbg').css('display','none');
							document.dispatchEvent(TouchStart); //開啟點擊
						}
					}
					console.log("iOS,Safari");
				}
				else
				{
					//其他瀏覽器
					$('#Blackbg').css('display','none');
					console.log("iOS,Other");
				}
			}
			else
			{
				//直向
				$('.TurnImg').css('display','block');
				$('.fullscreenImg').css('display','none');
				$('#Blackbg').css('display','block');
				console.log("直向");
			}
			
			console.log("iPhone");
		}
		else if(/iPad/i.test(userAgent))
		{
			//iPad
			$('.fullscreenImg').css('display','none');
			
			//轉向控制
			if(nowOri == 90 || nowOri == -90)
			{
				$('#Blackbg').css('display','none');
				$('.TurnImg').css('display','none');
				document.dispatchEvent(TouchStart); //開啟點擊
			}
			else
			{
				//直向
				$('#Blackbg').css('display','block');
				$('.TurnImg').css('display','block');
				document.dispatchEvent(TouchStop); //禁止點擊
			}
			console.log('iPad');
		}
		else if(/mobile/i.test(userAgent) && /Android/i.test(userAgent))
		{
			console.log('是否全屏AndroidPhone：'+NowIsFullScreen());
			//AndroidPhone				
			//判斷轉向
			if(nowOri == 90 || nowOri == -90)
			{
				//橫向
				//判斷全屏
				if(NowIsFullScreen() == true)
				{
					//全屏
					$('#Blackbg').css('display','none');
					document.dispatchEvent(TouchStart); //開啟點擊
				}
				else
				{
					//判斷載入完成
					if(Loading == 0)							
						$('#Blackbg').css('display','none'); //未完成
					else
					{
						$('.TurnImg').css('display','none');
						$('.fullscreenImg').css('display','block');
						$('#Blackbg').css('display','block');
					}
					document.dispatchEvent(TouchStop); //禁止點擊
				}
			}
			else
			{
				//直向
				$('.TurnImg').css('display','block');
				$('.fullscreenImg').css('display','none');
				$('#Blackbg').css('display','block');
				document.dispatchEvent(TouchStop); //禁止點擊
			}
			console.log("AndroidPhone");
		}
		else if(/Android/i.test(userAgent))
		{
			//Android平板
			//判斷轉向
			if(nowOri == 0 || nowOri == 180)
			{
				//橫向
				$('#Blackbg').css('display','none');
				document.dispatchEvent(TouchStart); //開啟點擊
				console.log('橫向');
			}
			else
			{
				//直向
				$('#Blackbg').css('display','block');
				$('.TurnImg').css('display','block');
				$('.fullscreenImg').css('display','none');
				document.dispatchEvent(TouchStop); //禁止點擊
				console.log('直向');
			}
			console.log("AndroidTablet");
		}
	}
	
	//建立事件
	var TouchEve = new Event('ListenerFullscreenEve');
	var TouchStop = new Event('ListenerTouchStop');
	var TouchStart = new Event('ListenerTouchStart');
	document.dispatchEvent(TouchStop); //禁止點擊
	
	var userAgent = navigator.userAgent;
	console.log(userAgent);
	
	//非移動裝置關閉判斷
	if(!/mobile/i.test(userAgent))
	{
		$('.fullscreenImg').css('display','none');
		$('#Blackbg').css('display','none');			
		$('.TurnImg').css('display','none');
		document.dispatchEvent(TouchStart); //開啟點擊
	}

	start_device();

	//畫面重畫
	$("#c2canvas").resize(function()
	{
		console.log('畫面重畫');
		start_device();
	});
	
	//裝置判斷
	if(/Android/i.test(userAgent) && /mobile/i.test(userAgent))
	{
		//全螢幕控制
		var bgObj = document.getElementById("Blackbg");			
		bgObj.addEventListener('touchstart',function (e)
		{
			var touch = e.targetTouches[0];
			startY = touch.pageY;
		});
		bgObj.addEventListener('touchmove',function (e)
		{
			var touch = e.targetTouches[0];
			endY = touch.pageY;
		});
		bgObj.addEventListener('touchend',function ()
		{
			var distance = endY != 0 ? startY - endY : 0;
			var nowOri = window.orientation;
			console.log("distance："+distance);
			console.log("NowIsFullScreen："+NowIsFullScreen());
			if((nowOri == 90 || nowOri == -90) && distance > 80 && NowIsFullScreen() == false)
			{
				console.log("全屏distance："+distance);
				document.dispatchEvent(TouchEve); //開啟全屏					
				document.dispatchEvent(TouchStart); //開啟點擊
				setTimeout(function()
				{
					if(NowIsFullScreen() == true)
						$('#Blackbg').css('display','none');
				}, 100);
			}
			startY = endY = 0;
		});
		console.log("ISandroid");
	}
	else if(/iPhone/i.test(userAgent))
	{
		//是否顯示"全屏請使用safari"
		if(!(/(?:Version\/)([\w\._]+)/i.test(userAgent)))
			$("#g_open_note").val('1'); //非safari,顯示"全屏請使用safari"
	}
	
	//監聽載入完成
	document.addEventListener('ListenerCheckAuth',function (e)
	{
		Loading = 1;
		console.log('note,Loading'+Loading);
		var nowOri = window.orientation;
		if(/mobile/i.test(userAgent) && /Android/i.test(userAgent))
		{
			if(nowOri == 90 || nowOri == -90)
			{
				//橫向
				$('.TurnImg').css('display','none');
				$('.fullscreenImg').css('display','block');
				$('#Blackbg').css('display','block');
			}
		}
		else if(/iPhone/i.test(userAgent))
		{
			start_device();
		}
		console.log("Loading OK!");
	});
});

function NowIsFullScreen()
{
	//現在是否為全屏
	return document.isFullScreen || document.mozIsFullScreen || document.webkitIsFullScreen || document.mozFullScreen || document.IsfullScreen || false;
}