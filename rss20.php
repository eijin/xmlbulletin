<?php
/*
*	This is a sample of Xmlbulletine Project
*/

// Model -----------------------------------------------
class model extends xmlbulletinModel {
		
	public $fore = array (
		'rss' => array (
			'channel' => array (
				'title' => 'RSS 2.0 Sample',
				'link' => '',
				'description' => '',
				'description' => '',
				'dc:language' => '',
				'dc:creator' => '',
				'dc:date' => '{date("Y-m-d H:i:s")}'
			)
		)
	);
	
	public $item_parent = "channel";
	
	public $item = array(
		'item' => array (
				'title' => '',
				'link' => '',
				'description' => '',
				'pubDate' => '{date("Y-m-d H:i:s")}',
				'category' => ''
		)
	);
	
}

// View -----------------------------------------------
class view extends xmlbulletinView {
		
	public $edit_mode = true;
	public $sort_mode = "descending";	// ascending or descending
	public $line_per_page = 20;
	public $column_per_page = 10;

	public $item_styles = array(
		'item/title' => array('type'=>'text',			'label'=>'TITLE', 'list'=>true),
		'item/link' => array('type'=>'text', 		'label'=>'LINK'),
		'item/description' => array('type'=>'textarea',	'label'=>'COMMENT'),
		'item/pubDate' => array('type'=>'text', 		'label'=>'DATE', 'list'=>true, 'readonly'=>'readonly'),
		'item/category' => array('type'=>'text',  		'label'=>'CATEGORY', 'list'=>true)
	);
	
}


?>
