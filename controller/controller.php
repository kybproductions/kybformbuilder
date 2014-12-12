<?php
/*******************************************************************************
* ONLINE FORMS BUILDER CONTROLLER											   *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    09-20-2012                                                          *
* Author:  Kimla Y. Beasley													   *
* Copyright 2012 KYB PRODUCTIONS											   *
*******************************************************************************/


interface builderfunctions {
	
}

class FormController { 
	public $mod;
	
	
	 public function __construct()  
     { 
		$this->pluginDir =  WP_PLUGIN_DIR . "/kybformbuilder";
		require_once($this->pluginDir . '/model/model.php');
		$this->mod = new builderclass;
		wp_enqueue_script('form-js-js', WP_PLUGIN_URL . '/kybformbuilder/js/form_js.js');
	 }
         
     public function invoke($process = "")  
     { 
			
		$process = $this->checkRequest('process');
		
		if ($process == "") {
			if (isset($_REQUEST['page']) && !isset($_REQUEST['Submit'])) {
				$pageCheck = $this->checkRequest('page');
				switch ($pageCheck) {
					case "kybformbuilder/index.php":
						$process = "formsettings";
					break;
					
					case "kybformbuilder-form-list":
						$process = "listforms";
					break;
					case "kybformbuilder-form-add":
						$process = "CreateForm";
					break;
					default:
						$process = $this->checkRequest('Submit');
					break;
				}
			} else {
				$process = $this->checkRequest('Submit');
			}
			
		} 
		$content = $this->modelProcesses($process);
		if (is_array($content)) {
			$html = $content['HTML'];
		}
		
		$management = "admin";
		$mgmt_title = "Online Form Management";

		
		if ($this->checkRequest('preview') == "1") {
			$showheader = false;
		} else {
			$showheader = true;
		}
		include_once($this->pluginDir . "/view/output.php");

     }  

	public function modelProcesses($process) {
		global $wpdb;
		$formID = $this->checkRequest('formID');
		switch ($process) {
			case "CreateForm":
				$content = $this->mod->initform($process);
			break;
			case "Continue":
				if ($formID == "" || $formID == 0) {
					$formID == $this->mod->form_editProcess($formID);
				} else {
					$this->mod->form_editProcess($formID);
				}
				$content = $this->mod->modifyform($process,$formID,$payment,$upload);
			break;
			case "Finish":
				$this->mod->form_editProcess($formID);
				$content = $this->mod->listforms();
			break;
			case "modifyform":
				$content = $this->mod->modifyform($process,$formID,0,0);
			break;
			case "update":
				$content = $this->mod->formview('cmsview', $formID, '', true);
			break;
			case "addfields":
				$this->mod->addfields($formID);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "deletefield":
				$this->mod->deletefields($formID);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "deletesection":
				$this->mod->deletesection($formID);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "moveup":
				$this->mod->moveFormItems('fields', $formID, $process);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "movedown":
				$this->mod->moveFormItems('fields', $formID, $process);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "movesectionup":
				$this->mod->moveFormItems('sections', $formID, $process);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "movesectiondown":
				$this->mod->moveFormItems('sections', $formID, $process);
				$content = $this->mod->formview($process, $formID, '', true);
			break;
			case "UpdateForm":
				$content = $this->mod->initform($process);
			break;
			case "Delete":
				$this->mod->DeleteForm($formID);
				echo "<p class=\"dbmessage\">The form has been deleted successfully.</p>";
				$content = $this->mod->listforms();
			break;
			case "Activation":
				$this->mod->formActivation();
				$content = $this->mod->listforms();
			break;
			case "submissions":
				$pagenum = $this->checkRequest('pagenum');
				$submit = $this->checkRequest('Submit');
				$keyword = $this->checkRequest('keyword');
				$action = $this->checkRequest('Action');
				$this->checkRequest('criteria') == "all" ? $criteria = array("firstname, lastname") : $criteria = $this->checkRequest('criteria');				
				$content = $this->mod->formInfo($formID, $pagenum, $submit, $keyword, $criteria, $action);
			break;
			case "submissionupdate":
				$this->mod->formsubmission_update($formID);
				$pagenum = $this->checkRequest('pagenum');
				$submit = $this->checkRequest('Submit');
				$keyword = $this->checkRequest('keyword');
				$action = $this->checkRequest('Action');
				$this->checkRequest('criteria') == "all" ? $criteria = array("firstname, lastname") : $criteria = $this->checkRequest('criteria');	
				echo "<p class=\"dbmessage\">The form submission has been updated successfully.</p>";
				$content = $this->mod->formInfo($formID, $pagenum, $submit, $keyword, $criteria, $action);
			break;
			case "formedit":
				//$content = $this->mod->formviewProcesses('print', $formID, '', true);
				$content = $this->viewForm('print', $formID);
			break;
			case "editsection":
				$formID = $this->checkRequest('formID');
				$sectionID = $this->checkRequest('sectionID');
				$content = $this->mod->formSectionEdit($sectionID, $formID, '', true);
			break;
			case "editsectionsubmit":
				$this->mod->formSectionSubmit();
				$content = $this->mod->formview('update', $formID, '', true);

			break;
			case "editfield" :
				$formID = $this->checkRequest('formID');
				$fieldID = $this->checkRequest('fieldID');
				$content = $this->mod->formfieldEdit($fieldID, $formID, '', true);
			break;
			case "editfieldsubmit" :
				$this->mod->formfieldSubmit();
				$content = $this->mod->formview('update', $formID, '', true);
				
			break;
			case "deleteform":
				$this->mod->formDelete();
				$content = $this->mod->formInfo($formID, $pagenum, $submit, $keyword, $criteria, $action);
			break;
			case "duplicateform":
				$formID = $this->checkRequest('formID');
				$this->mod->form_duplicate($formID);
				$content = $this->mod->listforms();
			break;
			case "listforms":
				$content = $this->mod->listforms();
			break;
			
			case "duplicate":
				$this->mod->duplicateform($formID);
				$content = $this->mod->listforms();
			break;
			case "formsettings":
				$content = $this->mod->form_settings();
			break;
			default:
				$content = $this->mod->listforms();
			break;
		}
		return $content;
	}

	
	
	public function cleanString($string) {
		$string = preg_replace("@<script[^>]*>.+</script[^>]*>@i", "", $string); 
		$cleaned = trim(strip_tags(stripslashes($string)));
		//$cleaned = addslashes($string);
		$cleaned = str_replace("'","''",$string);
		$cleaned = str_replace("''","&quot;",$string);
		return $cleaned;
	}

	public function checkRequest($var) {
		$value = "";
		 if (isset($_POST[$var]) || $_GET[$var] != "") {
			$_POST[$var] != "" ? $value = $this->cleanString($_POST[$var]) : $value = $this->cleanString($_GET[$var]);
		  }
		  return $value;		 
	}

	public function viewForm($process, $formID) {
		$wpdb = $this->wpdb;
		switch ($process) {
			case "view":
				$content = $this->mod->front_view($formID, false, $process);
			break;
			case "print":
				$content = $this->mod->formview ('print', $formID, '', true);				
			break;
			case "formsubmit";
				$formID = $this->checkRequest('formID');
				$content = $this->mod->front_submit($formID);				
			break;
			case "formcomplete";			
				$content = $this->mod->front_complete($formID);
			break;
		}
		//$content = $this->mod->formviewProcesses($process, $formid);
		
		if (is_array($content)) {
			$html = $content['HTML'];
		}
		if ($html != "") {
			$template =  WP_PLUGIN_DIR . "/kybformbuilder/view/html/$html";	
			$page = new FillPage ($template);
			$page->replace_tags($content);
			$output = $page->viewoutput();
			return $output;
		} else {
			return $content;
		}
	}

}



?>