$(document).ready(function(){
	$('input[name=select-all]').click(function(){
		if (!$(this).prop("checked"))
			$('input[type=checkbox]').not($(this)).prop("checked", false);
		else
			$('input[type=checkbox]:not(:checked)').not($(this)).prop("checked", "checked");
	});

	$('input[type=checkbox]').not($('input[name=select-all]')).click(function(){
		if (!$(this).prop("checked"))
			$('input[name=select-all]').prop("checked", false);
	});
});