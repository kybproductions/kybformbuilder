<?php
/*******************************************************************************
* SHAREDCLASSES                                                                *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    2010-26-10                                                          *
* Author:  KYB Productions													   *
* Copyright 2010 KYB Productions											   *
*******************************************************************************/

class kybformSharedProcesses {
	var $TDClass = "contactFieldwht";
	var	$TDAlign = "center";
	var	$AClass = "link";
	var $TDWidth;
	var $TDHeight;
	var $TDBackground;
	var $TDBGcolor;
	var $TDColspan;
	var $CellClass;
	var $CellMouseOverBackgroundColor;
	var $CellLink;
	var $CellMouseOutBackgroundColor;
	var $CellValign;
	var $CellAlign;
	var $activateOptions = array('1'=>'Activated', '0'=>'Deactivated');
	var $completeOptions = array('1'=>'Complete', '0'=>'Incomplete');
	var $bitOptions = array('1'=>'Yes', '0'=>'No');
	var $bitWordOptions = array('Y'=>'Yes', 'N'=>'No');
	var $pagename;
	var $currpage;
	var $javaupload;
	var $sharehtml;
	var $pluginURL;
	var $adminURL;
	var $plugin_dir;
	var $basedir;
	var $company;
	var $admin_email;
	var $server;
	var $nafa_server;
	var $db;
	var $dbuser;
	var $dbpass;
	var $ajaxUrl;
	var $storePageID;
	var $catPages;
	var $webdir;
	var $paypal_logo;
	var $pubDir;
	var $privateDir;
	var $wpdb;
	var $themeUrl;
	var $urlInfo;
	var $current_user;
	private static $this_quicktags = true;
	private static $has_tinymce = true;
	private static $this_tinymce = true;
	private static $editor_buttons_css = true;
	private static $has_medialib = false;

	public function __construct()
	{
		global $wpdb;
		$basedir = $_SERVER['DOCUMENT_ROOT'];
		$plugin_dir = WP_PLUGIN_DIR;
		$this->pagename = __FILE__;
		$this->javaupload = "$basedir/JavaPowUpload/pagefunctions.php";
		$this->sharehtml = site_url() . "/";
		$this->templateURL = get_bloginfo('template_url');
		$this->themeUrl = get_bloginfo('template_url');
		$this->storePageID = 4;
		$this->catPages = get_pages( array( 'child_of' => $this->storePageID, 'sort_column' => 'menu_order', 'sort_order' => 'asc' ) );
		if (isset($_REQUEST['page'])) {
			$shortcut = $this->cleanString($_REQUEST['page']);
			$this->adminURL = admin_url() . "admin.php?page=$shortcut";
		} else {
			$this->adminURL = admin_url() . "admin.php?page=kybstore/store_plugin.php";
		}
		$this->pluginURL = WP_PLUGIN_URL;
		$this->plugin_dir = $plugin_dir;
		$this->basedir = $basedir;
		$this->pubDir = WP_CONTENT_DIR . "/uploads/public";
		$this->privateDir = WP_CONTENT_DIR . "/uploads/private";
		$this->server = str_replace("www.","",$_SERVER['HTTP_HOST']);
		$this->company = get_bloginfo('name');
		$this->paypal_logo = $this->templateURL . "/images/paypal_logo.jpg";
		$this->admin_email = get_bloginfo('admin_email');
		$urlsegments = explode('/', parse_url($plugin_dir, PHP_URL_PATH));
		
		$folder = $urlsegments[12];
		$this->ajaxUrl = "/kybproductions/wp-content/plugins/" . $folder;
		if ($_SERVER['REDIRECT_URL'] != "") {
			$this->currpage = $_SERVER['REDIRECT_URL'];
		} else {
			$this->currpage = $_SERVER['PHP_SELF'];
		}
		$this->webdir = TEMPLATEPATH;
		$this->wpdb = $wpdb;
		$currURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$urlInfo = parse_url($currURL, PHP_URL_PATH);
		$this->urlInfo = explode("/", $urlInfo);
		$current_user = wp_get_current_user();
		
	}

	function adminprocess($sql, $line = "0") {
		$result = mssql_query($sql) or die('Unable to perform query on line ' . $line);
		return $result;
	}
	
	function showPage ($template, $content, $return = false) {
		$page = new formFillPage ($template);
		$page->replace_tags($content);
		if ($return) {
			$output = $page->viewoutput();
			return $output;
		} else {
			$page->output();
		}
	}

	function table_exist($table){
		$result = mysql_query("SHOW TABLES LIKE '$table'");
		$tableExists = mysql_num_rows($result);
		return $tableExists;
	}

	 function outputInfo($info) {
		echo $info;
	}

	function cmsHeader($area, $company) {
		$tdInfo1 = array("<strong style=\"font-size: 18px;\">$company CMS $area Management</strong>");
		$tdInfo2 = array("CMS $area: <strong>$company</strong>");
		$num = 1;
		$height = "30";
		
		$form = $this->BeginTable('100%','border-bottom: 2px solid #464646;');
		$form .= $this->buildRow($tdInfo1, '',$height, $num,'right','#ffffff','','','','','top');
		$form .= $this->EndTable();

		$form .= $this->BeginTable('100%','border-bottom: 2px solid #464646;');
		$form .= $this->buildRow($tdInfo2, '',$height, $num,'left','#ffffff','','','','padding: 5px;','top');
		$form .= $this->EndTable();
		$form .= "<br/>";
		
		$this->outputInfo($form);
	}

	
	function buildRow($content, $height, $num) {
		$table .= $this->BeginTR($this->CellClass, $this->CellMouseOverBackgroundColor, $this->CellLink, $this->CellMouseOutBackgroundColor, $this->CellValign, $this->CellAlign);
		for ($t = 0;$t < $num;$t++) {
			$table .= $this->BeginTD($this->TDWidth, $this->TDHeight, $this->TDAlign, $this->TDBackground, $this->TDBGcolor, $this->TDClass, $this->TDColspan);		
			$table .= $this->TDwithContent($content[$t], $height);
			$table .= $this->EndTD();					
		}
		$table .= $this->EndTR();	

		return $table;
	}

	function buildRow2($content, $widthset, $height, $num, $alignset, $bgcolor, $colspan, $class, $background, $style,$valign) {
		$table .= $this->BeginTR($this->CellClass, $this->CellMouseOverBackgroundColor, $this->CellLink, $this->CellMouseOutBackgroundColor, $this->CellValign, $this->CellAlign);
		for ($t = 0;$t < $num;$t++) {
			is_array($alignset) ? $align = $alignset[$t] : $align = $alignset;
			is_array($widthset) ? $width = $widthset[$t] : $width = $widthset;
			$table .= $this->BeginTD($width, $height, $align, $background, $bgcolor, $class, $colspan, $style,$valign);				
			$table .= $this->TDwithContent($content[$t], $height);
			$table .= $this->EndTD();					
		}
		$table .= $this->EndTR();	

		return $table;
	}

	function buildMultipleRow($rows, $tdInfo, $num) {
		for ($r=0; $r <$rows; $r++) {
			$info = array($tdInfo[$r]);
			$table .= $this->buildRow($info, $height, $num);
		}
		return $table;
	}

	

	function buildHeaders($content, $height, $width, $num, $class="", $align="", $background="", $bgcolor="", $colspan="") {
		$table .= $this->BeginTR($this->CellClass, $this->CellMouseOverBackgroundColor, $this->CellLink, $this->CellMouseOutBackgroundColor, $this->CellValign, $this->CellAlign);
		for ($t = 0;$t < $num;$t++) {
			$table .= $this->BeginTH($width, $height, $align, $background, $bgcolor, $class, $colspan);		
			$table .= $this->THwithContent($content[$t], $height);
			$table .= $this->EndTH();					
		}
		$table .= $this->EndTR();	

		return $table;
	}

	function buildFormTag ($name, $action, $method, $other) {
		$formtag = "<form name=\"$name\" action=\"$action\" method=\"$method\" $other>";
		return $formtag;
	}

	function buildStatusOptions ($optionSelected) {
		foreach ($this->activateOptions as $key => $value) {
			$optionSelected == $key ? $options .= "<option value=\"$key\" selected>$value</option>" : $options .= "<option value=\"$key\">$value</option>";
		}
		return $options;
	}

	function buildBitOptions ($optionSelected) {
	   $options = "";
		foreach($this->bitOptions as $key => $value) {
			
			if ($key == $optionSelected) {
				$options .= "<option value=\"$key\" selected>$value</option>";
			} else {
				$options .= "<option value=\"$key\">$value</option>";
			}
		}
		return $options;
   }

	function buildBitWordOptions ($optionSelected) {
	   $options = "";
		foreach($this->bitWordOptions as $key => $value) {
			
			if ($key == $optionSelected) {
				$options .= "<option value=\"$key\" selected=\"selected\">$value</option>";
			} else {
				$options .= "<option value=\"$key\">$value</option>";
			}
		}
		return $options;
   }

	function buildRadioOptions ($optionSelected, $optionList, $name, $spaced, $line) {
		foreach ($optionList as $key => $value) {
			$optionSelected == $key ? $options .= "<input type=\"radio\" name=\"$name\" id=\"$name\" value=\"$key\" checked>&nbsp;$value" : $options .= "<input type=\"radio\" name=\"$name\" id=\"$name\" value=\"$key\">&nbsp;$value";
			if ($spaced) {
				$options .= "&nbsp;&nbsp;";
			}

			if ($line) {
				$options .= "<br>";
			}
		}
		return $options;
	}

	function buildRadioOptions2 ($optionSelected, $optionList, $name, $spaced, $line, $values) {
		$cnt = 0;
		
		foreach ($optionList as $key => $value) {
			$v = trim(strip_tags($values[$cnt]));
			
			if (trim($optionSelected) == trim($v)) {
				$options .= "<div style=\"float:left;display:inline-block;width:25px;\"><input type=\"radio\" name=\"$name\" id=\"$name\" value=\"$v\" checked=\"checked\"/></div><div style=\"float:left;display:inline-block;padding-top:5px;\">$value</div>";
				if (!$spaced) {
					$options .= "<div style=\"clear:both;\"></div>";
				}
			} else {
				$key == 0 ? $top = "2px" : $top = "5px;";
				$options .= "<div style=\"float:left;display:inline-block;width:25px;\"><input type=\"radio\" name=\"$name\" id=\"$name\" value=\"$v\" /></div><div style=\"float:left;display:inline-block;padding-top:$top;\">$value</div>";
				if (!$spaced) {
					$options .= "<div style=\"clear:both;\"></div>";
				}
			}
			$cnt++;
		}
		return $options;
	}

	function buildOptions ($optionsList, $optionSelected, $fieldValue, $fieldName, $fromdb,$single = 0, $showselect = true, $table = "", $sql = "") {
		$wpdb = $this->wpdb;
		$options = "";
		if ($showselect) {
			$options .= "<option value=\"\">-- Please select --</option>";
		}
		if ($fromdb) {
			if (is_array($optionsList)) {
				while (list( , $ops) = each($optionsList)) { 
					if (is_array($optionSelected)) {
						$key = (int)$ops[$fieldValue];
						$value = trim($ops[$fieldName]);
						$value = str_replace("\"", "", $value);
						if (in_array($key,$optionSelected)) {
							$options .= "<option value=\"$key\" selected>$value</option>";
						} else {
							$options .= "<option value=\"$key\">$value</option>";
						}
					} else {
						if ($optionSelected != "" && (int)$optionSelected == (int)$ops[$fieldValue]) { 
							$options .= "<option value=\"{$ops[$fieldValue]}\" selected>{$ops[$fieldName]}</option>";
						} else {
							$options .= "<option value=\"{$ops[$fieldValue]}\">{$ops[$fieldName]}</option>";
						}
					}
				}
			} else {
				$result = $wpdb->get_results($sql);
				if (count($result) != 0) {
					foreach ($result as $row) {
						if (is_array($optionSelected)) {
							if (in_array($row->$fieldValue,$optionSelected)) {
								$options .= "<option value=\"{$row->$fieldValue}\" selected>{$row->$fieldName}</option>";
							} else {
								$options .= "<option value=\"{$row->$fieldValue}\">{$row->$fieldName}</option>";
							}
						} else {

							if ($optionSelected != "" && $optionSelected == $row->$fieldValue) { 
								$options .= "<option value=\"{$row->$fieldValue}\" selected>{$row->$fieldName}</option>";
							} else {
								$options .= "<option value=\"{$row->$fieldValue}\">{$row->$fieldName}</option>";
							}	
						}
					}
				}
			}
		} else {
			
			if (is_array($optionsList)) {
				if ($single == 0) {
					foreach($optionsList as $key => $value) {
						if (is_array($optionSelected)) {
							if (is_array($value)) {
								$itemTitle =  $value[$fieldValue];
								$itemValue =  $value[$fieldName];
								if (in_array($itemValue,$optionSelected)) {
									$options .= "<option value=\"$itemValue\" selected>$itemTitle</option>";
								} else {
									$options .= "<option value=\"$itemValue\">$itemTitle</option>";
								}
							} else {
								if (in_array($key,$optionSelected)) {
									$options .= "<option value=\"$key\" selected>$value</option>";
								} else {
									$options .= "<option value=\"$key\">$value</option>";
								}
							}
						} else {
							if ($key == $optionSelected) {
								$options .= "<option value=\"$key\" selected>$value</option>";
							} else {
								$options .= "<option value=\"$key\">$value</option>";
							}
						}
					}
				} else {
					if (is_array($optionSelected)) {
						for($i=0;$i<count($optionsList);$i++) {
							if (in_array($optionsList[$i],$optionSelected)) {
								$options .= "<option value=\"{$fieldValue[$i]}\"selected>{$fieldName[$i]}</option>";
							} else {
								$options .= "<option value=\"{$fieldValue[$i]}\">{$fieldName[$i]}</option>";
							}
						}
					} else {
						for($i=0;$i<count($optionsList);$i++) {
							if ($optionsList[$i] == $optionSelected) {
								$options .= "<option value=\"{$fieldValue[$i]}\" selected>{$fieldName[$i]}</option>";
							} else {
								$options .= "<option value=\"{$fieldValue[$i]}\">{$fieldName[$i]}</option>";
							}
						}
					}
				}

			}
		}
		return $options;
	}

	function build_dboptions($sql, $fieldName, $fieldValue, $optionSelected, $showselect = true) {
		$wpdb = $this->wpdb;
		$options = "";
		$result = $wpdb->get_results($sql);
		if (count($result) != 0) {
			foreach ($result as $row) {
				$key = $row->$fieldValue;
				$optArray [$key] = $row->$fieldName;
			}
			$options = $this->buildOptions ($optArray, $optionSelected, '', '', false, 0, $showselect, '','');
		}
		return $options;
	}

	function buildyearoptions($selyear, $startyear = '', $endyear = '') {
		$selyear == "" ? $selyear = date('Y') :'';
		$selyear == 0 ? $selyear = date('Y') :'';
		$startyear != "" ? $ystart = $startyear : $ystart = bcsub($selyear,7);
		$endyear != "" ? $yend = $endyear : $yend = bcadd($selyear,10);
		$options = "";
		for ($i=$ystart;$i<=$yend;$i++) {
			if ($i == $selyear) {
				$options .= "<option value=\"$i\" selected>$i</option>";
			} else {
				$options .= "<option value=\"$i\">$i</option>";
			}
		}
		return $options;
	}


	function BeginTR($CellClass, $CellMouseOverBackgroundColor, $CellLink, $CellMouseOutBackgroundColor, $CellValign, $CellAlign)
	{
		$table = "<tr ";
		
		if ($CellClass)
		{
			$table .= "class=\"$CellClass\" ";
		}
		
		if ($CellValign)
		{
			$table .= "valign=\"$CellValign\" ";
		}

		if ($CellAlign)
		{
			$table .= "align=\"$CellAlign\" ";
		}
		
		if ($CellMouseOverBackgroundColor)
		{
			$table .= "onMouseOver=\"this.style.backgroundColor='$CellMouseOverBackgroundColor'; ";

			if ($CellLink)
			{
				$table .= "this.style.cursor='hand'; ";
			}
			
			$table .= "\" ";
			
			if ($CellLink)
			{
				$table .= "onclick=\"location.href='$CellLink'\" ";
			}
			
			$table .= "onMouseOut=\"this.style.backgroundColor='$CellMouseOutBackgroundColor'\" ";
		}
		
		$table .= ">";
		
		return $table;
	}

	function EndTR()
	{
		$table = "</tr>";
		return $table;
	}

	function BeginTD($TDWidth, $TDHeight, $TDAlign, $TDBackground, $TDBGcolor, $TDClass, $TDColspan, $TDStyle="", $TDValign="")
	{
		$table = "<td ";
		
		if ($TDClass)
		{
			$table .= "class=\"$TDClass\" ";
		}
		
		if ($TDWidth)
		{
			$table .= "width =\"$TDWidth\" ";
		}
		
		if ($TDHeight)
		{
			$table .= "height=\"$TDHeight\" ";
		}

		if ($TDValign)
		{
			$table .= "valign=\"$TDValign\" ";
		}
		
		if ($TDValign)
		{
			$table .= "style=\"verticle-align:$TDValign\" ";
		}

		
		if ($TDAlign)
		{
			$table .= "align=\"$TDAlign\" ";
		}

		if ($TDStyle)
		{
			$table .= "style=\"$TDStyle\" ";
		}
		
		if ($TDBackground)
		{
			$table .= "background=\"$TDBackground\" ";
		}
		
		if ($TDBGcolor)
		{
			$table .= "bgcolor=\"$TDBGcolor\" ";
		}
		
		if ($TDColspan)
		{
			$table .= "colspan=\"$TDColspan\" ";
		}

		$table .= ">";
		
		return $table;
	}

	function TDwithContent($content, $height)
	{
		$table .= $content;
	
		return $table;
	}
	
	function EndTD()
	{
		$table = "</td>";
		return $table;
	}

	function BeginTH($TDWidth, $TDHeight, $TDAlign, $TDBackground, $TDBGcolor, $TDClass, $TDColspan)
	{
		$table = "<th ";
		
		if ($TDClass)
		{
			$table .= "class=\"$TDClass\" ";
		}
		
		if ($TDWidth)
		{
			$table .= "width =\"$TDWidth\" ";
		}
		
		if ($TDHeight)
		{
			$table .= "height=\"$TDHeight\" ";
		}
		
		if ($TDAlign)
		{
			$table .= "valign=\"$TDAlign\" ";
		}
		
		if ($TDBackground)
		{
			$table .= "background=\"$TDBackground\" ";
		}
		
		if ($TDBGcolor)
		{
			$table .= "bgcolor=\"$TDBGcolor\" ";
		}
		//======== Add custom background color//
		$table .= "style='background-color:#c0d3e8;'";
		
		if ($TDColspan)
		{
			$table .= "colspan=\"$TDColspan\" ";
		}

		$table .= ">";
		
		return $table;
	}

	function THwithContent($content, $height)
	{
		if ($content == true)
		{
			$table .= $content;
		}
		return $table;
	}
	
	function EndTH()
	{
		$table = "</th>";
		return $table;
	}

	function BeginTable($width, $border="0", $padding="0", $spacing="0", $height="") {
		$table = "<table cellpadding=\"$padding\" cellspacing=\"$spacing\" width=\"$width\" height=\"$height\" border=\"$border\">";
		return $table;
	}

	function EndTable() {
		$table = "</table>";
		return $table;
	}

	function dateFromdb($string) {
		//Get pub date
		$string = substr($string,0,strlen($string) - 9);
		$date_create = explode('-', $string);
		
		$dbdate = $date_create[1] . "/" . $date_create[2] . "/" . $date_create[0];
		if ($dbdate == "//") {
			$dbdate = "";
		}
		return $dbdate;
	}

	function cleanString($string) {
		$cleaned = mysql_real_escape_string(stripslashes(strip_tags($string)));
		return $cleaned;
	}

	function cleanHTML($string) {
		$cleaned = mysql_real_escape_string(stripslashes($string));
		return $cleaned;
	}

	function checkRequest($var, $checkURL = false, $urlCnt = 0) {
		$value = "";
		if (isset($_POST[$var]) || isset($_GET[$var])) {
			$_POST[$var] != "" ? $value = $this->cleanString($_POST[$var]) : $value = $this->cleanString($_GET[$var]);
		}

		if ($checkURL) {
			$value = $this->urlInfo[$urlCnt];
		}

		$value=$this->cleanString($value);
		return $value;		 
	}

	function delete_directory($dirname) {
		if (is_dir($dirname)){
		$dir_handle = opendir($dirname);
		}
						
		if (!$dir_handle){
			return false;
		}
						
			while($file = readdir($dir_handle)) {
				if ($file != "." && $file != "..") {
					if (!is_dir($dirname."/".$file)) {
					unlink($dirname."/".$file);
					}
				} 
			}
		closedir($dir_handle);
		rmdir($dirname);
		return true;
	} 

	//========= Date and Time functions ========//
	function ReformatDatedb($currDate){
		if (strlen($currDate) > 10) {
			$currDate = substr($currDate, 0, strlen($currDate) - 9);
		}
		$createdate = explode('-', $currDate);
		$newDate = $createdate[1] . "/" . $createdate[2] . "/" . $createdate[0];
		$newDate == "//" ? $newDate = '' : '';
		return $newDate;
	}

	function ReformatDateform($currDate) {
		$createdate = explode('/', $currDate);
		$newDate = $createdate[2] . "-" . $createdate[0] . "-" . $createdate[1];
		return $newDate;
	}

	

	function date_to_str ( $indate )
	{
		
		if ( strlen ( $indate ) == 0 )
		{
			$indate = date ( "Ymd" );
		} else {
			$createdate = explode('-', $indate);
			$indate = $createdate[0] . $createdate[1] . $createdate[2];
		}
		$y = (int) ( $indate / 10000 );
		$m = (int) ( $indate / 100 ) % 100;
		$d = $indate % 100;
		$date = mktime ( 3, 0, 0, $m, $d, $y );
		$wday = strftime ( "%w", $date );
		return sprintf ( "%s %d, %04d", $this->month_name ( $m ), $d, $y );
	}

	function date_to_jd ($indate) {
		if ( strlen ( $indate ) == 0 )
		{
			$indate = date ( "Ymd" );
		} else {
			$createdate = explode('-', $indate);
			$indate = $createdate[0] . $createdate[1] . $createdate[2];
		}
		$y = (int) ( $indate / 10000 );
		$m = (int) ( $indate / 100 ) % 100;
		$d = $indate % 100;
		$jd_date = unixtojd(mktime(0,0,0,$m,$d,$y));
		return $jd_date;
	}

	function time_hour ( $t )
	{
		switch ( $t ) {
		case 1: return ("00");
		case 1: return ("13");
		case 2: return ("14");
		case 3: return ("15");
		case 4: return ("16");
		case 5: return ("17"); // needs to be different than "May"
		case 6: return ("18");
		case 7: return ("19");
		case 8: return ("20");
		case 9: return ("21");
		case 10: return ("22");
		case 11: return ("23");
		case 12: return ("24");
		}
		return "unknown-time($t)";
	}

	function weekday_name ( $w )
	{
		switch ( $w )
		{
			case 0: return ("Sunday");
			case 1: return ("Monday");
			case 2: return ("Tuesday");
			case 3: return ("Wednesday");
			case 4: return ("Thursday");
			case 5: return ("Friday");
			case 6: return ("Saturday");
		}
		return "unknown-weekday($w)";
	}

	function month_name ( $m )
	{
		switch ( $m ) {
		case 1: return ("January");
		case 2: return ("February");
		case 3: return ("March");
		case 4: return ("April");
		case 5: return ("May"); // needs to be different than "May"
		case 6: return ("June");
		case 7: return ("July");
		case 8: return ("August");
		case 9: return ("September");
		case 10: return ("October");
		case 11: return ("November");
		case 12: return ("December");
		}
		return "";
	}

	function nth_day_of_month($nbr, $day, $mon, $year) 
	{ 
	   /** 
		* int nth_day_of_month(int $nbr, str $day, int $mon, int $year) 
		*   $nbr = nth weekday to find 
		*   $day = full name of weekday, e.g. "Saturday" 
		*   $mon = month 1 - 12 
		*   $year = year 1970, 2007, etc. 
		* returns UNIX time 
		*/ 
	   
	   $date = mktime(0, 0, 0, $mon, 0, $year); 
	   if($date == 0) 
	   { 
		  user_error(__FUNCTION__."(): Invalid month or year", E_USER_WARNING); 
		  return(FALSE); 
	   } 
	   $day = ucfirst(strtolower($day)); 
	   if(!in_array($day, array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 
			 'Thursday', 'Friday', 'Saturday'))) 
	   { 
		  user_error(__FUNCTION__."(): Invalid day", E_USER_WARNING); 
		  return(FALSE); 
	   } 
	   for($week = 1; $week <= $nbr; $week++) 
	   { 
		  $date = strtotime("next $day", $date); 
	   } 
	   return($date); 
	} 

	//======== End Date and Time functions ========//

	function buildDateOptions($no, $start, $type, $choice) {
		$view = true;
		for ($i=$start;$i<=$no;$i++) {
			switch ($type) {
				case "month":
					$value = $this->month_name($i);
				break;
				case "numberoptions":
					strlen($i) < 2 ? $value = "0" . $i : $value = $i;
				break;
				case "minutes":
					if ($i % 5 == 0) {
						strlen($i) < 2 ? $value = "0" . $i : $value = $i;
						$choice == $i ? $options .= "<option value=\"$i\" selected>$value</option>" : $options .= "<option value=\"$i\">$value</option>";
					}
					$view = false;
				break;
				default:
					$value = $i;
				break;
			}
			if ($view) {
				$choice == $i ? $options .= "<option value=\"$i\" selected>$value</option>" : $options .= "<option value=\"$i\">$value</option>";
			}
		}
		return $options;
	}

	function DrawLink($name, $class, $title, $link, $target, $pass_more_variables) {
		;
		// Target can be _blank, _top or _self
		$drawlink = "";
	
		$drawlink .= "<a href=\"$link\" class=\"$class\" title=\"$title\" 
		onMouseOver=\"self.status='$title'; 
		return true\" onMouseOut=\"self.status=''\"";
		
		if ($target) {
			$drawlink .= " target=\"$target\"";
		}
		
		$drawlink .= " $pass_more_variables>$name</a>";
		return $drawlink;
	}

	function printNextPrev($hr,$palign,$section1,$section2,$section1name,
	$jumptopage,$Nav1Class,$Nav2Class,$XtraQuery)
	{
		global $CurrentRowCount, $TotalRowCount;
		global $pagenum, $pages, $showNumbers, $limit, $offset, $page;
		$printlinks = "";

				
		// Only Print if Page > 1
		if ($pages > 1) {
			$BeginningSet = $offset + 1;
	
			if ($pagenum == 1 && $pages > 1)
			{
				$EndingSet = $limit;
	
			} elseif ($pagenum == $pages) {
	
				$EndingSet = $TotalRowCount;
	
			} else {
				$EndingSet = $limit + $offset;
			}
	
			if ($hr)
			{
				$printlinks .= "<hr color=\"#eeeeee\">";
				$test = "Hello";
			}
	
			$printlinks .= "<p align=\"$palign\" style=\"font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;\">";
			
			if ($section1 == true)
			{
				// Extensions 11 - 20 of 39
				//                        									$TotalRowCount
				$printlinks .= "$section1name $BeginningSet-$EndingSet of $TotalRowCount &nbsp;|&nbsp; ";
			}
	
			// Calculate the page number of the previous page and the next 
			$pPage = $pagenum - 1;  
			$nPage = $pagenum + 1; 
			
		 
			if ($pagenum != 1)
			{ 
				// We are not on the first page, so print the << Prev Page ink. 
				$printlinks .= DrawLink('<strong>&#171; Previous</strong>',"$Nav1Class",'Previous Page',
				"$PHP_SELF?pg=$pPage&$XtraQuery",'','');
	
				if ($section2)
				{
					$printlinks .= " &bull; ";
				}
	
			}
			
			if ($section2)
			{
				$printlinks .= " Page $pagenum of $pages ";
			} else {
				$printlinks .= " | ";
			}
			
			if ($pagenum != $pages)
			{ 
				if ($section2)
				{
					$printlinks .= " &bull; ";
				}
				// We are not on the last page yet, so print the Next Page >> link.
				$printlinks .= DrawLink('<strong>Next &#187;</strong>',"$Nav1Class",'Next Page',
				"$PHP_SELF?pg=$nPage&$XtraQuery",'',''); 
			}
			
			if ($section2)
			{
				$printlinks .= "<br>";
			}
	
			if ($jumptopage)
			{
				$printlinks .= "Jump to Page: ";
	
				for ($i = 1; $i <= $pages; $i += $inc)
				{  
					if ($page == $i)
					{
						// We are on the current page in the loop, don't link it. 
						$printlinks .= " <span style=\"color: #FF0000\"><strong>$i</strong></span> ";
	
					} else {
	
						// Show a link to the current page in the loop. 
						$newPage=$i;
						$printlinks .= " ";
						$printlinks .= DrawLink("$i","$Nav2Class","Goto Page $i","$PHP_SELF?pg=$newPage&$XtraQuery",'','');
						$printlinks .= " ";
					} 
	
					// Here is where we compensate for very large result sets. 
					// If there are more than 10 results, we will only show links 
					// to individual pages in multiples of five (1, 5, 10, 15, etc). 
					// If there are 10 or fewer pages, show a link for each page 
					// (1, 2, 3, 4, etc). 
				 
					if ($pages > 10 && $i == 1) { 
						$inc = 4; 
					} elseif ($pages > 10) { 
						$inc = 5; 
					} else { 
						$inc = 1; 
					}
				} // end for
			} // end if jump to page
			
			$printlinks .= "</p>";
		} // end print if pages > 1

		return $printlinks;
   }

   function showNextPrev ($offset, $pagenum, $pages, $limit, $TotalRowCount, $url = "", $keyword="", $process="") {
		$BeginningSet = $offset + 1;
		if ($pagenum == 1 && $pages > 1)
			{
			$EndingSet = $limit;
		} elseif ($pagenum == $pages) {
			$EndingSet = $TotalRowCount;
		} else {
			$EndingSet = $limit + $offset;
		}

		/*if ($url == "") {
			$url = "edit.php?post_type=page&page=DocManager/doc_plugin.php";
		}*/

		// Calculate the page number of the previous page and the next 
		$pPage = $pagenum - 1;  
		$nPage = $pagenum + 1; 

		if ($pagenum == 1) {
			$firstpage = "first-page disabled";
			$previouspage = "prev-page disabled";
		} else {
			$firstpage = "first-page";
			$previouspage = "prev-page";
		}

		if ($pages <= 1) {
			$nextclass = "next-page disabled";
			$lastclass = "last-page disabled";
		} else {
			$nextclass = "next-page";
			$lastclass = "last-page";
		}

		if (isset($_REQUEST['filtertype'])) {
			$filterType = $this->cleanString($_REQUEST['filtertype']);
			$firstPage = "$url&process=$process&filtertype=$filterType&keywords=$keyword";
			$previousPage = "$url&p=$pPage&process=$process&filtertype=$filterType&keywords=$keyword";
			$nextPage = "$url&p=$nPage&process=$process&filtertype=$filterType&keywords=$keyword";
			$lastPage = "$url&p=$pages&process=$process&filtertype=$filterType&keywords=$keyword";
		} else {
			$firstPage = "$url";
			$previousPage = "$url&p=$pPage";
			$nextPage = "$url&p=$nPage";
			$lastPage = "$url&p=$pages";
		}

		$pageturns = <<<PAGETURN
				<a class="$firstpage" title="Go to the first page" href="$firstPage">&laquo;</a>
				<a class="$previouspage" title="Go to the previous page" href="$previousPage">&lsaquo;</a>
				<span class="paging-input"><input class="current-page" title="Current page" name="paged" value="$pagenum" size="1" type="text"> of <span class="total-pages">$pages</span></span>
				<a class="$nextclass" title="Go to the next page" href="$nextPage">&rsaquo;</a>
				<a class="$lastclass" title="Go to the last page" href="$lastPage">&raquo;</a>
PAGETURN;
		

		return $pageturns;
	
	}

   function printNexPrev3($totalSubmissions, $pages, $pagenum, $offset, $limit, $url) {
	   $navigation = "";
	   
	   if ($pages > 1) {
		   $previousClass = "prev-page disabled";
			$nextClass = "next-page disabled";
			$firstClass = "first-page disabled";
			$firstAtt = "";
			$nextAtt = "";
			$previousAtt = "";
			$BeginningSet = $offset + 1;
	
			if ($pagenum == 1 && $pages > 1)
			{
				$EndingSet = $limit;
	
			} elseif ($pagenum == $pages) {
	
				$EndingSet = $TotalRowCount;
	
			} else {
				$EndingSet = $limit + $offset;
			}

			$pPage = $pagenum - 1;  
			$nPage = $pagenum + 1; 

			if (($pagenum - 1) < 0) {
				$pPage = 1;
			}
			

			if ($pagenum != 1)
			{ 
				// We are not on the first page, so print the << Prev Page ink.
				$previousClass = "prev-page";
				$previousurl = "$url&pagenum=$pPage";
				$previousAtt = "title=\"Go to the previous page\" href=\"$previousurl\"";
				$firstClass = "first-page";
				$firstAtt = "title=\"Go to the first page\" href=\"$url\"";
			}

			

			if ($pagenum != $pages)
			{ 
				// We are not on the last page yet, so print the Next Page >> link. 
				$nextClass = "next-page";
				$nexturl = "$url&pagenum=$nPage";
				$nextAtt = "title=\"Go to the next page\" href=\"$nexturl\"";
			}


	   $navigation = <<<NAV
		   <div class="tablenav-pages"><span class="displaying-num">$totalSubmissions items</span>
			<span class="pagination-links"><a class="$firstClass" $firstAtt>&#171;</a>
			<a class="$previousClass" $previousAtt>&lsaquo;</a>
			<span class="paging-input"><input class="current-page" title="Current page" name="paged" value="$pagenum" size="1" type="text"> of <span class="total-pages">$pages</span></span>
			<a class="$nextClass" $nextAtt>&rsaquo;</a>
			<a class="last-page" title="Go to the last page" href="$url&pagenum=$pages">&#187;</a></span></div>
NAV;
	   }
	   return $navigation;
   }

     function printNextPrev2($hr,$palign,$section1,$section2,$section1name, $jumptopage,$Nav1Class,$Nav2Class,$XtraQuery,$pages, $offset, $pagenum, $TotalRowCount, $limit, $javascript="", $script="", $operator = "")
	{
		//global $CurrentRowCount, $TotalRowCount;
		//global $pagenum, $pages, $showNumbers, $limit, $offset, $page;
		$printlinks = "";
		if ($operator == "") {
			//$operator = "?";
		}
		
				
		// Only Print if Page > 1
		if ($pages > 1) {
			$BeginningSet = $offset + 1;
	
			if ($pagenum == 1 && $pages > 1)
			{
				$EndingSet = $limit;
	
			} elseif ($pagenum == $pages) {
	
				$EndingSet = $TotalRowCount;
	
			} else {
				$EndingSet = $limit + $offset;
			}
	
			if ($hr)
			{
				$printlinks .= "<hr color=\"#eeeeee\">";
				$test = "Hello";
			}
			

			$printlinks .= "<div style=\"margin-right:30px;\" class=\"pagenav\">";
			
			if ($section1 == true)
			{
				// Extensions 11 - 20 of 39
				//                        									$TotalRowCount
				$printlinks .= "$section1name $BeginningSet-$EndingSet of $TotalRowCount &nbsp;|&nbsp; ";
			}
	
			// Calculate the page number of the previous page and the next 
			$pPage = $pagenum - 1;  
			$nPage = $pagenum + 1; 
			
		 
			if ($pagenum != 1)
			{ 
				// We are not on the first page, so print the << Prev Page ink. 
				$printlinks .= $this->DrawLink('<strong>&#171; Previous</strong>',"$Nav1Class",'Previous Page',
				"$XtraQuery" . $operator . "$pPage",'','', $javascript, $script);
	
				if ($section2)
				{
					$printlinks .= " &bull; ";
				}
	
			}
			
			if ($section2)
			{
				$printlinks .= " Page $pagenum of $pages ";
			} else {
				$printlinks .= " | ";
			}
			
			if ($pagenum != $pages)
			{ 
				if ($section2)
				{
					$printlinks .= " &bull; ";
				}
				// We are not on the last page yet, so print the Next Page >> link.
				$printlinks .= $this->DrawLink('<strong>Next &#187;</strong>',"$Nav1Class",'Next Page',
				"$XtraQuery" . $operator . "$nPage",'','', $javascript, $script); 
			}
			
			if ($section2)
			{
				$printlinks .= "<br>";
			}
	
			if ($jumptopage)
			{
				$printlinks .= "[";

				/*<div style="margin-right:30px;" class="pagenav">&nbsp;[<a class="pagenav" href="#">1</a>&nbsp;|&nbsp;<a href="#" class="pagenav">2</a>&nbsp;|&nbsp;<a href="#" class="pagenav">3</a>&nbsp;|&nbsp;<a href="#" class="pagenav">4</a>&nbsp;|&nbsp;<a href="#" class="pagenav">Next</a>]</div>*/
	
				for ($i = 1; $i <= $pages; $i += $inc)
				{  
					if ($pagenum == $i)
					{
						// We are on the current page in the loop, don't link it. 
						$printlinks .= " <span style=\"color: #FF0000\"><strong>$i</strong></span> ";
	
					} else {
						
						// Show a link to the current page in the loop. 
						$newPage=$i;
						$printlinks .= " ";
						$printlinks .= $this->DrawLink("$i","$Nav2Class","Goto Page $i","$XtraQuery" . "$newPage",'','', $javascript, $script);
						$printlinks .= " ";
					} 
	
					// Here is where we compensate for very large result sets. 
					// If there are more than 10 results, we will only show links 
					// to individual pages in multiples of five (1, 5, 10, 15, etc). 
					// If there are 10 or fewer pages, show a link for each page 
					// (1, 2, 3, 4, etc). 
				 
					if ($pages > 10 && $i == 1) { 
						$inc = 4; 
					} elseif ($pages > 10) { 
						$inc = 5; 
					} else { 
						$inc = 1; 
					}
				} // end for
				$printlinks .= "]";
			} // end if jump to page
			
			$printlinks .= "</div>";
		} // end print if pages > 1
		$test = "Hello";
		return $printlinks;
   }


   function queryDB($sql, $line, $return=false, $returnType = 1, $fields="", $page="", $company="") {
		global $wpdb;
		$company = $this->company;
		if($return) {
			$result = mysql_query($sql);
			if ($result !== false && mysql_num_rows($result) != 0) {
				while ($value = mysql_fetch_assoc($result)) {
					switch($returnType) {
						case 1:
							if (is_array($value)) {
								foreach($value as $key => $a) {
									$values[$key] = $a;
								}
							}
						break;
						case 2:
							if (is_array($fields)) {
								foreach($fields as $k => $v) {
									$values[] = $value[$v];
								}
							} else {
								//$values = $wpdb->get_var( $wpdb->prepare($sql));
								$values = $value[$fields];
							}
						break;
						case 3:
							$values[] = $value;
						break;
					}
				}
			}
			return $values;				
		} else {
			$wpdb->query($sql);
		}
   }

   function notifyWebmaster($message) {
		$adminEmail = $this->admin_email;
		$server = $this->server;
		$company = $this->company;
		$headers = "From: $server" . "\r\n" .
		"Reply-To: $adminEmail" . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
		$to = "webmaster@kybproductions.com";
		$subject = "Online Store Error - $company";
		mail ($to, $subject, $message, $headers);
   }

   function DrawFCKEditor($content,$fieldname) {
	   $oFCKeditor = new FCKeditor($fieldname) ;
		$oFCKeditor->BasePath = "../../fckeditor/";
		$oFCKeditor->Config['SkinPath'] = "../../../fckeditor/editor/skins/silver/" ;
		$oFCKeditor->Value = "$content" ;	
		$fck = $oFCKeditor->CreateHtml('100%') ;

		return $fck;
   }

   
	function processForm($process, $variableList, $table, $id, $outcome, $catID, $conditionList = "", $addedTable = false, $addfieldname = "", $tableToAdd = "", $fieldList = "", $addconditionList = "", $addprocess = "", $arrayKey = "",$lookup = "", $orderTable = false, $orderField = "") {
		
		global $wpdb;
		$sql = "Select * from $table ";
		$sqlPrimary = "SHOW INDEX FROM $table";
		$resultPrimary = $wpdb->get_results($sqlPrimary);
		if (count($resultPrimary) != 0) {
			foreach ($resultPrimary as $p) {
				$primary_column = $p->Column_name;
			}
		}
		$result = mysql_query($sql);
		$i = 0;
		$search = false;
		$multicondition = false;
		$insertfields = "";
		$insertvalues = "";
		switch ($process) {
			case "update":
				$sql = "UPDATE $table SET ";
				$search = true;
			break;
			case "add":
				$sql = "INSERT into $table ";
			break;
			case "delete":
				$sql = "DELETE from $table ";
				$search = true;
			break;
		}

		if (is_array($conditionList)) { $multicondition = true; }

		//==== If deleting it will not send an array in the request so will need to check to see if need to delete other items from other tables ====//
		if ($process == "delete" && $addedTable) {			
			$this->processForm ($addprocess, '', $tableToAdd, $id, '', '', $addconditionList);
			//print_r($conditionList);
			
		}

		if (is_array($conditionList)) {
			foreach ($conditionList as $k => $v) {
				$whereclause .= "$k = $v and ";				
			}
			$whereclause = substr($whereclause, 0 , strlen($whereclause) - 4);
			$whereclause = "where $whereclause";
		}
		
		
		foreach ( $wpdb->get_col( "DESC " . $table, 0 ) as $column_name ) {
		//while ($meta = mysql_fetch_field($result)) {
			
			//$meta = mysql_fetch_field($result, $i);
			//======If not set as multiple field search, then condition is set to primary key========//
			if (!$multicondition && $primary_column == $column_name) {
				$whereField = $column_name;
			}
				
			if (is_array($variableList)) {
			foreach($variableList as $key => $value) {	
				if ($key == $column_name && $primary_column != $column_name) {
					
					$insertfields .= $key . ",";
					
					//========Need to catch if value submitted is an array however if the array is to be submitted to a different table then need to add that info to that table ========//
					if (is_array($value)) {
					
						//==== Check to see if need to add information to a different table ===//
						if ($addedTable && $key==$arrayKey) {
							//======== Check to see if we've landed on the field name that is required to be added to a different table.  Could be more than one field =====//

							//======== Set one value in items in order to get into products table but don't include in array list. =======//
							$items .= $value[0] . ",";
							if (count($addfieldname) > 0) {
								if (in_array($key, $addfieldname)) {
									foreach ($fieldList as $k => $v) {
										if (is_array($v)) {
											foreach ($v as $d => $i) {
												$updateList[$d] = $i;
											}
										}										
										$this->processForm ($addprocess, $updateList, $tableToAdd, $id, '', '', $addconditionList);
										$updateList = "";
									}									
								}
							} else {
								if ($addfieldname == $key) {
									foreach ($fieldList as $k => $v) {
										if (is_array($v)) {
											foreach ($v as $d => $i) {
												$updateList[$d] = $i;
											}
										}										
										$this->processForm ($addprocess, $updateList, $tableToAdd, $id, '', '', $addconditionList);
										$updateList = "";
									}				
								}
							}
						 
						} else {
							$items = "";
							foreach ($value as $k => $v) {
								$items .= $v . ",";
							}
							$value = substr($items, 0, strlen($items) - 1);
							//====== clear any previous items ======//
							$items = "";
						}
					}

					//Get Column type
					$colType = $wpdb->get_var($wpdb->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = %s", $table, $column_name));
					//Replace counters
					$start = '\(';
					$end  = '\)';
					$colType = preg_replace('#('.$start.')(.*)('.$end.')#si', '', $colType);
					
					switch ($colType) {
						case "tinyint":
							if ($value == "" ) {
								$value = 0;
							}

							//Not sure why I have to do this!//
							if (is_array($value) && $addedTable) {
								$value = $value[0];
							}

							if ($search) {
								$sql .= "$key=$value,";
							} else {
								$insertvalues .= "$value,";
							}
						break;
						case "int":
							if ($value == "" ) {
								$value = 0;
							}

							//Not sure why I have to do this!//
							if (is_array($value) && $addedTable) {
								$value = $value[0];
							}

							if ($search) {
								$sql .= "$key=$value,";
							} else {
								$insertvalues .= "$value,";
							}
						break;
						case "float":
							if ($value == "" ) {
								$value = 0;
							}

							//Not sure why I have to do this!//
							if (is_array($value) && $addedTable) {
								$value = $value[0];
							}

							if ($search) {
								$sql .= "$key=$value,";
							} else {
								$insertvalues .= "$value,";
							}
						break;

						case "real":
							if ($value == "" ) {
								$value = 0;
							}
							if ($search) {
								$sql .= "$key=$value,";
							} else {
								$insertvalues .= "$value,";
							}
						break;

						case "string":
							if ($search) {
								$sql .= "$key='$value',";
							} else {
								$insertvalues .= "'$value',";
							}
						break;

						case "text":
							$value = $this->cleanHTML($value);
							if ($search) {
								$sql .= "$key='$value',";
							} else {
								$insertvalues .= "'$value',";
							}
						break;

						case "datetime":
							$pos = strpos($value, "/");
							if ($pos !== false) {
								$value = $this->ReformatDateform($value);
							}
							if ($search) {
								$sql .= "$key='$value',";
							} else {
								$insertvalues .= "'$value',";
							}
						break;

						case "date":
							$pos = strpos($value, "/");
							if ($pos !== false) {
								$value = $this->ReformatDateform($value);
							}
							if ($search) {
								$sql .= "$key='$value',";
							} else {
								$insertvalues .= "'$value',";
							}
						break;


						default:
							if ($search) {
								$sql .= "$key='$value',";
							} else {
								$insertvalues .= "'$value',";
							}
						break;
					}
				}
			}
			}
			
		}
		$sql = substr($sql, 0, strlen($sql) - 1);
		$insertfields = substr($insertfields, 0, strlen($insertfields) - 1);
		$insertvalues = substr($insertvalues, 0, strlen($insertvalues) - 1);
		if ($search) {
			if ($lookup != "") {
				$sql .= " where $lookup = '$id'";
			} else {
				if (isset($whereclause)) {
					$sql .= " $whereclause";
				} else {
					$sql .= " where " . $whereField . " = $id ";
				}
			}
		} else {
			$sql .= "($insertfields) VALUES ($insertvalues)";
		}
		//echo $sql;
		$wpdb->query($sql);
		if ($outcome != "") {
			print "<p class=dbmessage>$outcome</p>";
		}
	}

	function lastID($table, $field) {
		global $wpdb;
		$sql = "Select MAX($field) as lastID from $table";
		$lastID = $wpdb->get_var($sql);
		if ($lastID == "") {
			$lastID = 0;
		}
		return $lastID;
	}

	function NextOrder($table, $field, $lookup, $fieldLookup, $fieldType, $max = false) {
		global $wpdb;
		if ($lookup != "") {
			switch($fieldType) {
				case "s":
					if ($max) {
						$sql = "Select Max($field) as lastOrder from $table where $lookup = '$fieldLookup'";
					} else {
						$sql = "Select Count($field) as lastOrder from $table where $lookup = '$fieldLookup'";
					}
				break;
				case "i":
					if ($max) {
						$sql = "Select Max($field) as lastOrder from $table where $lookup = $fieldLookup";
					} else {
						$sql = "Select Count($field) as lastOrder from $table where $lookup = $fieldLookup";
					}
				break;
			}
		} else {
			if ($max) {
				$sql = "Select Max($field) as lastOrder from $table";
			} else {
				$sql = "Select Count($field) as lastOrder from $table";
			}
		}
		$lastOrder = $wpdb->get_var($sql);
		if ($lastOrder == "") {
			$lastOrder = 0;
		}

		$NextOrder = $lastOrder + 1;
		return $NextOrder;
	}

	function formImage($formtypeID, $imagefolder, $sharedir, $formID) {
		$sqlSize = "Select * from ipages_config where config_id = $formtypeID";
		$resultSize = mysql_query ($sqlSize) or die(trigger_error($queryfail . 'line ' . __LINE__));
		if (mysql_num_rows($resultSize) != 0) {
			$r = mysql_fetch_assoc($resultSize);
			$imagewidth = $r['img_width'];
			$imageheight = $r['img_height'];	
			$thumb_location = $r['thumb_location'];
		} else {
			$imagewidth = "245";						// Width of thumbnail image
			$imageheight = "129";						// Height of thumbnail image
		}

		$config = $sharedir . $this->config;
		$db = $sharedir . $this->db;
		$functions = $sharedir . $this->functions;
		$imagedir = $sharedir . $imagefolder;
		$path = $sharedir . $this->javaupload;
		$thumbdir = $imagedir;
		$gallery = "forms";
		$mediaTypeID = $formtypeID;
		$table = "form_info";
		$field = "logo";
		$fieldID = "form_id";
		$mediaType = "3";
		
		$body = "<?\n";
		$body .= "//Include required configurations\n";
		$body .= "require_once ('" . $config . "'); // configuration\n";
		$body .= "require_once ('" . $db . "'); // database functions\n";
		$body .= "require_once ('" . $functions . "'); // general functions\n";
		$body .= '$uploaddir = "' . $imagedir . '";';
		$body .= "\n";
		$body .= '$thumbdir = "' . $thumbdir . '";';
		$body .= "\n";
		$body .= '$imagewidth = "' . $imagewidth . '";';
		$body .= "\n";
		$body .= '$imageheight = "' . $imageheight . '";';
		$body .= "\n";
		$body .= '$imagefolder = "/' . $imagefolder . '";';
		$body .= "\n";
		$body .= '$formID = "' . $formID . '";';
		$body .= "\n";
		$body .= '$mediaType = "' . $mediaType . '";';
		$body .= "\n";
		$body .= '$table = "' . $table . '";';
		$body .= "\n";
		$body .= '$field = "' . $field . '";';
		$body .= "\n";
		$body .= '$fieldID = "' . $fieldID . '";';
		$body .= "\n";
		$body .= '$dir = "' . $sharedir . '";';
		$body .= "\n";
		$body .= '$gallery = "' . $gallery . '";';
		$body .= "\n?>";

		//Write configuration information
		/*$fhandle = fopen("$path", 'w');
		fwrite ($fhandle, $body);
		fclose($fhandle);*/

		file_put_contents($path, $body);
				
	}

	function showWPeditor2($content, $editor_id, $settings, $id = "") {
	   
	   wp_tiny_mce(false, // true makes the editor "teeny"
			array(
		    "editor_selector" => "$id",
		    "height" => 150
		    )
		);
		$output = <<<JS
			<script language="JavaScript" type="text/javascript">
			var id = '$id';
			function toggleEditor() {
				if (tinyMCE.get(id)) {
					tinyMCE.execCommand('mceRemoveControl', false, id); }
				else {
					tinyMCE.execCommand('mceAddControl', false, id);
			    }
			}

			</script>
JS;

		$output .= <<<EDITOR
			<div class="tinymce-tabs">
				<a class="html" onclick="toggleEditor()">HTML</a>
				<a class="visual" class="active" onclick="toggleEditor()">Visual</a>		    
			</div>
			<textarea class="$id" id="$id" name="$editor_id" style="width:100%">$content</textarea>
EDITOR;
		
		return $output;
	}

	function showWPeditor3($content, $id, $prev_id, $media_buttons, $tab_index, $extended) {
		ob_start();
		the_editor($content, $id, $prev_id, $media_buttons, $tab_index, $extended);
		$pageInfo = ob_get_clean();
		return $pageInfo;
		ob_end_clean();
	}

	function showWPeditor_multiple($content, $editor_id, $settings, $id = "") {
	   
	   wp_tiny_mce( false , // true makes the editor "teeny"
			array(
				"editor_selector" => 'tinymce-textarea'
			)
		);
		$visual = $id . "_visual";
		$html = $id . "_html";
		$output = <<<JS
			
			<script language="JavaScript" type="text/javascript">
				function toggleEditor(id) {
				if (tinyMCE.get(id)) {
					tinyMCE.execCommand('mceRemoveControl', false, id); }
				else {
					tinyMCE.execCommand('mceAddControl', false, id);
			    }
			}
			</script>
			
JS;
		$output .= <<<TABS
			<div class="tinymce-tabs">
				<a class="html" onclick="toggleEditor('$id')">HTML</a>
				<a class="visual" class="active" onclick="toggleEditor('$id')">Visual</a>
				<div style="clear: both;"></div>
			</div> 
TABS;
		$output .=  "<textarea class=\"tinymce-textarea\" id=\"$id\" name=\"$editor_id\" style=\"width:100%\">$content</textarea>";
		
		return $output;
	}

	

	function showWPeditor($content, $editor_id, $settings) {
		$set = _WP_Editors::parse_settings($editor_id, $settings);
		$editor_class = ' class="' . trim( $set['editor_class'] . ' wp-editor-area' ) . '"';
		$tabindex = $set['tabindex'] ? ' tabindex="' . (int) $set['tabindex'] . '"' : '';
		$rows = ' rows="' . (int) $set['textarea_rows'] . '"';
		$switch_class = 'html-active';
		$toolbar = $buttons = '';
		$output = "";

		if ( !current_user_can( 'upload_files' ) )
			$set['media_buttons'] = false;

		if ( self::$this_quicktags && self::$this_tinymce ) {
			$switch_class = 'html-active';

			if ( 'html' == wp_default_editor() ) {
				add_filter('the_editor_content', 'wp_htmledit_pre');
			} else {
				add_filter('the_editor_content', 'wp_richedit_pre');
				$switch_class = 'tmce-active';
			}

			$buttons .= '<a id="' . $editor_id . '-html" class="hide-if-no-js wp-switch-editor switch-html" onclick="switchEditors.switchto(this);">' . __('HTML') . "</a>\n";
			$buttons .= '<a id="' . $editor_id . '-tmce" class="hide-if-no-js wp-switch-editor switch-tmce" onclick="switchEditors.switchto(this);">' . __('Visual') . "</a>\n";
		}

		$output .= '<div id="wp-' . $editor_id . '-wrap" class="wp-editor-wrap ' . $switch_class . '">';

		if ( self::$editor_buttons_css ) {
			wp_print_styles('editor-buttons');
			self::$editor_buttons_css = false;
		}

		if ( !empty($set['editor_css']) )
			$output .= $set['editor_css'] . "\n";

		if ( !empty($buttons) || $set['media_buttons'] ) {
			$output .= '<div id="wp-' . $editor_id . '-editor-tools" class="wp-editor-tools">';
			$output .= $buttons;

			if ( $set['media_buttons'] ) {
				self::$has_medialib = true;

				if ( !function_exists('media_buttons') )
					include(ABSPATH . 'wp-admin/includes/media.php');

				$output .= '<div id="wp-' . $editor_id . '-media-buttons" class="hide-if-no-js wp-media-buttons">';
				$icon = "images/media-button.png?ver=20111005";
				$type = 'media';
				$output .= "Upload/Insert" . _media_button($editor_id, $icon, $type, $editor_id);
				//$output .= "<a href=\"" . admin_url() . "media-upload.php?post_id=0&amp;TB_iframe=1&amp;width=640&amp;height=586\" class=\"thickbox add_media\" id=\"$editor_id-add_media\" title=\"Add Media\" onclick=\"return false;\">Upload/Insert <img src=\"" . admin_url() . "images/media-button.png?ver=20111005\" height=\"15\" width=\"15\"></a>";
				$output .= "</div>\n";
			}
			$output .= "</div>\n";
		}

		$the_editor = apply_filters('the_editor', '<div id="wp-' . $editor_id . '-editor-container" class="wp-editor-container"><textarea' . $editor_class . $rows . $tabindex . ' cols="40" name="' . $set['textarea_name'] . '" id="' . $editor_id . '" style=\"width:100%\">' . $content . '</textarea></div>');
		$content = apply_filters('the_editor_content', $content);

		$output .= sprintf($the_editor, $content);
		$output .= "\n</div>\n\n";

		_WP_Editors::editor_settings($editor_id, $set);
		return $output;
	}

	function get_offset($page, $sql, $limit, $ID=0, $search=false, $table="", $lookup="", $field="", $criteria="", $keyword="", $orderby = "", $showrecords = false, $desc = false)
	{
		$wpdb = $this->wpdb;
		$records = array();
		$totalrecords = 0;
		if ($sql == "") {
			$sql="SELECT $lookup FROM $table where $field = $ID";
		}
		if ($desc) {
			$order = "DESC";
		} else {
			$order = "ASC";
		}


		//======== Determine record length ========
		if (empty($page) || $page < 1 || !is_numeric($page)) { 
			$page = 1; 
		} 

		$offset = ($page - 1) * $limit;
		
		//======== Determine Sequel Statement ========//

		if ($search) {
			if ($criteria != "") {
				if (is_array($criteria)) {
					if ($field != "" && $keyword != "") {
						$sql="SELECT $lookup FROM $table where $field = $ID and (";
						$sqlAN = "Select * from $table where $field = $ID and (";
						$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID and (";
					} elseif ($field != "" && $keyword == "") {
						$sql="SELECT $lookup FROM $table where $field = $ID ";
						$sqlAN = "Select * from $table where $field = $ID ";
						$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID ";
					} elseif ($field == "" && $keyword != "") {
						$sql="SELECT $lookup FROM $table where (";
						$sqlAN = "Select * from $table where (";
						$strTotal = "Select COUNT(*) as TOTAL from $table where (";
					} elseif ($field == "" && $keyword == "") {
						$sql="SELECT $lookup FROM $table ";
						$sqlAN = "Select * from $table ";
						$strTotal = "Select COUNT(*) as TOTAL from $table ";
					} 

					foreach ($criteria as $key => $value) {
						$value = $this->cleanString($value);
						if ($keyword != "") {
							$sql .= "$value like '%$keyword%' or ";
							$sqlAN .= "$value like '%$keyword%' or ";
							$strTotal .= "$value like '%$keyword%' or ";
						}
					}
					if ($keyword != "") {
						$sql = substr($sql, 0, strlen($sql) -3);
						$sql .= ")";
						$sqlAN = substr($sqlAN, 0, strlen($sqlAN) -3);
						$strTotal = substr($strTotal, 0, strlen($strTotal) -3);
						$sqlAN .= ") ";
						$strTotal .= ") ";
					}
					

					if ($orderby != "") {
						$sqlAN .= "ORDER BY $orderby $order LIMIT $offset, $limit";
					} else {
						$sqlAN .= "LIMIT $offset, $limit";
					}
				} else {
					if ($field != "" && $keyword != "" && $orderby != "") {
						$sql="SELECT $lookup FROM $table where $field = $ID and $criteria like '%$keyword%'";
						$sqlAN = "Select * from $table where $field = $ID and $criteria like '%$keyword%' ORDER BY $orderby $order LIMIT $offset, $limit"; 
						$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID and $criteria like '%$keyword%'";
					} elseif ($field != "" && $keyword == "" && $orderby != "") {
						$sql="SELECT $lookup FROM $table where $field = $ID ";
						$sqlAN = "Select * from $table where $field = $ID ORDER BY $orderby $order LIMIT $offset, $limit"; 
						$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID";
					} elseif ($field == "" && $keyword == "" && $orderby != "") {
						$sql="SELECT $lookup FROM $table ";
						$sqlAN = "Select * from $table ORDER BY $orderby desc LIMIT $offset, $limit"; 
						$strTotal = "Select COUNT(*) as TOTAL from $table";
					} else {
						$sql="SELECT $lookup FROM $table ";
						$sqlAN = "Select * from $table LIMIT $offset, $limit"; 
						$strTotal = "Select COUNT(*) as TOTAL from $table";
					}
				}
			} else {
				if ($field != "" && $keyword != "" && $orderby != "") {
					$sql="SELECT $lookup FROM $table where $field = $ID and $criteria like '%$keyword%'";
					$sqlAN = "Select * from $table where $field = $ID and $criteria like '%$keyword%' ORDER BY $orderby $order LIMIT $offset, $limit"; 
					$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID and $criteria like '%$keyword%'";
				} elseif ($field != "" && $keyword == "" && $orderby != "") {
					$sql="SELECT $lookup FROM $table where $field = $ID ";
					$sqlAN = "Select * from $table where $field = $ID ORDER BY $orderby $order LIMIT $offset, $limit"; 
					$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID";
				} elseif ($field == "" && $keyword == "" && $orderby != "") {
					$sql="SELECT $lookup FROM $table ";
					$sqlAN = "Select * from $table ORDER BY $orderby desc LIMIT $offset, $limit"; 
					$strTotal = "Select COUNT(*) as TOTAL from $table";
				} else {
					$sql="SELECT $lookup FROM $table ";
					$sqlAN = "Select * from $table LIMIT $offset, $limit"; 
					$strTotal = "Select COUNT(*) as TOTAL from $table";
				}
			}
		} else {
			if ($field != "" && $orderby != "") {
				$sqlAN="SELECT * FROM $table where $field = $ID ORDER BY $orderby $order LIMIT $offset, $limit";
				$strTotal = "Select COUNT(*) as TOTAL from $table where $field = $ID";
			} elseif ($field == "" && $orderby != "") {
				$sqlAN="SELECT * FROM $table ORDER BY $orderby $order LIMIT $offset, $limit";
				$strTotal = "Select COUNT(*) as TOTAL from $table";
			} elseif ($field == "" && $orderby == "") {
				$sqlAN="SELECT * FROM $table LIMIT $offset, $limit";
				$strTotal = "Select COUNT(*) as TOTAL from $table";
			} 
		}
		//======== End Determine Sequel Statement ========//
		$records = $wpdb->get_results($sqlAN);
		$rowCount = count($records);
		$TotalRowCount = $rowCount;
		$totalrecords = $wpdb->get_var( $strTotal );

		if ($limit > 0) { 
			$pages = intval($totalrecords / $limit); 
		} else { 
			$pages = 1; 
		} 
		
		
		if ($totalrecords % $limit || $pages == 0) { 
			$pages++; 
		} 
		
		if ($page > $pages)    { 
			// set $page to the last page and update $offset accordingly 
			$page = $pages; 
			$offset = ($page - 1) * $limit; 
		} elseif ($page < 0) { 
			// Page cannot be negative, so we default to the first page 
			$page = 1; 
			$offset = 0; 
		} 

		
		return array($offset,$pages,$TotalRowCount,$records,$totalrecords);
	}

	function sendEmail ($message, $subject, $from, $to) {
		
		/*$headers = 'From: ' . $from . "\r\n" .
	    'Reply-To: ' . $this->admin_email . "\r\n" .
		'X-Mailer: PHP/' . phpversion();*/

		$headers = "From: $from\r\n";
		$headers .= "Reply-To: " . $this->admin_email . "\r\n";
		//$headers .= "X-Mailer: PHP/" . phpversion();
		$headers .= "Content-type: text/html\r\n";
		if (mail ($to, $subject, $message, $headers)) {
			return true;
		} else {
			return false;
		}
	}

	function sendEmail2 ($body, $emailsubject, $fromName, $emailto, $replyto = '') {
		
		$headers = "From: $fromName\r\n";
		$headers .= "Reply-To: " . $emailto . "\r\n";
		$headers .= "Content-type: text/html\r\n";
		mail($emailto, $emailsubject, $body, $headers);
	}

	function sendEmail3 ($message, $subject, $from, $to, $replyto = '') {
		
		/*$headers = 'From: ' . $from . "\r\n" .
	    'Reply-To: ' . $this->admin_email . "\r\n" .
		'X-Mailer: PHP/' . phpversion();*/

		$headers = "From: $from\r\n";
		$headers .= "Reply-To: " . $replyto . "\r\n";
		//$headers .= "X-Mailer: PHP/" . phpversion();
		$headers .= "Content-type: text/html\r\n";
		if (mail ($to, $subject, $message, $headers)) {
			return true;
		} else {
			return false;
		}
	}


	function getRand() {
		//Unregister any previous code
		session_unregister("rand_code");
	
		if (empty($_SESSION['rand_code'])) { 
			$str = array(); 
			$length = 0; 

			for ($i = 0; $i < 6; $i++) { 
				// this numbers refer to numbers of the ascii table (small-caps) 
				$str[] = chr(rand(97, 122));
			} 
			//Remove duplicates
			$str = array_unique($str);

			//If clean takes away letters then try to up to 6.  However will still need to remove any duplicates.  If less than 6, it's ok.
			if (count($str) < 6) {
				$str[] = chr(rand(97, 122));
				//Clean again if necessary
				$str = array_unique($str);
			}

			//Set string for security display
			$rand_code = "";
			foreach ($str as $name => $value) {
				$rand_code .= $value . ",";
			}
			session_register("rand_code");
			$session_code = str_replace(",", "", $rand_code);
			session_register("session_code");
			
		} else {
			$rand_code = $_SESSION['rand_code'];
		}
		return $rand_code;
	}

	function StripSpecialQuotes($string,$mode)
	{
		if ($mode == 1)
		{
			$string = str_replace("‘", "", $string);
			$string = str_replace("’", "", $string);
			$string = str_replace("“", "", $string);
			$string = str_replace("”", "", $string);
		}
		
		if ($mode == 2)
		{
			$string = str_replace("'", "&#39;", $string);
			$string = str_replace("“", "&quot;", $string);
			$string = str_replace("”", "&quot;", $string);
		}

		if ($mode == 3)
		{
			$string = str_replace("‘", "'", $string);
			$string = str_replace("’", "'", $string);
		}
		
	return $string;
	}


	function showValidator() {
		$_SESSION['rand_code'] = "";
	
		if (empty($_SESSION['rand_code'])) { 
			$str = array(); 
			$length = 0; 

			for ($i = 0; $i < 6; $i++) { 
				// this numbers refer to numbers of the ascii table (small-caps) 
				$str[] = chr(rand(97, 122));
			} 
				//Remove duplicates
				$str = array_unique($str);

				//If clean takes away letters then try to up to 6.  However will still need to remove any duplicates.  If less than 6, it's ok.
				if (count($str) < 6) {
					$str[] = chr(rand(97, 122));
					//Clean again if necessary
					$str = array_unique($str);
				}

				//Set string for security display
				$rand_code = "";
				foreach ($str as $name => $value) {
					$rand_code .= $value . ",";
				}
			$session_code = str_replace(",", "", $rand_code);
			$_SESSION['session_code'] = $session_code;
			$_SESSION['rand_code'] = $rand_code;
		
		} else {
			$rand_code = $_SESSION['rand_code'];
		}
		$pluginUrl = $this->pluginURL;
		$validator .= "<center>";
		$validator .= "<input type=\"hidden\" name=\"sessioncode\" id=\"sessioncode\" value=\"$session_code\"/>";
		$validator .= "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"200\" height=\"55\" id=\"imagevalidation\" align=\"middle\">
		<param name=\"allowScriptAccess\" value=\"sameDomain\" />
		<param name=\"movie\" value=\"$pluginUrl/images/imagevalidation.swf\" />
		<param name=\"FlashVars\" value=\"rand_code=$rand_code\">
		<param name=\"quality\" value=\"high\" />
		<param name=\"bgcolor\" value=\"#ffffff\" />
		<embed src=\"$pluginUrl/images/imagevalidation.swf\" FlashVars=\"rand_code=$rand_code\" quality=\"high\" bgcolor=\"#ffffff\" width=\"200\" height=\"55\" name=\"imagevalidation\" align=\"middle\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />
		</object>
		</center>";
		return $validator;
	}

	function uploadFile($directory, $error, $size, $tmp_name, $fileName) {
		if (!is_dir($directory)) {
			mkdir($directory, 0755);
		}
		//Check to make sure the file upload is legit
		if($error) {
			echo "<p class=dbmessage>Error uploading file.  Please try again.</p>";
			echo "<input type=\"button\" class=\"LoginFormButton_sm\" onclick=\"javascript:window.location='$loc'\" value=\"$loctitle\">";
			exit;
		}

		if(!$size) {
			echo "<p class=dbmessage>File Size not detected.  Please try again.</p>";
			echo "<input type=\"button\" class=\"LoginFormButton_sm\" onclick=\"javascript:window.location='$loc'\" value=\"$loctitle\">";
			exit;
		}

		if ($tmp_name == "") {
			echo "<p class=dbmessage>File not uploaded to temporary storage.  Please try again.</p>";
			echo "<input type=\"button\" class=\"LoginFormButton_sm\" onclick=\"javascript:window.location='$loc'\" value=\"$loctitle\">";
			exit;
		}
		

		if ($fileName != "" && move_uploaded_file($tmp_name, $directory . $fileName)) {
			return true;
		} else {
			return false;
		}
	}

	function wp_page_dropdown($PageID, $fieldName) {
		$args = array(
			'depth'            => 0,
			'child_of'         => 0,
			'selected'         => $PageID,
			'echo'             => 0,
			'show_option_none' => 'Select',
			'name'             => $fieldName);
		$wp_pages = wp_dropdown_pages($args);
		return $wp_pages;
	}

	function fillStates($state) {
		$states = "<option value=\"\">-- Please Select --</option>"; 
		$statelist = array("AL" => "Alabama", "AK" => "Alaska", "AB" => "Alberta", "AE" => "Army Pacific", "AZ"=>"Arizona", "AR"=>"Arkansas", "BC" => "British Columbia", "CA"=>"California", "CO"=>"Colorado", "CT"=>"Connecticut", "DE"=>"Delaware", "DC"=>"District Of Columbia", "FL"=>"Florida", "GA"=>"Georgia", "HI"=>"Hawaii", "ID"=>"Idaho", "IL"=>"Illinois", "IN"=>"Indiana", "GU"=>"International", "VI"=>"International", "XX"=>"International", "IA"=>"Iowa", "KS"=>"Kansas", "KY"=>"Kentucky", "LA"=>"Louisiana", "ME"=>"Maine", "MB"=>"Manitoba", "MD"=>"Maryland", "MA"=>"Massachusetts", "MI"=>"Michigan", "MN"=>"Minnesota", "MS"=>"Mississippi", "MO"=>"Missouri", "MT"=>"Montana", "NE"=>"Nebraska", "NV"=>"Nevada", "NB"=>"New Brunswick", "NH"=>"New Hampshire", "NJ"=>"New Jersey", "NM"=>"New Mexico", "NY"=>"New York", "NF"=>"Newfoundland", "NC"=>"North Carolina", "ND"=>"North Dakota", "NT"=>"Northwest Territories", "NS"=>"Nova Scotia", "OH"=>"Ohio", "OK"=>"Oklahoma", "ON"=>"Ontario", "OR"=>"Oregon", "PA"=>"Pennsylvania", "PE"=>"Prince Edward Island", "PR"=>"Puerto Rico", "PQ"=>"Quebec", "RI"=>"Rhode Island", "SK"=>"Saskatchewan", "SC"=>"South Carolina", "SD"=>"South Dakota", "TN"=>"Tennessee", "TX"=>"Texas", "UT"=>"Utah", "VT"=>"Vermont", "VA"=>"Virginia", "WA"=>"Washington", "WV"=>"West Virginia", "WI"=>"Wisconsin", "WY"=>"Wyoming", "YT"=>"Yukon");

		//========= Set State Options ========//
		foreach ($statelist as $key=> $value) {
			if ($state == $key) {
				$states .= "<option value=\"$key\" selected>$value</option>";
			} else {
				$states .= "<option value=\"$key\">$value</option>";
			}
		}
		return $states;
	}

	function fillCountries($country) {
		$countries = "";
		$countrylist = array("AL" => "Albania", "DZ" => "Algeria" , "AS" => "American Samoa", "AD" => "Andorra", "AI" => "Anguilla", "AG" => " Antigua & Barbuda" , "AR" => "Argentina", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AP" => "Azores", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BO" => "Bolivia", "BL" => "Bonaire", "BA" => "Bosnia", "BW" => "Botswana", "BR" => "Brazil", "VG" => "British Virgin Islands", "BN" => "Brunei", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "IC" => "Canary Islands", "CV" => "Cape Verde Islands", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CD" => "Channel Islands", "CL" => "Chile", "CN" => "China", "Peoples Republic of", "CO" => "Colombia", "CG" => "Congo", "CK" => "Cook Islands", "CR" => "Costa Rica", "HR" => "Croatia", "CB" => "Curacao", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "EN" => "England", "GQ" => "Equitorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FO" => "Faeroe Islands", "FM" => "Federated States of Micronesia", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "GA" => "Gabon", "GM" => "Gambia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GN" => "Guinea", "GW" => "Guinea Bissau", "GY" => "Guyana", "HT" => "Haiti", "HO" => "Holland", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IL" => "Israel", "IT" => "Italy", "CI" => "Ivory Coast", "JM" => "Jamaica", "JP" => "Japan", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KO" => "Kosrae", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau", "MK" => "Macedonia", "MG" => "Madagascar", "ME" => "Madeira", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "MX" => "Mexico", "MD" => "Moldova", "MC" => "Monaco", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NF" => "Norfolk Island", "NB" => "Northern Ireland", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PL" => "Poland", "PO" => "Ponape", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "IE" => "Republic of Ireland", "YE" => "Republic of Yemen", "RE" => "Reunion", "RO" => "Romania", "RT" => "Rota", "RU" => "Russia", "RW" => "Rwanda", "SS" => "Saba", "SP" => "Saipan", "SA" => "Saudi Arabia", "SF" => "Scotland", "SN" => "Senegal", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "ZA" => "South Africa", "KR" => "South Korea", "ES" => "Spain", "LK" => "Sri Lanka", "NT" => "St. Barthelemy", "SW" => "St. Christopher", "SX" => "St. Croix", "EU" => "St. Eustatius", "UV" => "St. John", "KN" => "St. Kitts & Nevis", "LC" => "St. Lucia", "MB" => "St. Maarten", "TB" => "St. Martin", "VL" => "St. Thomas", "VC" => "St. Vincent & the Grenadines", "SD" => "Sudan", "SR" => "Suriname", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "TA" => "Tahiti", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TI" => "Tinian", "TG" => "Togo", "TO" => "Tonga", "TL" => "Tortola", "TT" => "Trinidad & Tobago", "TU" => "Truk", "TN" => "Tunisia", "TR" => "Turkey", "TC" => "Turks & Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "UI" => "Union Island", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UY" => "Uruguay", "VI" => "US Virgin Islands", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Vietnam", "VR" => "Virgin Gorda", "WK" => "Wake Island", "WL" => "Wales", "WF" => "Wallis & Futuna Islands", "WS" => "Western Samoa", "YA" => "Yap", "YU" => "Yugoslavia", "ZR" => "Zaire", "ZM" => "Zambia", "ZW" => "Zimbabwe");

		//========= Set Country Options ========//
		foreach ($countrylist as $key=> $value) {
			if ($country == $key) {
				$countries .= "<option value=\"$key\" selected>$value</option>";
			} else {
				if ($key == "US") {
					$countries .= "<option value=\"$key\" selected=\"selected\">$value</option>";
				} else {
					$countries .= "<option value=\"$key\">$value</option>";
				}
			}
		}
		return $countries;
	}

	//======== Formatting Functions ========//
	function _hyperlink ($link, $text, $target, $class, $style, $onclick = '') {
		$hyperlink = "<a href=\"$link\"  ";
		$class != "" ? $hyperlink .= "class=\"$class\" " : $hyperlink = $hyperlink;
		$style != "" ?  $hyperlink .= "style=\"$style\" " : $hyperlink = $hyperlink;
		$target != "" ?  $hyperlink .= "target=\"$target\" " : $hyperlink = $hyperlink;
		$onclick != "" ?  $hyperlink .= "onclick=\"$onclick\" " : $hyperlink = $hyperlink;
		$hyperlink .= ">$text</a>";
		return $hyperlink;
	}

	function _createImage($src, $border, $alt, $align, $width="", $onclick="", $onblur="", $height="", $class="", $style="", $onmouseover="", $onmouseout="") {
		$image = "<img src=\"$src\" ";		
		$border != "" ? $image .= "border=\"$border\" ": $image = $image;
		$alt != "" ? $image .= "alt=\"$alt\" title=\"$alt\" " : $image = $image;
		$class != "" ? $image .= "class=\"$class\" " : $image = $image;
		$align != "" ? $image .= "align=\"$align\" " : $image = $image;
		$width != "" ? $image .= "width=\"$width\" " : $image = $image;
		$height != "" ? $image .= "height=\"$height\" " : $image = $image;
		$onclick != "" ? $image .= "onclick=\"javascript:$onclick\" " : $image = $image;
		$onblur != "" ? $image .= "onblur=\"$onblur\" " : $image = $image;
		$style != "" ? $image .= "style=\"$style\" " : $image = $image;
		$onmouseover != "" ? $image .= "onmouseover=\"$onmouseover\" " : $image = $image;
		$onmouseout != "" ? $image .= "onmouseout=\"$onmouseout\" " : $image = $image;
		$image .= ">";
		return $image;
	}

	function _addInput($id, $name, $info, $style, $width, $color) {
		$input = "<div style=\"$style\" id=\"$id\"><input type=\"text\"  name=\"$name\" value=\"$info\" style=\"width:{$width};background-color: $color;\"/></div>";
		return $input;
	}

	function _addDiv($info, $id, $style, $class, $align) {
		$div .= "<div ";
		$id != "" ? $div .= "id=\"$id\" " : $div = $div;
		$style != "" ?  $div .= "style=\"$style\" " : $div = $div;
		$class != "" ? $div .= "class=\"$class\" " : $div = $div;
		$align != "" ? $div .= "align=\"$align\" " : $div = $div;
		$div .= ">$info</div>";
		return $div;
	}

	function _addDropDown($id, $name, $options, $width, $color, $style) {
		$options = "<div style=\"$style\" id=\"$id\"><select name=\"$name\" style=\"width:{$width};background-color: $color;\">$options</select></div>";
		return $options;
	}

	function _doInput ($id, $name, $type, $value, $length, $class, $alt, $onclick, $checked, $style, $src, $mouseover='', $mouseout='', $onblur = '') {		
		$input = "<input ";
		$type != "" ? $input .= "type=\"$type\" " : $input = $input;
		$name != "" ? $input .= "name=\"$name\" " : $input = $input;
		$id != "" ? $input .= "id=\"$id\" " : $input = $input;
		$src != "" ? $input .= "src=\"$src\" " : $input = $input;
		$class != "" ? $input .= "class=\"$class\" " : $input = $input;
		$value != "" ? $input .= "value=\"$value\" " : $input = $input;
		$onclick != "" ? $input .= "onclick=\"$onclick\" " : $input = $input;
		$onblur != "" ? $input .= "onblur=\"$onblur\" " : $input = $input;
		$checked != "" ? $input .= "checked=\"checked\" " : $input = $input;
		$length != "" ? $input .= "maxlength=\"$length\" " : $input = $input;
		$alt != "" ? $input .= "alt=\"$alt\" title=\"$alt\" " : $input = $input;
		$style != "" ? $input .= "style=\"$style\" " : $input = $input;
		$mouseover != "" ? $input .= "onmouseover=\"$mouseover\" " : $input = $input;
		$mouseout != "" ? $input .= "onmouseout=\"$mouseout\" " : $input = $input;

		$input .= ">";
		return $input;
	}

	function _doIcon($href, $onclick, $class, $target) {
		$input = "<a ";
		$href != "" ? $input .= "href=\"$href\" " : $input = $input;
		$target != "" ? $input .= "target=\"$target\" " : $input = $input;
		$onclick != "" ? $input .= "onclick=\"$onclick\" " : $input = $input;
		$class != "" ? $input .= "class=\"$class\" " : $input = $input;
		$input .= "><i></i></a>";
		//<a href=\"javascript:void(0);\" onclick=\"CheckSure2('$adminURL&process=Delete&formID=$formID')\" class=\"glyphicons bin icon_gray fs5\"><i></i></a>
		return $input;
	}

	function _doTextArea ($id, $name, $value, $class, $alt, $style) {
		$input = "<textarea name=\"$name\" id=\"$id\" class=\"$class\"  ";		

		if ($alt != "") {
			$input .= "alt=\"$alt\" title=\"$alt\" ";
		}		

		if ($style != "") {
			$input .= "style=\"$style\" ";
		}
		$input .= ">";
		$input .= $value;
		$input .= "</textarea>";
		
		return $input;
	}

	function _doSelect ($id, $name, $options, $multi, $class, $onchange, $style) {
		$input = "<select name=\"$name\" id=\"$id\" class=\"$class\" ";
		if ($onchange != "") {
			$input .= "onchange=\"$onchange\" ";
		}

		if ($style != "") {
			$input .= "style=\"$style\" ";
		}

		if ($multi) {
			$input .= "multi-select ";
		}
		$input .= ">";

		$input .= $options;

		$input .= "</select>";

		
		return $input;
	}
	//======== End Formatting Functions ========//

	// Clean an input field and return the modified string
	function clean_text_field ( $field_value )
	{
		// Check for magic_quotes_gpc setting
		if ( ini_get( 'magic_quotes_gpc' ) ) {
			//  On: strip slashes
			$field_value = trim( strip_tags( stripslashes( $field_value ) ) );
		} else {
			// Off: Do not strip the slashes.
			$field_value = trim( strip_tags( $field_value  ) );
		}
	return $field_value;
	}



	// Clean an associative array (usually an input form), by calling
	// the cleaning function - the function must work also in case of
	// three dimensional arrays.
	function clean_form( $input )
	{
		$form = array(); // initialize the answer
		foreach ( $input as $name => $value ) {
	
			// Is the value itself an array? (third dimension)
			if ( is_array( $value ) ) {
			
				foreach( $value as $key => $element_value ) {
					$form[$name][$key] = $this->clean_text_field( $element_value );
				}
		
			} else { // Scalar value
				$form[$name] = $this->clean_text_field( $value );
			}
		}
	
		return $form;
	}	

	function getpermalink($ID, $process) {
		$permalink = "";
		$wpdb = $this->wpdb;
		switch ($process) {
			case "category":
				//======== check to see if permalink exists already.  If not, will need to add it to the database for the future ========//
				$sql = "Select type_slug, type_name from product_types where type_id = $ID";
				$permalink = $wpdb->get_var($wpdb->prepare("Select type_slug from product_types where type_id = %d", $ID));
				$typeName = $wpdb->get_var($wpdb->prepare("Select type_name from product_types where type_id = %d", $ID));
				
				if ($permalink == "") {
					$permalink = $this->generate_user_permalink($typeName);	
					$cntperm = strlen($permalink)-1;
					$lastChr = $permalink[$cntperm];
					if ($lastChr == "-") {
						$permalink = substr($permalink, 0, strlen($permalink) -1);
					}
					$sqlupdate = "UPDATE product_types set type_slug = '$permalink' where type_id = %d";
					$wpdb->query($wpdb->prepare($sqlupdate, $ID));
				}				
			break;
			case "product":
				//======== check to see if permalink exists already.  If not, will need to add it to the database for the future ========//
				$sql = "Select product_slug, product_name from products where product_id = $ID";
				$permalink = $wpdb->get_var($wpdb->prepare("Select product_slug from products where product_id = %d", $ID));
				$productName = $wpdb->get_var($wpdb->prepare("Select product_name from products where product_id = %d", $ID));
				$binding = $wpdb->get_var($wpdb->prepare("Select binding from products where product_id = %d", $ID));
				
				if ($permalink == "") {
					$permalink = $this->generate_user_permalink($productName);
					$lastChr = $permalink[strlen($permalink)-1];
					if ($lastChr == "-") {
						$permalink = substr($permalink, 0, strlen($permalink) -1);
						if ($binding != "" ) {
							$binding = strtolower($binding);
							$permalink .= "-$binding";
						}
					}
					$sqlupdate = "UPDATE products set product_slug = '$permalink' where product_id = %d";
					$wpdb->query($wpdb->prepare($sqlupdate, $ID));
				}				
			break;
			case "provider":
				//======== check to see if permalink exists already.  If not, will need to add it to the database for the future ========//
				$permalink = $wpdb->get_var($wpdb->prepare("Select provider_slug from product_providers where id = %d", $ID));
				$providerName = $wpdb->get_var($wpdb->prepare("Select name from product_providers where id = %d", $ID));
				
				if ($permalink == "") {
					$permalink = $this->generate_user_permalink($providerName);
					$cntperm = strlen($permalink)-1;
					$lastChr = $permalink[$cntperm];
					if ($lastChr == "-") {
						$permalink = substr($permalink, 0, strlen($permalink) -1);
					}
					$sqlupdate = "UPDATE product_providers set provider_slug = '$permalink' where id = $ID";
					$wpdb->query($sqlupdate);
				}			
			break;
			case "gallerycategory":
				//======== check to see if permalink exists already.  If not, will need to add it to the database for the future ========//
				$permalink = $wpdb->get_var($wpdb->prepare("Select CATEGORY_SLUG from CATEGORIES where CATEGORY_ID = %d", $ID));
				$catName = $wpdb->get_var($wpdb->prepare("Select CATEGORY_NAME from CATEGORIES where CATEGORY_ID = %d", $ID));
				
				if ($permalink == "") {
					$permalink = $this->generate_user_permalink($catName);
					$cntperm = strlen($permalink)-1;
					$lastChr = $permalink[$cntperm];
					if ($lastChr == "-") {
						$permalink = substr($permalink, 0, strlen($permalink) -1);
					}
					$sqlupdate = "UPDATE CATEGORIES set CATEGORY_SLUG = '$permalink' where CATEGORY_ID = %d";
					$wpdb->query($wpdb->prepare($sqlupdate, $ID));
				}				
			break;
			case "gallerymedia":
				//======== check to see if permalink exists already.  If not, will need to add it to the database for the future ========//
				$permalink = $wpdb->get_var($wpdb->prepare("Select media_slug from media_files where media_id = %d", $ID));
				$mediaName = $wpdb->get_var($wpdb->prepare("Select media_title from media_files where media_id = %d",$ID));
				
				if ($permalink == "") {
					$permalink = $this->generate_user_permalink($mediaName);
					$cntperm = strlen($permalink)-1;
					$lastChr = $permalink[$cntperm];
					if ($lastChr == "-") {
						$permalink = substr($permalink, 0, strlen($permalink) -1);
					}
					$sqlupdate = "UPDATE media_files set media_slug = '$permalink' where media_id = %d";
					$wpdb->query($wpdb->prepare($sqlupdate,$ID));
				}				
			break;
		}		
		return $permalink;
	}

	function generate_user_permalink($str){
		 setlocale(LC_ALL, 'en_US.UTF8');
		 $plink = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		 $plink = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $plink);
		 $plink = strtolower(trim($plink, '-'));
		 $plink = preg_replace("/[\/_| -]+/", '-', $plink);
		 
		 return $plink;
	}

	function form_process($formType, $process, $items) {
		$wpdb = $this->wpdb;
		$fields = array();
		$dataTypes = array();
		$responseTxt = "<p align=\"center\">Thank you for your submission.<br/>Someone will be in contact<br/>with you shortly.</p>";		
		$sql = "Select * from form_types where form_type_id = $formType";
		$result = $wpdb->get_results($sql);
		if (count($result) != 0){
			foreach($result as $key => $row) {	
				$id = $row->form_type_id;
				$formFields = $row->form_fields;
				$formVar = $row->form_input;
				$formData = $row->form_dataType;
				$formName = $row->form_name;
				$formTable = $row->form_table;
				$responseTxt = $row->form_response;
				$formSession = $row->form_session;
				$formSessionFields = $row->form_session_fields;
			}

			switch ($process) {
				case "add":
					$sql = "INSERT into $formTable ($formFields) VALUES ($formType";
				break;

				case "update":
					$sql = "UPDATE $formTable ";
				break;

				case "delete":
					$sql = "DELETE from $formTable";
				break;
			}
				
			$fields = explode(",", $formFields);
			$dataTypes = explode(",", $formData);
				
			foreach($fields as $key=>$value) {
				switch ($dataTypes[$key]) {
					case "i":
						if ($value == 'order_id') {
							//======== Get last Order ID from database ========//
							$sqlorder = "Select Max(order_id) as lastID from form_info where form_type_id = $formType";
							$lastID = $wpdb->get_var($wpdb->prepare($sqlorder));
							if ($lastID != "") {
								$sql .= trim($lastID) . ", ";
							} else {
								$sql .= 1 . ", ";
							}
						} else {
							$sql .= trim($items[$value]) . ", ";
						}
					
					break;
	
					case "s":
						if ($value == "u_password") {
							$v = $this->checkRequest($value);
							$password = strip_tags($v);
							$password = md5($password);
							$sql .= "'" . trim($password) . "', ";
						} else {
							$sql .= "'" . trim($items[$value]) . "', ";
						}
					break;
				}			
			}
		}

		//remove last comma
		$sql = substr($sql,0,strlen($sql) - 2);
		$sql .= ")";
		//echo $sql;
		$wpdb->query($sql);
		//$result = mysql_query ($sql) or die (trigger_error('Unable to complete process.  Please contact the webmaster'));

		if ($formSession) {
			//======= Get last id entered to set session =======//
			$sessionFields = explode(",", $formSessionFields);
			$sqluser = "Select $formSessionFields from $formTable where {$sessionFields[0]} = LAST_INSERT_ID()";
			$resultuser = $wpdb->get_results($sqluser);
			
			if (count($resultuser) != 0) {
				foreach ($resultuser as $row) {
					//======= Set Sessions =======/
					$_SESSION['username'] = $row->$sessionFields[3];
					$_SESSION['fullname'] = $row->$sessionFields[1] . " " . $row->$sessionFields[2];
					$_SESSION['userid'] = $row->$sessionFields[0];
				}
			}
		}

		//Send email notice to administrator
		$message = "A $formName Request has been submitted.  Please go to the administration area to review.";
		//To help keep email address kosher
		//$Result = trim(preg_replace("/([\w\s]+)<([\S@._-]*)>/", " $2", $Input));

		$subject = "$formName Submission";
		$from = "server@kybproductions.net";
		//$to = "info@tbbbookstore.com,kbeasley@kybproductions.com";
		$to = "webmaster@kybproductions.com";
		$headers = 'From: TBB Bookstore' . "\r\n" .
	    'Reply-To: webmaster@kybproductions.com' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
		mail ($to, $subject, $message, $headers);
	
	}

	function romanNumerals($num) 
	{
		$n = intval($num);
		$res = '';
	 
		/*** roman_numerals array  ***/
		$roman_numerals = array(
					'M'  => 1000,
					'CM' => 900,
					'D'  => 500,
					'CD' => 400,
					'C'  => 100,
					'XC' => 90,
					'L'  => 50,
					'XL' => 40,
					'X'  => 10,
					'IX' => 9,
					'V'  => 5,
					'IV' => 4,
					'I'  => 1);
	 
		foreach ($roman_numerals as $roman => $number) 
		{
			/*** divide to get  matches ***/
			$matches = intval($n / $number);
	 
			/*** assign the roman char * $matches ***/
			$res .= str_repeat($roman, $matches);
	 
			/*** substract from the number ***/
			$n = $n % $number;
		}
	 
		/*** return the res ***/
		return $res;
	}

	function numtoalpha($number) { // function

	  $anum = "";

	  while($number >= 1) {

		$number = $number - 1;

		$anum = chr(($number % 26)+65).$anum;

		$number = $number / 26;

	  }

	  return $anum;

	}

	public function reorder_list($field, $updatefield, $table, $identity, $whereclause, $shortclause) {
		$wpdb = $this->wpdb;
		$ID = $this->checkRequest('ID');
		$process = $this->checkRequest('process');
		$typeID = $this->checkRequest('formtypeID');
		$oldID = $this->checkRequest('oldID');
		$newID = $this->checkRequest('newID');
		$oldnum = $this->checkRequest('oldnum');
		$newnum = $this->checkRequest('newnum');
		
		if ($newnum == "" || $newnum == 0) {
			$newnum = 1;
		}
		
		if ($newnum > $oldnum) {
			$move = "down";
		}
		
		if (isset($whereclause)) {
			$sql = "Select $field from $table $whereclause";
		} else {
			$sql = "Select $field from $table where $identity = $oldID";
		}
		$order = $wpdb->get_results($sql);

		
		//======== Update order of target ========
		if (isset($whereclause)) {
			$sqlUpdate = "UPDATE $table SET $field = $newnum $whereclause";
		} else {
			$sqlUpdate = "UPDATE $table SET $field = $newnum where $identity = $oldID";
		}
		$wpdb->query($sqlUpdate);
		
		
		//======== Roll through to reorder accordingly ========
		if (isset($shortclause)) {
			$sqlfields = "Select * from $table $shortclause order by $field";
		} else {
			$sqlfields = "Select * from $table where $updatefield = 1 order by $field";
		}
		$resultfields = $wpdb->get_results($sqlfields);
		
		
		$i = 1;
		$k = 1;

		if (count($resultfields) != 0) {
			foreach ($resultfields as $row) {
				//echo $i . "-$newnum<br>";
				$currfieldID = $row->$identity;
				//If we land on the one we are updating reiterate the updated number in the query because if not it will update with a new number.
				if ($currfieldID == $oldID) {	
					$CurrOrder = $newnum;
					$sqlOrder = "UPDATE $table SET $field = $CurrOrder where $identity = %d";
					$resultOrder = $wpdb->query($wpdb->prepare($sqlOrder,$currfieldID));
					$i--;
					//echo "Set new $currfieldID - $CurrOrder<br>";
				} elseif ((int)$i == (int)$newnum ) {
				 //if we find that the number we are looping matches the number in the database, but this is not the id that we are updating, make that id move down one from what's in the loop (this is key)
					
					//========Moving Backward======
					if ($oldnum > $newnum) {
						$CurrOrder = $i + 1;
						if ($CurrOrder == 0){
							$CurrOrder = 1;
						}	
						$i++;
						//echo "Move backwards $currfieldID - $CurrOrder<br>";
					} 

					//========Moving Forward======
					if ($newnum > $oldnum) {
						$CurrOrder = $i + 1;
						//========Catch if CurrOrder is 0 =======
						if ($CurrOrder == 0){
							$CurrOrder = 1;
						}	
						//echo "Move forwards $currfieldID - $CurrOrder<br>";
					} 
						  
					if ($currfieldID != $oldID) {
						//echo "Set Current - $CurrOrder<br>";
						$sqlOrder = "UPDATE $table SET $field = %d where $identity = %d";
						$resultOrder = $wpdb->query($wpdb->prepare($sqlOrder,$CurrOrder,$currfieldID));
					}

				} else {
					//echo "Set Regular $currfieldID - $i<br>";
					 //Set the position with current loop number
					$sqlOrder = "UPDATE $table SET $field = $i where $identity = %d";
					$resultOrder = $wpdb->query($wpdb->prepare($sqlOrder,$currfieldID));
				}
				//For debug to reset back in order.
				$sqlreorder = "UPDATE $table SET $field = $k where $identity = %d";
				//$resultreorder = $wpdb->query($wpdb->prepare($sqlreorder,$currfieldID));
				$i++;
				$k++;
			}
		}
	}

	function get_excerpt_by_id($post_id, $excerpt_length){
		$the_post = get_post($post_id); //Gets post ID
		$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
		$words = explode(' ', $the_excerpt, $excerpt_length + 1);

		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '...');
			$the_excerpt = implode(' ', $words);
		endif;

		return $the_excerpt;
	}

	function get_excerpt($the_excerpt, $excerpt_length){
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
		$words = explode(' ', $the_excerpt, $excerpt_length + 1);

		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '...');
			$the_excerpt = implode(' ', $words);
		endif;
		return $the_excerpt;
	}

	function get_attachment_id_from_src($image_src) {
		global $wpdb;
		$sql = "Select ID from {$wpdb->posts} where guid = %s";
		$id = $wpdb->get_var($wpdb->prepare($sql, $image_src));
		return $id;
	}

}
?>