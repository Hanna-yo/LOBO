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
var nIsIosFullScreen = 1;
	
$(document).ready(function()
{
	var userAgent = navigator.userAgent;
	var nAgent = 0;
	var nIsSafari = 0;
	var DomTimeIos;
	var dIosVer = GetIosVersion();
	
	//建立事件
	var TouchEve = new Event('ListenerFullscreenEve');
	var TouchStop = new Event('ListenerTouchStop');
	var TouchStart = new Event('ListenerTouchStart');
	document.dispatchEvent(TouchStop); //禁止點擊	
	
	if(/iPhone/i.test(userAgent))
	{
		nAgent = 1; // iPhone
		if(dIosVer > 12)
		{
			//IOS版本可正常切換全屏
			if(/(?:Version\/)([\w\._]+)/i.test(userAgent))
			{
				nIsSafari = 1;
				nIsIosFullScreen = 0;
				chkIosFullScreen();
			}
		}
		start_device_iPhone();		
	}
	else if(/iPad/i.test(userAgent))
	{
		nAgent = 2; // iPad
		start_device_iPad();
		nIsIosFullScreen = 1;
	}
	else if(/mobile/i.test(userAgent) && /Android/i.test(userAgent))
	{
		nAgent = 3; // Android手機
		start_device_Android();
		nIsIosFullScreen = 1;
	}
	else if(/Android/i.test(userAgent))
	{
		nAgent = 4; // Android平板
		nIsIosFullScreen = 1;
	}
	
	//旋轉
	window.addEventListener('orientationchange',function()
	{
		switch(nAgent)
		{
			case 1:
				start_device_iPhone();
				break;
			case 2:
				start_device_iPad();
				break;
			case 3:
				start_device_Android();
				break;
			case 4:
				start_device_AndroidTablet();
				break;
			default:
				start_device();
				break;
		}
	},false);
	
	function chkIosFullScreen()
	{		
		clearInterval(DomTimeIos);			
		DomTimeIos = setInterval(function()
		{
			var nowOri = window.orientation;
			if(nowOri == 90 || nowOri == -90)
			{
				if(window.innerHeight == nowWidth)
					nIsIosFullScreen = 1;
				else
					nIsIosFullScreen = 0;
			}
			else
				nIsIosFullScreen = 3;
		}, 500);
	}	

	function start_device_iPhone()
	{
		var nowOri = window.orientation;
		console.log("旋轉角度:"+nowOri);
		
		if(nowOri == 90 || nowOri == -90)
		{
			console.log("橫向");
			//橫向
			//是否為safari
			if(nIsSafari == 1)
			{
				//safari
				//是否載入完成
				console.log('Loading'+Loading);
				if(Loading == 0)
				{
					$('#Blackbg').css('display','none'); //未完成
					$('.TurnImg').css('display','none');
				}
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
				
				//$('#Blackbg').css('display','none');
				console.log("iOS,Safari");
			}
			else
			{
				//其他瀏覽器
				$('#Blackbg').css('display','none');
				$('.fullscreenImg').css('display','none');
				$('.TurnImg').css('display','none');
				document.dispatchEvent(TouchStart); //開啟點擊
				console.log("iOS,Other");
			}
		}
		else
		{
			//直向
			$('.TurnImg').css('display','block');
			$('.fullscreenImg').css('display','none');
			$('#Blackbg').css('display','none');
			document.dispatchEvent(TouchStart); //開啟點擊
			console.log("直向");
		}
			
		console.log("iPhone");
	}
	
	function start_device_iPad()
	{
		var nowOri = window.orientation;
		console.log("旋轉角度:"+nowOri);
		
		//iPad
		$('.fullscreenImg').css('display','none');
		$('#Blackbg').css('display','none');
		document.dispatchEvent(TouchStart); //開啟點擊
		
		//轉向控制
		if(nowOri == 90 || nowOri == -90)
			$('.TurnImg').css('display','none');
		else
			//直向			
			$('.TurnImg').css('display','block');
		console.log('iPad');
	}
	
	function start_device_Android()
	{
		var nowOri = window.orientation;
		document.dispatchEvent(TouchStart); //開啟點擊
		console.log("旋轉角度:"+nowOri);
		
		console.log('是否全屏AndroidPhone：'+NowIsFullScreen());
		//AndroidPhone				
		//判斷轉向
		if(nowOri == 90 || nowOri == -90)
		{
			//橫向
			//判斷全屏
			nowWidth = window.screen.height;
			var hh = $('#c2canvas').height();
			
			if(NowIsFullScreen() == true || nowWidth == hh)
			{
				//全屏
				$('#Blackbg').css('display','none');
				$('.TurnImg').css('display','none');
			}
			else
			{
				//判斷載入完成
				if(Loading == 0)
				{					
					$('#Blackbg').css('display','none'); //未完成
					$('.TurnImg').css('display','none');
				}
				else
				{
					$('.TurnImg').css('display','none');
					$('.fullscreenImg').css('display','block');
					$('#Blackbg').css('display','block');
					document.dispatchEvent(TouchStop); //禁止點擊
				}				
			}
		}
		else
		{
			//直向
			$('.fullscreenImg').css('display','none');
			$('#Blackbg').css('display','none');
			
			if(Loading == 1)		
				$('.TurnImg').css('display','block');
			else
				$('.TurnImg').css('display','none');
		}
		
		console.log("AndroidPhone");
	}
	
	function start_device_AndroidTablet()
	{
		//Android平板
		var nowOri = window.orientation;
		document.dispatchEvent(TouchStart); //開啟點擊
		$('#Blackbg').css('display','none');
		$('.fullscreenImg').css('display','none');
		console.log("旋轉角度:"+nowOri);
		
		//判斷轉向
		if(nowOri == 0 || nowOri == 180)
		{
			//橫向
			$('.TurnImg').css('display','none');
			console.log('橫向');
		}
		else
		{
			//直向
			$('.TurnImg').css('display','block');			
			console.log('直向');
		}
		console.log("AndroidTablet");
	}
	
	function start_device()
	{
		//非移動裝置關閉判斷
		$('.fullscreenImg').css('display','none');
		$('#Blackbg').css('display','none');			
		$('.TurnImg').css('display','none');
		document.dispatchEvent(TouchStart); //開啟點擊
	}
	
	//畫面重畫
	$('#c2canvas').resize(function()
	{
		console.log('畫面重畫');
		switch(nAgent)
		{
			case 1:
				start_device_iPhone();
				break;
			case 2:
				start_device_iPad();
				break;
			case 3:
				start_device_Android();
				break;
			case 4:
				start_device_AndroidTablet();
				break;
			default:
				start_device();
				break;
		}
	});

		
	//裝置判斷
	if(nAgent == 3)
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
			nowWidth = window.screen.height;
			var hh = $('#c2canvas').height();
			if((nowOri == 90 || nowOri == -90) && distance > 20 && (NowIsFullScreen() == false || nowWidth != hh))
			{
				console.log("全屏distance："+distance);
				document.dispatchEvent(TouchEve); //開啟全屏					
				document.dispatchEvent(TouchStart); //開啟點擊
				setTimeout(function()
				{
					if((NowIsFullScreen() == true || nowWidth == hh))
						$('#Blackbg').css('display','none');
				}, 100);
			}
			startY = endY = 0;
		});
		console.log("ISandroid");
	}
	else if(nAgent == 1)
	{
		//是否顯示"全屏請使用safari"
		if(nIsSafari != 1 && dIosVer > 12)
			$("#g_open_note").val('1'); //非safari,顯示"全屏請使用safari"
	}
	
	//監聽載入完成
	document.addEventListener('ListenerLoadingStart',function (e)
	{
		Loading = 1;
		console.log('note,Loading'+Loading);
		var nowOri = window.orientation;
		if(nAgent == 3)
		{
			start_device_Android();
		}
		else if(nAgent == 1)
		{
			start_device_iPhone();
		}
		console.log("Loading OK!");
	});
});

function GetIosVersion()
{
	//得到版本号
	var ua = navigator.userAgent.toLowerCase();
	var v = null;
	if (ua.indexOf("like mac os x") > 0) 
	{
		var reg = /os [\d._]+/gi;
		var v_info = ua.match(reg);
		v = (v_info + "").replace(/[^0-9|_.]/ig, "").replace(/_/ig, ".");
		if(v.indexOf(".") != -1)
		{
			var vv = v.split('.');
			if(vv.length > 2)
				v = vv[0]+'.'+vv[1];
		}
	}

	return v;
}

function NowIsFullScreen()
{
	//現在是否為全屏
	return document.isFullScreen || document.mozIsFullScreen || document.webkitIsFullScreen || document.mozFullScreen || document.IsfullScreen || false;
}