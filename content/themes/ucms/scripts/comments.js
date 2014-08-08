$('document').ready(function(){
	$("a.reply-to-comment").bind('click', function() {
		$(this).parent().append($('#add-comment'));
		$('#add-comment').find('input[name=parent]').val($(this).parent().parent().parent().attr('id').substr(8, $(this).parent().parent().parent().attr('id').length));
		$('#comments').find('#add-comment').not($(this).parent().find($('#add-comment'))).remove();
		return false;
	});
});