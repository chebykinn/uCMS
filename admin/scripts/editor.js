var image = "";
var link = "";
var leave = false;
bind_events();


$('div#shadow').click(function(){
	$('div#editor-image').hide();
	$("div#make-link").hide();
	$(this).hide();
});

$('select.edits').on('change', function(){
	setFormat($(this).attr('id'), $(this).val());
	$(this).find('option:first').prop('selected', true); 
});

function bind_events(){
	$('img#close-splash').click(function(){
		$('div#editor-image').hide();
		$("div#make-link").hide();
		$('div#shadow').hide();
	});

	$('a.ucms-add-button.image').click(function(){
		image = $(this).attr("href");
		$('div#editor-image').hide();
		$('div#shadow').hide();
		setFormat('InsertImage');
		return false;
	});
	
	$('a.change_dir').click(function(){
		var dir_url = $(this).attr("href");
		$('#editor-image').load('#editor-image #images', {"dir": dir_url}, bind_events);
		return false;
	});
	
	$('a.ucms-add-button.link').click(function(){
		link = $("input[name=url]").val();
		$("div#make-link").hide();
		$('div#shadow').hide();
		setFormat('CreateLink');
		return false;
	});
	
	$('a.update-images').click(function(){
		$('#editor-image').load('#editor-image #images', bind_events);
		return false;
	});

	$('a.go-back').click(function(){
		var dir_url = $(this).attr("href");
		$('#editor-image').load('#editor-image #images', {"dir": dir_url}, bind_events);
		return false;
	});
}


function chooseLink(){
	$("#make-link").show();
	$("#shadow").show();
}

function chooseImage(){
	$("#editor-image").show();
	$("#shadow").show();
}

(function($) {
   $.fn.wrapSelected = function(open, close) {
	 return this.each(function() {

	   var textarea = $(this);

	   var value = textarea.val();
	   var start = textarea[0].selectionStart;
	   var end = textarea[0].selectionEnd;
	   textarea.val(
		 value.substr(0, start) + 
		 open + value.substring(start, end) + close + 
		 value.substring(end, value.length)
	   );

	 });
   };
})(jQuery);

var visualEditor = true;
var isGecko = navigator.userAgent.toLowerCase().indexOf("gecko") != -1 ? true : false;
var iframe = (isGecko) ? document.getElementById("editor") : frames["editor"];
var iWin = (isGecko) ? iframe.contentWindow : iframe.window;
var iDoc = (isGecko) ? iframe.contentDocument : iframe.document;
iHTML = "<html><head><meta charset=\"utf-8\"></head><body style='background: #fff;'>"+($("#body").val() != "" ? $("#body").val() : "")+"</body></html>";
iDoc.open();
iDoc.write(iHTML);
iDoc.close();
$('#editor').contents().prop('designMode','on');

$(function(){
	$('form').submit(function(){
		leave = true;
	});
});

$( window ).on("beforeunload", function() {
	if(!leave){
		return " ";
	}
}); 


function setFormat(type, param){
	switch(type){
		case "CreateLink":
			param = link;
		break;

		case "InsertImage":
			param = unescape(image);
		break;
	}

	if(visualEditor){
		iWin.focus();
		iWin.document.execCommand("styleWithCSS", null, true);
		iWin.document.execCommand("enableObjectResizing", null, true);
		iWin.document.execCommand(type, null, param);
	}else{
		switch(type){
			case "bold":
				$("#html-code").wrapSelected("<strong>", "</strong>");
			break;

			case "italic":
				$("#html-code").wrapSelected("<em>", "</em>");
			break;

			case "underline":
				$("#html-code").wrapSelected("<u>", "</u>");
			break;

			case "CreateLink":
				$("#html-code").wrapSelected("<a href=\""+param+"\">", "</a>");
			break;

			case "InsertImage":
				$("#html-code").wrapSelected("<img src=\""+param+"\" alt=\"\">", "");
			break;

			case "JustifyLeft":
				$("#html-code").wrapSelected("<p style=\"text-align: left;\">", "</p>");
			break;

			case "JustifyCenter":
				$("#html-code").wrapSelected("<p style=\"text-align: center;\">", "</p>");
			break;

			case "JustifyRight":
				$("#html-code").wrapSelected("<p style=\"text-align: right;\">", "</p>");
			break;

			case "RemoveFormat":
				$("#html-code").unwrap();
			break;

			case "insertText":
				$("#html-code").val($("#html-code").val()+param);
			break;

			case "formatBlock":
				$("#html-code").wrapSelected("<"+param+">", "</"+param+">");
			break;

			case "fontsize":
				$("#html-code").wrapSelected("<font size=\""+param+"\">", "</font>");
			break;

			case "forecolor":
				$("#html-code").wrapSelected("<span style=\"color: #"+param+";\">", "</span>");
			break;

			case "backcolor":
				$("#html-code").wrapSelected("<span style=\"background-color: #"+param+";\">", "</span>");
			break;
		}
	}
}

function ShowHTML(){
	visualEditor = !visualEditor;
	$("#html-code").val(iDoc.body.innerHTML);
	$("#htmlb").toggle();
	$("#wysiwygb").toggle();
	
	$("#html-code").toggle(); 
	$("iframe").toggle();
}

function ShowVisualEditor(){
	visualEditor = !visualEditor;
	iDoc.body.innerHTML = $("#html-code").val();
	$("#htmlb").toggle();
	$("#wysiwygb").toggle();

	$("#html-code").toggle(); 
	$("iframe").toggle();
}

function makePublish(){
	if($('#html-code').css('display') == 'inline') 
		iDoc.body.innerHTML = $('#html-code').val();
	$('#body').val(iDoc.body.innerHTML);
}