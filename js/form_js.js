////////////////////////////////////////////////////////////////////////////////
/*******************************************************************************
* FORM BUILDER                                                                 *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    2009-15-09                                                          *
* Author:  KYB Productions													   *
* Copyright 2009 KYB Productions											   *
*******************************************************************************/
////////////////////////////////////////////////////////////////////////////////


//Copy contents to clipboard.  If not allowed will show link on page to copy
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


//Check first to make sure user wants to delete form
function CheckSure(varlink) {
			if (confirm("Are you sure you want to delete?")) {
				showForm(varlink);
				
			}
}

function CheckSure2(varlink) {
			if (confirm("Are you sure you want to delete?")) {
				window.location = varlink;
			}
}






function lightup(imgName, rowID)
 {
	var imgID = imgName + rowID
   if (document.images)
    {
      imgOn=eval(imgName + "on.src");
	  
	  document.getElementById(imgID).src = imgOn;
    }
 }


function turnoff(imgName, rowID)
 {
	var imgID = imgName + rowID
   if (document.images)
    {
      imgOff=eval(imgName + "off.src");
	  document.getElementById(imgID).src = imgOff;
    }
 }



//Form check for required fields
function formCheck(formobj, items, security, checkpayment, titles){
	
	// Enter name of mandatory fields
	var fieldRequired = new Array();
	fieldRequired = items.split(",");
	// Enter field description to appear in the dialog box
	var fieldDescription = new Array();
	fieldDescription = titles.split(",");
	// dialog message
	var alertMsg = "Please complete the following fields:\n";

	/*if (security == "")
	{
		var security = document.getElementById("sessioncode").value;
	}*/
	
	var Msg = alertMsg.length;
	
	
	for (var i = 0; i < fieldRequired.length; i++){
		var obj = formobj.elements[fieldRequired[i]];
				
		if (obj){
			switch(obj.type){
			case "select-one":
				if (obj.selectedIndex == -1 || obj.options[obj.selectedIndex].text == "" || obj.options[obj.selectedIndex].text == "Select..."){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "select-multiple":
				if (obj.selectedIndex == -1){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "text":
				if (obj.value == "" || obj.value == null || obj.value == "Username" || obj.value == "Password" || obj.value == "Your Name" || obj.value == "Your Email"){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "hidden":
				if (obj.value == "" || obj.value == null || obj.value == "Your Name" || obj.value == "Your Email"){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "textarea":
				if (obj.value == "" || obj.value == null){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "radio":
				var radobj = document.getElementsByName(fieldRequired[i]);
				var blnchecked = false;
				for (var j = 0; j < radobj.length; j++){
					if (radobj[j].checked){
						blnchecked = true;
					}
				}
				if (!blnchecked){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "checkbox":
				if (!obj.checked)
				{
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				
			break;
			default:
			}
			if (obj.type == undefined){
				var blnchecked = false;
				var objnum = obj.length;
				if (fieldRequired[i] == "item")
				{
					objnum = document.getElementById('totalitems').value;
					objnum = parseInt(objnum);
					for (var j = 1; j <= objnum; j++){
						var itemName = "item_" + j;
						var amtName = "amount_" + j;
						var qtyName = "quantity_" + j;
						var itemCheck = document.getElementById(itemName);
						var amtCheck = document.getElementById(amtName);
						var qtyCheck = document.getElementById(qtyName);
						if (itemCheck.checked && amtCheck.value != "" && qtyCheck.value != "") {
							blnchecked = true;
						}
					}
				} else {
					for (var j = 0; j < objnum; j++){
						if (obj[j].checked){
							blnchecked = true;
						}
					}
				}
				if (!blnchecked){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			}
		}
	}
	

	//Check Security code
	/*var checkobj = formobj.elements["validator"];
	if (checkobj.value == "" || checkobj.value == null || checkobj.value != security){
		alertMsg += "Correct Security Code\n";
	}*/
	customcheck = false;
	if (customcheck)
	{
		var cust_checked = false;
		var item1 = document.getElementById('item_name_1');
		var item2 = document.getElementById('item_name_2');
		var item3 = document.getElementById('item_name_3');
		var item4 = document.getElementById('item_name_4');
		var item5 = document.getElementById('item_name_5');
		//quantity_1
		if (item1.checked)
		{
			cust_checked = true;
		}

		if (item2.checked)
		{
			cust_checked = true;
		}

		if (item3.checked)
		{
			cust_checked = true;
		}

		if (item4.checked)
		{
			cust_checked = true;
		}

		if (item5.checked)
		{
			cust_checked = true;
		}

		if (!cust_checked)
		{
			alertMsg += " - Registration Choice and Quantity"
		}
		
	}

	if (checkpayment)
	{
		var paycheck = CheckRequiredFields(formobj);
		if(paycheck.length > 2) {
			alertMsg += paycheck;
		}
	}

	if (security)	{
		
		url = "/dev/coppell/wp-content/plugins/kybformbuilder/model/form_ajax.php?process=getsecurecode";
		var securecode = $.ajax({
			url: url,
			type: 'POST',
			dataType: 'html',
			context: document.body,
			global: false,
			async:false,
			success: function (resp) {
				return resp;
			},	
			error: function(e) {
				alert('Security Error: '+e);
			}  
		}).responseText;

		var valentry = document.getElementById('validator').value;
		if (securecode != valentry) {
			alertMsg += " - Please Enter Correct Security Code\n";
			
		}
		document.getElementById("checkprocess").innerHTML = "Validating Form Please wait...";
	}
	

	if (alertMsg.length == Msg){
		return true;
	}else{
		alert(alertMsg);
		if (security)	{
			document.getElementById("checkprocess").innerHTML = "";
		}
		return false;
	}

}

function formCheck2(formobj, items, security, checkpayment, titles) {
	// Enter name of mandatory fields
	var fieldRequired = new Array();
	fieldRequired = items.split(",");
	// Enter field description to appear in the dialog box
	var fieldDescription = new Array();
	fieldDescription = titles.split(",");
	// dialog message
	var alertMsg = "Please complete the following fields:\n";

	/*if (security == "")
	{
		var security = document.getElementById("sessioncode").value;
	}*/
	
	var Msg = alertMsg.length;
	
	
	for (var i = 0; i < fieldRequired.length; i++){
		var obj = formobj.elements[fieldRequired[i]];

		if (obj){
			switch(obj.type){
			case "select-one":
				if (obj.selectedIndex == -1 || obj.options[obj.selectedIndex].text == "" || obj.options[obj.selectedIndex].text == "Select..."){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "select-multiple":
				if (obj.selectedIndex == -1){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				break;
			case "text":
				if (obj.value == "" || obj.value == null || obj.value == "Username" || obj.value == "Password" || obj.value == "Your Name" || obj.value == "Your Email"){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "hidden":
				if (obj.value == "" || obj.value == null){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "textarea":
				if (obj.value == "" || obj.value == null){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "radio":
				var radobj = document.getElementsByName(fieldRequired[i]);
				var blnchecked = false;
				for (var j = 0; j < radobj.length; j++){
					if (radobj[j].checked){
						blnchecked = true;
					}
				}
				if (!blnchecked){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			break;
			case "checkbox":
				if (!obj.checked)
				{
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
				
			break;
			default:
			}
			if (obj.type == undefined){
				var blnchecked = false;
				var objnum = obj.length;
				if (fieldRequired[i] == "item")
				{
					objnum = document.getElementById('totalitems').value;
					objnum = parseInt(objnum);
					for (var j = 1; j <= objnum; j++){
						var itemName = "item_" + j;
						var amtName = "amount_" + j;
						var qtyName = "quantity_" + j;
						var itemCheck = document.getElementById(itemName);
						var amtCheck = document.getElementById(amtName);
						var qtyCheck = document.getElementById(qtyName);
						if (itemCheck.checked && amtCheck.value != "" && qtyCheck.value != "") {
							blnchecked = true;
						}
					}
				}
				if (!blnchecked){
					alertMsg += " - " + fieldDescription[i] + "\n";
				}
			}
		}
	}
	alert(alertMsg);
	customcheck = false;
	if (customcheck)
	{
		var cust_checked = false;
		var item1 = document.getElementById('item_name_1');
		var item2 = document.getElementById('item_name_2');
		var item3 = document.getElementById('item_name_3');
		var item4 = document.getElementById('item_name_4');
		var item5 = document.getElementById('item_name_5');
		//quantity_1
		if (item1.checked)
		{
			cust_checked = true;
		}

		if (item2.checked)
		{
			cust_checked = true;
		}

		if (item3.checked)
		{
			cust_checked = true;
		}

		if (item4.checked)
		{
			cust_checked = true;
		}

		if (item5.checked)
		{
			cust_checked = true;
		}

		if (!cust_checked)
		{
			alertMsg += " - Registration Choice and Quantity"
		}
		
	}

	
	return false;
	
}

function fillQty(obj) {
	var qty = "quantity_" + obj.value;
	if (obj.checked) {
		
		if(document.getElementById("numofguest")){
			var guest = document.getElementById("numofguest").value;
			if (guest != "")
			{
				document.getElementById(qty).value = guest;
			} else {
				document.getElementById(qty).value = "1";
			}
			
		} else {
			document.getElementById(qty).value = "1";
		}
	}

	if (!obj.checked)
	{
		document.getElementById(qty).value = "";
	}
}

function CheckRequiredFields(obj) {
	//SubmissionForm
	var errormessage = new String();	
	//var pm = obj.elements["paymethod_integer"];
	var auth = obj.elements["authorization_integer"];
	
	// Put field checks below this point.
	/*if(NoneWithCheck(pm)){ 
		errormessage += "\n\nPlease select Payment Method in order to proceed.";
	} */

	/*for(var i = 0; i < pm.length; i++) {
		if (pm[i].checked) {
			if (pm[i].value == 1)
			{
				if(WithoutSelectionValue(obj.elements["cardtype"]))
					{ errormessage += "\n\nPlease select Credit Card Type in order to proceed."; }
				if(WithoutContent(obj.elements["acctnum"].value))
					{ errormessage += "\n\nYour Account Number is required to proceed."; }
				if(WithoutContent(obj.elements["exp_month"].value))
					{ errormessage += "\n\nYour Expiration Date is required to proceed."; }	
				if(WithoutContent(obj.elements["exp_year"].value))
					{ errormessage += "\n\nYour Expiration Year is required to proceed."; }	
				if(WithoutContent(obj.elements["cardholder"].value))
					{ errormessage += "\n\nYour Cardholder Name is required to proceed."; }	
				if(NoneWithCheck(auth))
					{ errormessage += "\n\nPlease authorize the charge of your credit card."; }
				for(var i = 0; i < auth.length; i++) {
					if (auth[i].checked) {
						if (auth[i].value == 0)
						{
							errormessage += "\n\nYou must authorize your credit card in order to proceed.";
						}
					}
				}
			}
		}
	}*/

	if(WithoutSelectionValue(obj.elements["cardtype"]))
		{ errormessage += "\n\nPlease select Credit Card Type in order to proceed."; }
	if(WithoutContent(obj.elements["acctnum"].value))
		{ errormessage += "\n\nYour Account Number is required to proceed."; }
	if(WithoutContent(obj.elements["exp_month"].value))
		{ errormessage += "\n\nYour Expiration Date is required to proceed."; }	
	if(WithoutContent(obj.elements["exp_year"].value))
		{ errormessage += "\n\nYour Expiration Year is required to proceed."; }	
	if(WithoutContent(obj.elements["cardholder"].value))
		{ errormessage += "\n\nYour Cardholder Name is required to proceed."; }	
	if(NoneWithCheck(auth))
		{ errormessage += "\n\nPlease authorize the charge of your credit card."; }
	for(var i = 0; i < auth.length; i++) {
		if (auth[i].checked) {
			if (auth[i].value == 0)
			{
				errormessage += "\n\nYou must authorize your credit card in order to proceed.";
			}
		}
	}

	return errormessage;
	
} // end of function CheckRequiredFields()
	
function WithoutContent(ss) {
	if(ss.length > 0) { return false; }
	return true;
}

function NoneWithContent(ss) {
	for(var i = 0; i < ss.length; i++) {
		if(ss[i].value.length > 0) { return false; }
	}
	return true;
}

function NoneWithCheck(ss) {
	for(var i = 0; i < ss.length; i++) {
		if(ss[i].checked) { return false; }
		}
	return true;
}

function CheckAttendance(ss) {
	for(var i = 0; i < ss.length; i++) {
		if (ss[i].value == 0) {
			var errorattendance = new String();
			if(WithoutContent(document.AppSubmission.rep_name.value))
				{ errorattendance += "\n\nYour Replacement Name is required to proceed."; }
			if(WithoutContent(document.AppSubmission.rep_phone1.value))
				{ errorattendance += "\n\nYour Replacement Phone Number is required to proceed."; }
			
			return false;
		}
		if(ss[i].checked) { return false; }
		}
	return true;
}


function WithoutCheck(ss) {
	if(ss.checked) { return false; }
	return true;
}

function WithoutSelectionValue(ss) {
	for(var i = 0; i < ss.length; i++) {
		if(ss[i].selected) {
			if(ss[i].value.length) { return false; }
		}
	}
return true;
}




//Form check for valid email addres on form
function validateEmail(addr,man,db,formObj) {
if (addr == '' && man) {
   if (db) alert('please enter an email address');
	formObj.focus();
   return false;
}
var invalidChars = '\/\'\\ ";:?!()[]\{\}^|';
for (i=0; i<invalidChars.length; i++) {
   if (addr.indexOf(invalidChars.charAt(i),0) > -1) {
      if (db) alert('email address contains invalid characters');
	  formObj.focus();
      return false;
   }
}
for (i=0; i<addr.length; i++) {
   if (addr.charCodeAt(i)>127) {
      if (db) alert("email address contains non ascii characters.");
	  formObj.focus();
      return false;
   }
}

var atPos = addr.indexOf('@',0);
if (atPos == -1) {
   if (db) alert('email address must contain an @');
   formObj.focus();
   return false;
}
if (atPos == 0) {
   if (db) alert('email address must not start with @');
   formObj.focus();
   return false;
}
if (addr.indexOf('@', atPos + 1) > - 1) {
   if (db) alert('email address must contain only one @');
   formObj.focus();
   return false;
}
if (addr.indexOf('.', atPos) == -1) {
   if (db) alert('email address must contain a period in the domain name');
   formObj.focus();
   return false;
}
if (addr.indexOf('@.',0) != -1) {
   if (db) alert('period must not immediately follow @ in email address');
   formObj.focus();
   return false;
}
if (addr.indexOf('.@',0) != -1){
   if (db) alert('period must not immediately precede @ in email address');
   formObj.focus();
   return false;
}
if (addr.indexOf('..',0) != -1) {
   if (db) alert('two periods must not be adjacent in email address');
   formObj.focus();
   return false;
}
var suffix = addr.substring(addr.lastIndexOf('.')+1);
if (suffix.length != 2 && suffix != 'com' && suffix != 'net' && suffix != 'org' && suffix != 'edu' && suffix != 'int' && suffix != 'mil' && suffix != 'gov' & suffix != 'arpa' && suffix != 'biz' && suffix != 'aero' && suffix != 'name' && suffix != 'coop' && suffix != 'info' && suffix != 'pro' && suffix != 'museum') {
   if (db) alert('invalid primary domain in email address');
   formObj.focus();
   return false;
}

return true;
}

function checkNumber(item) {
	var checknum = item.value;
	var checkname = item.name;
	if (isNaN(checknum)) {
		alert ("Entry must be a number.  Please try again.");
		item.value = "";
		item.focus();
	}
}
function loadFrames(url) {
var formID = document.getElementById("formID").value.replace("'","%27");
var section = document.getElementById("section").value.replace("'","%27");
var section = section.replace("#","%23");
var sectionID = document.getElementById("sectionID").value.replace("'","%27");
var sorder = document.getElementById("sorder").value.replace("'","%27");
var choices = document.getElementById("options").value.replace("'","%27");
var choices = document.getElementById("options").value.replace("’","%27");
var fname = document.getElementById("fname").value.replace("'","%27");
var fname = fname.replace("’","%27");
var fname = fname.replace("#","%23");
var sfname = document.getElementById("sfname").value.replace("'","%27");
var sfname = sfname.replace("’","%27");
var sfname = sfname.replace("#","%23");

var finst = document.getElementById("finstruction").value.replace("'","%27");
var finst = finst.replace("’","%27");
var finst = finst.replace("#","%23");

var forder = document.getElementById("forder").value.replace("'","%27");
var sel = document.getElementsByName("fftype");
var req = document.getElementsByName("frequired");
var placement = document.getElementsByName("fplacement");
var title = document.getElementsByName("ftplacement");
var item = "";
var cost = 0;

var fieldtype = getCheckedValue(sel);
var frequired = getCheckedValue(req);
var fplacement = getCheckedValue(placement);
var ftplacement = getCheckedValue(title);


if (section == "Product Information")
{
	item = document.getElementById("item").value.replace("'","%27");
	item = document.getElementById("item").value.replace("’","%27");
	cost = document.getElementById("cost").value.replace("'","%27");
	//cost = parseInt(document.getElementById("cost").value);
}
//may need to remove $sign from cost and parse as integer;

if (fieldtype == "")
{ 
	fieldtype = 1;
}

if (frequired == "")
{ 
	frequired = 0;
}

url = url + "&sectionID=" + sectionID + "&section=" + section + "&sorder=" + sorder + "&fieldname=" + fname + "&shortfieldname=" + sfname + "&ftype=" + fieldtype + "&required=" + frequired + "&options=" + choices + "&forder=" + forder + "&addfield=1" + "&item=" + item + "&cost=" + cost + "&fplacement=" + fplacement + "&ftplacement=" + ftplacement + "&finstruction=" + finst;
//url = url + "&sectionID=" + sectionID + "&section=" + section ;

//alert(url);
//eval("parent.viewform.location='"+page1+"'");
//showForm(url);

	document.getElementById("formview").innerHTML="Processing form...";
	var xmlHttp;
	//var url="form_view.php?formID=4&process=update";
	url=url+"&sid="+Math.random();
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4)
			{
				document.getElementById("formview").innerHTML=xmlHttp.responseText;
				if (sectionID == "") {
					ajaxFunction("getsectionID");
				}
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);



}


function enterText(item) {
	var choice = item.value;
	var len = choice.length;
	var dash = choice.indexOf('-');
	var secID = choice.substring(dash+1,len);
	var secOrder = choice.substring(0,dash);
	var paymentlbl = document.getElementById("paymentArea").style;

	for (i=0;i<document.getElementById("selsection").length;i++ )
        {
            if (document.getElementById("selsection").options[i].value == choice )
            {
             var seltxt = document.getElementById("selsection").options[i].text;
            }
        }
	if (choice != "")
	{
		document.getElementById("sorder").value = secOrder;
		document.getElementById("section").value = seltxt;
		document.getElementById("sectionID").value = secID;
	} else {
		document.getElementById("sorder").value = "";
		document.getElementById("section").value = "";
		document.getElementById("sectionID").value = "";
	}

	/*if (seltxt = "Product Information") {		
		paymentlbl.display = "none";
	} 

	if (seltxt != "Product Information") {		
		paymentlbl.display = "block";
	}*/
	
		
	//alert(choice);
}

function toggleMenu(item, currMenu) {
	var choice = item.value;
	var fields = document.getElementById("fieldArea").style;
	var Tlabel = document.getElementById("typeLabel").style;
	var Tlist = document.getElementById("typeList").style;
	var Oinstr = document.getElementById("optionInstructions").style;
	var Oarea = document.getElementById("optionArea").style;
	
	for (i=0;i<document.getElementById("selsection").length;i++ )
        {
            if (document.getElementById("selsection").options[i].value == choice )
            {
             var seltxt = document.getElementById("selsection").options[i].text;
            }
        }
	
	if (document.getElementById) {
		thisMenu = document.getElementById(currMenu).style
		if (seltxt == "Product Information") {
			thisMenu.display = "block"
			fields.display = "none";
			Tlabel.display = "none";
			Tlist.display = "none";
			Oinstr.display = "none";
			Oarea.display = "none";
		}else {
			thisMenu.display = "none"
			fields.display = "block";
			Tlabel.display = "block";
			Tlist.display = "block";
			Oinstr.display = "block";
			Oarea.display = "block";
		}

		return false
	}

	else {
		return true

	}
}


function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function addTitle() {
	
	document.getElementById("sectionID").value = "";
	document.getElementById("section").value = "";
	document.getElementById("forder").value = "1";

	var thisLabel = document.getElementById("titlelabel").style;
	var thisTitle = document.getElementById("sectiontitle").style;
	
	thisLabel.display = "block";
	thisTitle.display = "block";

	ajaxFunction("add");

}



function hideTitle() {

	var newID = document.getElementById("sectionID").value;
	var newSection = document.getElementById("section").value;
	var thisLabel = document.getElementById("titlelabel").style;
	var thisTitle = document.getElementById("sectiontitle").style;
	
	thisLabel.display = "none";
	thisTitle.display = "none";	

	AddItem(newSection,newID)

}

function AddItem(txt,Value)   {
        // Create an Option object        

        var opt = document.createElement("option");

        // Add an Option object to Drop Down/List Box
        document.getElementById("selsection").options.add(opt);

        // Assign text and value to Option object
        opt.text = txt;
        opt.value = Value;
		opt.selected = "selected";
}

function DeleteItem(section)   {
    
	for (i=0;i<document.getElementById("selsection").length;i++ )
        {
            var choice = document.getElementById("selsection").options[i].value;
			var len = choice.length;
			var dash = choice.indexOf('-');
			var secID = choice.substring(dash+1,len);
			var secOrder = choice.substring(0,dash);
			
			if (secID == section)
            {
             //Delete section item from drop down list
			 document.getElementById("selsection").remove(i);
            }
        }
		
}

function insertList(type) {
	document.getElementById("options").value= "";
	if (type == "state") {
	document.getElementById("options").value="Alabama, Alaska, Alberta, Arizona, Arkansas, California, Colorado, Connecticut, Delaware, District Of Columbia, Florida, Georgia, Hawaii, Idaho, Illinois, Indiana, International, Iowa, Kansas, Kentucky, Louisiana, Maine, Manitoba, Maryland, Massachusetts, Michigan, Minnesota, Mississippi, Missouri, Montana, Nebraska, Nevada, New Brunswick, New Hampshire, New Jersey, New Mexico, New York, Newfoundland, North Carolina, North Dakota, Northwest Territories, Nova Scotia, Ohio, Oklahoma, Ontario, Oregon, Pennsylvania, Prince Edward Island, Puerto Rico, Quebec, Rhode Island, Saskatchewan, South Carolina, South Dakota, Tennessee, Texas, Utah, Vermont, Virginia, Washington, West Virginia, Wisconsin, Wyoming, Yukon";
	}

	if (type == "country") {
	document.getElementById("options").value="Albania, Algeria, American Samoa, Andorra, Anguilla, Antigua, Argentina, Aruba, Australia, Austria, Azores, Bahamas, Bahrain, Bangladesh, Barbados, Belarus, Belgium, Belize, Benin, Bermuda, Bolivia, Bonaire, Bosnia, Botswana, Brazil, British Virgin Islands, Brunei, Bulgaria, Burkina Faso, Burundi, Cambodia, Cameroon, Canada, Canary Islands, Cape Verde Islands, Cayman Islands, Central African Republic, Chad, Channel Islands, Chile, China, Peoples Republic of, Colombia, Congo, Cook Islands, Costa Rica, Croatia, Curacao, Cyprus, Czech Republic, Denmark, Djibouti, Dominica, Dominican Republic, Ecuador, Egypt, El Salvador, England, Equitorial Guinea, Eritrea, Estonia, Ethiopia, Faeroe Islands, Federated States of Micronesia, Fiji, Finland, France, French Guiana, French Polynesia, Gabon, Gambia, Georgia, Germany, Ghana, Gibraltar, Greece, Greenland, Grenada, Guadeloupe, Guam, Guatemala, Guinea, Bissau, Guyana, Haiti, Holland, Honduras, Hong Kong, Hungary, Iceland, India, Indonesia, Israel, Italy, Ivory Coast, Jamaica, Japan, Jordan, Kazakhstan, Kenya, Kiribati, Kosrae, Kuwait, Kyrgyzstan, Laos, Latvia, Lebanon, Lesotho, Liberia, Liechtenstein, Lithuania, Luxembourg, Macau, Macedonia, Madagascar, Madeira, Malawi, Malaysia, Maldives, Mali, Malta, Marshall Islands, Martinique, Mauritania, Mauritius, Mexico, Moldova, Monaco, Montserrat, Morocco, Mozambique, Myanmar, Namibia, Nepal, Netherlands, Netherlands Antilles, New Caledonia, New Zealand, Nicaragua, Niger, Nigeria, Norfolk Island, Northern Ireland, Northern Mariana Islands, Norway, Oman, Pakistan, Palau, Panama, Papua New Guinea, Paraguay, Peru, Philippines, Poland, Ponape, Portugal, Puerto Rico, Qatar, Republic of Ireland, Republic of Yemen, Reunion, Romania, Rota, Russia, Rwanda, Saba, Saipan, Saudi Arabia, Scotland, Senegal, Seychelles, Sierra Leone, Singapore, Slovakia, Slovenia, Solomon Islands, South Africa, South Korea, Spain, Sri Lanka, St. Barthelemy, St. Christopher, St. Croix, St. Eustatius, St. John, St. Kitts and Nevis, St. Lucia, St. Maarten, St. Martin, St. Thomas, St. Vincent and the Grenadines, Sudan, Suriname, Swaziland, Sweden, Switzerland, Syria, Tahiti, Taiwan, Tajikistan, Tanzania, Thailand, Tinian, Togo, Tonga, Tortola, Trinidad and Tobago, Truk, Tunisia, Turkey, Turks and Caicos Islands, Tuvalu, Uganda, Ukraine, Union Island, United Arab Emirates, United Kingdom, United States, Uruguay, US Virgin Islands, Uzbekistan, Vanuatu, Venezuela, Vietnam, Virgin Gorda, Wake Island, Wales, Wallis and Futuna Islands, Western Samoa, Yap, Yugoslavia, Zaire, Zambia, Zimbabwe";
	}

	if (type == "stateabb")
	{
		document.getElementById("options").value="AL,AK,AB,AE,AZ,AR,BC,CA,CO,CT,DE,DC,FL,GA,HI,ID,IL,IN,GU,VI,XX,IA,KS,KY,LA,ME,MB,MD,MA,MI,MN,MS,MO,MT,NE,NV,NB,NH,NJ,NM,NY,NF,NC,ND,NT,NS,OH,OK,ON,OR,PA,PE,PR,PQ,RI,SK,SC,SD,TN,TX,UT,VT,VA,WA,WV,WI,WY,YT";
	}

	if (type == "empty") {
		document.getElementById("options").value="";
	}
}

function ajaxFunction(process)
{

var xmlHttp;
var pluginURL = document.getElementById("pluginURL").value;
var url = pluginURL + "/model/form_ajax.php?process=" + process;
url=url+"&sid="+Math.random();
xmlHttp=GetXmlHttpObject();
if (xmlHttp==null)
  {
  alert ("Your browser does not support AJAX!");
  return;
  } 
  xmlHttp.onreadystatechange=function()
    {
    if(xmlHttp.readyState==4)
      {
		
		
		if (process == "add")
		{
			document.getElementById("sorder").value= trim11(xmlHttp.responseText);
		} 

		if (process == "getsectionID")
		{
			document.getElementById("sectionID").value= trim11(xmlHttp.responseText);
			var newID = trim11(xmlHttp.responseText);
			var newSection = document.getElementById("section").value;
			var thisLabel = document.getElementById("titlelabel").style;
			var thisTitle = document.getElementById("sectiontitle").style;
	
			thisLabel.display = "none";
			thisTitle.display = "none";	

			AddItem(newSection,newID)
		}

		if (process == "checkname")
		{
			document.getElementById("fieldnamecheck").value= trim11(xmlHttp.responseText);
		}
		
		
      
      }
    }
  xmlHttp.open("GET",url,true);
  xmlHttp.send(null);

}


function trim11 (str) {
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return str;
}



function showForm(url)
{
	document.getElementById("formview").innerHTML="Processing form...";
	var xmlHttp;
	//var url="form_view.php?formID=4&process=update";
	url=url+"&sid="+Math.random();
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	alert ("Your browser does not support AJAX!");
	return;
	} 

	xmlHttp.onreadystatechange=function()
		{
			if(xmlHttp.readyState==4)
			{
				document.getElementById("formview").innerHTML=xmlHttp.responseText;
				
			}
		}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 

function editForm(fields, url) {
	var fieldNames = new Array();
	fieldNames = fields.split(",");
	for (var i = 0; i < fieldNames.length; i++){
		var fieldItem = document.getElementById(fieldNames[i]).value;
		url = url + "&" + fieldNames[i] + "=" + fieldItem;
	}
	showForm(url);
}





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

function getcaptcha2(url) {
	$.ajax({
		url: url,
		type: 'POST',
		success: function (resp) {
			document.getElementById("captcha").innerHTML = resp;
		},
		error: function(e) {
			alert('Captcha Error: '+e);
		}  
	});
}

