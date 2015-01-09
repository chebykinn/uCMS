$(document).ready(function(){
	$('input[name=select-all]').click(function(){
		var table = $(this).parent().parent().parent();
		if (!$(this).prop("checked"))
			table.find($('input[type=checkbox]')).not($(this)).prop("checked", false);
		else
			table.find($('input[type=checkbox]:not(:checked)')).not($(this)).prop("checked", "checked");
	});

	// $('a[class=delete]').click(function(){
	// 	var sure = confirm();
	// 	if(!sure){
	// 		return false;
	// 	}
	// });

	$('input[type=checkbox]').not($('input[name=select-all]')).click(function(){
		var table = $(this).parent().parent().parent();
		if (!$(this).prop("checked"))
			table.find($('input[name=select-all]')).prop("checked", false);

		if(table.find($('input[type=checkbox]:not(:checked)')).not($('input[name=select-all]')).length == 0)
			table.find($('input[name=select-all]')).prop("checked", "checked");
	});

	$('input[name=disable]').click(function(){
		var table = $(this).parent().parent().parent();
		if (!$(this).prop("checked")){
			table.find($('input[type=text]')).prop("disabled", true);
			table.find($('input[type=text]')).val("");
		}else{
			table.find($('input[type=text]')).prop("disabled", false);
		}
	});
	
	$('textarea').keydown(function(evt){
		evt = evt || window.event;
		var keyCode = evt.keyCode || evt.which || 0;
		
		if(keyCode == 9){
			if(document.selection)
			{															
				document.selection.createRange().duplicate().text = "\t";
			}
			else if(this.setSelectionRange)
			{				
				var strFirst = this.value.substr(0, this.selectionStart);
				var strLast  = this.value.substr(this.selectionEnd, this.value.length);
	
				this.value = strFirst + "\t" + strLast;
			
				var cursor = strFirst.length + "\t".length;
	
				this.selectionStart = this.selectionEnd = cursor;
			}
			
			if(evt.preventDefault && evt.stopPropagation)
			{
				evt.preventDefault();
				evt.stopPropagation();
			}
			else {
				evt.returnValue = false;
				evt.cancelBubble = true;
			}
			return false;
		}
	});
	
	$('input[name=date_format]').click(function(){
		$('input[name=date_format_manual]').val($(this).val());
	});

	$('input[name=time_format]').click(function(){
		$('input[name=time_format_manual]').val($(this).val());
	});

	$(window).on('keydown', function(event) {
		if (event.ctrlKey || event.metaKey) {
			switch (String.fromCharCode(event.which).toLowerCase()) {
				case 's':
					event.preventDefault();
					$('body').find('form').trigger('submit');
					$('input[name=submit]').trigger('click');
				break;
			}
		}
	});

	$('#editor').contents().find('body').on('keydown', function(event) {
		if (event.ctrlKey || event.metaKey) {
			switch (String.fromCharCode(event.which).toLowerCase()) {
				case 's':
					event.preventDefault();
					$('body').find('form').trigger('submit');
					$('input[name=submit]').trigger('click');
				break;
			}
		}
	});
});