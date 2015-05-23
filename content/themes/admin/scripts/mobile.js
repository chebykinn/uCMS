$(document).ready(function(){
	$('a.show-sidebar').on('click', function(){
		if( $('#sidebar').css("display") == 'none' ){
			$('#sidebar').show();
			$(this).addClass('selected');
			
		}else{
			$('#sidebar').hide();
			$(this).removeClass('selected');
		}
		return false;
	});
});