<?php
/*******************************************************************************
* ONLINE FORM BUILDER FUNCTIONS CLASSES			                               *
*                                                                              *
* Version: 1.2                                                                 *
* Date:    10-8-2014                                                          *
* Author:  Kimla Y. Beasley													   *
* Copyright 2012-2014 KYB PRODUCTIONS LLC									   *
*******************************************************************************/
class builderclass extends kybformSharedProcesses implements builderfunctions {	

	public $imageFolder;
	public $pluginDir;
	public $pluginURL;
	var $urlInfo;
	var $currURL;
	var $table;

	public function __construct() {
		parent::__construct();
		$this->imageFolder = KYBFORMS_URLPATH;
		$this->pluginDir = KYBFORMS_ABSPATH;
		$this->pluginURL = KYBFORMS_URLPATH;
		$this->secure_pluginURL = site_url() . "/wp-content/plugins/kybformbuilder";
		$this->currURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$urlInfo = parse_url($this->currURL, PHP_URL_PATH);
		$this->urlInfo = explode("/", $urlInfo);
		$this->defineCompanySettings();
		$this->table = KYBFORMS_TABLE;
	}
	
	/*Form Administration Functions */
	public function form_settings() {
		global $wpdb;
		if (isset($_POST) && $_POST['process'] == 'formsettings') {
			$setInfo = $wpdb->get_row($wpdb->prepare("Select * from " . $this->table . "form_config where status = %d", 1));
			$_POST['phone'] = $this->checkRequest('phone1') . "-" . $this->checkRequest('phone2') . "-" . $this->checkRequest('phone3');
			$_POST['logo'] = $this->checkRequest('upload_image2');
			$_POST['paypal_logo'] = $this->checkRequest('upload_image');
			
			if (count($setInfo) == 0) {
				$outcome = "The form settings have been added successfully.";
				$this->processForm("add", $_POST, $this->table . 'form_config', 0, $outcome, 0);
			} else {
				$configID = $setInfo->config_id;			
				$outcome = "The form settings have been updated successfully.";
				$this->processForm("update", $_POST, $this->table . 'form_config', $configID, $outcome, 0);
			}
		}
		$setInfo = $wpdb->get_row($wpdb->prepare("Select * from " . $this->table . "form_config where status = %d", 1));
		
		$intro = apply_filters('the_content', $setInfo->settings_intro);
		$stateOptions = $this->fillStates($setInfo->state);
		$phoneInfo = explode("-", $setInfo->phone);
		$phone1 = $phoneInfo[0];
		$phone2 = $phoneInfo[1];
		$phone3 = $phoneInfo[2];
		$args = array(
			'depth'            => 0,
			'child_of'         => 0,
			'selected'         => $setInfo->response_page,
			'echo'             => 0,
			'show_option_none' => "-- Please Select --",
			'name'             => 'response_page');
		$wpPages = wp_dropdown_pages($args);
		

		$template = "form_settings.html";
		$content = array(
			'ADMINURL' => $this->adminURL,
			'PROCESS' => 'formsettings',
			'INTRODUCTION' => $intro,
			'COMPANY' => $setInfo->company,
			'TAGLINE' => $setInfo->tagline,
			'ADDRESS' => $setInfo->address,
			'webpages' => $wpPages,
			'CITY' => $setInfo->city,
			'STATEOPTIONS' => $stateOptions,
			'ZIP' => $setInfo->zip,
			'PHONE1' => $phone1,
			'PHONE2' => $phone2,
			'PHONE3' => $phone3,
			'EMAIL' => $setInfo->email,
			'LOGOIMAGE' => $setInfo->paypal_logo,
			'ANETMERCHANT' => $setInfo->anet_merchant,
			'ANETKEY' => $setInfo->anet_key,
			'PAYPALBUSINESS' => $setInfo->paypal_business,
			'HEADERIMAGE' => $setInfo->paypal_logo,
			'HTML' => $template
			);
		return $content;
	}

	public function listforms() {
		$wpdb = $this->wpdb;
		$formList = "";
		$adminURL = $this->adminURL;
		$siteURL = site_url();
		$imageFolder = $this->imageFolder;
		$formInfo = array();
		$sql = "Select * from " . $this->table . "forms order by form_expiration desc";
		$result = $wpdb->get_results($sql);
		if (count($result) != 0) {
			foreach ($result as $row) {
				$createdate = explode('-', $row->form_expiration);
				$expdate = "Does not expire";
				if ($row->form_toexpire) {
					$expdate = $createdate[1] . "/" . $createdate[2] . "/" . $createdate[0];
				}
				$formID = $row->form_id;
				$formTitle = $row->form_title;
				$submissionCount = $this->formCount($formID);
				$submissionsLink = "$adminURL&process=submissions&formID=$formID";
				$row->form_link != "" ? $formLink = $row->form_link : $formLink = "$siteURL/onlineforms/?process=view&formID=$formID";
				if ($row->form_status) { 
					$chk_act = "checked";
					$chk_deact = "";
				} else {
					$chk_act = "";
					$chk_deact = "checked";
				}
				$activated = $this->_doInput ("status-$formID", "status-$formID", 'radio', '1', '', '', '', "window.location='$adminURL&process=Activation&status=1&formID=$formID'", $chk_act, '', '', '', '', '') . "Activated";
				$deactivated = $this->_doInput ("status-$formID", "status-$formID", 'radio', '0', '', '', '', "window.location='$adminURL&process=Activation&status=0&formID=$formID'", $chk_deact, '', '', '', '', '') . "Deactivated";
				$formStatus = "{$activated}&nbsp;{$deactivated}";
								
				$duplicate_link = $this->_doIcon("javascript:void(0);", "javascript:window.location='$adminURL&process=duplicateform&formID=$formID'", "icon-copy2 icon_gray fs5", '');
				$clipboard_link = $this->_doIcon("javascript:void(0);", "ClipBoard('$formLink')", "icon-copy icon_gray fs5", '');
				$edit_link = $this->_doIcon("javascript:void(0);", "javascript:window.location='$adminURL&process=modifyform&formID=$formID&Submit=Modify'", "icon-pencil icon_gray fs5", '');
				$delete_link = $this->_doIcon("javascript:void(0);", "CheckSure2('$adminURL&process=Delete&formID=$formID')", "icon-trash icon_gray fs5", '');
				$preview_link = $this->_doIcon("$formLink", "", "icon-binoculars icon_gray fs5", '_blank');
				$form_hook = "[kybformview formid=$formID]";

				$formInfo = array("<a style=\"font-size:13px;\" href=\"$adminURL&process=UpdateForm&formID=$formID&Submit=Modify\">$formTitle</a>", $expdate, $formStatus, "<div align='center'>$form_hook</div>", "<div align='center'>$clipboard_link</div>", "<div align=\"center\"><span class=\"post-com-count-wrapper\"><a href=\"$submissionsLink\" title=\"$submissionCount submitted\" class=\"post-com-count\"><span class=\"comment-count\">$submissionCount</span></a></span><a href=\"$submissionsLink\">View All</a></div>", "<div align='center'>$edit_link</div>","<div align='center'>$delete_link</div>", "<div align='center'>$preview_link</div>", "<div align='center'>$duplicate_link</div>");
				$formList .= $this->buildRow($formInfo, '', count($formInfo));	
				
			}
		} else {
			$formList = "<tr><td colspan=\"9\">There are currently no forms available.  <a href=\"$adminURL&process=CreateForm\">Click here</a> to add a new form.</td></tr>";
		}
		$template = "form_list.html";
		$content = array(
			'ADMINURL' => $adminURL,
			'IMAGEURL' => $imageFolder,
			'FORMLIST' => $formList,
			'HTML' => $template
			);
		return $content;
	}

	public function form_editProcess($formID) {
		global $wpdb;
		$payment = $this->checkRequest('form_payment');
		$upload = $this->checkRequest('form_upload');
		$formWPID = $this->checkRequest('form_wp_page');
		$showBorder = $this->checkRequest('section_border_show');
		$showBorder == "on" ? $_POST['section_border_show'] = 1 : $_POST['section_border_show'] = 0;
		$labelBold = $this->checkRequest('label_bold');
		$labelBold == "on" ? $_POST['label_bold'] = 1 : $_POST['label_bold'] = 0;
		//======== Reformat incoming date ========//
		if ($_POST['form_expiration'] != "") {
			$expiration = $this->checkRequest('form_expiration');
			$_POST['form_expiration'] = $this->ReformatDateform($expiration);
		}
		if ($formWPID != "") {
			$_POST['form_link'] = get_permalink($formWPID) . "$formID";
		}
		$_POST['form_instructions'] = str_replace("\\", "", $_POST['form_instructions']);
		$_POST['form_list_intro'] = str_replace("\\", "", $_POST['form_list_intro']);
		$_POST['form_response'] = str_replace("\\", "", $_POST['form_response']);
		$_POST['section_title_color'] = str_replace("#", "", $_POST['section_title_color']);
		$_POST['section_border_color'] = str_replace("#", "", $_POST['section_border_color']);
		$_POST['label_color'] = str_replace("#", "", $_POST['label_color']);
		$_POST['input_bg'] = str_replace("#", "", $_POST['input_bg']);

		if ($formID == "" || $formID == 0) {
			$this->processForm('add', $_POST, $this->table . 'forms', $formID, '', '', '', false, '', '' , '', '', '', '', '', false, '');
			$formID = $this->lastID($this->table . 'forms', 'form_id');
		} else {
			$this->processForm('update', $_POST, $this->table . 'forms', $formID, '', '', '', false, '', '' , '', '', '', '', '', false, '');
		}	
		return $formID;
	}

	public function formlistings($formID) {
		$wpdb = $this->wpdb;
		$formInfo = $this->get_form_info($formID);
		$list_type = $formInfo->form_listing_type;
		$wp_id = $formInfo->form_wp_page;
		$formtitle = $formInfo->form_title;
		$listcontent = "";
		$themeURL = $this->templateURL;
		$records = array();
		$formList = "";
		switch ($list_type) {
			case 1:
				$returnURL = get_permalink($wp_id);
				$sql = "Select * from " . $this->table . "form_info where form_type_id = %d";
				$result = $wpdb->get_results($wpdb->prepare($sql, $formID));
				$TotalRowCount = count($result);
				$limit = 10;		
				isset($_GET['pg']) ? $pagenum = $this->checkRequest('pg') : $pagenum = 1;
				$list_range = $this->listRange($limit, $TotalRowCount, $pagenum);
				if (is_array($list_range)) {
					$start = $list_range[0];
					$end = $list_range[1];
					$offset = $list_range[2];
					$pages = $list_range[3];
				}				
				$pageturns = $this->printNextPrev2(true,'',true,true,$formtitle, true,'','',$returnURL,$pages, $offset, $pagenum, $TotalRowCount, $limit, "", "", "");
				$sqlsub = "SELECT * FROM " . $this->table . "form_info WHERE status=1 ORDER BY form_id DESC LIMIT $offset, $limit";
				$resultsub = $wpdb->get_results($sqlsub);
				$formList .= "<p align=\"right\">";
				$formList .= "<img src=\"$themeURL/images/icons/bullet.jpg\" width=10 height=9> ";
				$formList .= $this->DrawLink('<strong>Sign our $formtitle</strong>','bodylink1','Sign our $formtitle',
					"$returnURL?formview=1",'','');
				$formList .= "</p>";
				if (count($resultsub) != 0) {
					foreach ($resultsub as $row) {
						$comments = $row->comments;
						$listinfo = explode(",", $comments);
						foreach ($listinfo as $key => $value) {
							$info = explode("=>", $value);
							if ($info[0] == "guestmessage") {
								$message = stripslashes($info[1]);
								$message = nl2br("$message");
								$listcontent .= $message . ",";
							}

							$info[0] == "guestname" ? $listcontent .= $info[1] . "," : '';
							$info[0] == "location" ? $listcontent .= $info[1] . "," : '';
						}
						$listcontent = substr($listcontent,0,strlen($listcontent)-1);
						$listrecords = explode(",",$listcontent);
						array_push($records, $listrecords);
						
						$listcontent = "";
					}
				}
			break;
		}
		
		foreach ($records as $k => $v) {
			$formList .= "<p>{$v[2]}<br>";
			$formList .= "<strong>{$v[0]}, {$v[1]}</strong><br>";
			$formList .= "<span class=\"textsize10px\" style=\"color: #999999;\"></span></p>";
		}
		
		$formList .= $pageturns;
		$formList .= "<hr color=\"#eeeeee\">\n";
		$formList .= "<p align=\"center\">";
		$formList .= "<img src=\"$themeURL/images/icons/bullet.jpg\" width=10 height=9> ";
		$formList .= $this->DrawLink('<strong>Sign our $formtitle</strong>','bodylink1','Sign our $formtitle',
		"$returnURL?formview=1",'','');
		$formList .= "</p>";
		$template = "form_content.html";
		$content = array(
			'CONTENT' => $formList,
			'HTML' => $template
			);
		return $content;
	}

	public function formCount($formtypeID) {
		$wpdb = $this->wpdb;
		$sql = "Select Count(form_id) as totalSubmissions from " . $this->table . "form_info where form_type_id = %d";
		$totalSubmissions = $wpdb->get_var( $wpdb->prepare( $sql, $formtypeID ) );
		return $totalSubmissions;
	}

	public function initform($process) {
		$wpdb = $this->wpdb;
		$pluginDir = $this->pluginDir;
		$formID = $this->checkRequest('ID');
		$formname = "";
		$email = "";
		$expdate = "00/00/0000";
		$payment = "";
		$upload = "";
		$instructions = "";
		$response = "";
		$fields = "";
		$buttons = "";
		$listIntro = "";
		$payment = 0;
		$upload = 0;
		$expire = 1;
		$listURL = site_url() . "/wp-admin/admin.php?page=kybformbuilder-form-list";
		$alignments = array('left'=>'left','center'=>'center','right'=>'right');
		$settings = array( 'media_buttons' => true);
		$formAlign = "";
		$showListing = 0;
		$formWPID = 134;
		$formLink = get_permalink($formWPID);
		$sectionBorderShow = 1;
		$labelBold = 1;
		$sectionColor = "9f1d1d";
		$sectionBorderColor = "DDDDDD";
		$inputBg = "DDDDDD";
		$form_paypal_account = $wpdb->get_var("Select paypal_business from " . $this->table . "form_config where config_id = 1");
		$form_rsvp = 0;
		$product_id = 0;
		$form_addtowp = 0;
		$form_login = 0;
		$btn_cancel = $this->_doInput ('', "Submit", 'submit', 'Cancel', '', "btn btn-block btn-inverse", 'Cancel', '', '', '', '', '', '', '');
		$btn_continue = $this->_doInput ('', "Submit", 'submit', 'Continue', '', "btn btn-block btn-inverse", 'Cancel', '', '', '', '', '', '', '');
		$btn_finish = $this->_doInput ('', "Submit", 'submit', 'Finish', '', "btn btn-block btn-inverse", 'Cancel', '', '', '', '', '', '', '');
		$btn_modify = $this->_doInput ('', "Submit", 'submit', 'Modify', '', "btn btn-block btn-inverse", 'Cancel', '', '', '', '', '', '', '');
		switch ($process) {
			case "CreateForm":
				$instructions_editor = $this->showWPeditor3($instructions, "form_instructions", 'form_instructions', true, 1, false);
				$response_editor = $this->showWPeditor3($response, "form_response", 'form_response', true, 1, false);
				$intro_editor = $this->showWPeditor3($listIntro, "form_list_intro", 'form_list_intro', true, 1, false);
				$fieldContent = array (
					'formname' => $formname,
					'email'=>$email,
					'expdate' => $expdate,
					'files'=>$upload,
					'form_link' => $formLink,
					'instructions' => $instructions_editor,
					'listintro' => $intro_editor,
					'response' => $response_editor
				);
				$form_buttons = $this->_addDiv($btn_continue, '', '', 'divbtn', '');
				$form_buttons .= $this->_addDiv($btn_cancel, '', '', 'divbtn', '');
				$buttons = $this->_addDiv($form_buttons, '', 'width: 300px;', '', '');
			break;
			case "UpdateForm":
				$formID = $this->checkRequest('formID');
				$sql = "Select * from " . $this->table . "forms where form_id = %d";
				$result = $wpdb->get_results($wpdb->prepare($sql, $formID));
				if (count($result) != 0) {
					foreach ($result as $row) {
						$formname = $row->form_title;
						$email = $row->email_responses;
						$expdate = $this->ReformatDatedb($row->form_expiration);
						$payment = $row->form_payment;
						$upload = $row->form_fileupload;
						$expire = $row->form_toexpire;
						$formWPID = $row->form_wp_page;
						$formLink = $row->form_link;
						$instructions = $row->form_instructions;
						$listIntro = $row->form_list_intro;
						$response = $row->form_response;
						$formAlign = $row->form_align;
						$showListing = $row->form_listing;
						$sectionColor = $row->section_title_color;
						$sectionBorderShow = $row->section_border_show;
						$sectionBorderColor = $row->section_border_color;
						$labelColor = $row->label_color;
						$labelBold = $row->label_bold;
						$inputBg = $row->input_bg;
						$form_paypal_account = $row->form_paypal_account;
						$form_rsvp = $row->form_rsvp;
						$product_id = $row->product_id;
						$form_addtowp = $row->form_addtowp;
						$form_login = $row->form_login;						
					}
					$instructions_editor = $this->showWPeditor3($instructions, "form_instructions", 'form_instructions', true, 1, false);
					$response_editor = $this->showWPeditor3($response, "form_response", 'form_response', true, 1, false);
					$intro_editor = $this->showWPeditor3($listIntro, "form_list_intro", 'form_list_intro', true, 1, false);

					$fieldContent = array (
						'formname' => $formname,
						'email'=>$email,
						'expdate' => $expdate,
						'files'=>$upload,
						'form_link' => $formLink,
						'instructions' => $instructions_editor,
						'listintro' => $intro_editor,
						'response' => $response_editor
					);
				}

				$form_buttons = $this->_addDiv($btn_continue, '', '', 'divbtn', '');
				$form_buttons .= $this->_addDiv($btn_finish, '', '', 'divbtn', '');
				$form_buttons .= $this->_addDiv($btn_cancel, '', '', 'divbtn', '');
				$buttons = $this->_addDiv($form_buttons, '', 'width: 300px;', '', '');
			break;
			case "Modify":
				$form_buttons = $this->_addDiv($btn_modify, '', '', 'divbtn', '');
				$form_buttons .= $this->_addDiv($btn_cancel, '', '', 'divbtn', '');
				$buttons = $this->_addDiv($form_buttons, '', 'width: 300px;', '', '');
			break;
		}

		$paymentOptions = $this->buildOptions ($this->bitOptions, $payment, '', '', false, 0);
		$uploadOptions = $this->buildOptions ($this->bitOptions, $upload, '', '', false, 0);
		$expireOptions = $this->buildOptions ($this->bitOptions, $expire, '', '', false, 0);
		$rsvpOptions = $this->buildOptions ($this->bitOptions, $form_rsvp, '', '', false, 0);
		$loginOptions = $this->buildOptions ($this->bitOptions, $form_login, '', '', false, 0);
		$wpOptions = $this->buildOptions ($this->bitOptions, $form_addtowp, '', '', false, 0);
		$alignOptions = $this->buildOptions ($alignments, $formAlign, '', '', false,0, "", "");
		$listingOptions = $this->buildOptions ($this->bitOptions, $showListing, '', '', false, 0);
		if ($this->table_exist('products')) {
			$sql = "Select product_id, product_name from products";
			$productOptions = $this->build_dboptions($sql, 'product_name', 'product_id', $product_id, true);
		}

		//======== Settings Options ========//
		$sectionBorderShow == 1 ? $bordercheck = "checked" : $bordercheck = "";
		$sectionBorderShow == 1 ? $showBorder = 1 : $showBorder = 0;
		$labelBold == 1 ? $boldCheck = "checked" : $boldCheck = "";

		//======== Set defaults ========//
		$labelColor == "" ? $labelColor = "000000" : $labelColor = $labelColor;
		$sectionColor == "" ? $sectionColor = "5888b4" : $sectionColor = $sectionColor;
		$sectionBorderColor == "" ? $sectionBorderColor = "DDDDDD" : $sectionBorderColor = $sectionBorderColor;
		$inputBg == "" ? $inputBg = "FFFFFF" : $inputBg = $inputBg;

		//======== Get Word Press Page dropdown ========//
		$formWPID != 0 ? $pageLink = get_permalink($formWPID) . $formID : $pageLink = "#";
		$formWPID != 0 ? $pageEdit = "post.php?post=$formWPID&action=edit" : $pageEdit = "#";
		$args = array(
			'depth'            => 0,
			'child_of'         => 0,
			'selected'         => $formWPID,
			'echo'             => 0,
			'show_option_none' => "-- Please Select --",
			'name'             => 'form_wp_page');
		$docPage = wp_dropdown_pages($args);
		$docPage .= "<a href=\"$pageLink\" target=\"_blank\">view</a> &nbsp;&nbsp; <a href=\"$pageEdit\">edit</a>";

		$fieldContent['alignmentoptions'] = $alignOptions;
		$fieldContent['listingsoptions'] = $listingOptions;
		$fieldContent['section_title_color'] = $sectionColor;
		$fieldContent['section_border_color'] = $sectionBorderColor;
		$fieldContent['bordercheck'] = $bordercheck;
		$fieldContent['showborder'] = "$showBorder";
		$fieldContent['label_color'] = $labelColor;
		$fieldContent['boldcheck'] = $boldCheck;
		$fieldContent['input_bg'] = $inputBg;
		$fieldContent['webpages'] = $docPage;	
		$fieldContent['paymentoptions'] = $paymentOptions;
		$fieldContent['fileoptions'] = $uploadOptions;
		$fieldContent['expireoptions'] = $expireOptions;
		$fieldContent['form_paypal_account'] = $form_paypal_account;
		$fieldContent['rsvpoptions'] = $rsvpOptions;
		$fieldContent['productoptions'] = $productOptions;
		$fieldContent['loginoptions'] = $loginOptions;
		$fieldContent['wpoptions'] = $wpOptions;
		$fields = $this->showPage ("$pluginDir/view/html/createform.html", $fieldContent, true);
		
		$template = "form_create.html";
		$content = array (
			'FORMFIELDS' => $fields,
			'ADMINURL' => $this->adminURL,
			'FORMID' => $formID,
			'BUTTONS' => $buttons,
			'LISTPAGE' => $listURL,
			'HTML' => $template
		);
		return $content;
	}

	public function modifyform($process, $formID, $payment, $upload) {
		$wpdb = $this->wpdb;
		$output = "";
		$records = array();
		$title = array();
		$order = array();
		$sectionID = "";
		$fieldtypeslist = "";
		$fname = "";
		$sfname = "";
		$forder = "";
		$sorder = "";
		$fieldinstruction = "";
		$listURL = site_url() . "/wp-admin/admin.php?page=kybformbuilder-form-list";
		$this->processElements($formID,$payment,$upload);
		
		$sql = "Select section_id, section_title, section_order from " . $this->table . "form_sections where form_id = %d order by section_order";
		$result = $wpdb->get_results($wpdb->prepare($sql, $formID));	
		foreach($result as $row) {
			$records[] = $row->section_id;
			$title[] = $row->section_title;
			$order[] = $row->section_order;
		}	
		
		switch ($process) {
			case "Continue":
				$sectionheader = "Select Section";
				$output = "<p class=dbmessage><b>Continue entering Form Criteria below</b></p>";
				$sectionList = $this->getSectionlist(1, $section, $records, $title, $order);
				$fieldtypeslist = $this->GetFieldTypes();
			break;
			case "modifyform":
				$sectionheader = "Select Section";
				$output = "<p class=dbmessage><b>Enter Form Criteria below</b></p>";
				$sectionList = $this->getSectionlist(1, $section, $records, $title, $order);
				$fieldtypeslist = $this->GetFieldTypes();
			break;
			case "Finish":
				$sectionheader = "Select Section";
				$output = "<p class=dbmessage><b>Form Creation is complete.  In order for user to view your form, copy the link to your clip board below and paste the link into your desired page as a hyperlink.</b></p>";
				$sectionList = $this->getSectionlist(2, $section, $records, $title, $order);
			break;
		}

		//======== Get Current Section ID ========//
		$sqlSection = "Select section_id, section_order from " . $this->table . "form_sections where form_id = %d order by section_order desc";
		$resultSection = $wpdb->get_results($wpdb->prepare($sqlSection,$formID));
		if (count($resultSection) != 0) {
			foreach($resultSection as $row) {
				$sectionID = $row->section_id;
				$sorder = $row->section_order;
			}
		}
		
		$template = "createfields.html";
		$content = array (
			'formID' => $formID,
			'sectionID'=> "$sectionID",
			'fieldtypes'=>$fieldtypeslist,
			'section' => $sectionList,
			'sectionheader' => $sectionheader,
			'sectionorder'=>"$sorder",
			'fieldname' => $fname,
			'shortfieldname' => $sfname,
			'fieldinstruction' => $fieldinstruction,
			'fieldorder'=> $forder,
			'PLUGINURL' => WP_PLUGIN_URL,
			'LISTPAGE' => $listURL,
			'HTML' => $template
		);
		return $content;
	}

	public function formSubmissions($navInfo,$formID) {	
		$wpdb = $this->wpdb;
		$copylink = "";
		$areatitle1 = "";
		$areatitle2 = "";
		$instruction = "";
		$listing = "";
		$form_list = "";
		$bgcolor = "";
		$markedfeatured = array();
		$adminURL = $this->adminURL;
		$siteURL = site_url();
		$records = array();
		$imgFld = $this->imageFolder;
		$shareImgFld = WP_PLUGIN_URL . "/kybshares";
		$listInfo = array();
		
		if ($this->checkRequest('filtertype') != "") {
			$filtertype = $this->checkRequest('filtertype');
			switch ($filtertype) {
				case "search":
					$keywords = $this->checkRequest('keywords');
					$sql = "Select * from " . $this->table . "form_info where form_type_id = %d and comments like %s order by signature_date desc";
					$adminUrl = $this->adminURL . "&process=submissions&formID=$formID&filtertype=$filtertype&keywords=$keywords";
					$result = $wpdb->get_results($wpdb->prepare($sql, $formID, $keywords));
				break;
				default:
					$sql = "Select * from " . $this->table . "form_info where form_type_id = %d order by signature_date desc";
					$adminUrl = $this->adminURL . "&process=submissions&formID=$formID";
					$result = $wpdb->get_results($wpdb->prepare($sql, $formID));
				break;
			}
		} else {
			$sql = "Select * from " . $this->table . "form_info where form_type_id = %d order by signature_date desc";
			$adminUrl = $this->adminURL . "&process=submissions&formID=$formID";
			$result = $wpdb->get_results($wpdb->prepare($sql, $formID));
		}		
		$TotalRowCount = count($result);
		$limit = 25;		
		isset($_GET['p']) ? $pagenum = $this->cleanString($_GET['p']) : $pagenum = 1;
			
		$speaker_range = $this->listRange($limit, $TotalRowCount, $pagenum);
		if (is_array($speaker_range)) {
			$start = $speaker_range[0];
			$end = $speaker_range[1];
			$offset = $speaker_range[2];
			$pages = $speaker_range[3];
		}
		
		$pageturns = $this->showNextPrev ($offset, $pagenum, $pages, $limit, $TotalRowCount, $adminUrl);
		$curr = 0;
		if (count($result) != 0) {
			$c = 0;
			$cnt = 0;
			foreach ($result as $row) {
  			  $curr = $cnt + 1;
			  if ($curr >= $start && $curr <= $end) {
				$items = explode(",", $row->comments);
				foreach ($items as $key => $value) {
					$subItems = explode("=>", $value);
					$label = $subItems[0];
					$v = $subItems[1];
					$findfirst = 'first';
					$findlast = 'last';
					$findtitle = 'title';
					$findemail = 'email';
					$findname = 'name';
					
					$f = strpos(strtolower($label), $findfirst);
					$l = strpos(strtolower($label), $findlast);
					$t = strpos(strtolower($label), $findtitle);
					$e = strpos(strtolower($label), $findemail);
					$n = strpos(strtolower($label), $findname);

					if ($f !== false) {
						$records[$c]['firstname'] = $v;
					}

					if ($l !== false) {
						$records[$c]['lastname'] = $v;
					}

					if ($t !== false) {
						$records[$c]['title'] = $v;
					}

					if ($e !== false) {
						$records[$c]['email'] = $v;
					}

					if ($n !== false) {
						
						$fullname = explode(" ", $v);
						if (!isset($records[$c]['firstname'])) {
							$records[$c]['firstname'] = $fullname[0];
						}

						if (!isset($records[$c]['lastname'])) {
							$records[$c]['lastname'] = $fullname[1];
						}
					}
				}
				$records[$c]['signature_date'] = $row->signature_date;
				$records[$c]['form_id'] = $row->form_id;
				$records[$c]['authNum'] = $row->auth_num;
				$records[$c]['transID'] = $row->trans_id;
				$records[$c]['payAmt'] = "$" . number_format($row->payment_amt, 2);
				$c++;
			  }
			  $cnt++;
			}
			
		}
		

		if (count($records) != 0) {
			for ($i=0;$i<count($records);$i++) {
				$submissionName = "";
				$submissionEmail = "";
				$payAmt = $records[$i]['payAmt'];
				$authNum = $records[$i]['authNum'];
				$transID = $records[$i]['transID'];
				$form_id = $records[$i]['form_id'];

				$id = $records[$i]['form_id'];
				if ($records[$i]['firstname'] != "" || $records[$i]['lastname'] != "") {
					$submissionName = $records[$i]['firstname'] . " " . $records[$i]['lastname'];
				}
				if ($records[$i]['email'] != "") {
					$submissionEmail = "<a href=\"mailto:{$records[$i]['email']}\">{$records[$i]['email']}</a>";
				}
				$createDate = $this->ReformatDatedb($records[$i]['signature_date']);
				$editLink = "$adminURL&process=formedit&formID=$formID&submissionID=$id";
				$deleteLink = "$adminURL&process=deleteform&formID=$formID&submissionID=$id";
				$delete = $this->_doIcon("javascript:void(0)", "javascript:checkSure('$deleteLink')", "glyphicons remove_2 icon_gray fs5", '');
				$edit = $this->_doIcon("$editLink", "", "glyphicons edit icon_gray fs5", '');
				$listcontent = array(
					$form_id, $createDate, $submissionName, $submissionEmail, $edit, $delete
				);
				$alignset = array('center','center','left','left','center','center');
				$form_list .= $this->buildRow2($listcontent, '', '', count($listcontent), $alignset, '', '', '', '', '', 'top');				
			}
		} else {
			$listcontent = array(
				'There are currently no listings to view.'
			);
			$form_list = $this->buildRow2($listcontent, '', '', count($listcontent), '', '', '6', '', '', '', 'top');
		}

		$listInfo = array($form_list, $pageturns, $start, $end, $TotalRowCount);
		return $listInfo;
		
	}

	public function listRange($limit, $rowCount, $pagenum) {
		$offset = ($pagenum - 1) * $limit; 
		

		if ($limit > 0) { 
			$pages = intval($rowCount / $limit); 
		} else { 
			$pages = 1; 
		} 
		
		if ($rowCount % $limit || $pages == 0) { 
			$pages++; 
		} 

	
		if ($pagenum > $pages)    { 
			// set $page to the last page and update $offset accordingly 
			$pagenum = $pages; 
			$offset = ($pagenum - 1) * $limit; 
		} elseif ($pagenum < 0) { 
			// Page cannot be negative, so we default to the first page 
			$pagenum = 1; 
			$offset = 0; 
		} 

		$start = $offset + 1;
		$end = ($start + $limit) - 1;

		if ($end > $rowCount) {
			$end = $rowCount;
		}
		
		$range = array($start,$end, $offset, $pages);
		return $range;
	}

	public function formInfo ($formID, $pagenum, $submit, $keyword, $criteria, $action) {
		$wpdb = $this->wpdb;
		$returnMsg = "Return To Main Listings";
		$returnURL = $this->adminURL;
		$this->checkRequest('pagenum') != "" ? 	$pagenum = $this->checkRequest('pagenum') : $pagenum = 1;
		$search = false;
		$limit = 20;
		$fieldTitle = array();
		$orderfield = "signature_date";
		$submissionUrl = $this->adminURL . "&process=submissions&formID=$formID";
		//Get Form Type Header
		
		if ($submit != "Search" && $keyword != "") {
			$search = true;
		}

		$selectOptions = '<option value="all">All</option><option value="name">First Name</option><option value="name">Last Name</option>';
		//======== Get Form Name ========//
		$sql = "Select form_title from " . $this->table . "forms where form_id = %d";
		$formName = $wpdb->get_var($wpdb->prepare($sql, $formID));
		
		$navInfo = $this->get_offset($pagenum, '', $limit, $formID, $search, 'form_info', 'form_id' , 'form_id', $criteria, $keyword, $orderfield, true);
		$listInfo = $this->formSubmissions($navInfo, $formID);		
		$listings = $listInfo[0];
		$pageTurns = $listInfo[1];
		$start = $listInfo[2];
		$end = $listInfo[3];
		$totalRows = $listInfo[4];
		$url = $this->adminURL . "&process=$action&formtypeID=$formtypeID";
		$addNewUrl = $this->adminURL . "&process=edit&formID=$formtypeID";
		/*$filterOptions = <<<OPTIONS
			<option value="">Filter Actions</option>
			<option value="submission_name">Name/Title</option>
			<option value="submission_date">Submission Date</option>
OPTIONS;*/


		//=== If listing is showing from an update and is associated with a product, create links to return to product form. ========//
		if (isset($_REQUEST['product_id'])) {
			$returnMsg = "Return To Product Listing";
			$productID = $this->checkRequest('product_id');
			$returnURL = "?page=kybstore/store_plugin.php&view=update&ID=$productID";
		} 
		$template = "form_submission_list.html";
		$content = array (
			'TITLE' => "",
			'ADMINURL' => $this->adminURL,
			'SUBMISSIONURL' => $submissionUrl,
			'PLUGINURL' => $this->pluginURL,
			'FORMID' => "$formID",
			'RETURNURL' => $returnURL,
			'RETURNMSG' => $returnMsg,
			'ADDNEWURL' => $addNewUrl,
			'TYPEID' => "$formtypeID",
			'LISTINGS' => $listings,
			'WEEKLYFEATURES' => "",
			'FEATURES' => "",
			'PAGETURNS' => $pageTurns,
			'START' => "$start",
			'END' => "$end",
			'LISTCOUNT' => "$totalRows",
			'SELECTOPTIONS' => $selectOptions,
			'HEADER' => $formName,
			'HTML' => $template
		);
		return $content;
	}

	

	public function getSectionlist($type, $section, $records, $title, $order) {
		$wpdb = $this->wpdb;
		switch ($type) {
			case 1 :
				//======== Get form field types ========//
				$sectionTitle = $section;
				
				if (is_array($records)) {	
					$num = count($records);
					$sectionitem = $title[0];
					for ($i=0; $i<=$num-1; $i++) {
						$id = $records[$i];
						$sectionTitle = $title[$i];
						$sectionOrder = $order[$i];
						if ($sectionitem == $sectionTitle) {
							$sectionOption .= "<option value=\"".$sectionOrder."-" . $id ."\" selected=\"selected\">" . $sectionTitle . "</option>";
						} else {
							$sectionOption .= "<option value=\"".$sectionOrder."-" . $id ."\">" . $sectionTitle . "</option>";
						}
					}
				} 
				$sectionlist = $this->_doSelect ('selsection', 'selsection', $sectionOption, false, 'selectpicker', "enterText(this);", '');
				$sectionlist .= "&nbsp;&nbsp;<a href=\"javascript:void(0);\" onclick=\"addTitle()\">Add New Section</a>";
			break;
			case 2:				
				
				$sectionOption = "<option value=\"\" selected=\"selected\">Select...</option>";
				$sqlList = "Select * from " . $this->table . "form_sections where form_id = %d order by section_order";
				$resultList = $wpdb->get_results($wpdb->prepare($sqlList, $formID));
				if (count($resultList) != 0) {
					foreach ($resultList as $s) {
						$id = $s->section_id;
						$sectionTitle = $s->section_title;
						$sectionOrder = $s->section_order;
						$sectionOption .= "<option value=\"".$sectionOrder."-" . $id ."\" >" . $sectionTitle . "</option>";
					}
				}
				$sectionlist = $this->_doSelect ('selsection', 'selsection', $sectionOption, false, 'selectpicker', "enterText(this);return toggleMenu(this,'paymentArea')", '');
				$sectionlist .= "&nbsp;&nbsp;<a href=\"javascript:void(0);\" onclick=\"addTitle()\">Add New Section</a>";
			break;
		}
		return $sectionlist;
	}

	public function get_submission_info($submissionID) {
		$wpdb = $this->wpdb;
		$submitInfo = array();
		$sql = "Select * from " . $this->table . "form_info where form_id = %d";
		$row = $wpdb->get_row($wpdb->prepare($sql, $submissionID));
		if (count($row) != 0) {
			$comments = explode(",", $row->comments);
			
			foreach ($comments as $key => $value) {
				$info = explode("=>", $value);
				if ($info[0] != "") {
					$submitInfo[$info[0]] = $info[1];
				}
			}
		}
		return json_decode(json_encode($submitInfo));
	}

	public function processElements($formID,$payment,$upload) {
		$sectionPayTitle = "Billing Information";
		$sectionFormTitle = "Upload Files";
		$wpdb = $this->wpdb;
		if (!$payment) {
			//Get section ID
			$sqlsection = "Select section_id from " . $this->table . "form_sections where section_title = %s and form_id = %d";
			$sectionID = $wpdb->get_var($wpdb->prepare($sqlsection, $sectionPayTitle, $formID));
			if ($sectionID != "") {
				$this->clearSections ($sectionID, $formID);
			}
		} else{
			//$this->processFields($sectionPayTitle, $formID);
		}

		if (!$upload) {
			//Get section ID
			$sqlsection = "Select section_id from " . $this->table . "form_sections where section_title = %s and form_id = %d";
			$sectionID = $wpdb->get_var($wpdb->prepare($sqlsection, $sectionFormTitle, $formID));
			if ($sectionID != "") {
				$this->clearSections ($sectionID, $formID);
			}
		} else{
			$this->processFields($sectionFormTitle, $formID);
		}
	}

	public function processFields($sectionTitle, $formID) {
		$wpdb = $this->wpdb;
		//======== First check to see if this section already exists  ========
		$sqlcheck = "Select section_id from " . $this->table . "form_sections where section_title = %s and form_id = %d";
		$resultcheck = $wpdb->get_results($wpdb->prepare($sqlcheck, $sectionTitle, $formID));
		
		if (count($resultcheck) == 0) {			
			//======== Get last section order ======//
			$sqlorder = "Select MAX(section_order) as LASTORDER from " . $this->table . "form_sections where form_id = %d";
			$sorder = $wpdb->get_var($wpdb->prepare($sqlorder, $formID));
			if ($sorder == "") {
				$sorder = 1;
			}
			//======== Insert open section into database ========//
			$sqlsection = "INSERT INTO " . $this->table . "form_sections (section_title, section_order, form_id) VALUES (%s, %d, %d)";
			$wpdb->query($wpdb->prepare($sqlsection, $sectionTitle, $sorder, $formID));
			$sectionID = $this->lastID($this->table . 'form_sections', 'section_id');
			switch ($sectionTitle) {
				case "Billing Information" :
					//======== Obtain section ID and enter contact information fields with PayPal name requirements ======== //					
					$field_names = array('first_name', 'last_name', 'address1', 'address2', 'city', 'state', 'country', 'zip', 'email');
					for ($i=0;$i < count($field_names); $i++) {
						$options = "";
						$required = 1;
						$fieldType = 1;
						$fieldOrder = $i + 1;
						switch ($field_names[$i]) {
							case "address2":
								$required = 0;
							break;
							case  "state": 
								$fieldType = 3;
							break;
							case  "country": 
								$fieldType = 3;
							break;
							case "email":
								$required = 0;
							break;				
						}
						$sqlInsert = "INSERT INTO " . $this->table . "form_fields (form_id, field_name, field_type_id, section_id, field_order, field_required, field_options) VALUES (%d, %s, %d, %d, %d, %d, %s)";
						$wpdb->query($wpdb->prepare($sqlInsert, $formID, $field_names[$i], $fieldType, $sectionID, $fieldOrder, $required, $options));
					}
				break;
				case "Upload Files":
					$options = "";
					$required = 0;
					$fieldType = 6;
					$fieldOrder = 1;
					$fieldName = "Upload File";
					$sqlInsert = "INSERT INTO " . $this->table . "form_fields (form_id, field_name, field_type_id, section_id, field_order, field_required, field_options) VALUES (%d, %s, %d, %d, %d, %d, %s)";
					$wpdb->query($wpdb->prepare($sqlInsert, $formID, $fieldName, $fieldType, $sectionID, $fieldOrder, $required, $options));
				break;
			}
		}
	}	

	public function clearSections ($sectionID, $formID) {
		$wpdb = $this->wpdb;
		//Delete form sections
		$sqlsections = "DELETE FROM " . $this->table . "form_sections WHERE section_id=%d";
		$wpdb->query($wpdb->prepare($sqlsections, $sectionID));
	
		//Delete form fields
		$sqlfields = "DELETE FROM " . $this->table . "form_fields WHERE section_id = %d and form_id= %d";
		$wpdb->query($wpdb->prepare($sqlfields, $sectionID, $formID));
	}

	public function GetFieldTypes () {
		$wpdb = $this->wpdb;
		$sql = "Select * from " . $this->table . "form_field_types where status = '1'";
		$result = $wpdb->get_results($sql);
		foreach($result as $row) {
			$field_type_id = $row->ff_type_id;
			$field_name = $row->ff_type_name;
			$fieldtypes .= "<tr><td><input type=\"radio\" name=\"fftype\" id=\"fftype\" value=\"" . $field_type_id . "\"/>&nbsp;" . $field_name . "</td></tr>";					
		}
		return $fieldtypes;
	}

	/*Form Frontend Vew Processes */
	public function front_view($formID, $admin, $process) {
		$wpdb = $this->wpdb;
		$showListing = $wpdb->get_var($wpdb->prepare("Select form_listing from " . $this->table . "forms where form_id = %d", $formID));
		$formview = $this->checkRequest('formview');
		if ($formview == "1") {
			$content = $this->formview($process, $formID, '', $admin);
		} else if ($showListing == 1) {
			$content = $this->formlistings($formID);
		} else {
			$content = $this->formview($process, $formID, '', $admin);
		}
		return $content;
	}

	public function front_submit($formID) {
		$status = 1;
		if (isset($_POST['validator']) && $_POST['validator'] != $_SESSION["code"]) {
			$status = 0;
		} else if (!isset($_POST['validator'])) {
			$status = 0;
		} else if (isset($_POST['phone'])) {
			switch ($_POST['phone']) {
				case "123456":
					$status = 0;
				break;
				case "12345":
					$status = 0;
				break;
				case "1234":
					$status = 0;
				break;
				case "123":
					$status = 0;
				break;
				case "Your Contact Number:":
					$status = 0;
				break;
				default:
					$submitInfo = $this->formsubmit();
					$status = $submitInfo[0];
				break;
			}
		} else if (isset($_POST['contactemail'])) {
			$bademails = array('sina.com', 'mail.ru');
			foreach ($bademails as $key => $value) {
				if(strpos($_POST['contactemail'], $value) !== FALSE) {
					$status = 0;
				}
			}
		} 
										
		if ($status == 1) {
			$submitInfo = $this->formsubmit();
			$status = $submitInfo[0];
			if ($status == 1) {
				$submissionID = $submitInfo[8];
				$this->sendformemail($submissionID);
				$content = $this->formsubmitprocess($submitInfo);
			} else {
				$content = $this->formview ('view', $formID, $submitInfo, false);
			}
		} else {
			$content = $this->formview ('view', $formID, $submitInfo, false);
		}
		return $content;
	}

	public function front_complete($formID) {
		$wpdb = $this->wpdb;
		if (isset($_SESSION['submitInfo']) && is_array($_SESSION['submitInfo']) && count($_SESSION['submitInfo']) != 0 && $_SESSION['submitInfo']['submitstatus'] == 0) {				
			//Transfer session information to post
			$formID = $_SESSION['submitInfo']['submissionInfo']['formID'];
			$_POST = $_SESSION['submitInfo']['submissionInfo'];
			
			$submitInfo['submitstatus'] = 0;
			$submitInfo['res_txt'] = $_SESSION['submitInfo']['reason'];
			$submitInfo = json_decode(json_encode($submitInfo), FALSE);
			$content = $this->formview ('view', $formID, $submitInfo);
		} else {
			
			if ($this->checkRequest('ID') != "") {
				//======== Get form Information ========//	
				$row = $wpdb->get_row($wpdb->prepare("Select * from " . $this->table . "forms where form_id = %d", $formID));
				$output = "<h1>" . $row->form_title . "</h1>";
				$output .= apply_filters('the_content', $row->form_response);
				$submissionID = $this->checkRequest('ID');
				$template = "output.html";
				$content = array(
					'CONTENT' => $output,
					'HTML' => $template
				);
			} else {
				$content = $this->formview('view', $formID,'');
			}
		}
		return $content;
	}

	public function adminview ($process, $formID, $submitInfo = array(), $admin=false) {
	}

	public function formview ($process, $formID, $submitInfo = array(), $admin=false) {		
		$wpdb = $this->wpdb;
		global $post;
		
		if (isset($_SESSION['submitInfo']) && is_array($_SESSION['submitInfo']) && count($_SESSION['submitInfo']) != 0) {
			$submitStatus = $_SESSION['submitInfo']['submitstatus'];
			$res_txt = $_SESSION['submitInfo']['reason'];
			$outcome = "<h2 align='center' style='color:red;'>We're sorry, your submission was unsuccessful for the following reasons.<br><br>{$_SESSION['submitInfo']['reason']}<br><br>Please review the form below and resubmit.</h2>";
			$submissionID = 0;
		} else {
			$submitStatus = $submitInfo[0];
			$res_txt = $submitInfo[2];
			$outcome = $submitInfo[2];
			$submissionID = $submitInfo[8];
		}
		$fields = "";
		$pluginURL = $this->pluginURL;
		$userPluginURL = plugins_url() . "/kybformbuilder";
		$imageFolder = $this->imageFolder;
		$adminURL = $this->adminURL;
		$siteURL = site_url();
		$printLink = "";
		$returnLink = "";
		$checkPay = false;
		$required = "";
		$reqTitles = "";
		$formInstructions = "";
		$cnt = 1;
		if ($_SERVER['SERVER_PORT'] == 443) {
			$pluginURL = str_replace("http", "https", $pluginURL);
			$userPluginURL = str_replace("http", "https", $userPluginURL);
		}
		$sectionTitle = $wpdb->get_var($wpdb->prepare("Select section_title from " . $this->table . "form_sections where form_id = %d and section_order = 1", $formID));
		//======== Query to get form data ========//
		$sql = "Select * from " . $this->table . "forms where form_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($sql, $formID)); 
		
		$sqlsections = "Select * from " . $this->table . "form_sections where form_id = %d and status = 1 order by section_order";
		$resultsections = $wpdb->get_results($wpdb->prepare($sqlsections, $formID)); 
		
		foreach ($result as $row)  {
			//Get form information variables
			$formTitle = $row->form_title;
			$formLink = $row->form_link;
			if ($submitStatus == 0 || $submitStatus == 3) {
				$formInstructions = apply_filters('the_content',$row->form_instructions);
			}
			$formEmail = $row->email_responses;
			$formExpiration = $row->form_expiration;
			$formResponse = apply_filters('the_content',$row->form_response);
			$formPayment = $row->form_payment;
			$formUpload = $row->form_fileupload;
			$form_wp_page = $row->form_wp_page;
			$createdate = explode('-', $formExpiration);
			$expdate =  mktime(0,0,0,$createdate[1],$createdate[2],$createdate[0]);

			$formAlign = $row->form_align;
			$sectionColor = $row->section_title_color;
			$sectionBorderShow = $row->section_border_show;
			$sectionBorderColor = $row->section_border_color;
			$labelColor = $row->label_color;
			$labelBold = $row->label_bold;
			$inputBg = $row->input_bg;
			$showListing = $row->form_listing;
		}

		if ($process == "print") {
			$formInstructions = "";
		}

		!$sectionBorderShow ? $fieldBorder = "style='border:0px;'" : $fieldBorder = "style='border: 1px solid #{$sectionBorderColor};'";
		!$labelBold ? $labelStyle = "style='color:#{$labelColor}'" : $labelStyle = "style='color:#{$labelColor};font-weight:bold;padding-bottom:2px;'";
		$sectionColor != "" ? $fieldColor = "style='color:#{$sectionColor}'" :$fieldColor = "";
		

		//If no sections just yet notate that we are awaiting sections
		if (count($resultsections) == 0) { 
			if ($process == "view") {
				$fields .= "<p class=\"dbmessage\">This form is currently unavailable.</p>";
			} else {
				$fields .= "Awaiting Sections <br>";
			}
		} elseif ($process == "view" && $formExpiration == 1 && $expdate < time()) {
			$fields .= "<p class=\"dbmessage\">This form is currently not active or has expired.</p>";
		} else {

			if ($process == "print") {
				$fields .= "<link href=\"$pluginURL/css/form_styles.css\" rel=\"stylesheet\" type=\"text/css\" />";
			}
			if ($process == "print") {
				$submissionURL =  $this->adminURL;
				$fields .= "<form name=\"SubmissionForm\" action=\"$submissionURL\" method=\"POST\">";
				$submissionURL =  $this->adminURL;
				$submissionID = $this->checkRequest('submissionID');
				$fields .= "<input type='hidden' name='formID' value=\"$formID\">";
				$fields .= "<input type='hidden' name='process' value=\"submissionupdate\">";
				$fields .= "<input type='hidden' name='submissionID' value=\"$submissionID\">";

			}

			
			if ($process == "view" || $process == "cmsview" || $process == "update" || $this->checkRequest('preview') == "1") {
				//Get required field names
				$sqlfields = "Select * from " . $this->table . "form_fields where form_id = %d and field_required = 1 order by section_id,field_order";
				$resultfields = $wpdb->get_results($wpdb->prepare($sqlfields,$formID));
				if (count($resultfields) != 0) {
					foreach($resultfields as $r) {
						
						$fieldType = $r->field_type_id;
						if ($fieldType == 8) {
							$fieldName = trim($r->field_name);
							$fieldshort = trim($r->field_short_name);
							$required .= "{$fieldshort}phone0,{$fieldshort}phone1,{$fieldshort}phone2,";
							$reqTitles .= "$fieldName Area Code,$fieldName Prefix,$fieldName Suffix,";
						} else if ($fieldType == 13) {//Required fields for donations
							$required .= "item, ";
							$reqTitles .= "Payment Type and Amount, ";
						} else {
							$r->field_short_name != "" ? $required .= $r->field_short_name . "," : $required .= $r->field_name . ",";
							$reqTitles .= trim($r->field_name) . ",";
						}
						
					}
					
					//Remove last comma from string
					$required = substr($required,0,strlen($required)-1);
					$reqTitles = substr($reqTitles,0,strlen($reqTitles)-1);
				}
				if ($formPayment) {
					$checkPay = false;
				}

				
				if ($formAlign != "") {
					$fields .= "<div align=\"$formAlign\"><div >";
				}
				//$currentpage = get_permalink($form_wp_page);
				$currentpage = plugins_url() . "/kybformbuilder/model/form_ajax.php";
				$fields .= "<link href=\"$pluginURL/css/form_styles.css\" rel=\"stylesheet\" type=\"text/css\" />";
				if ($submitStatus == 0) {
					$fields .= $outcome;
				}
				$fields .= "<form name=\"SubmissionForm\" id=\"formbuilder\" action=\"$currentpage\" method=\"POST\" onsubmit=\"return formCheck(this,'" . $required . "', true, '$checkPay','$reqTitles')\">";
				$fields .= "<input type='hidden' name='formID' value=\"$formID\">";
				$fields .= "<input type='hidden' name='process' value=\"formsubmit\">";
				$fields .= "<input type='hidden' name='page_id' value=\"$form_wp_page\">";
				if ($formID == 2) {
					$fields .= "<input type=\"hidden\" name=\"redirect\" value=\"$siteURL/online-training/?vid=5\">";
					$fields .= "<input type=\"hidden\" name=\"checkdb\" value=\"sample_email\">";
				}
				if (isset($submissionID)) {
					$fields .= "<input type='hidden' name='submissionID' id='submissionID' value='$submissionID'>";
				}

			}
			
			
			foreach ($resultsections as $rowsections) {
				$section_name = $rowsections->section_title;
				$section_id = $rowsections->section_id;
				$section_order = $rowsections->section_order;
				$section_columns = $rowsections->section_columns;
				if ($process != "view" && $process != "print" ) {
					$fields .= "<br>";
					$fields .= "<div  class=\"required\">";
					//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=movesectionup&action=movesectionup&order=$section_order&sectionID=$section_id')", "glyphicons up_arrow icon_gray fs3", '');
					$fields .= "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=movesectionup&action=movesectionup&order=$section_order&sectionID=$section_id')\"><img src=\"$pluginURL/images/arrowup_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/arrowup_on.gif';window.status='Move Section Up'; return true;\" onmouseout=\"this.src='$pluginURL/images/arrowup_off.gif';window.status=''; return true;\" alt=\"Move Section Up\" border=\"0\"/></a>";
					$fields .= "&nbsp;";
					//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=movesectiondown&action=movesectiondown&order=$section_order&sectionID=$section_id')", "glyphicons down_arrow icon_gray fs3", '');
					$fields .= "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=movesectiondown&action=movesectiondown&order=$section_order&sectionID=$section_id')\"><img src=\"$pluginURL/images/arrowdown_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/arrowdown_on.gif';window.status='Move Section Down'; return true;\" onmouseout=\"this.src='$pluginURL/images/arrowdown_off.gif';window.status=''; return true;\" alt=\"Move Section Down\" border=\"0\" /></a>";
					$fields .= "&nbsp;";		
					//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:CheckSure('$userPluginURL/model/form_view.php?formID=$formID&sectionID=$section_id&process=deletesection&action=deletesection')", "glyphicons remove_2 icon_gray fs3", '');
					$fields .= "<a href=\"javascript:CheckSure('$userPluginURL/model/form_view.php?formID=$formID&sectionID=$section_id&process=deletesection&action=deletesection');DeleteItem($section_id); \"><img src=\"$pluginURL/images/delete_off.gif\" onmouseover=\"this.src='$pluginURL/images/delete_on.gif';window.status='Delete Section'; return true;\" onmouseout=\"this.src='$pluginURL/images/delete_off.gif'; window.status=''; return true;\" alt=\"Delete Section\" border=\"0\"/></a>";
					$fields .= "&nbsp;";			
					//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=editsection&action=editsection&sectionID=$section_id')", "glyphicons edit icon_gray fs3", '');
					$fields .= "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&process=editsection&action=editsection&sectionID=$section_id')\"><img src=\"$pluginURL/images/edit_off.gif\" onmouseover=\"this.src='$pluginURL/images/edit_on.gif';window.status='Edit Section'; return true;\" onmouseout=\"this.src='$pluginURL/images/edit_off.gif'; window.status=''; return true;\" alt=\"Edit Section\" border=\"0\"/></a></div>";
					//$fields .= "<div class='clear'></div><br>";
				}

				if ($process == "view" || $process == "print") {
					$formLink != "" ? $currentpage = $formLink : $currentpage = $_SERVER['SCRIPT_URI'];

				}

				/*if ($section_name == "Billing Information") {
					$paytemplate = $this->pluginDir . "/view/html/paymentfields.html";
					$paycontent = array (
						'STYLE3' => $style3
					);
					$paypage = new FillPage ($paytemplate);
					$paypage->replace_tags($paycontent);
					$fields .= "<fieldset $fieldBorder><legend $fieldColor>Registration Information</legend>";
					$fields .= $paypage->viewoutput();
					$fields .= "</fieldset>";
					$fields .= "<br>";
				}*/
				
				$fields .= "<fieldset $fieldBorder><legend $fieldColor>".$section_name."</legend>";

				$sqlfields = "Select * from " . $this->table . "form_fields where form_id = %d and section_id = %d and status = 1 order by field_order";
				$resultfields = $wpdb->get_results($wpdb->prepare($sqlfields,$formID,$section_id));
				
				//======== If no fields then notate that we are awaiting fields ========//
				if ($resultfields === false) { 
					$fields .= "Awaiting Fields";
				} else { 
					if ($this->checkRequest('submissionID') != "") {
						$submitFields = array();
						$submissionID = $this->checkRequest('submissionID');
						$sql = "Select * from " . $this->table . "form_info where form_id = %d";
						$result = $wpdb->get_results($wpdb->prepare($sql,$submissionID));
						if (count($result) != 0) {
							$c = 0;
							foreach ($result as $row) {
								$items = explode(",", $row->comments);
								$transID = $row->trans_id;
								$authNum = $row->auth_num;
								$payAmt = $row->payment_amt;
								foreach ($items as $key => $value) {
									$subItems = explode("=>", $value);
									$label = str_replace(" ", "_", $subItems[0]);
									$v = $subItems[1];
									$submitFields[$label] = $v;
								}
							}
						}
					}

					

					
					
					$fieldoptions = array();
					$fieldoptionValues = array();
					$fields .= "<table cellpadding=\"2\" cellspacing=\"2\" width=\"100%\" >";
					
					foreach ($resultfields as $rowfields) {
						$formname = $section_id . "-" . $rowfields->id . "-" . $formID;
						$fieldname = $rowfields->field_name;
						$fieldShortName = $rowfields->field_short_name;
						$fieldPlacement = $rowfields->field_placement;
						$fieldTitlePlacement = $rowfields->field_title_placement;
						$fieldInstruction = $rowfields->field_instruction;
						$fieldIcon = $rowfields->field_instruction_icon;
						$fieldViewPlacement = $rowfields->view_placement;
						$fieldOrder = $rowfields->field_order;
						$fieldPara = $rowfields->field_para;
						$fieldScript = $rowfields->field_script;
						$fieldSpan = $rowfields->field_span;
						$fieldTypeID = $rowfields->field_type_id;
						$submitValue = $rowfields->field_value;
						$identification = "";
						$orderInfo = "";
						$this->checkRequest($fieldShortName) != "" ? $submitValue = $this->checkRequest($fieldShortName) : "";
						if ($fieldTypeID == 12 || $fieldTypeID == 13) {
							$orderInfo = $submitFields['orderitems'];
						}
						$fieldSpan != 0 ? $colSpan = "colspan=\"$fieldSpan\"" : $colSpan = "colspan=''";
						if (isset($submitFields) && count($submitFields) != 0 ) {
							
							if (!isset($submitFields[$fieldname])) {
								$submitValue = $submitFields[$fieldShortName];
							} else {
								$submitValue = $submitFields[$fieldname];
							}
						} 

						if (isset($_SESSION['submitInfo']) && is_array($_SESSION['submitInfo']) && count($_SESSION['submitInfo']) != 0 && $process == "view") {
							$submitValue = $_SESSION['submitInfo']['submissionInfo'][$fieldShortName];
						}
						if ($fieldPara != "") {
							preg_match('/{\w+\}/', $fieldPara, $shortcode);
							if (count($shortcode) != 0 ) {
								$sc = str_replace("{", "", $shortcode[0]);
								$sc = str_replace("}","", $sc);
								switch ($sc) {
									//Here where special codes are performed
								}
								$fields .= "<tr><td colspan=\"6\"><p><i>$fieldPara</i></p></td></tr>";
							} else {
								$fields .= "<tr><td colspan=\"6\"><p><i>$fieldPara</i></p></td></tr>";
							}
						}

						if ($fieldShortName != "") {
							$identification = $fieldShortName;
						} else {
							$identification = $fieldname;
						}
										
						$field_id = $rowfields->id;
						$options = $rowfields->field_options;
						$optionValues = $rowfields->field_option_values;
						$optionInstructions = $rowfields->field_option_instructions;
						$optionSelections = $rowfields->field_option_selections;
						$order = $rowfields->field_order;
						$cost = "$" . number_format($rowfields->field_cost,2);
						
						if ($cnt > $section_columns) {
							$fields .= "<tr>";
						} elseif ($fieldSpan > 0) {
							$fields .= "<tr>";
						}
						
						if ($rowfields->field_required == 1) {	
							if ($fieldTitlePlacement) {
								$fields .= "<td class=\"label\" $labelStyle $colSpan><font color=\"red\">*</font><b>$fieldname&nbsp;:&nbsp;</b>";
								$fields .= "</td>";
							}
						} else {							
							switch ($section_name) {
								default:
									if ($fieldTitlePlacement) {
										if ($formPayment && isset($altfieldname)) {
											$fields .= "<td class=\"label\" $labelStyle $colSpan><b>$altfieldname&nbsp;:&nbsp;</b></td>";
										} else { 
											$fields .= "<td class=\"label\" $labelStyle $colSpan><b>$fieldname&nbsp;:&nbsp;</b></td>";
										}
									} 
								break;
							}
						}
						$fieldMulti == 1 ? $nameID = $identification . "[$fieldKey]" : $nameID = $identification;

						$fields .= "<td $labelStyle $colSpan>";
						switch ($rowfields->field_type_id) {
							case 1: //regular text area
								$fields .= $this->get_regulartxt($fieldTitlePlacement, $rowfields->field_required, $fieldname, $submitStatus, $submitValue, $identification, $fieldInstruction, $fieldIcon);
							break;								
							case 2: //checkboxes
								$submitValue = explode(":",$submitValue);
								$fields .= $this->get_checkboxes($options, $optionValues, $fieldTitlePlacement, $rowfields->field_required, $fieldSpan, $fieldname, $fieldInstruction, $fieldIcon, $submitValue, $identification, $fieldViewPlacement, $labelColor);
							break;								
							case 3: //select options
								$fields .= $this->get_selectfield($options, $optionValues, $fieldTitlePlacement, $rowfields->field_required, $fieldSpan, $fieldname, $labelColor, $identification, $submitValue, $formID, 'lgregular');
							break;

							case 4: //textarea
								$fields .= $this->get_textareafield($fieldTitlePlacement, $rowfields->field_required, $fieldname, $fieldInstruction, $fieldIcon, $submitStatus, $submitValue, $identification);
							break;
							case 5: //radio options
								$fields .= $this->get_radiofield($fieldTitlePlacement, $rowfields->field_required, $fieldname, $labelColor, $fieldSpan, $options, $optionValues, $fieldViewPlacement, $fieldScript, $submitValue, $identification, $nameID, $fieldViewPlacement, $fieldInstruction, $fieldIcon);
							break;
							case 6: //file upload
								$fields .= "<input type=\"file\" name=\"form[$identification][]\" id=\"$identification\" />";
							break;
							
							case 7:
								$fields .= $this->get_longtextfield($fieldTitlePlacement, $rowfields->field_required, $fieldname, $submitStatus, $submitValue, $identification, $fieldInstruction, $fieldIcon);
							break;
							case 8://Phone number
								$fields .= $this->get_phonefield($identification, $fieldShortName, $submitValue, $fieldTitlePlacement, $rowfields->field_required, $fieldname, $submitStatus, $fieldInstruction, $fieldIcon);
							break;
							case 9: //paragraph information.
								$fields .= $this->get_parafield($fieldTitlePlacement, $field_required, $fieldname, $options);
							break;

							case 10: //select dropdown
								$fields .= $this->get_selectfield($options, $optionValues, $fieldTitlePlacement, $field_required, $fieldSpan, $fieldname, $labelColor, $identification, $submitValue, $formID, 'regular');
							break;

							case 11: //file upload
								$fields .= $this->get_fileuploadfield($identification, $fieldInstruction, $fieldIcon);
							break;

							case 12: //Payment fields
								$fields .= $this->get_paymentfields ($options, $optionValues, $optionInstructions, $optionSelections, $orderInfo, $submitValue, $fieldScript, $formID, $process);
							break;
							case 13: //Donation Payment Fields
								$fields .= $this->get_paymentfields ($options, $optionValues, $optionInstructions, $optionSelections, $orderInfo, $submitValue, $fieldScript, $formID, $process);
							break;
							case 14: //Authorize.net fields
								$fields .= $this->get_paymentfields ($options, $optionValues, $optionInstructions, $optionSelections, $orderInfo, $submitValue, $fieldScript, $formID, $process);
							break;
						
							default:
								if (!$fieldTitlePlacement) {
									$fields .= "<b>$fieldname</b><br>";
									$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" style=\"width:140px\" value=\"$submitValue\" />";
								} else {
									$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" style=\"width:140px\"  value=\"$submitValue\"/>";
								}
							break;
						}				
						if ($process != "view" && $process != "print") {
							$fields .=  "<br>";
							//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=moveup&order=$order&sectionID=$section_id')", "glyphicons up_arrow icon_gray fs3", '');
							$fields .=  "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=moveup&order=$order&sectionID=$section_id')\"><img src=\"$pluginURL/images/arrowup_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/arrowup_on.gif';window.status='Move Field Up'; return true;\" onmouseout=\"this.src='$pluginURL/images/arrowup_off.gif';window.status=''; return true;\" alt=\"Move Field Up\" border=\"0\"/></a>";
							$fields .=  "&nbsp;";
							//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=movedown&order=$order&sectionID=$section_id')", "glyphicons down_arrow icon_gray fs3", '');
							$fields .=  "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=movedown&order=$order&sectionID=$section_id')\"><img src=\"$pluginURL/images/arrowdown_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/arrowdown_on.gif';window.status='Move Field Down'; return true;\" onmouseout=\"this.src='$pluginURL/images/arrowdown_off.gif';window.status=''; return true;\" alt=\"Move Field Down\" border=\"0\" /></a>";
							$fields .=  "&nbsp;";
							//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:CheckSure('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=deletefield')", "glyphicons remove_2 icon_gray fs3", '');
							$fields .=  "<a href=\"javascript:CheckSure('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=deletefield')\"><img src=\"$pluginURL/images/delete_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/delete_on.gif';window.status='Delete Field'; return true;\" onmouseout=\"this.src='$pluginURL/images/delete_off.gif';window.status=''; return true;\" alt=\"Delete Field\" border=\"0\"/></a>";
							$fields .=  "&nbsp;";
							//$fields .=  $this->_doIcon("javascript:void(0)", "javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=editfield&sectionID=$section_id')", "glyphicons edit icon_gray fs3", '');
							$fields .=  "<a href=\"javascript:showForm('$userPluginURL/model/form_view.php?formID=$formID&fieldID=$field_id&process=editfield&sectionID=$section_id')\"><img src=\"$pluginURL/images/edit_off.gif\" style=\"curser:pointer\" onmouseover=\"this.src='$pluginURL/images/edit_on.gif';window.status='Edit Field'; return true;\" onmouseout=\"this.src='$pluginURL/images/edit_off.gif';window.status=''; return true;\" alt=\"Edit Field\" border=\"0\" /></a>";
							//$fields .= "<div class='clear'></div><br>";
						}
						$fields .= "</td>";

						if ($cnt == $section_columns) {
							$fields .=  "</tr>";
							$cnt = 0;
						}	elseif ($fieldSpan > 0) {
							$fields .= "<tr>";
							$cnt = 0;
						}						
						$cnt++;						
					}
					$fields .=  "</table>";
					
				}
				$fields .=  "</fieldset>";
			}

			if ($submitStatus == 0 || $submitStatus == 3) {
				if ($formPayment && $process != "print") {
					$ccs = array('MasterCard','American Express','VISA');
					$ccselect = $this->checkRequest('cardtype');
					$ccoptions = "<option value=\"\">Select</option>";
					foreach ($ccs as $k => $v) {
						if ($v == $ccselect) {
							$ccoptions .= "<option value=\"$v\" selected>$v</option>";
						} else {
							$ccoptions .= "<option value=\"$v\">$v</option>";
						}
					}	
					$acctnum = $this->checkRequest('acctnum');
					$expmonth = $this->checkRequest('exp_month');
					$expyear = $this->checkRequest('exp_year');
					$cardholder = $this->checkRequest('cardholder');
					//$paymethod = $this->checkRequest('paymethod_integer');
					$paymethod = 1;

					switch ($paymethod) {
						case 0:
							$checkSelected = "checked";
							$ccSelected = "";
						break;
						case 1:
							$checkSelected = "";
							$ccSelected = "checked";
						break;
					}

					if ($paymethod == 0) {
						$checkSelected = "selected";
						$ccSelected = "";
					}
					$style1 = "style=\"display:block;\"";
					$style2 = "style=\"display:none;\"";
					$style3 = "style=\"display:none;\"";
					$paytemplate = $this->pluginDir . "/view/html/paymentfields.html";
					$paycontent = array (
						'CHECKADDRESS' => $formTitle,
						'ACCTNUM' => $acctnum,
						'EXPMONTH' => $expmonth,
						'EXPYEAR' => $expyear,
						'CARDHOLDER' => $cardholder,
						'CCOPTIONS' => $ccoptions,
						'CHECKSELECT' => $checkSelected,
						'CCSELECT' => $ccSelected,
						'STYLE1' => $style1,
						'STYLE2' => $style2,
						'STYLE3' => $style3
					);
					//$paypage = new FillPage ($paytemplate);
					//$paypage->replace_tags($paycontent);
					/*$fields .= "<fieldset $fieldBorder><legend $fieldColor>Registration Information</legend>";
					$fields .= $paypage->viewoutput();
					$fields .= "</fieldset>";*/
				}
				if ($process == "view" ) {
					//$fields .= $this->showValidator();
					//$fields .= "<div align=\"center\"><input style=\"width:300px;\" class='form2_blue_dkblue' type='text' name='validator' id='validator' value='Please enter letters as they appear above.'><br><br>";
					$fields .=  "<br>";
					$fields .= "<fieldset $fieldBorder><legend $fieldColor>Secure Submission</legend>";
					$url = plugins_url() . "/kybformbuilder/model/form_ajax.php?process=createcaptcha";
					$fields .= "<p align='center'><span id='captcha'></span>&nbsp;<a href=\"javascript:void(0);\" onclick=\"getcaptcha2('$url')\"><img src='$userPluginURL/images/btn_refresh.png' border='0'></a></p>";
					$fields .= "<script type=\"text/javascript\">getcaptcha2('$url');</script>";
					$fields .= "<div align=\"center\"><input type=\"text\"  style=\"border:solid #464646 1px;width:225px;height:25px;\" name=\"validator\" id=\"validator\"  ><div class=\"valtext\">Please enter validation code above</div></div>";
					$fields .= "<div align=\"center\"><span id=\"checkprocess\" style=\"color:red;font-family:Arial, sans-serif;font-size:14px;\"></span></div>";
					$fields .=  "</fieldset>";
					$fields .=  "<br>";
					$fields .= "<div align=\"center\"><input type='submit' class='formbutton' name='Submit' value='Submit Form'></div>";
					$fields .= "</form>";
					if ($formAlign != "") {
						$fields .= "</div></div>";
					}
				
				}

				if ($process == "print") {
					if ($formPayment) {
						$fields .= "<b>Transaction ID</b><input type='text' name='trans_id' id='trans_id' value='$transID'>";
					}
					$fields .= "<br><br><div align=\"left\"><input type='submit' class='formbutton' name='Submit' value='Update Form'></div>";
				}

				if (isset($_REQUEST['submissionID'])&& $process == "print") {
					$printLink = "<input type=\"button\" name=\"Print\" class=\"button-secondary\" value=\"Print Form\" style=\"height:20px;\" onclick=\"javascript:window.print();\"/>";
					$link = "$adminURL&process=submissions&formID=$formID";
					$returnLink = "<input type=\"button\" name=\"Return\" class=\"button-secondary\" value=\"Return To Submissions\" style=\"height:20px;\" onclick=\"javascript:window.location = '$link';\"/>";
					
				}
			} else {
				$COST = 0;
				$total = number_format($COST, 2);
				$ccselect = $this->checkRequest('cardtype');
				$fields .= "<b>Total Fee:&nbsp;<font color=\"red\">\$$total</font></b>";
				$paymethod = 1;
				if ($formPayment && $paymethod == 1) {
					$style1 = "style=\"display:none;\"";
					$style2 = "style=\"display:block;\"";
					$style3 = "style=\"display:none;\"";
				}

				if ($formPayment && $paymethod == 0) {
					$style1 = "style=\"display:none;\"";
					$style2 = "style=\"display:none;\"";
					$style3 = "style=\"display:block;\"";
				}
				$paytemplate = $this->pluginDir . "/view/html/paymentfields.html";
				$paycontent = array (
					'CHECKADDRESS' => $formTitle,
					'AUTHNUM' => $auth_num,
					'CCOPTION' => $ccselect,
					'STYLE1' => $style1,
					'STYLE2' =>$style2,
					'STYLE3' => $style3
				);
				//$paypage = new FillPage ($paytemplate);
				//$paypage->replace_tags($paycontent);
				//$fields .= $paypage->viewoutput();

			}
		}

		if ($submitStatus == 1) {
			$fields = $outcome;
		}
		
		$template = "viewfields.html";
		$content = array (
			'OUTCOME' => "",
			'PLUGINURL' => $pluginURL,
			'FORMTITLE'=> $formTitle,
			'FORMINSTRUCTIONS'=>$formInstructions,
			'FIELDS' => $fields,
			'PRINTLINK' => $printLink,
			'RETURNLINK' => $returnLink,
			'HTML' => $template
		);
		return $content;
	}

	public function get_regulartxt($fieldTitlePlacement, $field_required, $fieldname, $submitStatus, $submitValue, $identification, $fieldInstruction, $fieldIcon) {
		$fields = "";
		if (!$fieldTitlePlacement) {
									
			if ($field_required == 1) {
				$fields .= "<font color=\"red\">*</font><b>$fieldname</b><br>";
			} else {
				$fields .= "<div class=\"labeltop\"><b>$fieldname</b></div>";
			}

			if ($submitStatus == 1) {
				$fields .= "<div class=\"regular\">$submitValue</div>";
			} else {
				$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" class=\"regular\" value=\"$submitValue\"/>";
			}
		} else {
			if ($submitStatus == 1) {
				$fields .= "<div class=\"regular\">$submitValue</div>";
			} else {
				$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" class=\"regular\" value=\"$submitValue\"/>";
			}
		}

		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div><br>";
		}
		return $fields;
	}

	public function get_checkboxes($options, $optionValues, $fieldTitlePlacement, $field_required, $fieldSpan, $fieldname, $fieldInstruction, $fieldIcon, $submitValue, $identification, $fieldViewPlacement, $labelColor ) {
		$fields = "";
		$fieldcount = count(explode(',', $options));
		$fieldoptions = explode(',', $options);
		$fieldOptionValues = explode(',',$optionValues);

				
		if (!$fieldTitlePlacement) {
			if ($rowfields->field_required == 1) {												
				if ($fieldSpan > 0) {
					$fields .= "<br>";
					if ($fieldname != "") {
						$fields .= "<div class=\"labeltop\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;text-align:left;\"><font color=\"red\">*</font>$fieldname:</div>";
					}
				} else {
					$fields .= "<div class=\"labeltop\"><font color=\"red\">*</font><b>$fieldname:</b></div>";
				}
			} else {
				if ($fieldSpan > 0) {
					$fields .= "<br>";
					$fields .= "<div class=\"labeltop\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;\">$fieldname:</div>";
				} else {
					$fields .= "<div class=\"labeltop\"><b>$fieldname:</b></div>";
				}
			}
		}

		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div>";
		}
		

		if ($fieldcount > 0) {
			for ($i=0; $i<=$fieldcount-1; $i++) {
				$fieldoption = $fieldoptions[$i];
				$fieldoptionvalue = $fieldOptionValues[$i];
				$fieldoptionvalue == "" ? $fieldoptionvalue = $fieldoption : '';
				if ($fieldoption == "break") {
					$fields .= "<div style=\"clear:both;height:10px;\"></div>";
				} else {
					$iden = $identification . "[]";
					if (is_array($submitValue)) {						
						foreach ($submitValue as $o => $v) {
							if (trim($v) == trim($fieldoptionvalue)) {
								$subVal = $fieldoptionvalue;
							}
						}						
					} else {
						$subVal = $submitValue;
					}
					

					if (trim($subVal) == trim($fieldoptionvalue)) {
						
						$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-right: 10px;\"><input type=\"checkbox\" name=\"$iden\" id=\"$identification\" value=\"$fieldoptionvalue\" checked style='width:25px;'/></div><div style=\"float:left;display:inline-block;padding-top:2px;\">$fieldoption</div>";
						
						if (!$fieldViewPlacement) {
							$fields .= "<div style=\"clear:both;height:10px;\"></div>";
						}
					} else if ($submitValue == 1) {
						$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-right: 10px;\"><input type=\"checkbox\" name=\"$iden\" id=\"$identification\" value=\"$fieldoptionvalue\" checked style='width:25px;'/></div><div style=\"float:left;display:inline-block;padding-top:2px;\">$fieldoption</div>";
						
						if (!$fieldViewPlacement) {
							$fields .= "<div style=\"clear:both;height:10px;\"></div>";
						}

					} else {
						
						$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-right: 10px;\"><input type=\"checkbox\" name=\"$iden\" id=\"$identification\" value=\"$fieldoptionvalue\" style='width:25px;'/></div><div style=\"float:left;display:inline-block;padding-top:2px;\">$fieldoption";
						if ($fieldoption == "other" || $fieldoption == "Other") {
							$fields .= "&nbsp;<input type=\"text\" width=\"175\" name=\"{$identification}_other\" id=\"$identification\" style='width:175px;'>";
						}
						
						$fields .= "</div>";
						if (!$fieldViewPlacement) {
							$fields .= "<div style=\"clear:both;height:10px;\"></div>";
						}
					}
				}
			}
			$fields .= "<div style=\"clear:both;\"></div>";
		} else {
			if ($fieldoption == "break") {
				$fields .= "<br>";
			} else {
				$iden = $identification . "[]";
				if ($submitValue == $fieldname) {
					
					$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-right: 10px;\"><input type=\"checkbox\" name=\"$iden\" id=\"$identification\" value=\"$fieldname\" checked=\"checked\" /></div><div style=\"float:left;display:inline-block;padding-top:2px;\">$fieldname</div>";
						if (!$fieldViewPlacement) {
							$fields .= "<div style=\"clear:both;height:10px;\"></div>";
						}
				} else {
					$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-right: 10px;\"><input type=\"checkbox\" name=\"$iden\" id=\"$identification\" value=\"$fieldname\" /></div><div style=\"float:left;display:inline-block;padding-top:2px;\">$fieldname</div>";
					
					if (!$fieldViewPlacement) {
						$fields .= "<div style=\"clear:both;height:10px;\"></div>";
					}
				}
			}
		}
			
		return $fields;
	}

	public function get_selectfield($options, $optionValues, $fieldTitlePlacement, $field_required, $fieldSpan, $fieldname, $labelColor, $identification, $submitValue, $formID, $class) {
		$fields = "";
		$fieldcount = count(explode(',', $options));
		$fieldoptions = explode(',', $options);
		$fieldoptionValues = explode(',', $optionValues);
		
		if (!$fieldTitlePlacement) {
			if ($field_required == 1) {
				
				if ($fieldSpan > 0 && $fieldname != "") {
					$fields .= "<br>";
					$fields .= "<div class=\"labeltop\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;\"><font color=\"red\">*</font>$fieldname</div>";
				} else {
					$fields .= "<div class=\"labeltop\"><font color=\"red\">*</font><b>$fieldname</b></div>";
				}

			} else {										
				if ($fieldSpan > 0 && $fieldname != "") {
					$fields .= "<br>";
					$fields .= "<div class=\"labeltop\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;\">$fieldname</div>";
				} else {
					$fields .= "<div class=\"labeltop\"><b>$fieldname</b></div>";
				}
			}
		}
		if ($fieldSpan > 0) {
			$fields .= "<select name=\"$identification\" id=\"$identification\" class=\"$class\"   />";
		} else {
			$fields .= "<select name=\"$identification\" id=\"$identification\" class=\"$class\"  />";
		}
			
		//}
		$statelist = $this->fillStates($submitValue);
		$countrylist = $this->fillCountries($submitValue);
		if ($formPayment && $fieldname == "country") {
			if ($submitStatus) {
				$fields .= "<div class=\"lgregular\">$submitValue</div>";
			} else {
				$fields .= $countryList;
			}
		} elseif ($formPayment && $fieldname == "state") {
			if ($submitStatus) {
				$fields .= "<div class=\"lgregular\">$submitValue</div>";
			} else {
				$fields .= $statelist;
			}
		} elseif ($fieldname == "State") {
			$fields .= $statelist;
		} elseif ($fieldname == "Country") {
			$fields .= $countrylist;
		} else {
			$fields .= "<option value=''>Select...</option>";
			for ($i=0; $i<=$fieldcount-1; $i++) {
				isset($fieldoptionValues[$i]) && $fieldoptionValues[$i] != "" ? $fieldoption = $fieldoptionValues[$i] :$fieldoption = $fieldoptions[$i];
				$fieldTitle = $fieldoptions[$i];
				preg_match('/{\w+\}/', $fieldoption, $shortcode);
				if (count($shortcode) != 0 ) {
					$tag = str_replace("{" , "", $shortcode[0]);
					$tag = str_replace("}" , "", $tag);
					//======== Set up form data from database for any dropdowns ========//
					$tag == "eventlist" ? $eventlist = $this->get_formdata('EVENTS',$submitValue,$formID) : '';
					$fields .= $$tag;

				} else {
					$fieldoption = trim($fieldoption);
					$fieldTitle = trim($fieldTitle);
					if (trim($submitValue) == trim($fieldoption)) {
						$fields .= "<option value=\"$fieldoption\" selected>$fieldTitle</option>";
					} else if (trim($fieldoption) == "United States") {//Catches country set to US option
						$fields .= "<option value=\"$fieldoption\" selected>$fieldTitle</option>";
					} else {
						$fields .= "<option value=\"$fieldoption\">$fieldTitle</option>";
					}
				}
			}
		}
		$fields .= "</select>";
		return $fields;
	}

	public function get_textareafield($fieldTitlePlacement, $field_required, $fieldname, $fieldInstruction, $fieldIcon, $submitStatus, $submitValue, $identification) {
		$fields = "";
		if (!$fieldTitlePlacement && $fieldname != "") {
			if ($field_required == 1) {
				$fields .= "<div class=\"labeltop\"><font color=\"red\">*</font><b>$fieldname:</b></div>";
			} else {
				$fields .= "<div class=\"labeltop\"><b>$fieldname:</b></div>";
			}
		}

		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;font-style:italic;font-size:13px;\">($fieldInstruction)</div>";
		}

		if ($submitStatus == 1) {
			$fields .= "<div class=\"lgregular\" >$submitValue</div>";
		} else {
			$fields .= "<textarea  name=\"$identification\" id=\"$identification\" class=\"lgregular\" style=\"height:75px;\" />$submitValue</textarea>";
		}
		return $fields;
	}

	public function get_radiofield($fieldTitlePlacement, $field_required, $fieldname, $labelColor, $fieldSpan, $options, $optionValues, $fieldViewPlacement, $fieldScript, $submitValue, $identification, $nameID, $fieldViewPlacement, $fieldInstruction, $fieldIcon) {
		
		$fields = "";
		if (!$fieldTitlePlacement) {
			if ($field_required == 1 && $fieldname != "") {
				$fieldname != "" ? $fields .= "<div class=\"label\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;text-align:left;\"><font color=\"red\">*</font><b>$fieldname</b></div><br>" : '';
			} else {
				if ($fieldSpan > 0) {
					$fieldname != "" ? $fields .= "<div class=\"label\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;text-align:left;\">$fieldname</div><br>" : '';
				} else {
					$fieldname != "" ? $fields .= "<div class=\"label\" style=\"color:#{$labelColor};font-weight:bold;padding-top:5px;width:100%;height:15px;text-align:left;\">$fieldname</div><br>" : '';
				}
			}
		}
		
		$fieldcount = count(explode(',', $options));
		$fieldoptions = explode(',', $options);
		$fieldOptionValues = explode(',', $optionValues);
		if ($fieldViewPlacement) {
			$fields .= "<br>";
		}
		$moveforward = true;
		$fieldScript != "" ? $onclick = "onclick=\"$fieldScript\"" : $onclick = "";
		for ($i=0; $i<=$fieldcount-1; $i++) {
			$fieldoption = $fieldoptions[$i];		
			$fieldoptionvalue = $fieldOptionValues[$i];
			$fieldoptionvalue == "" ? $fieldoptionvalue = $fieldoption : '';
			preg_match_all('/{\w+\}/', $fieldoption, $fieldshortcode);
			
			if (count($fieldshortcode)!= 0 ) {
				foreach ($fieldshortcode as $key => $sc) {
					if (count($sc) != 0) {
						foreach ($sc as $u => $f) {
							$fieldtag = $f;
							//Do shortcodes here
						}
					}
				}
				
			}
			preg_match('/{\w+\}/', $fieldoptionvalue, $shortcode);
			if (count($shortcode) != 0 ) {
				$tag = str_replace("{" , "", $shortcode[0]);
				$tag = str_replace("}" , "", $tag);	
				$modelOptions = explode(",",$form_box6);
				
				//Set function codes here
				
				$fields .= $$tag;
				$moveforward = false;
			} else {
				if ($moveforward) {
					if (trim($submitValue) == trim($fieldoptionvalue)) {
						$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-top:10px;\"><input type=\"radio\" name=\"$nameID\" id=\"$identification\" value=\"$fieldoptionvalue\" checked=\"checked\" $onclick style='width:25px;' /></div><div style=\"float:left;display:inline-block;padding-top:10px;\">$fieldoption&nbsp;&nbsp;</div>";
						if (!$fieldViewPlacement) {
							//$fields .= "<div style=\"clear:both;\"></div>";
						}
					} else {
						$fields .= "<div style=\"float:left;display:inline-block;width:25px;padding-top:10px;\"><input type=\"radio\" name=\"$nameID\" id=\"$identification\" value=\"$fieldoptionvalue\" $onclick style='width:20px'/></div><div style=\"float:left;display:inline-block;padding-top:10px;\">$fieldoption&nbsp;&nbsp;";
						if ($fieldoption == "other") {
							$fields .= "&nbsp;<input type=\"text\" width=\"75\" name=\"$nameID\" id=\"$identification\" style='width:25px;'>";
						}
						$fields .= "</div>";
						
						if (!$fieldViewPlacement) {
							//$fields .= "<div style=\"clear:both;\"></div>";
						}
					}
				}
			}
		}
		if ($fieldViewPlacement) {
			$fields .= "<div style=\"clear:both;\"></div>";
		}
		
		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div><br>";
		}
		return $fields;
	}

	public function get_longtextfield($fieldTitlePlacement, $field_required, $fieldname, $submitStatus, $submitValue, $identification, $fieldInstruction, $fieldIcon) {
		$fields = "";
		if (!$fieldTitlePlacement) {
			if ($field_required == 1) {
				$fields .= "<div class=\"labeltop\"><font color=\"red\">*</font><b>$fieldname</b></div>";
			} else {
				$fields .= "<div class=\"labeltop\"><b>$fieldname</b></div>";
			}

			if ($submitStatus == 1) {
				$fields .= "<div class=\"lgregular\">$submitValue</div>";
			} else {
				$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" class=\"lgregular\" value=\"$submitValue\"/>";
			}
		} else {
			if ($submitStatus == 1) {
				$fields .= "<div class=\"lgregular\">$submitValue</div>";
			} else {
				$fields .=  "<input type=\"text\" name=\"$identification\" id=\"$identification\" class=\"lgregular\" value=\"$submitValue\"/>";
			}
		}

		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div><br>";
		}
		return $fields;
	}

	public function get_phonefield($identification, $fieldShortName, $submitValue, $fieldTitlePlacement, $field_required, $fieldname, $submitStatus, $fieldInstruction, $fieldIcon) {
		$fields = "";
		$phone0_id = $identification . "phone0";
		$phone1_id = $identification . "phone1";
		$phone2_id = $identification . "phone2";
		$identification = $identification . "[]";
		$phone0 = "";
		$phone1 = "";
		$phone2 = "";
		$fullphone = "";
		if (isset($_POST)) {
			$phone0 = $_POST[$fieldShortName][0];
			$phone1 = $_POST[$fieldShortName][1];
			$phone2 = $_POST[$fieldShortName][2];
			$fullphone = "($phone0) $phone1-$phone2";
		}
		if ($submitValue != "") {
			if (is_array($submitValue)) {
				$phone0 = $submitValue[0];
				$phone1 = $submitValue[1];
				$phone2 = $submitValue[2];
			} else {
				$dbPhone = explode("-", $submitValue);
				$phone0 = $dbPhone[0];
				$phone1 = $dbPhone[1];
				$phone2 = $dbPhone[2];
			}
		}
		if (!$fieldTitlePlacement && $fieldname != "") {
			if ($field_required == 1) {
				$fields .= "<div class=\"labeltop\"><font color=\"red\">*</font><b>$fieldname:</b></div>";
			} else {
				$fields .= "<div class=\"labeltop\"><b>$fieldname:</b></div>";
			}
			if ($submitStatus == 1) {
				$fields .= "<div class=\"lgregular\">$fullphone</div>";
			} else {
				$fields .=  "(<input type=\"text\" name=\"$identification\" id=\"$phone0_id\" class=\"inputreg\" style=\"width:40px\" value=\"$phone0\" />)&nbsp;<input type=\"text\" name=\"$identification\" id=\"$phone1_id\" class=\"inputreg\" style=\"width:40px\" value=\"$phone1\" />&nbsp;-&nbsp;<input type=\"text\" name=\"$identification\" id=\"$phone2_id\" class=\"inputreg\" style=\"width:50px\" value=\"$phone2\" />";
			}
		} else {
			if ($submitStatus == 1) {
				$fields .= "<div class=\"lgregular\">$fullphone</div>";
			} else {
				$fields .=  "(<input type=\"text\" name=\"$identification\" id=\"$phone0_id\" class=\"inputreg\" style=\"width:40px\" value=\"$phone0\" />)&nbsp;<input type=\"text\" name=\"$identification\" id=\"$phone1_id\" class=\"inputreg\" style=\"width:40px\" value=\"$phone1\" />&nbsp;-&nbsp;<input type=\"text\" name=\"$identification\" id=\"$phone2_id\" class=\"inputreg\" style=\"width:50px\" value=\"$phone2\" />";
			}
		}

		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div><br>";
		}
		return $fields;
	}

	public function get_parafield($fieldTitlePlacement, $field_required, $fieldname, $options) {
		$fields = "";
		if (!$fieldTitlePlacement) {
			if ($field_required == 1) {
				$fields .= "<font color=\"red\">*</font><b>$fieldname</b><br>";
			} else {
				$fields .= "<div class=\"labeltop\"><b>$fieldname</b></div>";
			}
			$fields .=  "$options";
		} else {
			$fields .=  "$options";
		}
		return $fields;
	}

	public function get_fileuploadfield($identification, $fieldInstruction, $fieldIcon) {
		$fields = "";
		$fields .= "<input type=\"file\" name=\"form[$identification][]\" id=\"$identification\" />";
		if ($fieldInstruction != "" && !$fieldIcon) {
			$fields .= "<div style=\"font-weight:normal;\"><i>($fieldInstruction)</i></div><br>";
		}
		return $fields;
	}

	public function get_paymentfields ($options, $optionValues, $optionInstructions, $optionSelections, $orderInfo, $submitValue, $fieldScript, $formID, $process) {
		$fields = "";
		$fieldcount = count(explode(',', $options));
		$fieldoptions = explode(',', $options);
		$fieldoptionValues = explode(',', $optionValues);
		$fieldoptionInstructions = explode(',', $optionInstructions);
		$fieldoptionSelections = explode(',', $optionSelections);
		$includeOptions = false;
		if ($orderInfo != "") {
			$orderSubmission = explode("|",$orderInfo);
		}
										
		$fields .= $this->BeginTable('100%', '0', '2', '2');
		$headers = array("Item Name", "Quantity");
		if ($optionInstructions != '') {
			$headers[] = "Options";
			$includeOptions = true;
		}
		$fields .= $this->buildHeaders($headers, '', '', count($headers), '', 'center', '', '', '');
		for ($i=0; $i<=$fieldcount-1; $i++) {
			$cnt = $i + 1;
			$fieldoption = $fieldoptions[$i];
			$fieldoptionValue = $fieldoptionValues[$i];
			$fieldSelections = $fieldoptionSelections[$i];
			$checked = "";
			$field_option = "";
			$submitValue = "";
			$quantity = "";
			$seloption = "";

			if (is_array($orderSubmission)) {
				foreach ($orderSubmission as $od => $ord) {
					$orderOptions = explode(";",$ord);
					if ($orderOptions[0] == $fieldoption) {
						$submitValue = $orderOptions[0];
						$quantity = $orderOptions[1];
						$quantity == "" ? $quantity = "1" : '';
						if (isset($orderOptions[2])) {
							$seloption = $orderOptions[2];
						}
						if (isset($orderOptions[3])) {
							$amount = $orderOptions[3];
						}
					}
				}
			} else {
				if (isset($_POST[$itemNum]) && $_POST[$itemNum] == "on") {
					$submitValue = $_POST[$itemName];
					$quantity = $_POST[$qtyID];
					$amount = $_POST[$amtID];
				}
			}
			
			if (trim($submitValue) == trim($fieldoption)) {
				$checked = "checked";
			}
			$fieldScript != "" ? $doscript = "javascript:$fieldScript" : $doscript = "";
			$checkbox = $this->_doInput ("item_" . $cnt, "item_" . $cnt, 'checkbox', '', '', '', '', $doscript, $checked, $style);
			$itemName = $this->_addDiv($checkbox . "&nbsp;" . $fieldoption, '', 'padding-top:8px;', '','');
			$itemName .= $this->_doInput ("item_name_" . $cnt, "item_name_" . $cnt, 'hidden', $fieldoption, '', '', '', '', '', '','');
			$quantity = $this->_doInput ("quantity_" . $cnt, "quantity_" . $cnt, 'hidden', $quantity, '', '', '', '', '', 'width:50px;','');
			if ($fieldoptionValue == "donation") {
				$amount = $this->_doInput ("amount_" . $cnt, "amount_" . $cnt, 'text', $amount, '', '', '', '', '', '', '', '', '', 'javascript:checkNumber(this);');
				$paycontent = array($itemName,$quantity,$amount); 
			} else {
				$itemName .= $this->_doInput ("amount_" . $cnt, "amount_" . $cnt, 'hidden', $fieldoptionValue, '', '', '', '', '', '','');
				$paycontent = array($itemName,$quantity); 
			}

			if ($fieldSelections != "") {
				$dropitems = explode(";",$fieldSelections);
				$field_option = "<select name=\"option_$cnt\" id=\"option_$cnt\" class=\"regular\" />";
				$field_option .= "<option value=''>Select...</option>";
				while (list($key, $val) = each($dropitems)) {
					if ($seloption == $val) {
						$field_option .= "<option value='$val' selected>$val</option>";
					} else {
						$field_option .= "<option value='$val'>$val</option>";
					}
				}
				$field_option .= "</select><br>";	
			}
			if ($fieldoptionInstructions[$i] != "") {
				$field_option .= "<i>" .  $fieldoptionInstructions[$i] . "</i>";
			}

			if ($includeOptions) {
				$paycontent[] = $field_option;
			}
			
			
			$fields .= $this->buildRow2($paycontent, '', '', count($paycontent), '', '', '', '', '', '','top');
		}

		$fields .= $this->EndTable();

		//======== Add in field for total items ========//
		$fields .= $this->_doInput ("totalitems", "totalitems", 'hidden', $fieldcount, '', '', '', '', '', '');
		if ($process == "print") {									
			$payAmt = "$" . number_format($payAmt, 2);			
			$fields .= "Payment Amount:&nbsp;$payAmt<br>";
			$fields .= "Transaction Number:&nbsp;$transID<br>";
			$fields .= "Authorization Number:&nbsp;$authNum<br>";
		} else {
			//Need to determine which payment gateway is being used here.  Maybe from form_config
			//$fields .= $this->getCreditCardFields();
			//======== Include paypal fields ========//
			$fields .= $this->getPayPalInfo($formID);
		}
		return $fields;
	}

	public function get_formdata($formCode, $selected, $formID) {
		$wpdb = $this->wpdb;
		$formData = "";
		$today = date("Y-m-d");
		switch ($formCode) {
			
		}
		return $formData;
	}

	public function formsubmitprocess($submitInfo) {
		$fields = $submitInfo[2];
		$formpayment = $submitInfo[5];
		$orderItems = $submitInfo[6];
		$formID = $this->checkRequest('formID');
		$submissionID = $submitInfo[8];
		
		
		if ($formpayment == 1 ) {
			$link = $submitInfo[4];
			//echo $link;
			$fields = "<p>We are redirecting you for payment.  Please wait...</p>";
			$fields .= "<META http-equiv=\"refresh\" content=\"0;URL=$link\">";
			

			/*if (!$this->processPaypal($submitInfo)) {
				$fields = $_SESSION['error'];
				$formview = $this->formview ('view', $formID, $submitInfo, false);
				
			} else {
				$this->sendformemail($submissionID);
			}*/
		} else {
			//$this->sendformemail($submissionID);
		}
		$template = WP_PLUGIN_DIR . "/kybformbuilder/view/html/viewfields.html";
		$submitcontent = array (
			'OUTCOME' => "",
			'PLUGINURL' => $pluginURL,
			'FORMTITLE'=> $formTitle,
			'FORMINSTRUCTIONS'=>$formInstructions,
			'FIELDS' => $fields,
			'PRINTLINK' => $printLink,
			'RETURNLINK' => $returnLink,
			'HTML' => $template
		);
		$content = $this->showPage ($template, $submitcontent, true);
		
		return $content;

	}

	public function DeleteForm($formID) {
		$wpdb = $this->wpdb;
		$sql = "DELETE FROM " . $this->table . "forms WHERE form_id=%d";
		$wpdb->query($wpdb->prepare($sql, $formID));
		
		//Delete form sections
		$sqlsections = "DELETE FROM " . $this->table . "form_sections WHERE form_id=%d";
		$wpdb->query($wpdb->prepare($sqlsections, $formID));
		
		//Delete form fields
		$sqlfields = "DELETE FROM " . $this->table . "form_fields WHERE form_id=%d";
		$wpdb->query($wpdb->prepare($sqlfields, $formID));
		
	}

	public function addfields($formID) {
		$wpdb = $this->wpdb;
		$section = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['section'], 2) ) ) );
		$sorder = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['sorder'], 2) ) ) );
		$fname = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['fieldname'], 2) ) ) );
		$sfname = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['shortfieldname'], 2) ) ) );
		$ftype =trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['ftype'], 2) ) ) );
		$forder = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['forder'], 2) ) ) );
		$frequired = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['required'], 2) ) ) );
		$fplacement = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['fplacement'], 2) ) ) );
		$ftplacement = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['ftplacement'], 2) ) ) );
		$finstruction = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['finstruction'], 2) ) ) );
		$options = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['options'], 2) ) ) );
		$sectionID = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['sectionID'], 2) ) ) );
		$item = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['item'], 2) ) ) );
		$cost = trim( strip_tags( stripslashes( $this->StripSpecialQuotes($_GET['cost'], 2) ) ) );

		
		
		if ($sorder == "") {
			//======== Get last section order ======//
			$sqlorder = "Select MAX(section_order) as LASTORDER from " . $this->table . "form_sections where form_id = %d";
			$sorder = $wpdb->get_var($wpdb->prepare($sqlorder, $formID));
		}
				
		//First check to see if this section name is already in use.  If so utilize that section ID vs. creating a new section ID.
		$IDcheck = explode("-", $sectionID);
		if (count($IDcheck) > 1) {
			$sectionID = $IDcheck[1];
		}
		$sectionID == "" ? $sectionID = 0 : '';
		echo $sectionID . "-" . $formID;
		$sqlcheck = "Select section_id from " . $this->table . "form_sections where section_id = %d";
		$resultcheck = $wpdb->get_results($wpdb->prepare($sqlcheck, $sectionID));

		if (count($resultcheck) == 0) {
			$sorder = $sorder +1;
			//Need to enter section information first and then get the id of that section to enter into form_fields table.
			$sqlsection = "INSERT INTO " . $this->table . "form_sections (section_title, section_order, form_id) VALUES (%s, %d, %d)";
			$wpdb->query($wpdb->prepare($sqlsection, $section, $sorder, $formID));
			$sectionID = $this->lastID($this->table . 'form_sections', 'section_id');
		} 

		//======== Get last field order ======//
		$sqlorder = "Select MAX(field_order) as LASTORDER from " . $this->table . "form_fields where form_id = %d and section_id = %d";
		$forder = $wpdb->get_var($wpdb->prepare($sqlorder, $formID, $sectionID)) + 1;
		if ($forder == "") {
			$forder = 1;
		}
							
		//Insert differently according to section
		if ($sectionID != "") {
			$sql = "INSERT INTO " . $this->table . "form_fields (form_id, field_name, field_short_name, field_type_id, section_id, field_order, field_required, field_options, field_placement, field_title_placement, field_instruction) VALUES (%d, %s, %s, %d, %d, %d, %d, %s, %d, %d, %s)";
			$wpdb->query($wpdb->prepare($sql, $formID, $fname, $sfname, $ftype, $sectionID, $forder, $frequired, $options, $fplacement, $ftplacement, $finstruction));
		}
		
	}

	public function deletefields($formID) {
		$wpdb = $this->wpdb;
		$fieldID = $this->checkRequest('fieldID');
		$sqldelete = "Delete from " . $this->table . "form_fields where id = %d";
		$wpdb->query($wpdb->prepare($sqldelete, $fieldID));
	}

	public function deletesection($formID) {
		$wpdb = $this->wpdb;
		$sectionID = $this->checkRequest('sectionID');
		//======== Delete fields associated with the section ========//
		$sqlDeleteFields = "Delete from " . $this->table . "form_fields where form_id = %d and section_id = %d";
		$wpdb->query($wpdb->prepare($sqlDeleteFields, $formID, $sectionID));

		//======== Delete Section ========//
		$sqlDeleteSection = "Delete from " . $this->table . "form_sections where section_id = %d";
		$wpdb->query($wpdb->prepare($sqlDeleteSection, $sectionID));
		$this->reorder('sections', 'movesectionup', 0, $formID, 0,$sectionID );
	}

	public function moveFormItems($fieldType, $formID, $process) {
		$fieldID = $this->checkRequest('fieldID');
		$order = $this->checkRequest('order');
		$sectionID = $this->checkRequest('sectionID');
		$this->reorder($fieldType, $process, $fieldID, $formID, $order, $sectionID);
	}

	public function formActivation() {
		$wpdb = $this->wpdb;
		$formStatus = $this->checkRequest('status');
		$formID = $this->checkRequest('formID');
		$sql="UPDATE " . $this->table . "forms SET form_status = %d WHERE form_id = %d";
		$wpdb->query($wpdb->prepare($sql, $formStatus, $formID));
		echo "<p class='dbmessage'>Form Status has been updated successfully</p>";
	}

	
	public function reorder($area, $process, $fieldID, $formID, $order, $sectionID) {
		$wpdb = $this->wpdb;
		
		if ($process == "moveup" || $process == "movesectionup" ) {
			$order = $order - 1;
		} elseif ($process == "movedown" || $process == "movesectiondown") {
			$order = $order + 1;
		}

		if ($order == 0) {
			$order = 1;
		}

		switch ($area) {
			case "fields":

				//======== Roll through to reorder accordingly ========
				$sqlfields = "Select * from " . $this->table . "form_fields where form_id = %d and section_id = %d order by field_order";
				$resultfields = $wpdb->get_results($wpdb->prepare($sqlfields, $formID, $sectionID));
				
				$i = 1;	

				//======== Update field order to move up or down ========
				$sql = "UPDATE " . $this->table . "form_fields SET field_order=%d where id=%d";
				$wpdb->query($wpdb->prepare($sql, $order, $fieldID));

				if (count($resultfields) != 0) {
					foreach ($resultfields as $row) {
						$currfieldID = $row->id;
						$ordernumber = $row->field_order;						
						$currorder = $i;
						
						if ($currfieldID != $fieldID ) {
							
							if ($ordernumber == $order && $process == "moveup") {
								$i++;
								$currorder = $i;
							}

							if ($ordernumber == $order && $process == "movedown") {
								$i--;
								$i < 1 ? $i = 1 : '';
								$currorder = $i;
							}
							
							$sqlupdate = "UPDATE " . $this->table . "form_fields SET field_order = %d where id = %d";
							$wpdb->query($wpdb->prepare($sqlupdate, $currorder, $currfieldID));
							
						}
						
						$i++;
						

					}
				}
			break;

			case "sections";
				
				
				//======== Roll through to reorder accordingly ========
				$sqlsections = "Select * from " . $this->table . "form_sections where form_id = %d order by section_order";
				$resultsections = $wpdb->get_results($wpdb->prepare($sqlsections, $formID));
				$i = 1;	

				//======== Update section order to move up or down ========
				$sql = "UPDATE " . $this->table . "form_sections SET section_order=%d where section_id=%d";
				$wpdb->query($wpdb->prepare($sql, $order, $sectionID));

				if (count($resultsections) != 0) {
					foreach ($resultsections as $row) {
						$currsectionID = $row->section_id;
						$ordernumber = $row->section_order;
						$currorder = $i;
						if ($currsectionID != $sectionID) {
							if ($ordernumber == $order && $process == "movesectionup") {
								$i++;
								$currorder = $i;
							}

							if ($ordernumber == $order && $process == "movesectiondown") {
								$i--;
								$i < 1 ? $i = 1 : '';
								$currorder = $i;
							}

							$sqlupdate = "UPDATE " . $this->table . "form_sections SET section_order = %d where section_id = %d";
							$wpdb->query($wpdb->prepare($sqlupdate, $currorder, $currsectionID));
						} 
						$i++;
					}
				}
			break;
		}
	}


	public function formsubmission_update($formID) {
		$wpdb = $this->wpdb;
		$form = array();
		$form = $this->clean_form($_POST);
		$submissionID = $this->checkRequest('submissionID');
		$dbFields = "";
		$shortfields = array();
		$ccfields = array();
		$ccverification = array();
		$fields = "";
		$totalitems = 0;
		$ccField = "";
		$ccVerify = "";
		//======== Get Field Names ========//
		$sqlfields = "Select * from " . $this->table . "form_fields where form_id = %d order by field_order";
		$resultfields = $wpdb->get_results($wpdb->prepare($sqlfields, $formID));
		if (count($resultfields) != 0) {
			foreach($resultfields as $r) {
				$sectionID = $r->section_id;
				//$ccField = $wpdb->get_var($wpdb->prepare("Select section_hide_cc_field from " . $this->table . "form_sections where section_id=%d", $sectionID));
				//$ccVerify = $wpdb->get_var($wpdb->prepare("Select section_hide_cc_verification from " . $this->table . "form_sections where section_id=%d", $sectionID));
				if ($r->field_short_name == $ccField) {
					$ccfields[] = $ccField;
				}

				if ($r->field_short_name == $ccVerify) {
					$ccverification[] = $ccVerify;
				}
				
				if ($r->field_short_name != "") {
					$shortfields [$r->field_name] = str_replace(" ", "_",trim($r->field_short_name));
				}
				$fields .= str_replace(" ", "_",trim($r->field_name)) . ",";
				if ($r->field_type_id == 12) {//Payment fields
					$fieldOptions = explode(",",$r->field_options);
					$totalitems = count($fieldOptions);
				}

				if ($r->field_type_id == 13) {//Donation fields
					$fieldOptions = explode(",",$r->field_options);
					$totalitems = count($fieldOptions);
				}
			}						
			//Remove last comma from string
			$fields = substr($fields,0,strlen($fields)-1);
			$fields = explode(",", $fields);

		}

		

		

		//======== Insert Order from user ========//
		$orderItems = "";
		for ($i=1; $i<=$totalitems; $i++) {
			$checkItem = "item_" . $i;
			if (isset($_POST[$checkItem]) && $_POST[$checkItem] == "on") {
				$itemName = "item_name_" . $i;
				$item = $_POST[$itemName];
				$qtyName = "quantity_" . $i;
				$quantity = $_POST[$qtyName];
				$amtName = "amount_" . $i;
				$amount = $_POST[$amtName];

				$optName = "option_" . $i;
				$option = $_POST[$optName];

				$orderItems .= $item . ";" . $quantity . ";" . ";" . $amount;
				if ($option != "") {
					$orderItems .= $option . ";";
				}
				$orderItems .= "|";

			}
		}
		if ($orderItems != "") {
			$dbFields .= "orderitems=>" . $orderItems . ",";
		}
		

		foreach ( $form as $name => $value ) {
			$fieldID = $name;
			if (is_array($value) ) {
				$fieldTypeID = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d", $name, $formID));
				foreach ($value as $k => $i) {
					if ($fieldTypeID == 8) {
						$i != '' ? $info .= $i . "-" : '';
					} else {
						$i != '' ? $info .= $i . ":" : '';
					}
				}
				//======== take off last dash ========;
				$info = substr($info, 0, strlen($info) -1);
				$value = $info;
				//======== clear info ========//
				$info = "";
			}
			

			if (in_array($name, $fields)) {
				
				$emailfield = $wpdb->get_var($wpdb->prepare("Select field_name from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
				$fieldType = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
				$name = str_replace("_", " ", $name);
				$value = str_replace("\r\n", "<br>", $value);
				$value = str_replace(",","&#44;", $value);
				$emailContent = array($emailfield . ":", $value);
				if ($fieldType != "") {
					$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
				}
				//$emailbody .= $name . ":  " . $value . "\n";
				if (in_array($fieldID, $ccfields)) {
					$ccnum = "XXXX-XXXX-XXXX-" . substr($this->cleanString($value),-4,4);
					$dbFields .= str_replace("_", " ", $this->cleanString($name)) . "=>" . $ccnum . ",";
				} else if (in_array($fieldID, $ccverification)) {
					$ccverify = "XXX";
					$dbFields .= str_replace("_", " ", $this->cleanString($name)) . "=>" . $ccverify . ",";
				} else {
					$dbFields .= str_replace(" ", "_", $this->cleanString($name)) . "=>" . $this->cleanString($value) . ",";
				}
			} else if (count($shortfields) != 0 ) {
				foreach ($shortfields as $key => $v) {
					if ($name == $v) {
						$fieldType = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
						$n = str_replace("_", " ", $key);
						$value = str_replace("\r\n", "<br>", $value);
						$value = str_replace(",","&#44;", $value);
						$emailContent = array($n . ":", $value);
						if ($fieldType != "") {
							$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
						}
						//$emailbody .= str_replace("_", " ", $key) . ":  " . $value . "\n";
						if (in_array($fieldID, $ccfields)) {
							$ccnum = "XXXX-XXXX-XXXX-" . substr($this->cleanString($value),-4,4);
							$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $ccnum . ",";
						} else if (in_array($fieldID, $ccverification)) {
							$ccverify = "XXX";
							$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $ccverify . ",";
						} else {
							$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $this->cleanString($value) . ",";
						}
					}
				}
			}
		}
		$sqlUpdate = "Update " . $this->table . "form_info set comments = %s where form_id = %d";
		$wpdb->query($wpdb->prepare($sqlUpdate, $dbFields, $submissionID));
		if (isset($_POST['trans_id'])) {
			$transID = $this->checkRequest('trans_id');
			$sqlUpdate = "Update " . $this->table . "form_info set trans_id = %s where form_id = %d";
			$wpdb->query($wpdb->prepare($sqlUpdate, $transID, $submissionID));
		}
	}

	public function formsubmit() {
		$wpdb = $this->wpdb;
		//Clean incoming data
		$form = array();
		$form = $this->clean_form($_POST);
		$formID = $this->checkRequest('formID');
		$fields = "";
		$shortfields = array();
		$submitStatus = 0;
		$submitInfo = array();
		$COST = 0;
		$emailTxt = "";
		$info = "";
		$outcome;
		$emailInfo = "";
		$orderItems = "";
		$ccfields = array();
		$ccverification = array();
		$ccField = "";
		$ccVerify = "";

		//======== Get Field Names ========//
		$sqlfields = "Select * from " . $this->table . "form_fields where form_id = %d order by field_order and field_email = 1";
		$resultfields = $wpdb->get_results($wpdb->prepare($sqlfields, $formID));
		if (count($resultfields) != 0) {
			foreach($resultfields as $r) {
				$sectionID = $r->section_id;
				//$ccField = $wpdb->get_var($wpdb->prepare("Select section_hide_cc_field from " . $this->table . "form_sections where section_id=%d", $sectionID));
				//$ccVerify = $wpdb->get_var($wpdb->prepare("Select section_hide_cc_verification from " . $this->table . "form_sections where section_id=%d", $sectionID));
				if ($r->field_short_name == $ccField) {
					$ccfields[] = $ccField;
				}

				if ($r->field_short_name == $ccVerify) {
					$ccverification[] = $ccVerify;
				}
				if ($r->field_short_name != "") {
					$shortfields [$r->field_name] = str_replace(" ", "_",trim($r->field_short_name));
				}
				$fields .= str_replace(" ", "_",trim($r->field_short_name)) . ",";
			}						
			//Remove last comma from string
			$fields = substr($fields,0,strlen($fields)-1);
			$fields = explode(",", $fields);

		}
		
		//Retrieve form information
		$sql = "Select * from " . $this->table . "forms where form_id = %d and form_status = 1";
		$result = $wpdb->get_results($wpdb->prepare($sql, $formID));

		if (count($result) != 0) {
			
			foreach ($result as $row) {
				//Set variables from database
				$formTitle = $row->form_title;
				$formInstructions = apply_filters('the_content',$row->form_instructions);
				$formEmail = $row->email_responses;
				$formExpiration = $row->form_expiration;
				$formResponse = apply_filters('the_content',$row->form_response);
				$formPayment = $row->form_payment;
				$product_id = $row->product_id;
			}

			//Establish Payal Link
			if ($formPayment) {	
				$linkInfo = $this->getPaypalLink();
				$link = $linkInfo->paypal_link;
				$orderItems = $linkInfo->order_items;
				$submitStatus = 1;
				$res_txt = "";
				$auth_num = "";
				$trans_id = "";
				$emailTxt = "";
				$outcome = "<h2 align=\"center\">Submission Successful!</h2><p>$formResponse<p>";
			}

			
			//$_POST['paymethod_integer'] == 0;
			/*if ($formPayment && $_POST['paymethod_integer'] == 0) {
				$submitStatus = 1;
				$res_txt = "";
				$auth_num = "";
				$trans_id = "";
				$outcome = "<h2 align=\"center\">Registration Submission Successful!</h2>$formResponse<p>You have opted to pay by check.  Upon receipt of your check payment, we will then send you further confirmation for your registration. <a href=\"javascript:print();\">Click here</a> to print this form for your records.</p>";
				$emailTxt = "<p>Option was made to pay by check.  Upon receipt of check payment, please proceed with registration confirmation.";
			}*/

			if(!$formPayment) {
				$submitStatus = 1;
				$res_txt = "";
				$auth_num = "";
				$trans_id = "";
				$outcome = "<h2 align=\"center\">Submission Successful!</h2><p>$formResponse<p>";
				$emailTxt = "";
			}

			


			//$emailbody = "<p>The following online form has been submitted from the " . $this->company . " Website.</p>";
			//$emailbody = "<p><b>" . $formTitle . "</b></p>";
			$dbFields = "";
			if ($orderItems != "") {
				$dbFields .= "orderitems=>" . $orderItems . ",";
			}
			$emailInfo .= $this->BeginTable('500', '1', '2', '2');
			
			//Preview form information
			foreach ( $form as $name => $value ) {
				$fieldID = $name;
				if (is_array($value) ) {
					$fieldTypeID = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d", $name, $formID));
					foreach ($value as $k => $i) {
						if ($fieldTypeID == 8) {
							$i != '' ? $info .= $i . "-" : '';
						} else {
							$i != '' ? $info .= $i . ":" : '';
						}
					}
					//======== take off last dash ========;
					$info = substr($info, 0, strlen($info) -1);
					$value = $info;
					//======== clear info ========//
					$info = "";
				}

				if (in_array($name, $fields)) {
					
					$emailfield = $wpdb->get_var($wpdb->prepare("Select field_name from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
					$fieldType = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
					$name = str_replace("_", " ", $name);
					$value = str_replace("\r\n", "<br>", $value);
					$value = str_replace(",","&#44;", $value);
					$emailContent = array($emailfield . ":", $value);
					if ($fieldType != "") {
						$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
					}
					//$emailbody .= $name . ":  " . $value . "\n";
					if (in_array($fieldID, $ccfields)) {
						$ccnum = "XXXX-XXXX-XXXX-" . substr($this->cleanString($value),-4,4);
						$dbFields .= str_replace("_", " ", $this->cleanString($name)) . "=>" . $ccnum . ",";
					} else if (in_array($fieldID, $ccverification)) {
						$ccverify = "XXX";
						$dbFields .= str_replace("_", " ", $this->cleanString($name)) . "=>" . $ccverify . ",";
					} else {
						$dbFields .= str_replace(" ", "_", $this->cleanString($name)) . "=>" . $this->cleanString($value) . ",";
					}
				} else if (count($shortfields) != 0 ) {
					foreach ($shortfields as $key => $v) {
						if ($name == $v) {
							$fieldType = $wpdb->get_var($wpdb->prepare("Select field_type_id from " . $this->table . "form_fields where field_short_name = %s and form_id = %d and field_email = 1", $name, $formID));
							$n = str_replace("_", " ", $key);
							$value = str_replace("\r\n", "<br>", $value);
							$value = str_replace(",","&#44;", $value);
							$emailContent = array($n . ":", $value);
							if ($fieldType != "") {
								$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
							}
							//$emailbody .= str_replace("_", " ", $key) . ":  " . $value . "\n";
							if (in_array($fieldID, $ccfields)) {
								$ccnum = "XXXX-XXXX-XXXX-" . substr($this->cleanString($value),-4,4);
								$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $ccnum . ",";
							} else if (in_array($fieldID, $ccverification)) {
								$ccverify = "XXX";
								$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $ccverify . ",";
							} else {
								$dbFields .= str_replace("_", " ", $this->cleanString($v)) . "=>" . $this->cleanString($value) . ",";
							}

							if ($this->cleanString($v . "_other") != "") {
								$dbFields .= $v . "_other" . "=>" . $this->cleanString($v . "_other");
							}
						}
					}
				}
			}
			$emailInfo .= $this->EndTable();

			if ($COST != 0) {
				$total = number_format($COST,2);
				$emailInfo .= "<p><b>Total Fee:&nbsp;<font style=\"color:red\">\$$total</font></b></p>";
			}
			if ($COST == "") {
				$COST = 0;
			}
			if ($auth_num == "" && $formPayment && $res_txt != "") {
				$auth_num = "card declined - $res_txt";
			}

			$emailInfo .= $emailTxt;

			//======== Enter information into database ========//
			
			//======== Add Sales Tax ========//
			$tax = bcmul($COST,0.0825);
			$COST = bcadd($COST, $tax);			
			
			$submissionID = $this->checkRequest('submissionID');
			if ($submissionID != "" && $submissionID != 0) {	
				$comments = $wpdb->get_var($wpdb->prepare("Select comments from " . $this->table . "form_info where form_id = %d", $submissionID));
				$comments .= $dbFields;
				$sql = "Update " . $this->table . "form_info set comments = %s where form_id = %d";
				$wpdb->query($wpdb->prepare($sql,$comments,$submissionID));	
			} else {
				$sql = "Insert into " . $this->table . "form_info (form_type_id, comments) VALUES (%d, %s)";
				$wpdb->query($wpdb->prepare($sql, $formID, $dbFields));	
				$submissionID = $this->lastID($this->table . 'form_info', 'form_id');
			}
			$link .= "&custom=" . $submissionID;
			

			$submitInfo[] = $submitStatus;
			$submitInfo[] = $res_txt;
			$submitInfo[] = $outcome;			
			$submitInfo[] = $emailInfo;
			$submitInfo[] = $link;
			$submitInfo[] = $formPayment;
			$submitInfo[] = $orderItems;
			$submitInfo[] = $COST;
			$submitInfo[] = $submissionID;			
		}
		
		
		return $submitInfo;
		
	}

	public function getPaypalLink() {	
		$linkInfo = array();
		$link = "https://www.paypal.com/cgi-bin/webscr/?";
		$orderplaced = 0;
		$cnt = 0;
		$points = array();
		$processInfo = array();
		$totalitems = $this->checkRequest('totalitems');
		//======== Insert Order from user ========//
		$c = 1;
		$orderItems = "";
		for ($i=1; $i<=$totalitems; $i++) {
			$checkItem = "quantity_" . $i;
			//if (isset($_POST[$checkItem]) && $_POST[$checkItem] != "") {
				$itemID = "item_id_" . $i;
				$item_id = $_POST[$itemID];

				$itemName = "item_name_" . $i;
				$item = $_POST[$itemName];
				$itemName = "item_name_" . $c;

				$itemNumber = "item_num_" . $i;
				$itemNum = $_POST[$itemNumber];
				$itemNumber = "item_num_" . $c;

				$qtyName = "quantity_" . $i;
				$quantity = $_POST[$qtyName];
				$qtyName = "quantity_" . $c;

				$amtName = "amount_" . $i;
				$amount = $_POST[$amtName];
				$amtName = "amount_" . $c;

				

				$optName = "option_" . $i;
				$option = $_POST[$optName];
				
				$COST += bcmul($amount,$quantity);
				if ($item != "") {
					$link .= "$itemName=$item&$qtyName=$quantity&$amtName=$amount&";
				}
				$c++;

				//Remove semicolons from data going into database
				$item = str_replace(";", ":", $item);

				$orderItems .= $item . ";" . $quantity . ";" . ";" . $amount . ";" . $item_id;
				if ($option != "") {
					$orderItems .= $option . ";";
				}
				$orderItems .= "|";

			//}
		}
		
		$payfields = array(
			'cmd',
			'upload',
			'business',
			'image_url',
			'address_override',
			'handling_cart',
			'cbt',
			'first_name',
			'last_name',
			'email',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'night_phone_a',
			'night_phone_b',
			'night_phone_c',
			'discount_amount_cart'
		);
		//======== Add on fields ========//
		foreach($payfields as $key=>$value) {
			if ($_POST[$value] != "") {
				$info = $this->checkRequest($value);
				$info = str_replace("#","",$info);
				$link .= $value . "=" .  $this->cleanString(trim($info)) . "&";
			}
		}
		
			$tax = bcmul($COST,0.08,2);
			if ($this->checkRequest('othercompany') != "" && $this->checkRequest('taxcompany') == "") {				 
				$link .= "tax_cart=$tax" . "&";
			} else if ($this->checkRequest('othercompany') == "" && $this->checkRequest('taxcompany') == "") {
				$link .= "tax_cart=$tax" . "&";
			}

		
		$link = substr($link, 0, strlen($link) - 1);
		
		$linkInfo['paypal_link'] = $link;
		$linkInfo['order_items'] = $orderItems;
		return json_decode(json_encode($linkInfo));
	
	}

	public function getPayPalInfo($formID) {
		$paypal_logo = $this->wpdb->get_var("Select paypal_logo from " . $this->table . "form_config where status = 1");
		$business = $this->wpdb->get_var("Select form_paypal_account from " . $this->table . "forms where form_id = 1");
		$type = "hidden";
		$ppInfo = "";
		$ppInfo .= $this->_doInput ('cmd', 'cmd', $type, '_cart', '', '', '', '', '', '','');
		$ppInfo .= $this->_doInput ('upload', 'upload', $type, '1', '', '', '', '', '', '','');
		$ppInfo .= $this->_doInput ('business', 'business', $type, "$business", '', '', '', '', '', '','');
		$ppInfo .= $this->_doInput ('currency_code', 'currency_code', $type, 'US', '', '', '', '', '', '','');
		$ppInfo .= $this->_doInput ('image_url', 'image_url', $type, "$paypal_logo", '', '', '', '', '', '','');
		$ppInfo .= $this->_doInput ('address_override', 'address_override', $type, "0", '', '', '', '', '', '','');
		return $ppInfo;
	}

	public function getCreditCardFields() {
		$ccfields = "";
		$template = WP_PLUGIN_DIR . "/kybformbuilder/view/html/creditcard_fields.html";
		$content = array(
			'SITEURL' => site_url(),
			'THEMEURL' => $this->templateURL,
		);
		$ccfields = $this->showPage ($template, $content, true);
		return $ccfields;
	}

	public function processAnet($cart_total,$userID, $productName, $invoice_num, $tax) {
		$wpdb = $this->wpdb;
		$payresponse = array();
		require_once (WP_PLUGIN_DIR . '/kybstore/model/anet/AuthorizeNet.php');
		//======== Get Payment Gateway Information ========//
		$sqlgate = "SELECT  SSLHost, MerchantID, MerchantKey, Partner FROM  cyber_cash WHERE  EtailerID = 1";
		$resultgate = $wpdb->get_results($sqlgate);
		if (count($resultgate) != 0) {
			foreach ($resultgate as $row) {
				$merchID = $row->MerchantID;
				$merchKey = $row->MerchantKey;
				$sslHost = $row->SSLHost;
				$partner = $row->Partner;
				$anetURL = $row->SSLHost;
			}
		}
		
		$expmonth = $this->checkRequest('cc_expiration_month');
		$expyear = $this->checkRequest('cc_expiration_year');
		$acctnum = $this->checkRequest('customer_credit_card_number');
		$firstname = $this->checkRequest('first_name');
		$lastname = $this->checkRequest('last_name');
		$phone = "(" . $this->cleanString($_POST['phone'][0]) . ") " . $this->cleanString($_POST['phone'][1]) . "-" . $this->cleanString($_POST['phone'][2]);
		$email = $this->checkRequest('email');
		define("AUTHORIZENET_API_LOGIN_ID", $merchID);
		define("AUTHORIZENET_TRANSACTION_KEY", $merchKey);
		define("AUTHORIZENET_SANDBOX", false);
		$sale = new AuthorizeNetAIM;
		$sale->amount = $cart_total;
		$sale->card_num = $acctnum;
		$sale->invoice_num = $invoice_num;
		$sale->exp_date = "$expmonth/$expyear";
		$sale->email_customer = false;
		$sale->cust_id = $userID;
		$sale->first_name = $firstname;
		$sale->last_name = $lastname;
		$sale->email = $email;
		$sale->phone = $phone;
		$sale->tax = $tax;
		$sale->description = $productName;
		$sale->ship_to_first_name = $firstname;
		$sale->ship_to_last_name = $lastname;

		$response = $sale->authorizeAndCapture();
		$payresponse['status'] = $response->approved;
		if ($response->approved) {
			$payresponse['transaction_id'] = $response->transaction_id;
			$payresponse['auth_num'] = $response->authorization_code;
			$payresponse['payType'] = $response->card_type;
			$payresponse['payAcct'] = $response->account_number;
		} else {
			$payresponse['reason'] = $response->response_reason_text;
		}
		return json_decode(json_encode($payresponse));
	}

	public function processPaypal($cart_total) {
		
		$wpdb = $this->wpdb;
		//Include Store classes for consistent processing
		$pluginDir = WP_PLUGIN_DIR;
		require_once("$pluginDir/kybstore/controller/controller.php"); // controller class
		require_once("$pluginDir/kybstore/model/store_cart.php"); // cart class
		$cls = new cartprocesses ();
		
		$httpParsedResponseAr = array();
				
		// Set request-specific fields.
		$paymentType = urlencode('Sale');				// 'Authorization' or 'Sale'
		$firstName = urlencode($_POST['first_name']);
		$lastName = urlencode($_POST['last_name']);
		$creditCardType = urlencode($_POST['customer_credit_card_type']);
		$creditCardNumber = urlencode($_POST['customer_credit_card_number']);
		$expDateMonth = $_POST['cc_expiration_month'];
		// Month must be padded with leading zero
		$padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));
		 
		$expDateYear = urlencode($_POST['cc_expiration_year']);
		$cvv2Number = urlencode($_POST['cc_cvv2_number']);
		$address1 = urlencode($_POST['address1']);
		$address2 = urlencode($_POST['address2']);
		$city = urlencode($_POST['city']);
		$country = ltrim($_POST['country']);
		$country = rtrim($country);
		$country = $wpdb->get_var($wpdb->prepare("Select country_code from countries where country_desc = %s", $country));
		$state = ltrim($_POST['state']);
		$state = rtrim($state);
		$state = $wpdb->get_var($wpdb->prepare("Select state_code from states where state_name = %s", $state));
		$state = urlencode($state);
		$zip = urlencode($_POST['zip']);
		$country = urlencode($country);				// US or other valid country code
		$amount = urlencode($cart_total);
		$currencyID = urlencode('USD');							// or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		 
		// Add request-specific fields to the request string.
		$nvpStr =	"&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber".
					"&EXPDATE={$padDateMonth}{$expDateYear}&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName".
					"&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";
		
		// Execute the API operation; see the PPHttpPost function above.
		$httpParsedResponseAr = $cls->PPHttpPost('DoDirectPayment', $nvpStr);
		
		return $httpParsedResponseAr;

	}
	
	public function formCheck ($formID) {

		
	}

	public function formSectionEdit($sectionID, $formID) {
		$wpdb = $this->wpdb;
		$pluginURL = WP_PLUGIN_URL . "/kybformbuilder/model/form_view.php?formID=$formID&process=editsectionsubmit";
		$formEdit = "<br>";
		$formEdit .= "<h2>Edit Form Section</h2>";
		$formEdit .= $this->BeginTable('100%', "0", "0", "0");
		$sql = "Select * from " . $this->table . "form_sections where section_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($sql, $sectionID));

		if (count($result) != 0) {
			foreach ($result as $row) {
				$content = array(
					$this->_addDiv('<strong>Section ID&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('section_id', 'section_id', 'text', $row->section_id, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Section Order&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('section_order', 'section_order', 'text', $row->section_order, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
				

				$content = array(
					$this->_addDiv('<strong>Section Title&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('section_title', 'section_title', 'text', $row->section_title, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Section Columns&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('section_columns', 'section_columns', 'text', $row->section_columns, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				/*$sql = "Select field_short_name, field_name from " . $this->table . "form_fields where form_id = $formID"; 
				$fieldOptions = $this->build_dboptions($sql, 'field_name', 'field_short_name', $row->section_hide_cc_field, true);
				$content = array(
					$this->_addDiv('<strong>Credit Card Field&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('section_hide_cc_field', 'section_hide_cc_field', $fieldOptions, false, 'form1', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$sql = "Select field_short_name, field_name from " . $this->table . "form_fields where form_id = $formID"; 
				$fieldOptions = $this->build_dboptions($sql, 'field_name', 'field_short_name', $row->section_hide_cc_verification, true);
				$content = array(
					$this->_addDiv('<strong>Credit Card Verification Field&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('section_hide_cc_verification', 'section_hide_cc_verification', $fieldOptions, false, 'form1', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');*/

				

				$statusOptions = $this->buildStatusOptions ($row->status);
				$content = array(
					$this->_addDiv('<strong>Status&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('status', 'status', $statusOptions, false, 'form1', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
			}
		}
		$formEdit .= $this->EndTable();
		$formEdit .= "<br>";
		$formEdit .= $this->_doInput ('Submit', 'Submit', 'button', 'Submit', '', 'button_sm','', "javascript:editForm('section_id,section_order,section_title,section_columns,status', '$pluginURL')", '', '','');
		return $formEdit;
	}

	public function formSectionSubmit() {
		$sectionID = $this->checkRequest('section_id');
		$formID = $this->checkRequest('formID');
		$outcome = "Form section has been updated";
		$this->processForm('update', $_GET, $this->table . 'form_sections', $sectionID, $outcome, '', '', false, '', '' , '', '', '', '', '', false, '');
	}

	public function formfieldEdit($fieldID, $formID) {
		$wpdb = $this->wpdb;
		$fieldTypes = array();
		$pluginURL = WP_PLUGIN_URL . "/kybformbuilder/model/form_view.php?formID=$formID&process=editfieldsubmit&fieldID=$fieldID";
		$formEdit = "<br>";
		$formEdit .= "<h3>Edit Form Field</h3>";
		$formEdit .= $this->BeginTable('100%', "0", "0", "0");
		$sql = "Select * from " . $this->table . "form_fields where id = %d";
		$result = $wpdb->get_results($wpdb->prepare($sql, $fieldID));

		//======== Get Field Types Options ========//
		$sqlfieldTypes = "Select * from " . $this->table . "form_field_types";
		$resultTypes = $wpdb->get_results($sqlfieldTypes);
		if (count($resultTypes) != 0) {
			foreach ($resultTypes as $t) {
				$fieldTypes[$t->ff_type_id] = $t->ff_type_name;
			}
		}

		if (count($result) != 0) {
			foreach ($result as $row) {
				$content = array(
					$this->_addDiv('<strong>Field ID&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('id', 'id', 'text', $row->id, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$sql = "Select * from " . $this->table . "form_sections where form_id = $formID";
				$sectionOptions = $this->build_dboptions($sql, 'section_title', 'section_id', $row->section_id, true);
				$sections = $this->_doSelect ('section_id', 'section_id', $sectionOptions, false, 'form1', '', 'width:295px;');

				$content = array(
					$this->_addDiv('<strong>Section ID&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $sections
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Order&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('field_order', 'field_order', 'text', $row->field_order, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');


				$content = array(
					$this->_addDiv('<strong>Field Name&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('field_name', 'field_name', 'text', $row->field_name, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Short Field Name&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('field_short_name', 'field_short_name', 'text', $row->field_short_name, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Column Span&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('field_span', 'field_span', 'text', $row->field_span, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Instruction&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_instruction', 'field_instruction', $row->field_instruction, 'form1', '', 'width:295px;height:75px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$requiredOptions = $this->buildOptions ($this->bitOptions, $row->field_required, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong>Required Field&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_required', 'field_required', $requiredOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$placementOptions = $this->buildOptions ($this->bitOptions, $row->field_placement, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong>Place Field on Same Line&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_placement', 'field_placement', $placementOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$titleOptions = $this->buildOptions ($this->bitOptions, $row->field_title_placement, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong>Place Field Title on Same Line&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_title_placement', 'field_title_placement', $titleOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$emailOptions = $this->buildOptions ($this->bitOptions, $row->field_email, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong>Include Field Information in Email&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_email', 'field_email', $emailOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

					
				$fieldOptions = $this->buildOptions ($fieldTypes, $row->field_type_id, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong> Form Field Type&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_type_id', 'field_type_id', $fieldOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
				

				$content = array(
					$this->_addDiv('<strong>Field Value&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doInput ('field_value', 'field_value', 'text', $row->field_value, '', 'form1', '', '', '', 'width:295px;','')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Paragraph&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_para', 'field_para', $row->field_para, 'form1', '', 'width:295px;height:150px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Options&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_options', 'field_options', $row->field_options, 'form1', '', 'width:295px;height:75px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Option Values&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_option_values', 'field_option_values', $row->field_option_values, 'form1', '', 'width:295px;height:75px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Option Instructions&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_option_instructions', 'field_option_instructions', $row->field_option_instructions, 'form1', '', 'width:295px;height:75px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$content = array(
					$this->_addDiv('<strong>Field Option Choices&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doTextArea ('field_option_selections', 'field_option_selections', $row->field_option_selections, 'form1', '', 'width:295px;height:75px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');

				$script_types = array("fillQty(this)" => "Fill Quantity");
				$checkScriptOptions = $this->buildOptions ($script_types, $row->field_script, '', '', false, 0);
				$content = array(
					$this->_addDiv('<strong> Field Script&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('field_script', 'field_script', $checkScriptOptions, false, 'form1', '', 'width:250px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
				

				$statusOptions = $this->buildStatusOptions ($row->status);
				$content = array(
					$this->_addDiv('<strong>Status&nbsp;:&nbsp;</strong>', '', 'text-align:right;', "",''), $this->_doSelect ('status', 'status', $statusOptions, false, 'form1', '', 'width:295px;')
				);
				$formEdit .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
			}
		}
		$formEdit .= $this->EndTable();
		$formEdit .= "<br>";
		$formEdit .= $this->_doInput ('Submit', 'Submit', 'button', 'Submit', '', 'btn btn-block btn-inverse divbtn','', "javascript:editForm('field_order,section_id,field_name,field_short_name,field_span,field_instruction,field_required,field_placement,field_title_placement,field_email,field_type_id,field_value,field_para,field_options,field_option_values,field_option_instructions,field_option_selections,field_script,status', '$pluginURL')", '', '','');
		return $formEdit;

	}

	public function formfieldSubmit() {
		$fieldID = $this->checkRequest('fieldID');
		$formID = $this->checkRequest('formID');
		$outcome = "Form field has been updated";
		$this->processForm('update', $_GET, $this->table . 'form_fields', $fieldID, $outcome, '', '', false, '', '' , '', '', '', '', '', false, '');
	}

	public function formDelete() {
		$wpdb = $this->wpdb;
		$pagenum = $this->checkRequest('pagenum');
		$submit = $this->checkRequest('Submit');
		$keyword = $this->checkRequest('keyword');
		$action = $this->checkRequest('Action');
		$ID = $this->checkRequest('submissionID');
		$this->checkRequest('criteria') == "all" ? $criteria = array("firstname, lastname") : $criteria = $this->checkRequest('criteria');
		$sql = "DELETE FROM " . $this->table . "form_info WHERE form_id=%d";
		$wpdb->query($wpdb->prepare($sql, $ID));
		echo "<p class=\"dbmessage\">The submission form has been deleted successfully.</p>";
	}

	
	function get_themefields($themeID, $themefields,$themecustom) {
		$wpdb = $this->wpdb;
		$fields = "";
		$formWPID = 0;
		$pluginURL = WP_PLUGIN_URL;
		$i = 0;
		$cnt = 1;
		$currentpage = site_url() . "/wp-admin/admin.php?page=kybformbuilder-form-template";
		$rowfields = explode(",", $themefields);
		$fields .= "<form name=\"SubmissionForm\" action=\"$currentpage\" method=\"POST\" >\n";
		$fields .= $this->_doInput ('themeID', 'themeID', 'hidden', $themeID, '', '', '', '', '', '');
		$fields .= $this->BeginTable('', "0", '2', '2') . "\n"; 
		foreach ($rowfields as $key => $value) {
			$rowInfo = explode("=>", $value);
			$fieldLabel = $this->_addDiv($rowInfo[0] . "&nbsp;:&nbsp;", '', 'font-weight:bold;', 'label');
			$fieldname = strtolower(str_replace(" ", "_", $rowInfo[0]));
			if (isset($themecustom[$i])) {
				$fieldValue = str_replace(";", ",",$themecustom[$i]);
			}
			switch ($rowInfo[1]) {
				case 1:
					$fieldInput = $this->_doInput ($fieldname, $fieldname, 'text', $fieldValue, '', 'regular', '', '', '', 'width:500px;');
				break;
				case 8://Phone number
					$identification = $fieldname . "[]";
					$phone0 = "";
					$phone1 = "";
					$phone2 = "";
					$fullphone = "";
					$phoneparts = explode("-",$fieldValue);
					if (count($phoneparts) != 0) {
						$phone0 = $phoneparts[0];
						$phone1 = $phoneparts[1];
						$phone2 = $phoneparts[2];
						$fullphone = "($phone0) $phone1-$phone2";
					}
					
					$fieldInput =  "(<input type=\"text\" name=\"$identification\" id=\"phone0\" class=\"inputreg\" style=\"width:40px\" value=\"$phone0\" />)&nbsp;<input type=\"text\" name=\"$identification\" id=\"phone1\" class=\"inputreg\" style=\"width:40px\" value=\"$phone1\" />&nbsp;-&nbsp;<input type=\"text\" name=\"$identification\" id=\"phone2\" class=\"inputreg\" style=\"width:40px\" value=\"$phone2\" />";
					
				break;
				case 9: //Editor 
					$fieldInput = $this->showWPeditor3($fieldValue, $fieldname, $fieldname, false, 1, false);
				break;
				case 11: //Image/file upload
					$fieldInput = <<<IMAGEAREA
					<script language="javascript" type="text/javascript">
						jQuery(document).ready(function() {
 
						jQuery('#upload_image_button_$cnt').click(function() {
						 formfield = jQuery('#upload_image_$cnt').attr('name');
						 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
						 window.send_to_editor = function(html) {
						  imgurl = jQuery('img',html).attr('src');
						  jQuery('#upload_image_$cnt').val(imgurl);
						  jQuery('#image_view_$cnt').attr('src', imgurl);
						  tb_remove();
						 }
						 return false;
						});

						jQuery('#upload_file_button_$cnt').click(function() {
						 formfield = jQuery('#upload_file_$cnt').attr('name');
						 tb_show('', 'media-upload.php?type=file&amp;TB_iframe=true');
						 window.send_to_editor = function(html) {
						  imgurl = jQuery('img',html).attr('src');   
						  jQuery('#upload_file_$cnt').val(imgurl);
						  tb_remove();
						 }
						 return false;
						});


						   
						});
					</script>
					
					<table cellpadding="0" cellspacing="0" width="300">
						<tr>
							<td>
								<label for="upload_image">
								<input id="upload_image_$cnt" type="text" class="contact" style="width:500px;height:25px;" name="upload_image_$cnt" value="$fieldValue" />
								<input id="upload_image_button_$cnt" type="button" class="button-primary" style="width:150px;" value="Upload/Select Image" />
								<br />Enter a URL or upload an image.
								<p><img src="$fieldValue" width="150" id="image_view_$cnt"/></p>
								
								</label>
							</td>							
						</tr>
					</table>
IMAGEAREA;
					$cnt++;
				break;
				case 12: //Word Press pages dropdown
					//======== Get Word Press Page dropdown ========//
					$fieldValue != 0 ? $pageLink = get_permalink($fieldValue) : $pageLink = "#";
					$fieldValue != 0 ? $pageEdit = "post.php?post=$fieldValue&action=edit" : $pageEdit = "#";
					$pageAdd = "post-new.php?post_type=page";
					$args = array(
						'depth'            => 0,
						'child_of'         => 0,
						'selected'         => $fieldValue,
						'echo'             => 0,
						'show_option_none' => "-- Please Select --",
						'name'             => 'form_wp_page');
					$docPage = wp_dropdown_pages($args); 
					$docPage .= "&nbsp; <a href=\"$pageLink\" target=\"_blank\">view page</a> &nbsp;&nbsp; <a href=\"$pageEdit\">edit page</a> &nbsp;&nbsp; <a href=\"$pageAdd\">add page</a>";
					$fieldInput = $docPage;
				break;
				default:
					$fieldInput = $this->_doInput ($fieldname, $fieldname, 'text', $submitValue, '', 'regular', '', '', '', 'width:295px;');
				break;
			}
			$content = array (
				$fieldLabel, $fieldInput
			);
			$fields .= $this->buildRow2($content, '', '', count($content), '', '', '', '', '', '', 'top');
			$i++;
			
		}			
		$fields .= $this->EndTable();
		$fields .= "<input type=\"submit\" name=\"SettingsSubmit\" class=\"button-primary\" value=\"Submit\">";
		
		$fields .= "</form>";

		return $fields;
	}

	public function wp_dashboard() {
		$dataURL = site_url() . "/wp-admin/admin.php?page=kybdata/index.php";
		$formsURL = site_url() . "/wp-admin/admin.php?page=kybformbuilder-form-list";
		$photoURL = site_url() . "/wp-admin/admin.php?page=kybgallery-photos";
		$mediaURL = site_url() . "/wp-admin/admin.php?page=kybgallery-media";
		$calendarURL = site_url() . "/wp-admin/admin.php?page=kybcalendar/index.php";
		$template = "dashboard.html";
		$content = array (
			'DATAURL' => $dataURL,
			'FORMSURL' => $formsURL,
			'PHOTOURL' => $photoURL,
			'MEDIAURL' => $mediaURL,
			'CALENDARURL' => $calendarURL,
			'HTML' => $template
		);
		return $content;

	}

	public function duplicateform($formID) {
		$wpdb = $this->wpdb;
		$formItems = array();
		$fieldTitles = array();
		//======== First duplicate form information ========//
		$sql = "Select * from " . $this->table . "forms where form_id = $formID";
		$result = mysql_query($sql);
		
		while ($meta = mysql_fetch_field($result)) {
			$fieldName = $meta->name;
			$fieldInfo = $wpdb->get_var($wpdb->prepare("Select $fieldName from " . $this->table . "forms where form_id = %d", $formID));
			$formItems[$fieldName] = $fieldInfo;
		}
		$this->processForm('add', $formItems, $this->table . 'forms', '', '', '', '', false, '', '', '', '', '',"", '', false, "");
		mysql_free_result($result);
		//======== End duplicate form information ========//

		//======== Get entered form ID ========//
		$newformID = $this->lastID($this->table . 'forms', 'form_id');



		//======== Duplicate form sections ========//
		$formItems = array();
		$newsectionID = array();
		$currentsectionID = array();
		$sql = "Select * from " . $this->table . "form_sections where form_id = $formID";
		$result = mysql_query($sql);
		$roll = $wpdb->get_results($sql);
		
		while ($meta = mysql_fetch_field($result)) {
			$fieldName = $meta->name;
			$fieldTitles[] = $fieldName;
		}
		
		foreach ($roll as $r) {
			foreach ($fieldTitles as $key => $value) {
				$fieldName = $fieldTitles[$key];
				if ($fieldName == 'form_id') {
					$formItems[$fieldName] = $newformID;
				} else {
					$formItems[$fieldName] = $r->$fieldName;
				}

				if ($fieldName == 'section_id') {
					$currentsectionID[] = $r->$fieldName;
				}
			}
			$this->processForm('add', $formItems, $this->table . 'form_sections', '', '', '', '', false, '', '', '', '', '',"", '', false, "");
			//======== Get entered section ID ========//
			$newsectionID[] = $this->lastID($this->table . 'form_sections', 'section_id');			
		}
		mysql_free_result($result);
		//======== End Duplicate form sections ========//

		
		foreach ($newsectionID as $k => $v) {
			$sectionID = $currentsectionID[$k];
			//======== Duplicate form fields ========//
			$formItems = array();
			$sql = "Select * from " . $this->table . "form_fields where form_id = $formID and section_id = $sectionID";
			$resultfields = mysql_query($sql);
			$roll = $wpdb->get_results($sql);
			
			while ($meta = mysql_fetch_field($resultfields)) {
				$fieldName = $meta->name;
				$fieldTitles[] = $fieldName;
			}
			
			foreach ($roll as $r) {
				foreach ($fieldTitles as $key => $value) {				
					$fieldName = $fieldTitles[$key];
					if ($fieldName == 'form_id') {
						$formItems[$fieldName] = $newformID;
					} elseif ($fieldName == 'section_id') {
						$formItems[$fieldName] = $v;
					} else {
						$formItems[$fieldName] = $r->$fieldName;
					}
				}
				$this->processForm('add', $formItems, $this->table . 'form_fields', '', '', '', '', false, '', '', '', '', '',"", '', false, "");
			}
			mysql_free_result($resultfields);
			//======== End Duplicate form fields ========//
			$done = true;
		}
	}

	public function sendformemail($submissionID) {
		$wpdb = $this->wpdb;
		//======== Send email notice to owner and to customer ========//
		
		$userInfo = $wpdb->get_var($wpdb->prepare("Select comments from " . $this->table . "form_info where form_id = %d", $submissionID));
		$userItems = explode(",", $userInfo);
		$formID = $wpdb->get_var($wpdb->prepare("Select form_type_id from " . $this->table . "form_info where form_id = %d", $submissionID));
		$formInfo = $this->get_form_info($formID);
		//$emailbody = "<p>The following online form has been submitted from the " . $this->company . " Website.</p>";
		$emailbody = "<p><b>" . $formTitle . "</b></p>";
		$emailInfo .= $this->BeginTable('650', '1', '2', '2');
		$submitDate = $wpdb->get_var($wpdb->prepare("Select signature_date from " . $this->table . "form_info where form_id = %d", $submissionID));
		$submitDate = $this->ReformatDatedb($submitDate);
		//$paymethod = $wpdb->get_var($wpdb->prepare("Select card_type from form_info where form_id = %d", $submissionID));
		$subtotal = $wpdb->get_var($wpdb->prepare("Select payment_amt from " . $this->table . "form_info where form_id = %d", $submissionID));
		$subtotal = "$" . number_format($subtotal, 2);
		$total = $subtotal;
		
		if (count($userItems) != 0) {
			foreach ($userItems as $k => $u) {
				$userFieldItems = explode("=>", $u);
				$label = $userFieldItems[0];
				$labelTitle = $wpdb->get_var($wpdb->prepare("Select field_name from " . $this->table . "form_fields where form_id = %d and field_short_name = %s and field_email = 1", $formID, $label));
				$userEntry = $userFieldItems[1];
				if ($labelTitle != "") {
					$emailContent = array($labelTitle . ":", $userEntry);
					$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
				}
				if ($label == "orderitems") {
					$orderInfo = explode("|", $userEntry);
					foreach ($orderInfo as $k => $v) {
						$items = explode(";", $v);
						$itemTitle = $items[0];
						$itemEntry = $items[3];
						if ($itemTitle != "") {
							//$emailContent = array($itemTitle . ":", $itemEntry);
							//$emailInfo .= $this->buildRow2($emailContent, '', '', count($emailContent), 'left', '', '', '', '', '','top');
						}
					}
				}

				//====== Set first, last and email ========//
				$findemail = 'email';
				$findfirst = 'first';
				$findlast = 'last';
				$findname = 'name';
				$findaddress1 = 'address1';
				$findaddress2 = 'address2';
				$findcity = 'city';
				$findstate = 'state';
				$findzip = 'zip';
				$findphone = 'phone';
				
				$f = strpos(strtolower($label), $findfirst);
				$l = strpos(strtolower($label), $findlast);
				$e = strpos(strtolower($label), $findemail);
				$n = strpos(strtolower($label), $findname);
				$a1 = strpos(strtolower($label), $findaddress1);
				$a2 = strpos(strtolower($label), $findaddress2);
				$c = strpos(strtolower($label), $findcity);
				$st = strpos(strtolower($label), $findstate);
				$z = strpos(strtolower($label), $findzip);
				$ph = strpos(strtolower($label), $findphone);

				if ($f !== false) {
					$firstname = $userEntry;
				}

				if ($l !== false) {
					$lastname = $userEntry;
				}

				if ($e !== false) {
					$useremail = $userEntry;
				}

				if ($a1 !== false) {
					$address1 = $userEntry;
				}

				if ($a2 !== false) {
					$address2 = $userEntry;
				}

				if ($c !== false) {
					$city = $userEntry;
				}

				if ($st !== false) {
					$state = $userEntry;
				}

				if ($z !== false) {
					$zip = $userEntry;
				}

				if ($ph !== false) {
					$phone = $userEntry;
				}

				if ($n !== false) {
					
					$fullname = explode(" ", $userEntry);
					if (!isset($firstname)) {
						$firstname = $fullname[0];
					}

					if (!isset($lastname)) {
						$lastname = $fullname[1];
					}
				}
				//======== End set first, last and email ========//
			}
			
			$emailInfo .= $this->EndTable();
			$emailTitle = $formInfo->formTitle;
			//Set email variables
			$emailto = $formInfo->formEmail;
			$emailsubject = "Form Submission - " . $formInfo->formTitle;  
			$emailbody .= "\n\n";
			$fromName = str_replace("&amp;", "and",$this->company) . " <$emailto>";
			$template = $this->pluginDir . "/view/html/form_email_confirmation.html";
			$content = array (
				'FORM_TITLE' => $emailTitle,
				'LETTER' => $emailbody,
				'INFORMATION' => $emailInfo,
				'CONFIRMTITLE' => 'Online Form Submission',
				'CONFIRMTYPE' =>'Online Form Submission',
				'FIRSTNAME' => $firstname,
				'LASTNAME' => $lastname
			);
			$body = $this->showPage ($template, $content, true);	
			$companyName = str_replace("&amp;", "and",$this->company);
			$emailsubject = "Submission to $companyName";
			$template = $this->pluginDir . "/view/html/form_useremail_confirmation.html";
			$usercontent = array (
				'FORM_TITLE' => $emailTitle,
				'LETTER' => "<p>Thank you so very much $firstname for your interest in our services. Someone will be in contact with you shortly.  We look forward to speaking with you.</p><br><p>Sincerely</p><br><p>$companyName</p>",
				'CONFIRMTITLE' => "Thank You For Your Submission!",
				'CONFIRMTYPE' => "Thank You For Your Submission!",
				'YEAR' => date("Y"),
				'COMPANYNAME' => $companyName
			);
			$userbody = $this->showPage ($template, $usercontent, true);	
			if ($formInfo->form_payment) {
				$receipt_template = $this->pluginDir . "/view/html/form_receipt.html";
				$receiptcontent = array(
					'BILLNAME' => $firstname . " " . $lastname,
					'COMPANY' => COMPANYNAME,
					'COMPANYEMAIL' => COMPANYEMAIL,
					'COMPANYPHONE' => COMPANYPHONE,
					'LOGOURL' => COMPANYLOGO,
					'URL' => site_url(),
					'LOGOTITLE' => COMPANYNAME,
					'INSTRUCTIONS' => $body,
					'INVOICE' => "$submissionID",
					'RECEIPTDATE' => $submitDate,
					'BILLADDRESS1' => $address1,
					'BILLADDRESS2' => $address2,
					'BILLCITY' => $city,
					'BILLSTATE' => $state,
					'BILLZIP' => $zip,
					'BILLPHONE' => $phone,
					'BILLEMAIL' => $useremail,
					'PAYMENTMETHOD' => $paymethod,
					'SHIPTYPE' => "N/A",
					'FULLNAME' => $firstname . " " . $lastname,
					'ADDRESS1' => $address1,
					'ADDRESS2' => $address2,
					'CITY' => $city,
					'STATE' => $state,
					'ZIP' => $zip,
					'PHONE' => $phone,
					'EMAIL' => $useremail,
					'SHIPPINGMETHOD' => "N/A",
					'SUBTOTAL' => "$subtotal",
					'DISCOUNT' => "\$0.00",
					'TAX' => "\$0.00",
					'SHIPPING' => "\$0.00",
					'TOTAL' => "$total",
				);

				$userbody = $this->showPage ($receipt_template, $receiptcontent, true);

			}
			//Send email to headquarters
			$this->sendEmail3 ($body, $emailsubject, $fromName, $emailto, $emailto);
			if ($useremail != "") {
				//$this->sendEmail ($userbody, $emailsubject, $fromName, $useremail, $emailto);
			}

		}
	}

	

	function get_form_info($formID)
	{
		$wpdb = $this->wpdb;
		$formInfo = array();
		//======== Get Form Information ========//
		$sql = "select * from " . $this->table . "forms where form_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($sql, $formID));

		if (count($result) != 0) {
			foreach ($result as $row) {
				$formInfo['formTitle'] = $row->form_title;
				$formInfo['emailInfo'] = $row->form_preview_link;
				$formInfo['fullDate'] = $this->ReformatDatedb($row->form_startdate);
				$formInfo['presentation'] = $row->form_presentation_file;
				$formInfo['formEmail'] = $row->email_responses;
				$formInfo['recordingLink'] = $row->form_preview_link;
				$formInfo['loginLink'] = $row->form_box5;
				$formInfo['form_payment'] = $row->form_payment;
				$formInfo['fullWrdDate'] = date('l F d, Y', strtotime($row->form_startdate));
				$formInfo['startTime'] = $row->form_start_time;
				$formInfo['endTime'] = $row->form_end_time;
				$formInfo['timezone'] = $row->form_timezone;
				$formInfo['form_listing_type'] = $row->form_listing_type;
				$formInfo['form_wp_page'] = $row->form_wp_page;
			}
		}
		
		return json_decode(json_encode($formInfo), FALSE);
	}

	public function defineCompanySettings() {
		 $wpdb = $this->wpdb;
		 $sql = "Select * from " . $this->table . "form_config where status = 1";
		 $result = $wpdb->get_results($sql);
		 if (count($result) != 0) {
			 foreach ($result as $row) {
				 define ('COMPANYNAME' , $row->company);
				 define ('COMPANYPHONE' , $row->phone);
				 define ('COMPANYEMAIL' , $row->email);
				 define ('COMPANYADDRESS' , $row->address);
				 define ('COMPANYCITY' , $row->city);
				 define ('COMPANYSTATE' , $row->state);
				 define ('COMPANYZIP' , $row->zip);
				 define ('COMPANYLOGO' , $row->logo);
			 
			 }
		 }
	}

	public function form_duplicate($formID) {
		$wpdb = $this->wpdb;
		$fieldInfo = array();
		$agendaInfo = array();
		$sql = "Select * from " . $this->table . "forms where form_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($sql, $formID), ARRAY_A);
		
		if (count($result) != 0) {
			foreach ($result[0] as $key => $row) {
				if ($key == "form_status") {
					$fieldInfo['form_status'] = 0;
				} else {
					$fieldInfo[$key] = $this->cleanHTML($result[0][$key]);
				}
			}
			
			if (count($fieldInfo) != 0) {
				$this->processForm('add', $fieldInfo, $this->table . 'forms', 0, '', '', '', false, '', '' , '', '', '', '', '', false, '');
				$newFormID = $this->lastID($this->table . 'forms', 'form_id');
			}
			

			//Will also need to duplicate form sections and fields with new form ID
			$formSections = array();
			$formFields = array();			
			$sqlSections = "Select * from " . $this->table . "form_sections where form_id = %d";
			$resultSections = $wpdb->get_results($wpdb->prepare($sqlSections, $formID));
			if (count($resultSections) != 0) {
				foreach ($resultSections as $sec) {
					$section_id = $sec->section_id;
					$formSections['section_title'] = $sec->section_title;
					$formSections['section_order'] = $sec->section_order;
					$formSections['form_id'] = $newFormID;
					$formSections['section_columns'] = $sec->section_columns;
					$formSections['status'] = $sec->status;
					$this->processForm('add', $formSections, $this->table . 'form_sections', 0, '', '', '', false, '', '' , '', '', '', '', '', false, '');
					$newSectionID = $this->lastID($this->table . 'form_sections', 'section_id');						
					$sqlFields = "Select * from " . $this->table . "form_fields where form_id = %d and section_id = %d";
					$resultFields = $wpdb->get_results($wpdb->prepare($sqlFields, $formID, $section_id));
					if (count($resultFields) != 0) {
						foreach ($resultFields as $f) {
							$formFields['form_id'] = $newFormID;
							$formFields['field_name'] = $f->field_name;
							$formFields['field_short_name'] = $f->field_short_name;
							$formFields['field_identity'] = $f->field_identity;
							$formFields['field_instruction'] = $f->field_instruction;
							$formFields['field_type_id'] = $f->field_type_id;
							$formFields['section_id'] = $newSectionID;
							$formFields['field_order'] = $f->field_order;
							$formFields['field_required'] = $f->field_required;
							$formFields['field_value'] = $f->field_value;
							$formFields['field_para'] = $f->field_para;
							$formFields['field_placement'] = $f->field_placement;
							$formFields['field_title_placement'] = $f->field_title_placement;
							$formFields['field_feature'] = $f->field_feature;
							$formFields['field_list_order'] = $f->field_list_order;
							$formFields['field_style'] = $f->field_style;
							$formFields['view_placement'] = $f->view_placement;
							$formFields['field_delimiter'] = $f->field_delimiter;
							$formFields['field_options'] = $f->field_options;
							$formFields['field_option_values'] = $f->field_option_values;
							$formFields['field_option_instructions'] = $f->field_option_instructions;
							$formFields['field_option_selections'] = $f->field_option_selections;
							$formFields['field_span'] = $f->field_span;
							$formFields['field_check'] = $f->field_check;
							$formFields['field_script'] = $f->field_script;
							$formFields['status'] = $f->status;
							$formFields['field_email'] = $f->field_email;

							$this->processForm('add', $formFields, $this->table . 'form_fields', 0, '', '', '', false, '', '' , '', '', '', '', '', false, '');
							$formFields = array();
						}
					}
					$formSections = array();					
				}					
			}			
		}
	}
}
?>