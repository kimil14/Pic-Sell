jQuery(document).ready(function($){
	$('.button_pic.logout').click(function(){
		var data = {
			action : "picsell_logout"
		}

		console.log("logout...");
		
		$.post( picsell_ajax.ajaxurl, data, function( response ) {
			//document.location.href = document.location.href;
			//window.location.replace(document.location.href);
			//window.open(document.location.href);
			window.location.reload(true);
		}, "JSON" );
	});
});