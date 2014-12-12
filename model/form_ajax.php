<?php
@session_start();
error_reporting(E_ALL);
$basedir = $_SERVER['DOCUMENT_ROOT'] . "/dev/coppell";

require_once("$basedir/wp-config.php");
require_once(WP_PLUGIN_DIR . "/kybformbuilder/lib/shared.php");
require_once(WP_PLUGIN_DIR . "/kybformbuilder/lib/smarty.php");
require_once(WP_PLUGIN_DIR . "/kybformbuilder/controller/controller.php");
require_once(WP_PLUGIN_DIR . "/kybformbuilder/model/model.php");
$cls = new builderclass();
$process = $_REQUEST['process'];
define('KYBFORMS_TABLE', $table_prefix);

switch ($process) {
	case "add":
		//Get last section id
		$sqlid = "Select Max(section_order) as LASTORDER from " . KYBFORMS_TABLE . "form_sections";
		$sectionorder = $wpdb->get_var($sqlid);
		if ($sectionorder == "") {
			$sectionorder = 1;
		}
		print $sectionorder;
	break;
	
	case "getsectionID":
		$sql = "Select Max(section_id) as LASTID from " . KYBFORMS_TABLE . "form_sections";
		$sectionID = $wpdb->get_var($sql);	
		if ($sectionID == "") {
			$sectionID = 1;
		}

		//Get last section id
		$sqlid = "Select Max(section_order) as LASTORDER from " . KYBFORMS_TABLE . "form_sections";
		$sectionorder = $wpdb->get_var($sqlid);
		if ($sectionorder == "") {
			$sectionorder = 1;
		}
		
		print "$sectionorder-$sectionID";
	break;
	
	case "extract":
		$formID = $_GET['formID'];
		$fields = array();
		$fieldNames = array();
		$records = array();
		$sqlSections = "Select * from " . KYBFORMS_TABLE . "form_sections where form_id = %d and status = 1 order by section_order";
		$resultSections = $wpdb->get_results($wpdb->prepare($sqlSections, $formID));
		if (count($resultSections) != 0) {
			foreach ($resultSections as $sec) {
				$sectionID = $sec->section_id;
				$sqlFields = "Select * from " . KYBFORMS_TABLE . "form_fields where section_id = %d and form_id = %d";
				$resultFields = $wpdb->get_results($wpdb->prepare($sqlFields, $sectionID, $formID));

				if (count($resultFields) != 0) {
					foreach ($resultFields as $f) {
						$fields[] = $f->field_name;
						$fieldNames[] = $f->field_short_name;
					}
				}
			}
		}

		//Extract database fields
		for ($i = 0; $i < count($fields); $i++) {
			$header .= $fields[$i] . "\t";
		}
		


		$sql = "Select * from " . KYBFORMS_TABLE . "form_info where form_type_id = %d";
		$export = $wpdb->get_results($wpdb->prepare($sql, $formID));

		if (count($export) != 0) {
			foreach ($export as $row) {
				$entry = explode (",", $row->comments);
				foreach ($fieldNames as $k => $name) {
					$name = str_replace("_", " ", $name);
					foreach ($entry as $key => $v) {
						$fieldEntry = explode ("=>", $v);						
						if ($fieldEntry[0] == $name) {
							$data .= trim($fieldEntry[1]) . "\t";
						} 
					}
				}
				
				$data .= "\n";
			}
		}
		$data = str_replace("\r", ";", $data);
		

		/*foreach ($records as $list) {
			$line = "";
			 if ((!isset($list)) OR ($list == "")) {
				$value = "\t";
			} else {
				$value = str_replace('"', '""', $list);
				$value = '"' . $value . '"' . "\t";
			}
			$line .= $value;
			$data .= trim($line) . "\t";
		}*/

		

		//Set default message
		if ($data == "") {
			$data = "\n(0) Records Found!\n";
		}

		//Set up download

		$xlsdata=$header."\n".$data;
		# This line will stream the file to the user rather than spray it across the screen
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=formList.xls");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $header."\n".$data;
	break;
	case "checkname":
		echo "Name OK";
	break;
	case "formsubmit":
		
		$_SESSION['viewintro'] = 0;
		$refererURL = $_SERVER['HTTP_REFERER'];
		if (isset($_POST['validator']) && $_POST['validator'] != $_SESSION["code"]) {
			$submitInfo[] = 0;
			$err = "Incorrect Validation";
		} else if (isset($_POST['validator']) && $_POST['validator'] == $_SESSION["code"]) {
			if ($cls->checkRequest('checkdb') != "") {

				$formID = $cls->checkRequest('formID');
				$chkField = $cls->checkRequest('checkdb');
				$chkInfo = $cls->checkRequest($chkField);
				$submissionID = $wpdb->get_var("Select form_id from " . KYBFORMS_TABLE . "form_info where form_type_id = $formID and comments like '%$chkInfo%'");

				if ($submissionID != "") {
					$_SESSION['viewintro'] = 1;
					$submitInfo[] = 1;
				} else {
					$submitInfo = $cls->formsubmit();
					$submissionID = $submitInfo->submissionID;
					$cls->sendformemail($submissionID);
				}
			} else {
				$submitInfo = $cls->formsubmit();
				$submissionID = $submitInfo->submissionID;
				$cls->sendformemail($submissionID);
			}
			//Clear session Code
			$_SESSION["code"] = "";
		} 
		
		if ($submitInfo[0] == 1) {
			$submissionID = $submitInfo[8];
			$formID = $cls->checkRequest('formID');
			$sql = "Select response_page from " . KYBFORMS_TABLE . "form_config where status = %d";
			$response_page = $wpdb->get_var($wpdb->prepare($sql, 1));
			$url = get_permalink($response_page) . "?ID=$submissionID&formID=$formID"; //Send to thank you page
			$_SESSION['submitInfo'] = "";
			header("Location: $url");
		} else {
			$submitInfo['submitstatus'] = 0;
			$submitInfo['reason'] = $err;
			$submitInfo['submissionInfo'] = $_POST;		
			$_SESSION['submitInfo'] = $submitInfo;
			$formID = $cls->checkRequest('formID');
			$form_wp_page = $wpdb->get_var($wpdb->prepare("Select form_wp_page from " . KYBFORMS_TABLE . "forms where form_id = %d", $formID));
			$url = get_permalink($form_wp_page);
			header("Location: $url?submit=0");			
		}
		
	break;

	case "getsecurecode":
		echo $_SESSION["code"];
	break;

	case "submitform":
		$status = 1;
		if (isset($_POST['validator']) && $_POST['validator'] != $_SESSION["code"]) {
			$status = 0;
		}
		if ($status) {
			$submitInfo = $cls->formsubmit();
			$fields = $submitInfo[2];
			$formpayment = $submitInfo[5];
			$orderItems = $submitInfo[6];
			$formID = $cls->checkRequest('formID');
			$submissionID = $submitInfo[8];
			$cls->sendformemail($submissionID);
			$wpdb->query($wpdb->prepare("Update " . KYBFORMS_TABLE . "form_info set option_id = %d", $cls->checkRequest('option')));
			//Update submission with option choice and expiration date
			$m = date('m');
			$d = date('d');
			$y = bcadd(date('Y'), 1);
			$exp_date = "$y-$m-$d";
			$wpdb->query($wpdb->prepare("Update " . KYBFORMS_TABLE . "form_info set exp_date = %s", $exp_date));
		
			if ($formpayment == 1 ) {
				$link = $submitInfo[4];
				header("Location: $link");
			} else {
				$sql = "Select response_page from " . KYBFORMS_TABLE . "form_config where status = 1";
				$response_page = $wpdb->get_var($wpdb->prepare($sql, $formID));
				$link = get_permalink($response_page) . "?ID=$submissionID&formID=$formID"; //Send to thank you page
				header("Location: $link");
			}
		} else {
			$formID = $cls->checkRequest('formID');
			$form_wp_page = $wpdb->get_var($wpdb->prepare("Select form_wp_page from " . KYBFORMS_TABLE . "forms where form_id = %d", $formID));
			$link = get_permalink($form_wp_page);
			header("Location: $link");
		}
	break;

	
	case "createcaptcha":
		// Set the content-type
		// Set the enviroment variable for GD
		//putenv('GDFONTPATH=' . realpath('.'));
		$captchanumber = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
		$captchanumber = substr(str_shuffle($captchanumber), 0, 6); // Getting first 6 word after shuffle.
		$_SESSION["code"] = $captchanumber; // Initializing session variable with above generated sub-string
		

		// Create the image
		//$im = imagecreatetruecolor(120, 30);
		if (!function_exists("imagettftext")) {
			$captchaImg = $basedir . "/wp-content/plugins/kybformbuilder/images/captcha_bg_sm.png";
		} else {
			$captchaImg = $basedir . "/wp-content/plugins/kybformbuilder/images/captcha_bg.png";
		}
		$im = imagecreatefrompng($captchaImg); // Generating CAPTCHA
		
		$width = 200; 
		$height = 35;

		// Create some colors
		$white = imagecolorallocate($im, 255, 255, 255);
		$grey = imagecolorallocate($im, 128, 128, 128);
		$black = imagecolorallocate($im, 0, 0, 0);
		//imagefilledrectangle($im, 0, 0, 399, 29, $white);
		
		
		$font = "arial.ttf";
		$font_size = 20;
		$angle = 45;
		
		//imageline($im, 0, $height/2, $width, $height/2, $grey); 
		//imageline($im, $width/2, 0, $width/2, $height, $grey); 
		
		// Add some shadow to the text
		//imagettftext($im, $font_size, 0, 48, 26, $grey, $font, $captchanumber);

		// Add the text
		//imagettftext($im, $font_size, $angle, 48, 25, $black, $font, $captchanumber);
		if (!function_exists("imagettftext")) {
			
			//$im = imagecreate(100, 35);
			// White background and blue text
			//$bg = imagecolorallocate($im, 0, 0, 0);
			//$textcolor = imagecolorallocate($im, 255, 255, 255);

			// Write the string at the top left
			imagestring($im, 5, 25, 5, $captchanumber, $black);
			/*$ypos = 0;
			for($i=0;$i<5;$i++){
				// Position of the character horizontally
				$xpos = $i * imagefontwidth($font_size);
				// Draw character
				imagechar($im, 5, 75, 5, $captchanumber{$i}, $black);
				// Remove character from string
				$string = substr($string, 1);   
			   
			} */

			
		} else {
			for($i = 0; $i <= 5; $i++) {
				imagettftext($im, $font_size, mt_rand(-20, 20), $i*mt_rand(30, 36)+mt_rand(2,4), 25, $black, $font, $captchanumber{$i});
			}
		}
			
		ob_start();
		imagepng($im);
		printf('<img src="data:image/png;base64,%s" width="200" height="35"/>', 
				base64_encode(ob_get_clean()));
		imagedestroy($im);
		

		
	break;
}

?>