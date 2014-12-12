-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 10.6.186.84
-- Generation Time: Oct 16, 2014 at 09:27 AM
-- Server version: 5.0.96
-- PHP Version: 5.3.4


--
-- Database: `kybforms`
--

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `form_id` int(11) NOT NULL auto_increment,
  `form_instructions` text character set utf8,
  `form_response` text,
  `email_responses` varchar(255) character set utf8 default NULL,
  `form_title` varchar(255) character set utf8 default NULL,
  `form_link` varchar(150) character set utf8 default NULL,
  `form_wp_page` int(11) NOT NULL default '0',
  `form_expiration` date default NULL,
  `form_toexpire` tinyint(4) NOT NULL default '1',
  `form_autoresponse` tinyint(4) NOT NULL default '0',
  `form_list_intro` text,
  `form_security` tinyint(4) NOT NULL default '0',
  `form_payment` tinyint(4) NOT NULL,
  `form_cc_fields` tinyint(4) NOT NULL default '0',
  `form_paypal_account` varchar(200) default NULL,
  `form_fileupload` tinyint(4) NOT NULL,
  `form_upload` tinyint(4) NOT NULL default '0',
  `form_status` tinyint(4) NOT NULL default '1',
  `form_home` tinyint(4) NOT NULL default '0',
  `form_align` varchar(20) default NULL,
  `form_rsvp` tinyint(4) NOT NULL default '0',
  `form_listing` tinyint(4) NOT NULL default '0',
  `form_listing_type` int(11) NOT NULL default '0',
  `form_addtowp` int(11) NOT NULL default '0',
  `form_login` int(11) NOT NULL default '0',
  `section_title_color` varchar(20) default NULL,
  `section_border_show` tinyint(4) NOT NULL default '0',
  `section_border_color` varchar(20) default NULL,
  `label_color` varchar(20) default NULL,
  `label_bold` tinyint(4) NOT NULL default '0',
  `input_bg` varchar(20) default NULL,
  PRIMARY KEY  (`form_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `forms`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_config`
--

CREATE TABLE IF NOT EXISTS `form_config` (
  `config_id` int(11) NOT NULL auto_increment,
  `logo` varchar(255) NOT NULL default '',
  `company` varchar(200) NOT NULL default '',
  `address` varchar(255) default NULL,
  `city` varchar(150) default NULL,
  `state` varchar(20) default NULL,
  `zip` varchar(20) default NULL,
  `tagline` varchar(200) default NULL,
  `phone` varchar(100) default NULL,
  `anet_merchant` varchar(255) default NULL,
  `anet_key` varchar(255) default NULL,
  `paypal_logo` varchar(200) default NULL,
  `paypal_business` varchar(255) default NULL,
  `builderpage` varchar(100) NOT NULL default '',
  `response_page` int(11) NOT NULL default '0',
  `header` varchar(100) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `server` varchar(100) NOT NULL default '',
  `mail` varchar(100) NOT NULL default '',
  `response` text NOT NULL,
  `settings_intro` text,
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds form builder configurations' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `form_config`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE IF NOT EXISTS `form_fields` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL default '0',
  `field_name` varchar(150) NOT NULL default '',
  `field_short_name` varchar(100) default NULL,
  `field_identity` varchar(50) default NULL,
  `field_instruction` text NOT NULL,
  `field_instruction_icon` tinyint(4) NOT NULL default '0',
  `field_type_id` int(11) NOT NULL default '0',
  `section_id` int(11) NOT NULL default '0',
  `field_order` int(11) NOT NULL default '0',
  `field_required` tinyint(4) NOT NULL default '0',
  `field_value` varchar(100) NOT NULL default '',
  `field_para` text,
  `field_placement` tinyint(4) NOT NULL default '0',
  `field_title_placement` tinyint(4) NOT NULL default '0',
  `field_feature` tinyint(4) NOT NULL default '0',
  `field_list_order` int(11) NOT NULL default '0',
  `field_style` varchar(20) default NULL,
  `view_placement` tinyint(4) NOT NULL default '0',
  `field_delimiter` varchar(20) default NULL,
  `field_options` text,
  `field_option_values` text,
  `field_span` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  `field_email` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds form field variables' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `form_fields`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_field_types`
--

CREATE TABLE IF NOT EXISTS `form_field_types` (
  `ff_type_id` int(11) NOT NULL auto_increment,
  `ff_type` varchar(150) NOT NULL default '',
  `ff_type_name` varchar(50) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ff_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds form field types' AUTO_INCREMENT=14 ;

--
-- Dumping data for table `form_field_types`
--

INSERT INTO `form_field_types` VALUES(3, 'dropdown', 'Selection Dropdown', 1);
INSERT INTO `form_field_types` VALUES(2, 'checkbox', 'Check Box', 1);
INSERT INTO `form_field_types` VALUES(1, 'inputbox', 'Regular Input Text', 1);
INSERT INTO `form_field_types` VALUES(4, 'textarea', 'Larger Text Area', 1);
INSERT INTO `form_field_types` VALUES(5, 'radio', 'Options', 1);
INSERT INTO `form_field_types` VALUES(7, 'long', 'Longer Input Text', 1);
INSERT INTO `form_field_types` VALUES(8, 'telefield', 'Telephone Fields', 1);
INSERT INTO `form_field_types` VALUES(9, 'para', 'Paragraph', 1);
INSERT INTO `form_field_types` VALUES(10, 'shortdrop', 'Short Selection Dropdown', 1);
INSERT INTO `form_field_types` VALUES(11, 'upload', 'File Upload', 1);
INSERT INTO `form_field_types` VALUES(12, 'payment', 'Payment Fields', 1);
INSERT INTO `form_field_types` VALUES(13, 'donation', 'Donation Payment Fields', 1);

-- --------------------------------------------------------

--
-- Table structure for table `form_info`
--

CREATE TABLE IF NOT EXISTS `form_info` (
  `form_id` int(11) NOT NULL auto_increment,
  `form_type_id` int(11) NOT NULL default '0',
  `data_type_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `order_id` tinyint(4) NOT NULL default '0',
  `product_id` varchar(200) default '0',
  `post_id` int(11) NOT NULL default '0',
  `post_cat_id` int(11) NOT NULL default '0',
  `requestor` varchar(200) default '',
  `name` varchar(255) default NULL,
  `first_name` varchar(200) default NULL,
  `last_name` varchar(200) default NULL,
  `middle_name` varchar(150) default NULL,
  `company` varchar(200) default NULL,
  `company2` varchar(200) default NULL,
  `email` varchar(255) default NULL,
  `phone` varchar(50) default NULL,
  `address` varchar(255) default NULL,
  `city` varchar(200) default NULL,
  `state` varchar(3) default NULL,
  `zip` varchar(50) default NULL,
  `excerpt` varchar(255) default NULL,
  `comments` text,
  `referrel` varchar(200) default NULL,
  `subject` varchar(200) default NULL,
  `donation_amt` float default '0',
  `date_inquiry` date default NULL,
  `event_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `event_time` varchar(150) default NULL,
  `event_start` datetime default NULL,
  `event_end` datetime default NULL,
  `event_ticker` varchar(255) default NULL,
  `event_link` varchar(255) default NULL,
  `website` varchar(200) default NULL,
  `requestor_status` tinyint(4) NOT NULL default '0',
  `title` varchar(200) default NULL,
  `location` varchar(200) default NULL,
  `positions` int(11) NOT NULL default '0',
  `qualifications` varchar(255) default NULL,
  `rating` tinyint(4) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `auth_num` varchar(100) default NULL,
  `trans_id` varchar(100) default NULL,
  `payment_amt` float default '0',
  `discount_cost` float NOT NULL default '0',
  `featured` tinyint(4) NOT NULL default '0',
  `logo` varchar(255) default NULL,
  `target` varchar(100) default NULL,
  `year` varchar(50) default NULL,
  `file_name` varchar(255) default NULL,
  `categories` varchar(255) default NULL,
  `author_id` int(11) NOT NULL default '0',
  `sale_txt_color` varchar(100) default NULL,
  `bg_color` varchar(200) default NULL,
  `sale_txt_sm` varchar(200) default NULL,
  `sale_txt_lg` varchar(200) default NULL,
  `sale_txt_desc` varchar(255) default NULL,
  `banner_type` varchar(200) default NULL,
  `username` varchar(150) default NULL,
  `password` varchar(150) default NULL,
  `video_id` varchar(200) default NULL,
  `option_id` int(11) NOT NULL,
  `exp_date` datetime default NULL,
  `order_paid` tinyint(4) NOT NULL default '0',
  `layout1` text,
  `layout2` text,
  `layout3` text,
  `layout4` text,
  `layout5` text,
  `signature_date` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`form_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds information from all forms on the website' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `form_info`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_labels`
--

CREATE TABLE IF NOT EXISTS `form_labels` (
  `label_id` int(11) NOT NULL auto_increment,
  `fieldname` varchar(150) default NULL,
  `label` varchar(150) default NULL,
  `data_type` varchar(50) default NULL,
  `fieldform` varchar(20) default NULL,
  `field_table` varchar(20) NOT NULL,
  `field_view` tinyint(4) NOT NULL default '0',
  `table_fields` varchar(200) NOT NULL,
  `field_lookup_name` varchar(100) default NULL,
  `field_lookup` varchar(100) default NULL,
  `field_lookup_type` varchar(10) default NULL,
  PRIMARY KEY  (`label_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds field labels for form fields' AUTO_INCREMENT=69 ;

--
-- Dumping data for table `form_labels`
--

INSERT INTO `form_labels` VALUES(14, 'event_start', 'Event Start Date', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(13, 'event_time', 'Event Time', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(12, 'event_date', 'Event Date', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(11, 'donation_amt', 'Donation Amount', 'i', 'text', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(10, 'referrel', 'Referrel', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(9, 'comments', 'Comments', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(8, 'address', 'Address', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(7, 'phone', 'Phone', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(6, 'email', 'Email', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(5, 'name', 'Name', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(4, 'requestor', 'Requestor', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(3, 'order_id', 'Order Number', 'i', NULL, '', 0, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(2, 'form_type_id', 'Type ID', 'i', NULL, '', 0, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(1, 'form_id', 'ID', 'i', NULL, '', 0, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(15, 'event_end', 'Event End Date', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(16, 'event_ticker', 'Ticker Text', 's', 'textarea', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(17, 'event_link', 'Event Link', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(18, 'requestor_status', 'Requestor Status', 'i', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(19, 'status', 'Status', 'i', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(20, 'featured', 'Featured', 'i', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(21, 'signature_date', 'Create Date', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(22, 'city', 'City', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(23, 'state', 'State', 'st', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(24, 'zip', 'Zip', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(25, 'rating', 'Rating', 'i', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(26, 'product_id', 'Product ID', 'i', 'multi-select', 'products', 1, 'product_id,product_name', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(27, 'subject', 'Subject', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(28, 'website', 'Website', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(29, 'user_id', 'User ID', 'i', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(30, 'company', 'Company', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(31, 'date_inquiry', 'Date Of Inquiry', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(32, 'title', 'Title', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(33, 'location', 'Location', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(34, 'positions', 'No. of Positions', 'i', 'text', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(35, 'qualifications', 'Qualifications', 's', 'textarea', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(36, 'discount_type_id', 'Discount Type', 'i', 'select', 'discount_types', 1, 'discount_type_id,discount_type_name', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(37, 'discount_name', 'Discount Title', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(38, 'discount_percentage', 'Discount Percentage', 'i', 'text', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(39, 'discount_cost', 'Discount Cost', 'i', 'text', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(40, 'discount_code', 'Discount Code', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(41, 'discount_exp', 'Discount Expiration Date', 'd', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(42, 'max_cost', 'Cost Where Discount Is Applied', 'i', 'text', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(43, 'logo', 'Image', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(44, 'excerpt', 'Excerpt', 't', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(45, 'file_name', 'File Link', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(46, 'post_id', 'WordPress Page', 'wp-post', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(47, 'post_cat_id', 'WordPress Category', 'wp-cat', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(48, 'target', 'Link Target', 'i', 'select', '', 1, '_blank=>New Window,_top=>Same Window', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(49, 'first_name', 'First Name', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(50, 'last_name', 'Last Name', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(51, 'middle_name', 'Middle Name', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(52, 'company2', 'Company 2', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(53, 'author_id', 'Author', 'i', 'select', 'product_providers', 1, 'id,name', 'type_id', '4', 'i');
INSERT INTO `form_labels` VALUES(54, 'banner_type', 'Banner Type', 'i', 'select', '', 1, 'product=>Featured Product,author=>Featured Author,sales=>Featured Sales', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(55, 'year', 'Year', 'y', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(56, 'categories', 'Library Categories', 'i', 'select', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(57, 'bg_color', 'Background Color', 's', 'color', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(58, 'sale_txt_color', 'Sale Text Color', 's', 'color', '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(63, 'username', 'User Name', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(64, 'password', 'Password', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(65, 'sale_txt_sm', 'Sales Title', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(66, 'sale_txt_lg', 'Sales Call Out', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(67, 'sale_txt_desc', 'Sales Description', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(68, 'video_id', 'Video ID', 's', NULL, '', 1, '', NULL, NULL, NULL);
INSERT INTO `form_labels` VALUES(69, 'layout1', 'Data Layout 1', 's', 'wysiwig', '', 1, '', NULL , NULL , NULL);
INSERT INTO `form_labels` VALUES(70, 'layout2', 'Data Layout 2', 's', 'wysiwig', '', 1, '', NULL , NULL , NULL);
INSERT INTO `form_labels` VALUES(71, 'layout3', 'Data Layout 3', 's', 'wysiwig', '', 1, '', NULL , NULL , NULL);
INSERT INTO `form_labels` VALUES(72, 'layout4', 'Data Layout 4', 's', 'wysiwig', '', 1, '', NULL , NULL , NULL);
INSERT INTO `form_labels` VALUES(73, 'layout5', 'Data Layout 5', 's', 'wysiwig', '', 1, '', NULL , NULL , NULL);


-- --------------------------------------------------------

--
-- Table structure for table `form_sections`
--

CREATE TABLE IF NOT EXISTS `form_sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `section_title` varchar(150) default NULL,
  `section_order` int(11) default NULL,
  `form_id` int(11) NOT NULL default '0',
  `section_columns` int(11) NOT NULL default '1',
  `section_hide_cc_field` varchar(200) default NULL,
  `section_hide_cc_verification` varchar(200) default NULL,
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds form section information for fieldsets' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `form_sections`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_types`
--

CREATE TABLE IF NOT EXISTS `form_types` (
  `form_type_id` int(11) NOT NULL auto_increment,
  `form_name` varchar(50) NOT NULL default '',
  `form_title` varchar(100) NOT NULL default '',
  `form_header` varchar(100) NOT NULL default '',
  `form_fckeditor` tinyint(4) NOT NULL default '0',
  `form_xml` varchar(50) default '',
  `form_field_names` text,
  `form_field_labels` text,
  `form_column_names` text,
  `form_input` varchar(255) default NULL,
  `form_table` varchar(50) NOT NULL default '',
  `form_table_id` varchar(50) default NULL,
  `form_table_field` varchar(50) default NULL,
  `form_response` text,
  `form_session` tinyint(4) NOT NULL default '0',
  `form_session_fields` varchar(250) default NULL,
  `order_required` tinyint(4) NOT NULL default '0',
  `order_by` varchar(20) default NULL,
  `order_sort` varchar(50) default NULL,
  `form_to_fe` tinyint(4) NOT NULL default '0',
  `form_categories` varchar(255) default NULL,
  `form_dataType` varchar(255) default NULL,
  `form_type` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`form_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='List of forms types on the website' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `form_types`
--

