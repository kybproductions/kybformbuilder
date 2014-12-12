<?php
error_reporting(E_ALL ^ E_NOTICE);
$basedir = $_SERVER['DOCUMENT_ROOT'] . "/dev/coppell";
require_once("$basedir/wp-config.php");
define('KYBFORMS_TABLE', $table_prefix);

$sql = "Select * from guestbook";;
$result = $wpdb->get_results($sql);

$formTypeID = 2;
$cnt = 19;

$headers = array("form_id", "form_type_id", "data_type_id", "user_id", "order_id", "product_id", "post_id", "post_cat_id", "requestor", "name", "first_name", "last_name", "middle_name", "company", "company2", "email", "phone", "address", "city", "state", "zip", "excerpt", "comments", "referrel", "subject", "donation_amt", "date_inquiry", "event_date", "event_time", "event_start", "event_end", "event_ticker", "event_link", "website", "requestor_status", "title", "location", "positions", "qualifications", "rating", "status", "auth_num", "trans_id", "payment_amt", "featured", "logo", "target", "year", "file_name", "categories", "author_id", "sale_txt_color", "bg_color", "sale_txt_sm", "sale_txt_lg", "sale_txt_desc", "banner_type", "username", "password", "video_id", "signature_date");

$array[] = $headers;

if (count($result) != 0) {
	foreach ($result as $row) {
		$name = $row->name;
		$email = $row->email;
		$website = $row->website;
		$location = $row->location;
		$location = str_replace(",","&#44;",$location);
		$referrel = $row->heard_from;
		$favorite = $row->fav_page;
		$message = $row->message;
		$message = cleanHTML($message);
		$message = str_replace("\\n","<br>", $message);
		$message = str_replace("\\r","<br>", $message);
		$message = str_replace("\"", "&#34;", $message);
		$message = str_replace("’", "'", $message);
		$message = str_replace(",","&#44;",$message);
		$row->active == "y" ? $status = 1 : $status = 0;
		$signature_date = date("Y-m-d", strtotime($row->date));
		$formInfo = "guestname=>$name,email=>$email,website=>$website,location=>$location,referrel=>$referrel,favorite=>$favorite,guestmessage=>$message,";
		
		//Contacts Array
		$array[] = array(
			$cnt, //"form_id"
			$formTypeID, //"form_type_id"
			'0', //"data_type_id"
			'0', //user_id
			'0', //"order_id"
			'0', //"product_id"
			'0', //"post_id"
			'0', //"post_cat_id"
			'', //"requestor"
			'', //"name"
			'', //"first_name"
			'', //"last_name"
			'', //"middle_name"
			'', //"company"
			'', //"company2"
			'', //"email"
			'', //"phone"
			'', //"address"
			'', //"city"
			'', //"state"
			'', //"zip"
			'', //"excerpt"
			$formInfo, //"comments"
			'', //"referrel"
			'', //"subject"
			'0', //"donation_amt"
			'0000-00-00', //"date_inquiry"
			'0000-00-00', //"event_date"
			'', //"event_time"			
			'0000-00-00', //"event_start"
			'0000-00-00', //"event_end"
			'', //"event_ticker"
			'', //"event_link"
			'', //"website"
			'0', //"requestor_status"
			'', //"title"
			'', //"location"			
			'0', //"positions"
			'', //"qualifications"
			'0', //"rating"
			$status, //"status"
			'', //"auth_num"
			'', //"trans_id"
			'0', //"payment_amt"
			'0', //"featured"
			'', //"logo"
			'', //"target"
			'', //"year"
			'', //"file_name"
			'', //"categories"
			'0', //"author_id"
			'', //"sale_txt_color"
			'', //"bg_color"
			'', //"sale_txt_sm"
			'', //"sale_txt_lg"
			'', //sale_txt_desc
			'', //"banner_type"
			'', //"username"
			'', //"password"
			'', //video_id
			$signature_date //"signature_date"
		);
		$cnt++;
	}
}


function cleanString($string) {
	$cleaned = mysql_real_escape_string(stripslashes(strip_tags($string)));
	return $cleaned;
}

function cleanHTML($string) {
	$cleaned = mysql_real_escape_string(stripslashes($string));
	return $cleaned;
}



//$array = array($headers);
//$array = array($fieldContent);
function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
	  foreach ($row as $key => $value) {
          $row[$key] = $value."#@ @#";
      } 
      fputcsv($df, $row,',','"');
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

$filename = "form_submissions_";


download_send_headers("$filename" . date("m-d-Y") . ".csv");
echo array2csv($array);
?>