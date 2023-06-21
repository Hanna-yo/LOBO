<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../inc_all_game/css/ResetCss.css"/>
	<style type="text/css">
		body
		{
			background-color: #000;
			font-family: Arial,"微軟正黑體","PMingLiU","Microsoft JhengHei","Heiti TC","WenQuanYi Zen Hei";
		}
		img
		{
			max-width: 100%;
			display: block;
			margin: auto;
		}
		div
		{
			background: rgb(7,7,8); /* Old browsers */
			background: -moz-linear-gradient(top, rgba(7,7,8,1) 0%, rgba(33,33,34,1) 100%); /* FF3.6-15 */
			background: -webkit-linear-gradient(top, rgba(7,7,8,1) 0%,rgba(33,33,34,1) 100%); /* Chrome10-25,Safari5.1-6 */
			background: linear-gradient(to bottom, rgba(7,7,8,1) 0%,rgba(33,33,34,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#070708', endColorstr='#212122',GradientType=0 ); /* IE6-9 */
			margin: auto;
			padding: 0 10px;
			position: fixed;
			width: 260px;
			height: 200px;
			border: 1px solid #ffba26;
			border-radius: 18px;
			border-color: #ffbe01 #f9e502;
		}
		p
		{
			color: #fff;
			text-align: center;
			font-size: 16px;
			padding-top: 30px;
			line-height: 1.5em;
		}
		.f1
		{
			padding-top: 20px;
			font-size: 20px;
			font-weight: bold;
		}
		a, a:hover
		{
			position: absolute;
			padding: 3px 16px;
			bottom: 15px;
			left: 105px;
			background-color: #ffbe01;
			border: 0;
			border-radius: 5px;
			text-decoration: unset;
			color: #000000;
			font-size: 19px;
			font-weight: bold;
		}
	</style>
	<script type="text/javascript" src="../inc_all_game/js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			var iWh = parseInt($(window).height());
			var iWw = parseInt($(window).width());
			var dH = parseInt($('div').height());
			var dW = parseInt($('div').width()) + 20;
			iWh = iWh * 0.5;
			iWw = iWw * 0.5;
			$('div').css('top', (iWh - (dH / 2)) + 'px');
			$('div').css('left', (iWw - (dW / 2)) + 'px');
		});

		$(window).resize(function(){
			iWh = parseInt($(window).height());
			iWw = parseInt($(window).width());
			dH = parseInt($('div').height());
			dW = parseInt($('div').width()) + 20;
			iWh = iWh * 0.5;
			iWw = iWw * 0.5;
			$('div').css('top', (iWh - (dH / 2)) + 'px');
			$('div').css('left', (iWw - (dW / 2)) + 'px');
		});
	</script>
</head>
<body>
	<div>
		<p class="f1">系统</p>
		<p><?php echo $sStr;?></p>
		<?php
		if($sBackUrl != '')
		{
			echo '<a href="'.$sBackUrl.'">確定</a>-->';
		}
		?>
	</div>
</body>
</html>