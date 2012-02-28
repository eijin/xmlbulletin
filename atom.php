<?php
/*
*	This is a sample of Xmlbulletine Project
*/

// Model -----------------------------------------------
class model extends xmlbulletinModel {
		
	public $title = "Atom Sample";
		
	public $fore = array(
		'feed' => array(
				'title' => 'Atom Sample',
				'subtitle' => '',
				'updated' => '{date("Y-m-d H:i:s")}',
				'id' => 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',
				'generator' => 'xmlbulletine'
			)
	);
	
	public $fore_attributes = array(
		'/feed' => array ('xmlns'=>"http://www.w3.org/2005/Atom"),
		'/feed/subtitle' => array('type' => 'html')
	);
	
	public $item_parent = "feed";
	
	public $item = array(
		'entry' => array (
				'title' => '',
				'id'		=> 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',
				'published' => '{date("Y-m-d H:i:s")}',
				'author' => array(
					'name' => '',
					'uri'  => '',
					'email' => ''
				),
				'content' => ''
			)
	);
	
	public $item_attributes = array(
		'entry/content' => array('type' => 'html')
	);
	
	
}

// Model -----------------------------------------------
class view extends xmlbulletinView {
	
	public $edit_mode = true;
	public $sort_mode = "descending";	// ascending or descending
	public $line_per_page = 20;
	public $column_per_page = 10;

	public $item_styles = array(
		'entry/title' => 				array('type'=>'text',			'label'=>'TITLE', 'list'=>true),
		'entry/id' => 					array('type'=>'hidden'),
		'entry/published' => 		array('type'=>'text', 		'label'=>'DATE', 'list'=>true, 'readonly'=>'readonly'),
		'entry/author/name' => 	array('type'=>'text',  		'label'=>'NAME', 'list'=>true),
		'entry/author/uri' => 	array('type'=>'text', 		'label'=>'SITE'),
		'entry/author/email' => array('type'=>'text',			'label'=>'E-MAIL'),
		'entry/content' =>  		array('type'=>'textarea',	'label'=>'COMMENT')
	);
	
}

?>
