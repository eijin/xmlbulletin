<?php
/* =========================================================================
Xmlbulletin is to control XML file for Bulletin Board System
Copyright (C) 2011  Eiji Nakai www.smallmake.com

xmlbulletin.model.php: version 1.0.0 - May 8, 2011.

Xmlbulletin Project Revision 1.0.0 - May 8, 2011.
Xmlbulletin is licenced under the GPL.
- xmlbulletin.class.php
- xmlbulletin.model.php
- xmlbulletin.view.php

Using my following copyleft software
- utility.php

GPL:
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
============================================================================= */

include_once("utility.php");

class xmlbulletinModel {

	/* customizable values */
	
	public $title = "XML Bulletine Board";
	
	public $xml_file_name = "";
	public $xml_file_path = "";

	public $fore = array(
		"xmlbulletin" => array(
			'title' => 'XML Bulletine Board',
			'updated' => '{date("Y-m-d H:i:s")}',
			'id' => 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',
			'generator' => 'xmlbulletine'
		)
	);
	
	public $fore_attributes = array();
	
	public $item_parent = "xmlbulletin";
	
	public $item = array(
		'entry' => array (
				'title' => '',
				'id'		=> 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',
				'published' => '{date("Y-m-d H:i:s")}',
				'author' => '',
				'comment' => ''
			)
	);
	
	public $item_attributes = array();

	public $item_validations = array();
	
	
	/* valuables used inside */
	
	public $validation_errors = array();
	public $error;
	public $xe;
	
	private $xmlHead = '<?xml version="1.0" encoding="utf-8"?>';
	private $xmlFile;
	
	private $name;
	private $rootName;
	private $itemName;
	private $parentPath;
	private $itemPath;
		
	
	/*
	* Create or Load XML File
	*/
	function __construct($name) {
		$this->name = $name;
		$keys = array_keys($this->fore);
		$this->rootName = $keys[0];
		$keys = array_keys($this->item);
		$this->itemName = $keys[0];
		$this->parentPath = '/' . $this->searchPath($this->fore, $this->item_parent);	//　path from root to parent of item
		$this->itemPath = $this->parentPath . '/' . $this->itemName;
		//
		if (empty($this->xml_file_name)) {
			$xmlFileName = (empty($this->name)) ? 'xmlbulletin.xml' : $this->name . '.xml';
			$this->xmlFile = $this->xml_file_path . $xmlFileName;
		} else {
			$this->xmlFile = $this->xml_file_path . $this->xml_file_name;
		}
		
		if (!file_exists($this->xmlFile) || filesize($this->xmlFile)==0) {
			if(!$this->create()) {
				$this->error = "Cannot create XML file.";
			}
		} 
		if (!$this->xe = simplexml_load_file($this->xmlFile)) {
			$this->error = "Cannot load XML file.";
		}
	}
	
	function searchPath($a, $p, $path=array()) {
		foreach($a as $k=>$v) { 
			if ($k == $p) {
				return (empty($path))? $k : implode('/',$path). '/' . $k;
			}
			if(is_array($v)) {
				$c_path = $path;
				$c_path[] = $k;
				if (($res = $this->searchPath($v, $p, $c_path)) !== false) return $res;
			}
		}
		return false;
	}
	
	
	/*
	* TOOLS
	*/
	function getObjXPath(&$obj, $pathStr) {
		if(substr($pathStr,0,1) == '/') { $pathStr = substr($pathStr,1); }
		$path = preg_split('/\//',$pathStr);
		$ch = $obj;
		foreach($path as $c) {
			if(!empty($c) && ($c != $path[0])) {	// $path[0] is $obj itself so skip it
				$ch = $ch->{$c};
			}
		}
		return $ch;
	}
	
	function getDomXPath(&$dom, $pathStr) {
		if(substr($pathStr,0,1) == '/') { $pathStr = substr($pathStr,1); }
		$path = preg_split('/\//',$pathStr);
		$ch = $dom;
		foreach($path as $c) {
			if(!empty($c) && ($c != $path[0])) {	// $path[0] is $dom itself so skip it
				$ch = $ch->getElementsByTagName($c);
			}
		}
		return $ch;
	}
	
	/*
	* 
	*/
	public function get_item_objects() {
		return $this->getObjXPath($this->xe, $this->itemPath);
	}
	
	/*
	* Create XML File
	*/
	function create() {
		$xml = $this->xmlHead . "\n";
		$xml .= "<{$this->rootName}></{$this->rootName}>\n";
		$this->xe = new SimpleXMLElement($xml);
		$this->addChild_recursive($this->xe,$this->fore[$this->rootName]);
		$this->addAttr($this->xe,$this->fore_attributes);
		return $this->xe->asXML($this->xmlFile);
	}
	
	/* 
	* Save XML File
	*/
	function save(&$data,$pos=NULL) {
		$this->validation_errors = array();
		// $this->validate_recursive will set results to $this->validation_errors
		$this->validate_recursive($this->item[$this->itemName], $data[$this->itemName],array($this->itemName));
		if (empty($this->validation_errors)) {
			if($pos === NULL) {
				$parent = $this->getObjXPath($this->xe, $this->parentPath);
				$obj = $parent->addChild($this->itemName);
				$this->addChild_recursive($obj, $this->item[$this->itemName], $data[$this->itemName],array($this->itemName));
				$this->addAttr($obj,$this->item_attributes);
			} else {
				$parent = $this->getObjXPath($this->xe, $this->parentPath);
				$obj = $parent->{$this->itemName}[intval($pos)];
				$this->updateChild_recursive($obj, $this->item[$this->itemName], $data[$this->itemName],array($this->itemName));
			}
			return $this->xe->asXML($this->xmlFile);
		} else {
			return false;
		}
	}
	
	/* Set Attribute */
	function addAttr(&$obj, $attr) {
		foreach ($attr as $k=>$v) {
			$o = $this->getObjXPath($obj, $k);
			if (is_array($v)) {
				foreach($v as $ak=>$av) { $o->addAttribute($ak,$av); }
			} else {
				$o->addAttribute($v);
			}
		}
	}
	
	
	/*
	* Valudation before save
	*  should analyze the structute of item by $this->item
	*/
	function validate_recursive($item, $data=array(), $path) {
		foreach ($item as $k=>$v) {
			if(is_array($v)) {
				$s_path = $path;
				$s_path[] = $k;
				$this->validate_recursive($v, $data[$k], $s_path);
			} else {
				$pathStr = (empty($path))? $k : implode('/',$path). '/' . $k;
				$this->validate($pathStr, $k, $data[$k]);
			}
		}
	}

	function validate($pathStr, $key=NULL, $val) {
		$property = $this->item_properties[$pathStr];
		$validation = $this->item_validations[$pathStr];
		if(!empty($validation)) {
			foreach($validation as $rule=>$params) {
				if(is_array($params)) {	// exist param
					$param = $params['param'];
					$msg = $params['message'];
				} else {
					$param = '';
					$msg = $params;
				}
				if (validate($rule, $param, $val)!="OK") {  // <--- this is the function of utility.php
					$this->validation_errors[] = __($msg);
				}
			}
		}
	}

	/*
	* Update Children
	*  should analyze the structute of item by $this->item
	*/
	function updateChild_recursive(&$obj, $item, $data) {
		foreach($item as $k=>$v) {
			if (is_array($v)) {
				$this->updateChild_recursive($obj->{$k}, $item[$k], $data[$k]);
			} else {
				$d = $data[$k];
				if (is_array($d)) { $d = implode(',',$d); }
				if ( get_magic_quotes_gpc() ) { $d = stripslashes( $d ); }
				$obj->{$k} = $d;
			}
		}
	}
	
	/*
	* Add Child
	*  should analyze the structute of item by $this->item
	*/
	function addChild_recursive(&$obj,$item,$data=array(),$path=array()) {
		foreach($item as $k=>$v) {
			if(is_array($v)) {
				$c_path = $path;
				$c_path[] = $k;
				$ch = $obj->addChild($k);
				$this->addChild_recursive($ch,$item[$k],$data[$k],$c_path);
			} else {
				if (!empty($v)) {
					$v = $this->expandPhpStatement($v);
				} else {
					$v = $data[$k];
					if (is_array($v)) {
						foreach($v as $e) { $res .= $e . ','; }
						$v = substr($res, 0, strlen($res)-1);
					}
				}
				$obj->addChild($k,$v);
			}
		}
	}
		
	function expandPhpStatement($phpst) {
		return preg_replace_callback('/(\{.+?\})/',create_function('$proc','$func = substr($proc[0],1,strlen($proc[0])-2);eval(\'$res = \' . $func . \';\');return $res;'),$phpst);
	}
	
	/*
	* Delete XML Object
	*/
	function delete($pos=NULL) {
		if($pos !== NULL) {
			$parent = $this->getObjXPath($this->xe, $this->parentPath);
			unset($parent->{$this->itemName}[intval($pos)]);
			return $this->xe->asXML($this->xmlFile);
		}
	}
	
	/*
	* Move up XML Object
	*/
	function up($pos=NULL) {
		$dom = new DOMDocument;
		if (($dom->load($this->xmlFile))===false) echo "ERROR";
		$items = $this->getDomXPath($dom, $this->itemPath);
		if ($pos < $items->length-1 ) {
			$node1 = $items->item($pos);
			$node2 = $items->item($pos+1);
			$node1Clone = $node1->cloneNode(true);
			$node2Clone = $node2->cloneNode(true);
			$node1->parentNode->replaceChild($node2Clone, $node1);
			$node2->parentNode->replaceChild($node1Clone, $node2);
			$dom->save($this->xmlFile);
		}
	}
	
	/*
	* MOve down XML Object
	*/
	function down($pos=NULL) {
		if ($pos > 0 ) {
			$dom = new DOMDocument;
			if (($dom->load($this->xmlFile))===false) echo "ERROR";
			$items = $this->getDomXPath($dom, $this->itemPath);
			$node1 = $items->item($pos);
			$node2 = $items->item($pos-1);
			$node1Clone = $node1->cloneNode(true);
			$node2Clone = $node2->cloneNode(true);
			$node1->parentNode->replaceChild($node2Clone, $node1);
			$node2->parentNode->replaceChild($node1Clone, $node2);
			$dom->save($this->xmlFile);
		}
	}

}

?>
