$(document).ready(function(){			
	$('.tabW1').each(function(){
		var iTotW = 0;
		$(this).children('table').each(function(){
			iTotW += parseInt($(this).width());
		});
		$(this).css('width', iTotW + 'px');
	});
	$('.tabW2').each(function(){
		var iTotW = 0;
		$(this).children('table').each(function(){
			iTotW += parseInt($(this).width());
		});
		$(this).css('width', iTotW + 'px');
	});
});