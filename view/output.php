<?php
switch ($management) {
	case "formview":
		$output = $content;
	break;
	case "admin":
		if ($showheader) {
			echo "<div class=\"wrap\">";
			echo "<h2>" . __( "$mgmt_title", 'oscimp_trdom' ) . "</h2>"; 
			echo "</div>";	
		}
		if ($html != "") {
			$template =  WP_PLUGIN_DIR . "/kybformbuilder/view/html/$html";	
			$page = new formFillPage ($template);
			$page->replace_tags($content);
			$output = $page->viewoutput();
		} else {
			$output = $content;
		}
	break;
}


echo $output;
?>