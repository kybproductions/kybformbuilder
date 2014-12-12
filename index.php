<?php
/* 
Plugin Name: KYB Productions Form Builder
Plugin URI: http://www.kybproductions.net
Description: KYB Form Builder is a customized form management process system for building and managing forms in wordpress.
Author: KYB Productions
Author URI: http://www.kybproductions.net
Version: 2.0 

Copyright 2014 KYB PRODUCTIONS
*/  
//Record Errors
@ini_set('error_reporting', E_ALL ^ E_NOTICE);
@ini_set('log_errors',TRUE);
@ini_set("error_log", WP_PLUGIN_DIR . "/kybformbuilder/error.log");
@ini_set('display_errors',FALSE);

//Define constants

define ('KYBFORMS_VERSION', '1.2');
define ('KYBFORMS_FOLDER', dirname(plugin_basename(__FILE__)));
define ('KYBFORMS_BASENAME', plugin_basename(__FILE__) );
!defined('WP_CONTENT_URL') ? define('WP_CONTENT_URL', site_url('wp-content', $scheme)) : '';
!defined('WP_CONTENT_DIR') ? define( 'WP_CONTENT_DIR', str_replace('\\', '/', ABSPATH) . 'wp-content' ) : '';
define ('KYBFORMS_ABSPATH', WP_CONTENT_DIR . '/plugins/' . KYBFORMS_FOLDER);
define ('KYBFORMS_URLPATH', plugins_url() . "/" . KYBFORMS_FOLDER);
define('KYBFORMS_AJAXPATH', str_replace(ABSPATH, "", WP_PLUGIN_DIR) . "/" . KYBFORMS_FOLDER);
define('KYBFORMS_TABLE', $wpdb->prefix);
//======== Online Form Plugin Class========//
class Online_Forms {

	static $_instance = null;
	
	static function instance() {
		if(!self::$_instance) {
			self::$_instance = new Online_Forms();
		}
		
		return self::$_instance;
	}

	function __construct() {
		register_activation_hook(__FILE__, array($this, 'formbuilder_install'));
		register_deactivation_hook(__FILE__, array($this,'formbuilder_uninstall'));
		add_action('admin_menu', array($this, 'builder_admin_actions'));
		if(strpos($_SERVER['QUERY_STRING'], KYBFORMS_FOLDER) !== FALSE) {
			$this->share_includes();
			add_action('admin_init', array($this,'formbuilder_admin_head'));
			add_action('admin_init', array($this, 'editor_formbuilder_admin_init'));
			add_action('admin_init', array($this, 'builder_init_sessions'));
			add_action('admin_head', array($this, 'builder_editor_admin_head'));
			add_action('admin_head', array($this, 'background'));
		}
		add_shortcode('kybformview', array($this, 'form_view'));
		add_shortcode('form_thanks', array($this, 'form_thanks'));
	}

	function share_includes() {
		$dir = KYBFORMS_ABSPATH;
		require_once("$dir/lib/shared.php"); // shared classes
		require_once("$dir/lib/smarty.php"); // smarty engine
	}

	function background(){
		echo "<style>body {background-color:#ffffff;}</style>";
	}

	function formbuilder_admin_head()	{		
		wp_enqueue_style('admin-stylesheet-css', KYBFORMS_URLPATH . '/css/admin_stylesheet.css');
		wp_enqueue_style('stylesheet-form_styles-css', KYBFORMS_URLPATH . '/css/form_styles.css');
		wp_enqueue_script('sharedfunctions-js', KYBFORMS_URLPATH . '/js/sharedfunctions.js');
		wp_enqueue_script('form-js-js', KYBFORMS_URLPATH . '/js/form_js.js');
		wp_enqueue_style('calendar_ui-css', KYBFORMS_URLPATH . '/css/calendar_ui.css');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('wp-color-picker', admin_url( 'js/color-picker.min.js' ));
		wp_enqueue_style( 'wp-color-picker');
		wp_enqueue_script('jquerystart-js', KYBFORMS_URLPATH . '/js/jquerystart.js');		
	}

	function table_exist($table){
		$result = mysql_query("SHOW TABLES LIKE '$table'");
		$tableExists = mysql_num_rows($result);
		return $tableExists;
	}

	
	function editor_formbuilder_admin_init() {
		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		wp_enqueue_script('word-count');
		wp_enqueue_script('post');
		wp_enqueue_script('editor');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('uploader', KYBFORMS_URLPATH.'/js/uploader.js', array('jquery','media-upload','thickbox'));
		wp_enqueue_script('uploader');
		wp_enqueue_style('thickbox');
	}

	function builder_editor_admin_head() {
		wp_tiny_mce();
	}

	function builder_init_sessions() {
	   @session_start();
	}

	function builder_admin_actions() {
		$userLevel = 1;
		$dashboard = __("Online Forms", "index");
		$forms = __("Forms List", "index");
		$add = __("Add New Form", "index");
		add_menu_page("Online Forms &rsaquo; $dashboard", "Online Forms", $userLevel, __FILE__, array($this, "builder_controller"), plugins_url() . "/kybformbuilder/images/form-icon-16.png");
		add_submenu_page(__FILE__, "Forms List &rsaquo; $forms", $forms, $userLevel, "kybformbuilder-form-list", array($this, "builder_controller"));
		add_submenu_page(__FILE__, "Add New Form &rsaquo; $news", $add, $userLevel, "kybformbuilder-form-add", array($this, "builder_controller"));
	}

	function formbuilder_install() {
		global $wpdb;
		global $table_prefix;
		$dbtables = KYBFORMS_ABSPATH . "/admin/sql/forms_db_tables.sql";
		$backupfile = KYBFORMS_ABSPATH . "/admin/sql/kybforms_backup.sql";
		if (is_file($backupfile)) {
			$dbtables = $backupfile;
		} 
		$file_content = file($dbtables);
		$query = "";
		$tables = array("`forms`","`form_config`", "`form_fields`", "`form_field_types`", "`form_info`", "`form_labels`", "`form_sections`", "`form_types`");
		foreach($file_content as $sql_line){
			if(trim($sql_line) != "" && strpos($sql_line, "--") === false){
				$query .= $sql_line;
				if (substr(rtrim($query), -1) == ';'){
					if (is_file($backupfile)) {
						//Load last back up of database tables
						//Check to see if table has current table prefix
						$table = trim($table_prefix . str_replace("`","",$tables[$key]));
						if (strpos($query, $table) !== false) {
							$wpdb->query($query);
						} else {
							$query = str_replace($tables[$key],"`" . trim($table_prefix . str_replace("`","",$tables[$key])) . "`", $query);
							$wpdb->query($query);
						}
					} else {
						foreach ($tables as $key => $value) {
							if (strpos($query, $tables[$key]) !== false) {
								$table = trim($table_prefix . str_replace("`","",$tables[$key]));
								//if (!$this->table_exist($table)) {
									//This still would cause errors on insert if table exists so will need to include a deactivation
									$query = str_replace($tables[$key],"`" . trim($table_prefix . str_replace("`","",$tables[$key])) . "`", $query);
									$wpdb->query($query);
								//}
							}
						}
					}
					$query = "";
				}
			}
		}
		//Need to add a form response page here or put in query? and add/update to form_config table
	}

	function formbuilder_uninstall() {
		global $wpdb;
		global $table_prefix;
		//Back up any current form data for later reinstall if needed
		$tables = array("`forms`","`form_config`", "`form_fields`", "`form_field_types`", "`form_info`", "`form_labels`", "`form_sections`", "`form_types`");
		$this->backup_tables($tables);
		//Drop form tables
		foreach ($tables as $key => $value) {
			$table = trim($table_prefix . str_replace("`","",$tables[$key]));
			if ($this->table_exist($table)) {
				$sql = "DROP TABLE $table";
				$wpdb->query($sql);
			}
		}
	}

	function backup_tables($tables) {
		global $table_prefix;
		$tables = is_array($tables) ? $tables : explode(',',$tables);
		$return = "";
		$backupfile = KYBFORMS_ABSPATH . "/admin/sql/kybforms_backup.sql";
		foreach($tables as $table)
		{
			$table = trim($table_prefix . str_replace("`","",$table));
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);		
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		//save file
		$handle = fopen($backupfile,'w+');
		fwrite($handle,$return);
		fclose($handle);
	}

	function form_view($atts) {
		$this->share_includes();
		require_once(KYBFORMS_ABSPATH . "/controller/controller.php");
		$controller = new FormController();  
		 
		extract( shortcode_atts( array(
				'formid' => ''
			), $atts ) );
		
		
		if ($formid == "") {
			$formURL = get_permalink(158);
			$_SERVER['SERVER_PORT'] == 443 ? $currURL = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : $currURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$currURL = str_replace($formURL, "", $currURL);
			$urlInfo = explode("/", $currURL);
			$formid = $urlInfo[0];
		}
		
		
		if (isset($_POST['process']) && $_POST['process'] == "formsubmit") {
			$process = $_POST['process'];
		} else {
			$process = "view";
		}
		$content = $controller->viewForm($process, $formid);	
		return $content;
	}

	function form_thanks($atts) {
		global $post;
		$this->share_includes();
		require_once(KYBFORMS_ABSPATH . "/controller/controller.php");
		$controller = new FormController();  
		$cls = new kybformSharedProcesses();
		extract( shortcode_atts( array(
			'formid' => ''
		), $atts ) );
		$formid == "" ? $formid = $cls->checkRequest('formID') : '';
		$content = $controller->viewForm('formcomplete', $formid);
		return $content;
	}

	function builder_controller() {
		include_once("controller/controller.php");    
		$controller = new FormController();  
		$controller->invoke(); 
	}

}
// create shared instance
$onlineForm = Online_Forms::instance();

?>
