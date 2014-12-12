/*$(function() {
		$( "#datepicker" ).datepicker();
		$( "#datepicker1" ).datepicker();
		$( "#datepicker2" ).datepicker();
		$( "#datepicker3" ).datepicker();
		$( "#datepicker4" ).datepicker();
	});*/

jQuery(document).ready(function() {
    jQuery('#datepicker').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	jQuery('#datepicker1').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	jQuery('#datepicker2').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	jQuery('#datepicker3').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	jQuery('#datepicker4').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	jQuery('#datepicker5').datepicker({
        dateFormat : 'mm/dd/yy'
    });
});

jQuery(document).ready(function($){
	jQuery('#section_title_color').wpColorPicker();
	jQuery('#section_border_color').wpColorPicker();
	jQuery('#label_color').wpColorPicker();
	jQuery('#input_bg').wpColorPicker();
	
	
});