var isGecko = navigator.userAgent.toLowerCase().indexOf("gecko") != -1 ? true : false;
var iframe = (isGecko) ? document.getElementById("editor") : frames["editor"];
var iWin = (isGecko) ? iframe.contentWindow : iframe.window;
var iDoc = (isGecko) ? iframe.contentDocument : iframe.document;
iHTML = document.getElementById("body").value == null ? "<html><head></head><body style='background: #fff;'></body></html>" : "<body style='background: #fff;'>"+document.getElementById("body").value+"</body>";
iDoc.open();
iDoc.write(iHTML);
iDoc.close();
iDoc.designMode = "on";
function setBold() {
    iWin.focus();
    iWin.document.execCommand("bold", null, "");
}

function setItal() {
    iWin.focus();
    iWin.document.execCommand("italic", null, "");
}

function setUnderline(){
    iWin.focus();
    iWin.document.execCommand("underline", null, "");
}

function setLink() {
    var href = prompt("Введите адрес ссылки:");
    iWin.focus();
    iWin.document.execCommand("CreateLink", null, href);
}

function setImage() {
    var src = prompt("Введите путь к изображению:");
    iWin.focus();
    iWin.document.execCommand("InsertImage", null, src);
}

function setLeft() {
    iWin.focus();
    iWin.document.execCommand("JustifyLeft", null, "");
}

function setCenter() {
    iWin.focus();
    iWin.document.execCommand("JustifyCenter", null, "");
}

function setRight() {
    iWin.focus();
    iWin.document.execCommand("JustifyRight", null, "");
}

function RemoveFormat() {
    iWin.focus();
    iWin.document.execCommand("RemoveFormat", null, "");
}

function ShowHTML(){
    document.getElementById("html-code").value = iDoc.body.innerHTML;
    $("#htmlb").toggle();
    $("#wysiwygb").toggle();
    
    $("#html-code").toggle(); 
    $("iframe").toggle();
}

function ShowWYSIWSYG(){
    iDoc.body.innerHTML = document.getElementById("html-code").value;
    $("#htmlb").toggle();
    $("#wysiwygb").toggle();

    $("#html-code").toggle(); 
    $("iframe").toggle();
}

function makePublish(){
    if($('#html-code').css('display') == 'inline') 
        iDoc.body.innerHTML = document.getElementById('html-code').value;
    document.getElementById('body').value = iDoc.body.innerHTML;
}