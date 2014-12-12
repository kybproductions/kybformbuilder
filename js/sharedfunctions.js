function checkSure1(var1) {
   if (confirm("Are you sure you want to delete this item?")) {
         window.location=var1
    }
}



function checkSure(var1) {
	if (confirm("Are you sure you want to delete this item?")) {
		window.location=var1
    }
}


function applyRule (obj, element, ruletitle, optList, url) {
	var itemLen = obj.length;
	var ID = "";
	for (x=0; x < itemLen; x++) {
		if (obj[x].selected)
		{
			ID += obj[x].value + ",";
		}
	}
	//Remove the last comma
	ID = ID.slice(0, -1);
	
	if (url != "") {	
		url += ID
		var xmlHttp;
		xmlHttp=GetXmlHttpObject();
		
		if (xmlHttp==null)
		{
		alert ("Your browser does not support AJAX!");
		return;
		} 
		xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				var responseInfo = new Array();
				responseInfo = xmlHttp.responseText.split("|");
				var itemList = responseInfo[0];
				var idList = "";
				
				var idList = responseInfo[1];
				
				if (itemList != "")
				{
					var rulelist = ruletitle + ":<div style='margin-left:20px;'>" + itemList + "</div>";
					document.getElementById(optList).value=idList;
				} else {
					rulelist = "";
				}
				
				document.getElementById(element).innerHTML= rulelist;
				
			}
		}
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	} else {
		var responseInfo = new Array();
		var rulelist = ruletitle;
		var fillOptions = false;
		var optelement = "";
		for (i=0; i < itemLen; i++) {
			if (obj[i].selected)
			{
				var selItem = obj[i].value;
				responseInfo = selItem.split("|");
				rulelist += "<br><div style='margin-left:20px;'>" + responseInfo[0];
				fillType = responseInfo[2];
				switch (fillType)
				{
				case "input":
					rulelist += "&nbsp;<input type='text' name='salesrule[cart][" + responseInfo[1] + "]'><br>";
					rulelist += "</div>";
					document.getElementById(element).innerHTML=rulelist;
				break;

				case "multi":
					rulelist += "&nbsp;<input type='text' name='salesrule[cart][" + responseInfo[1] + "]'><br><i>to enter more than one, separate each by a comma.</i><br>";
					rulelist += "</div>";
					document.getElementById(element).innerHTML=rulelist;
				break;
				case "states":
					opturl = responseInfo[3];
					var listName = "salesrule[cart][" + responseInfo[1] + "]";
					var xmlHttp;
					xmlHttp=GetXmlHttpObject();
					
					if (xmlHttp==null)
					{
					alert ("Your browser does not support AJAX!");
					return;
					} 
					xmlHttp.onreadystatechange=function()
					{
						if(xmlHttp.readyState==4 && xmlHttp.status == 200)
						{
							rulelist += "<br><select name='salesrule[cart][" + responseInfo[1] + "][]' id='" + responseInfo[1] + "' multiple style='height:100px;width:250px;'>";
							rulelist += xmlHttp.responseText
							rulelist += "</select><br><i>to select more than one, hold down the ctrl key</i><br>";
							rulelist += "</div>";
							document.getElementById(element).innerHTML=rulelist;
						}
					}
					xmlHttp.open("GET",opturl,true);
					xmlHttp.send(null);
				break;
				case "countries":
					opturl = responseInfo[3];
					var listName = "salesrule[cart][" + responseInfo[1] + "]";
					var xmlHttp;
					xmlHttp=GetXmlHttpObject();
					
					if (xmlHttp==null)
					{
					alert ("Your browser does not support AJAX!");
					return;
					} 
					xmlHttp.onreadystatechange=function()
					{
						if(xmlHttp.readyState==4 && xmlHttp.status == 200)
						{
							rulelist += "<br><select name='salesrule[cart][" + responseInfo[1] + "]' id='" + responseInfo[1] + "' multiple style='height:100px;width:250px;'>";
							rulelist += xmlHttp.responseText
							rulelist += "</select><br><i>to select more than one, hold down the ctrl key</i><br>";
							rulelist += "</div>";
							document.getElementById(element).innerHTML=rulelist;
						}
					}
					xmlHttp.open("GET",opturl,true);
					xmlHttp.send(null);
				break;
				}
			}
		}
	}
}

function AddItem2(txt,Value,element)   {
        // Create an Option object       

        var opt = document.createElement("option");

        // Add an Option object to Drop Down/List Box
        document.getElementById(element).options.add(opt);

        // Assign text and value to Option object
        opt.text = txt;
        opt.value = Value;
		//opt.selected = "selected";
}

function submitForm(varform) {
	document[varform].submit();
}

function viewImage() {
	var imgarea = document.getElementById("showimage").style;
	var updatearea = document.getElementById("showupdate").style;
	imgarea.display = "block";
	updatearea.display = "none";
}

function updateImage() {
	var imgarea = document.getElementById("showimage").style;
	var updatearea = document.getElementById("showupdate").style;
	imgarea.display = "none";
	updatearea.display = "block";
}

function processImage(show,hide,process,url) {
	var showarea = document.getElementById(show);
	var hidearea = document.getElementById(hide);
	hidearea.style.display = "none";
	showarea.style.display = "block";

	if (process != "") {
		showarea.innerHTML = "<p align='center' style='margin-top:75px;'>Processing...<br/><img src='/SPLIB/images/loading.gif'/></p>";
		var xmlHttp;
		xmlHttp=GetXmlHttpObject();
		
		if (xmlHttp==null)
		{
		alert ("Your browser does not support AJAX!");
		return;
		} 
		xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				showarea.innerHTML = xmlHttp.responseText;
			}
		}
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
}

function formCheck(formobj, items, security){
	// Enter name of mandatory fields
	var fieldRequired = new Array();
	fieldRequired = items.split(", ");
	// Enter field description to appear in the dialog box
	var fieldDescription = new Array();
	fieldDescription = items.split(", ");
	// dialog message
	var alertMsg = "Please complete the following fields:\n";
	
	var l_Msg = alertMsg.length;
	
	for (var i = 0; i < fieldRequired.length; i++){
		var obj = formobj.elements[fieldRequired[i]];
				
		if (obj){
			//alert(obj.value);
			switch(obj.type){
			case "select-one":
				if (obj.selectedIndex == -1 || obj.options[obj.selectedIndex].text == ""){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "select-multiple":
				if (obj.selectedIndex == -1){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "text":
				if (obj.value == "" || obj.value == null){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "textarea":
				if (obj.value == "" || obj.value == null){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			default:
			}
			if (obj.type == undefined){
				var blnchecked = false;
				for (var j = 0; j < obj.length; j++){
					if (obj[j].checked){
						blnchecked = true;
					}
				}
				if (!blnchecked){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			}
		}
	}

	if (security) {
		//Check Security code
		var checkobj = formobj.elements["validator"];
		var checkinput = formobj.elements["security"];
		if (checkobj.value == "" || checkobj.value == null || checkobj.value != checkinput.value){
			alertMsg += "Correct Security Code\n";
		}
	}

	if (alertMsg.length == l_Msg){
		return true;
	}else{
		alert(alertMsg);
		return false;
	}

	//return false;
}


function validateEmail(man,db,formObj) {
obj = document.getElementById(formObj);
addr =  obj.value;

if (addr == '' && man) {
   if (db) alert('please enter an email address');
	obj.focus();
   return false;
}
var invalidChars = '\/\'\\ ";:?!()[]\{\}^|';
for (i=0; i<invalidChars.length; i++) {
   if (addr.indexOf(invalidChars.charAt(i),0) > -1) {
      if (db) alert('email address contains invalid characters');
	  obj.focus();
      return false;
   }
}
for (i=0; i<addr.length; i++) {
   if (addr.charCodeAt(i)>127) {
      if (db) alert("email address contains non ascii characters.");
	  obj.focus();
      return false;
   }
}

var atPos = addr.indexOf('@',0);
if (atPos == -1) {
   if (db) alert('email address must contain an @');
   obj.focus();
   return false;
}
if (atPos == 0) {
   if (db) alert('email address must not start with @');
   obj.focus();
   return false;
}
if (addr.indexOf('@', atPos + 1) > - 1) {
   if (db) alert('email address must contain only one @');
   obj.focus();
   return false;
}
if (addr.indexOf('.', atPos) == -1) {
   if (db) alert('email address must contain a period in the domain name');
   obj.focus();
   return false;
}
if (addr.indexOf('@.',0) != -1) {
   if (db) alert('period must not immediately follow @ in email address');
   obj.focus();
   return false;
}
if (addr.indexOf('.@',0) != -1){
   if (db) alert('period must not immediately precede @ in email address');
   obj.focus();
   return false;
}
if (addr.indexOf('..',0) != -1) {
   if (db) alert('two periods must not be adjacent in email address');
   obj.focus();
   return false;
}
var suffix = addr.substring(addr.lastIndexOf('.')+1);
if (suffix.length != 2 && suffix != 'com' && suffix != 'net' && suffix != 'org' && suffix != 'edu' && suffix != 'int' && suffix != 'mil' && suffix != 'gov' & suffix != 'arpa' && suffix != 'biz' && suffix != 'aero' && suffix != 'name' && suffix != 'coop' && suffix != 'info' && suffix != 'pro' && suffix != 'museum') {
   if (db) alert('invalid primary domain in email address');
   obj.focus();
   return false;
}

return true;
}

/*Ajax functions*/
function GetXmlHttpObject()
{
var xmlHttp=null;
try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
catch (e)
  {
  // Internet Explorer
  try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
  catch (e)
    {
    xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
return xmlHttp;
}

function pageProcess(url,process,security,hide,html,view,key,keyfield,fields)
{
	document.getElementById("load").innerHTML="<p align='center' style='margin-top:75px;'><img src='/images/admin_logo.jpg'><br/>Processing...<br/><img src='/images/loading.gif'/></p>";
	var xmlHttp;
	url=url+"?sid="+Math.random();
	//alert(url);

	//Add fields to url
	if (fields != "") {

		//Clean fields before appending to url
		var fieldlist = new Array
		fieldlist = fields.split(",");
		for (var i = 0; i < fieldlist.length; i++){
			var obj = document.getElementById(fieldlist[i]);
				if (obj) {
					switch(obj.type){
						case "select-one":
							if (obj.selectedIndex > 0 || obj.options[obj.selectedIndex].text != ""){
								//alert(obj.options[obj.selectedIndex].text);
							}
						break;
						case "select-multiple":
							if (obj.selectedIndex > 0){
								//alertMsg += " - " + fieldDescription[i] + "\n";
							}
						break;
						case "text":
							if (obj.value != "" || obj.value != null){
								//alert(obj.value);
							}
						break;
						case "textarea":
							if (obj.value != "" || obj.value != null){
								//alert(obj.value);
							}
						break;
						default:
					}
			
					if (obj.type == undefined){
						var blnchecked = false;
						for (var j = 0; j < obj.length; j++){
							if (obj[j].checked){
								blnchecked = true;
						}
					}
			
					if (blnchecked){
						//alert(obj[j].checked.value);
					}
				
				}
			}
			url += "&" + fieldlist[i] + "=" + cleanEntry(document.getElementById(fieldlist[i]).value);
			//alert(url);
		}
	}

	//First check security if necessary
	if (security == true) {
		key = document.getElementById(key).value;
		if(document.getElementById(keyfield).value != key) {
			alert("Security code does not match.  Please reenter.");
			document.getElementById(keyfield).value = "";
			document.getElementById(keyfield).focus();
		} else {
			getURL();
		}
	} else {
		getURL();
	}
	

	function getURL() {
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null)
		{
		alert ("Your browser does not support AJAX!");
		return;
		} 

		xmlHttp.onreadystatechange=function()
			{
				if(xmlHttp.readyState==4 && xmlHttp.status == 200)
				{
					document.getElementById("element").innerHTML=xmlHttp.responseText;
					//var t=setTimeout("showSite()",5000);
					document.getElementById("load").innerHTML="";
					loadMenu = document.getElementById("load").style;
					siteMenu = document.getElementById("element").style;
					frameMenu = document.getElementById("iframe").style;
					loadMenu.display = "none";
					siteMenu.display = "block";
					frameMenu.display = "none";
				}
			}
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
		
	}
	
} 
function updateArea(url,process,area,fieldValue,vartxt,changeLink,changeTxt) {
	//document.getElementById(area).innerHTML="<p align='center' style='margin-top:75px;'>Processing...<br/><img src='../SPLIB/images/loading.gif'/></p>";
	var xmlHttp;
	if (fieldValue != "")
	{
		// Enter name of mandatory fields
		var fields = new Array();
		fields = fieldValue.split(",");
		var fieldText = new Array();
		fieldText = vartxt.split(",");

		for (var i = 0; i < fields.length; i++){
			field_value = document.getElementById(fields[i]).value;
			url = url+"&" + fieldText[i] + "=" + field_value;
		}
		//alert (url);
	}

	if (changeLink != "")
	{
		document.getElementById(changeLink).innerHTML = "";
		document.getElementById(changeLink).innerHTML = changeTxt;
	}
	
	//var url="form_view.php?formID=4&process=update";
	//Need to determine if url has & in the query
	//url=url+"?sid="+Math.random();

	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				if (process == "html") {
					document.getElementById(area).innerHTML="";
					document.getElementById(area).innerHTML=xmlHttp.responseText;
				}

				if(process == "frames") {
					siteMenu = document.getElementById("element").style;
					frameMenu = document.getElementById("iframe").style;
					loadMenu.display = "none";
					siteMenu.display = "none";
					frameMenu.display = "block";					
					//eval("parent.info.location='"+url+"'");
					document.getElementById("info").src = url;
				}
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function showInfo(url,process)
{
	document.getElementById("load").innerHTML="<p align='center' style='margin-top:75px;'>Processing...<br/><img src='../SPLIB/images/loading.gif'/></p>";
	var xmlHttp;
	
	//var url="form_view.php?formID=4&process=update";
	//Need to determine if url has & in the query
	//url=url+"?sid="+Math.random();

	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				if (process == "html") {
				document.getElementById("element").innerHTML="";
				//var t=setTimeout("showSite()",5000);
				document.getElementById("load").innerHTML="";
				loadMenu = document.getElementById("load").style;
				siteMenu = document.getElementById("element").style;
				frameMenu = document.getElementById("iframe").style;
				loadMenu.display = "none";
				siteMenu.display = "block";
				frameMenu.display = "none";
				document.getElementById("element").innerHTML=xmlHttp.responseText;
				}

				if(process == "frames") {
					siteMenu = document.getElementById("element").style;
					frameMenu = document.getElementById("iframe").style;
					loadMenu.display = "none";
					siteMenu.display = "none";
					frameMenu.display = "block";					
					//eval("parent.info.location='"+url+"'");
					document.getElementById("info").src = url;
				}
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 

function cleanEntry(item){
	cleanitem = item.replace(/(<([^>]+)>)/ig,""); 
	return cleanitem;
}

function showCal(url,element)
{
	document.getElementById(element).innerHTML="<p align='center' style='margin-top:35px;'>Processing...<br/><img src='/images/loading.gif'/></p>";
	var xmlHttp;
	
	//var url="form_view.php?formID=4&process=update";
	//Need to determine if url has & in the query
	//url=url+"?sid="+Math.random();

	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				document.getElementById(element).innerHTML=xmlHttp.responseText;
				
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 


function checkSure2(url,field,process,message) {
   if (confirm("Are you sure you want to delete this item?")) {
         checkData(url,field,process,message);
    }
}

function checkData(url,field,identity,process,message) {
		
		url=url+"?sid="+Math.random();
		//Add field information to url
		var checkfield = document.getElementById(field).value;
		url = url + "&" + identity + "=" + checkfield;
	
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null)
		{
		alert ("Your browser does not support AJAX!");
		return;
		} 

		xmlHttp.onreadystatechange=function()
			{
				if(xmlHttp.readyState==4 && xmlHttp.status == 200)
				{
					
					if(process == "static") {
						var status = xmlHttp.responseText;

						if (status == "1") {
							alert(message);
							document.getElementById(field).value="";
							document.getElementById(field).focus();
						} 
					} else if (process == "delete") {
						//alert(status)
						showInfo(url, 'html')
					}
				}
			}
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
		
}


function CheckLikes() {
		var boxlength = document.user;
		boxes = boxlength.usernav.length;
		//alert(boxes)
		txt = ""
		var undefined;
		if (boxes == undefined)
		{
			if (boxlength.usernav.checked = true)
			{
				txt = boxlength.usernav.value;
			} else {
				txt = ""
			}
		} else {
			for (i = 0; i < boxes; i++) {
				//alert(boxlength.usernav[i].checked);
				if (boxlength.usernav[i].checked) {
					txt = txt + boxlength.usernav[i].value + ", "
				}
			}
		}


		if (txt == "") {
			Message = "No Boxes ticked"
			document.getElementById("navigations").value = "";
		}
			else {
			Message = ""
			document.getElementById("navigations").value = txt;
		}

		return Message
	}

function NewWindow2(mypage,myname,w,h,scroll,pos) {
if(pos=="random"){ LeftPosition=(screen.width)?Math.floor(Math.random()*(screen.width-w)):100;TopPosition=(screen.height)?Math.floor(Math.random()*((screen.height-h)-75)):100; }
if(pos=="center") { LeftPosition=(screen.width)?(screen.width-w)/2:100;TopPosition=(screen.height)?(screen.height-h)/2:100; }
else if((pos!="center" && pos!="random") || pos==null) { LeftPosition=0;TopPosition=20 }
settings='width='+w+',height='+h+',top='+TopPosition+',left='+LeftPosition+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes';
win=window.open(mypage,myname,settings); }

function testProcess(url,element,checkCat) {
	if (checkCat)
	{
		var catID = document.getElementById("categoryID").value;
		if (catID == 0 || catID == "")
		{
			alert("Please select a category for the product item.");
			return;
		}
		url = url + "&categoryID=" + catID;
	}
	
	var xmlHttp;
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				document.getElementById(element).innerHTML=xmlHttp.responseText;
				alert (xmlHttp.responseText);
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function setYouTubeImage(imageID,thumbID,obj) {
	var id = obj.value;
	var url = "http://img.youtube.com/vi/" + id + "/1.jpg";
	var videourl = "http://www.youtube.com/embed/" + id;
	document.getElementById(thumbID).value = url;
	document.getElementById(imageID).src = videourl;
}

function cmsProcess(url,element,checkCat)
{
	if (checkCat)
	{
		var catID = document.getElementById("categoryID").value;
		if (catID == 0 || catID == "")
		{
			alert("Please select a category for the product item.");
			return;
		}
		url = url + "&categoryID=" + catID;
	}

	
	
	var xmlHttp;
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				document.getElementById(element).innerHTML=xmlHttp.responseText;
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 

function moveHeight (process, area) {
		var image = document.getElementById(area).style;
		
		if (process == "open") {
			image.height = "325px";
		} 

		if (process == "close") {
			image.height = "200px";
		}
	}

function AddImage(item,url) {
	var catID = item.value;
	var area = url + '?process=addnew&categoryID=' + catID;
	cmsProcess(area,'viewimage');
}

function ismaxlength(obj)
	{
	var mlength=obj.getAttribute? parseInt(obj.getAttribute("maxlength")) : ""
	if (obj.getAttribute && obj.value.length>mlength)
	obj.value=obj.value.substring(0,mlength)
}

function ClipBoard(x){
if (navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion >= "4.0"){
	var obj = document.getElementById("holdtext");
	obj.innerText = x;
	Copied = obj.createTextRange();

	 if(Copied.execCommand("Copy")){
		  alert('The form link has been copied to your system clipboard.');    
    }else{
		  prompt('Sorry, we could not copy the publish location to your system clipboard.');
	}
} else {
	try
		{
			window.clipboard = new Clipboard(x);
		}
		catch (e)
		{
			alert("This function can only be used in an IE browser.  Please copy link from above the table.")
			var obj    = document.getElementById("linktocopy");
			if (document.all) {
				obj.innerText = x;
			} else {
				obj.textContent = x;
			}
		}
		
		// Function to return the data in clipboard
		function getClipboardContents()
		{
			return window.clipboard.paste(x);
		}
}

}

function checkRequired(fields, fieldnames) {
	//Check required fields
	var err = "";
	var fieldlist = new Array
	var fieldname = new Array
	fieldlist = fields.split(",");
	fieldname = fieldnames.split(",");
	for (var i = 0; i < fieldlist.length; i++){
		var obj = document.getElementById(fieldlist[i]);
		if (obj) {
			switch(obj.type){
				case "select-one":
					if (obj.selectedIndex == 0 || obj.options[obj.selectedIndex].text == ""){
							err += "\n\n--Your " + fieldname[i] + " is required to continue.";
						}
				break;
				case "select-multiple":
					if (obj.selectedIndex == 0){
							err += "\n\n--Your " + fieldname[i] + " is required to continue.";
						}
				break;
				case "text":
					if (obj.value == "" || obj.value == null){
						err += "\n\n--Your " + fieldname[i] + " is required to continue";
					}
				break;
				case "textarea":
					if (obj.value == "" || obj.value == null){
						err += "\n\n--Your " + fieldname[i] + " is required to continue";
					}
				break;
				default:
			}

			/*if (obj.type == undefined){
			var blnchecked = false;
			for (var j = 0; j < obj.length; j++){
				if (obj[j].checked){
					blnchecked = true;
				}
			}
			
			if (blnchecked){
				err += "\n\n--Your " + fieldname[i] + " is required to continue";
			}*/
		}
	}

	if (err != "")
	{
		alert(err);
		return false;
	} else {	
		return true;
	}
}


function showChange(fieldName, url, process, changeField, obj) {
	var objValue = obj.value;
	url = url + objValue;
	document.getElementById(fieldName).innerHTML = "Processing please wait...";
	
	var xmlHttp;
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4 && xmlHttp.status == 200)
			{
				var xmlInfo = xmlHttp.responseText;
				document.getElementById(fieldName).innerHTML = "";
				switch (process) {
					case "image":
						document.getElementById(changeField).src = xmlInfo;
					break;

					case "text":
						document.getElementById(changeField).text = xmlInfo;
					break;

					case "html":
						document.getElementById(changeField).innerHTML = xmlInfo;
					break;
				}
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
