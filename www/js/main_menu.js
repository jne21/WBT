$(document).ready(function(){
	$('ul.level1>li').addClass('level1-li');
	$('li.level1-li>ul').addClass('level2');
	$('ul.level2>li>ul').addClass('level3');
	$('ul.level3>li>ul').addClass('level4');
	$('ul.level1>li>a').addClass('level1-a');

	$('#menu li').hover(
		function () {
			$(this).find('ul:first').css({visibility: "visible", display: "none"}).fadeIn(200); // slideDown(100);
		},
		function(){
			$(this).find('ul:first').css({visibility: "hidden"});
		}
	);
/*
	$('#menu_home').click(
		function() {window.location.href='{site_root}';}
	);

	$('#menu_home').hover(
		function() {$(this).css({backgroundImage: 'url(/attachments/template/5/home_active.png)'});},
		function() {$(this).css({backgroundImage: 'url(/attachments/template/5/home.png)'});}
	);
*/
});
