$(document).ready(function(){
	$('input[name=select-all]').click(function(){
		var table = $(this).parent().parent().parent();
		if (!$(this).prop("checked"))
			table.find($('input[type=checkbox]')).not($(this)).prop("checked", false);
		else
			table.find($('input[type=checkbox]:not(:checked)')).not($(this)).prop("checked", "checked");
	});

	$('input[type=checkbox]').not($('input[name=select-all]')).click(function(){
		var table = $(this).parent().parent().parent();
		if (!$(this).prop("checked"))
			table.find($('input[name=select-all]')).prop("checked", false);

		if(table.find($('input[type=checkbox]:not(:checked)')).not($('input[name=select-all]')).length == 0)
			table.find($('input[name=select-all]')).prop("checked", "checked");
	});

});