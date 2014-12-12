<?php

/*******************************************************************************
* FORM BUILDER                                                                 *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    2009-15-09                                                          *
* Author:  KYB Productions													   *
* Copyright 2009 KYB Productions											   *
*******************************************************************************/

session_start();
error_reporting(E_ALL ^ E_NOTICE);
$basedir = $_SERVER['DOCUMENT_ROOT'] . "/dev/coppell";
require_once("$basedir/wp-config.php");
$dir = WP_PLUGIN_DIR;
require_once("$dir/kybformbuilder/lib/shared.php"); // shared classes
require_once("$dir/kybformbuilder/lib/smarty.php"); // smarty engine
wp_enqueue_style('stylesheet-form_styles-css', WP_PLUGIN_URL . '/kybformbuilder/css/form_styles.css');

include_once("$dir/kybformbuilder/controller/controller.php");    
$controller = new FormController();  
$controller->invoke(); 
?>