$(document).ready(function(){

	$('.arrow').click(function(){
		$('.panel-right').toggleClass('open');
		$('.fon-all-white').toggleClass('open');
	})
	$('.fon-all-white').click(function(){
		$('.panel-right').toggleClass('open');
		$('.fon-all-white').toggleClass('open');
	})

	$('div.meny-r ul ul').hide();
	// $('#menu ul:first').show();
	$('div.meny-r ul ul').each(function() {
		if ($(this).hasClass("active"))
			$(this).show();
	});

	$('body').on('click','.div.meny-r ul li a',function () {
		$('div.meny-r ul ul li').removeClass('active');
		$(this).parent().addClass('active');

		var checkElement = $(this).next();
		if ((checkElement.is('ul')) && (checkElement.is(':visible'))) {
			$('div.meny-r ul ul:visible').slideUp('slow');
			return false;
		}
		if ((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
			$('div.meny-r ul ul:visible').slideUp('slow');
			checkElement.slideDown('slow');
			return false;
		}
	});

});
