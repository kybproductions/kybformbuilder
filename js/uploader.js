jQuery(document).ready(function() {
 
jQuery('#upload_image_button').click(function() {
 formfield = jQuery('#upload_image').attr('name');
 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 window.send_to_editor = function(html) {
  imgurl = jQuery('img',html).attr('src');
  jQuery('#upload_image').val(imgurl);
  jQuery('#image_view').attr('src', imgurl);
  tb_remove();
 }
 return false;
});

jQuery('#upload_file_button').click(function() {
 formfield = jQuery('#upload_file').attr('name');
 tb_show('', 'media-upload.php?type=file&amp;TB_iframe=true');
 window.send_to_editor = function(html) {
  imgurl = jQuery(html).attr('href');   
  jQuery('#upload_file').val(imgurl);
  tb_remove();
 }
 return false;
});


   
});