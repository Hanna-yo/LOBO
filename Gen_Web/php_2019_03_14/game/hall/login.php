<?php require_once(dirname(dirname(dirname(__file__)))."/inc/config/config.php");?>
<?php define('ImgUrl','img/login/');?>
<?php
$nOk = 1;
if (isset($_COOKIE['m_user_c']) && ($_COOKIE['m_user_c'] <> ''))
{
	$nOk++;
}
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-with, initial-scale=1">
	<title>登入</title>
	<link rel="stylesheet" type="text/css" href="css/ResetCss.min.css">
	<link rel="stylesheet" type="text/css" href="css/hall.css?v=5">
	<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript">
		<?php
		if($nOk > 1)
		{
			echo 'location.href="index.php";';
		}
		?>
		function FormSubmit()
		{
			document.getElementById("login").submit();
		}
	</script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		const width = document.documentElement.clientWidth;
		const height = document.documentElement.clientHeight;
		const contentDOM = $('.login');
		console.log(width + ' ' + height);
		if (width < height)
		{			
			contentDOM.width(height);
			contentDOM.height(width);
			contentDOM.css('top',(height - width) / 2);
			contentDOM.css('left',0 - (height - width) / 2);
			contentDOM.css('transform',"rotate(90deg)");
		}		

		$(window).resize(function()
		{
			const width = document.documentElement.clientWidth;
			const height = document.documentElement.clientHeight;
			const contentDOM = $('.login');

			if (width > height)
			{ 
				// 横屏
				contentDOM.width(100 + '%');
				contentDOM.height(100 + '%');
				contentDOM.css('top',0);
				contentDOM.css('left',0);
				contentDOM.css('transform','none');
			}
			else if (width < height) 
			{
				// 竖屏
				const contentDOM = $('.login');
				contentDOM.width(height);
				contentDOM.height(width);
				contentDOM.css('top',(height - width) / 2);
				contentDOM.css('left',0 - (height - width) / 2);
				contentDOM.css('transform',"rotate(90deg)");
			}
		});
	});
	</script>
</head>
<body>
	<article class="login">
	<form id="login" action="index.php" method="POST">
		<div>
			<input type="hidden" name="a" value="<?php echo md5(adm_key.now_time);?>" />
			<input type="hidden" name="nt" value="<?php echo now_time;?>" />			
			<span class="positioning">
				<input type="text" name="acc" />
			</span>
			<img class="imgAcc" src="<?php echo ImgUrl;?>login_acc.png" />
		</div>		
		<img class="imgBtn" onclick="FormSubmit()" src="<?php echo ImgUrl;?>login_btn.png" />
	</form>
	</article>
</body>
</html>