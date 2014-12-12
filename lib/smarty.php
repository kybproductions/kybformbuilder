<?php
/*******************************************************************************
* HTML SMARTY CLASS                                                            *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    2012-16-06                                                          *
* Author:  Kimla Y. Beasley													   *
* Copyright 2012 KYB Productions											   *
*******************************************************************************/

class formFillPage
{
  var $page;

  //Insert template file
  function formFillPage($template)
	{
		if (file_exists($template))
	      $this->page = join("", file($template));
		else
	      die("Template file $template not found.");
	}

  //Output file contents
  function parse($file)
	{
		$buffer = "";
		if (is_file($file)) {
			ob_start();
			include($file);
			$buffer = ob_get_contents();
			ob_end_clean();
		}

    return $buffer;
  }

  
  //Replace tags in template with correct data
  function replace_tags($tags = array())
	{
		if (sizeof($tags) > 0)
			foreach ($tags as $tag => $data) {
				$data = (file_exists($data)) ? $this->parse($data) : $data;
				$this->page = str_replace("{" . $tag . "}", $data, $this->page);
			}
		else
			die("No tags designated for replacement.");
		}

	
	//Output data from template file
	function output() {
		echo $this->page;
	}

	function viewoutput() {
		return $this->page;
	}
}

?>