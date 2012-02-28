<?php
/*
*	This is a sample of Xmlbulletine Project
*/

// Model -----------------------------------------------
class model extends xmlbulletinModel {
		
	public $title = "Bulletine Board System";
	
	public $fore = array(
		'bbs' => array(
			'title' => 'Bulletine Board System',
			'subtitle' => '',
			'updated' => '{date("Y-m-d H:i:s")}',
			'id' => 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',
			'generator' => 'xmlbulletine'
		)
	);
	
	public $fore_attributes = array (
		'/bbs/subtitle' => array('type' => 'html')
	);
		
	public $item_parent = "bbs";
	
	public $item = array(
		'entry' => array (
				'title' => '',
				'id'		=> 'tag:smallmake.com,{date("Y-m-d")},{uniqid	()}',
				'published' => '{date("Y-m-d H:i:s")}',
				'author' => array(
					'name' => '',
					'uri'  => '',
					'email' => '',
					'gender' => '',
					'age' => '',
					'pc' => ''
				),
				'content' => ''
			)
	);
	
	public $item_attributes = array(
		'entry/content' => array('type' => 'html')
	);
	
	public $item_validations = array(
		'entry/title' => 				array('require'=>'Title is required.'),
		'entry/published' => 		array('date'=>array('param'=>'Y-m-d H:i:s', 'message'=>'Invalid Date format(need Y-m-d H:i:s)')),
		'entry/author/name' => 	array('require'=>'Your name is required.'),
		'entry/author/uri' => 	array('url'=>'Invalid Site URL format.'),
		'entry/author/email' => array('require'=>'Your E-Mail is required.',
																	'email'=>'Invalid E-Mail format.'),
		'entry/author/gender' => 	array('require'=>'Select your gender.'),
		'entry/content' =>  		array('require'=>'Comment is required.')
	);
	
}

// View -----------------------------------------------
class view extends xmlbulletinView {	

	public $edit_mode = true;
	public $sort_mode = "descending";	// ascending or descending
	public $line_per_page = 10;
	public $column_per_page = 5;

	public $item_styles = array(
		'entry/title' => 				array('type'=>'text',			'label'=>'TITLE', 'list'=>true),
		'entry/id' => 					array('type'=>'hidden'),
		'entry/published' => 		array('type'=>'text', 		'label'=>'DATE', 'list'=>true, 'readonly'=>'readonly'),
		'entry/author/name' => 	array('type'=>'text',  		'label'=>'NAME', 'list'=>true),
		'entry/author/uri' => 	array('type'=>'text', 		'label'=>'SITE'),
		'entry/author/email' => array('type'=>'text',			'label'=>'E-MAIL'),
		'entry/author/gender'		=>	array('type'=>'radio',		'label'=>'GENDER', 'list'=>true,
																	'options'=> array('1'=>'Male', '2'=>'Female', '3'=>'Other')),
		'entry/author/age'		=>	array('type'=>'select',		'label'=>'AGE', 
																	'options'=> array('0'=>'--', '10'=>'10&acute;s','20'=>'20&acute;s','30'=>'30&acute;s','40'=>'40&acute;s','50'=>'50&acute;s','60'=>'60&acute;s','70'=>'70&acute;s','80'=>'80&acute;s','90'=>'90&acute;s')),
		'entry/author/pc'		=>	array('type'=>'checkbox',		'label'=>'PC', 'list'=>true,
																	'options'=> array('Win'=>'Windows', 'Mac'=>'Machintosh', 'Lnx'=>'Linux', 'Oth'=>'Others')),
		'entry/content' =>  		array('type'=>'textarea',	'label'=>'COMMENT')
	);
	
}

?>
