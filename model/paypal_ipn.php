<?php
$basedir = $_SERVER['DOCUMENT_ROOT'] . "/dev/candace";
require_once("$basedir/wp-config.php");
$plugin_dir = WP_PLUGIN_DIR . "/kybformbuilder";
require_once("$plugin_dir/lib/shared.php"); // shared classes
require_once("$plugin_dir/lib/smarty.php"); // smarty engine
require_once("$plugin_dir/controller/controller.php"); // builder controller
require_once("$plugin_dir/model/model.php"); // builder model
define('KYBFORMS_TABLE', $table_prefix);
$cls = new builderclass;

// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream. 
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}
 
// STEP 2: POST IPN data back to PayPal to validate
 
$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set 
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    // error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);

if (strcmp ($res, "VERIFIED") == 0) {
    // The IPN is verified, process it:
    // check whether the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process the notification
 
    // assign posted variables to local variables
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
 
    // IPN message values depend upon the type of notification sent.
    // To loop through the &_POST array and print the NV pairs to the screen:
    foreach($_POST as $key => $value) {
      echo $key." = ". $value."<br>";
		$message .= $key." = ". $value."<br>";
    }

	//Register as a user from submission
	$submissionID = $_POST['custom'];
	$subInfo = $cls->get_submission_info($submissionID);
	if (count($subInfo) != 0) {
		$firstname = $subInfo->first_name;
		$lastname = $subInfo->last_name;
		$address1 = $subInfo->address1;
		$address2 = $subInfo->address2;
		$phone = $subInfo->phone;
		$city = $subInfo->city;
		$state = $subInfo->state;
		$zip = $subInfo->zip;
		$email = $subInfo->email;
		$userID = $cls->registerNewUser($firstname, $lastname, $address1, $address2, $phone, $city, $state, $zip, $country, $email);
		//Record Transaction Information
		$wpdb->query($wpdb->prepare("Update ". KYBFORMS_TABLE . "form_info set trans_id = %s, order_paid = 1 where form_id = %d", $txn_id, $submissionID));
		//Set user ID to submission ID
		$wpdb->query($wpdb->prepare("Update ". KYBFORMS_TABLE . "form_info set user_id = %d where form_id = %d", $userID, $submissionID));
		//make sure login is set in user table
		//$wpdb->query($wpdb->prepare("Update mywiz_training_users set u_login = %s where u_id = %d", $email, $userID));
	}
	$message .= "userID = $userID";
	$emailto = "webmaster@kybproductions.com";
	$emailsubject = "Test IPN Receipt";
	$fromName = "KYB Productions LLC"; 
	$mailAddress = "webmaster@kybproductions.com";
	$headers = "From: $fromName <$mailAddress>\r\n";
	$headers .= "Reply-To: $mailAddress\r\n";
	//$headers .= "X-Mailer: PHP/" . phpversion();
	$headers .= "Content-type: text/html\r\n";

	mail($emailto, $emailsubject, $message, $headers);
} else if (strcmp ($res, "INVALID") == 0) {
	$submissionID = $_POST['custom'];
	$subInfo = $cls->get_submission_info($submissionID);
	if (count($subInfo) != 0) {
		//Record Transaction Information
		$wpdb->query($wpdb->prepare("Update ". KYBFORMS_TABLE . "form_info set trans_id = %s where form_id = %d", $res, $submissionID));
		
	}
}

?>
