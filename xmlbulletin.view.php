<?php
/* =========================================================================
Xmlbulletin is to control XML file for Bulletin Board System
Copyright (C) 2011  Eiji Nakai www.smallmake.com

xmlbulletin.view.php: version 1.0.0 - May 8, 2011.

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

class xmlbulletinView {

	/* customizable values */
		
	public $edit_mode = true;
	public $sort_mode = "descending";	// ascending or descending
	public $line_per_page = 10;
	public $column_per_page = 5;

	public $item_styles = array(
		'entry/title' => 				array('type'=>'text',			'label'=>'TITLE', 'list'=>true),
		'entry/id' => 					array('type'=>'hidden'),
		'entry/published' => 		array('type'=>'text', 		'label'=>'DATE', 'list'=>true, 'readonly'=>'readonly'),
		'entry/author' => 	array('type'=>'text',  		'label'=>'NAME', 'list'=>true),
		'entry/comment' => array('type'=>'textarea',	'label'=>'COMMENT')
	);
	
	/* valuables used inside */
	
	public $error;
	
	private $name;
	private $page;
	private $pageMode;
	
	
	/*
	* Initialize
	*/
	function __construct($name) {
		$this->name = $name;
		$this->pageMode = "";
		$this->page = 0;
		$path_array = preg_split("/\//",$_SERVER['PATH_INFO']);
		foreach($path_array as $v) {
			if ($pos = strstr($v, 'page=')) { $this->page = intval(substr($v,5)); $this->pageMode = 'page'; }
			if ($pos = strstr($v, 'list=')) { $this->page = intval(substr($v,5)); $this->pageMode = 'list'; }
		}
		if (empty($this->pageMode)) { $this->pageMode='page'; $this->page = 0;}
	}

	/*
	* Make URL ** this is important utility for Paging **
	*/
	function make_url($action, $params, $page=NULL, $pageMode=NULL, $jumppos=NULL) {
		$pathArray = preg_split("/\//",$_SERVER['PATH_INFO']);
		$paramsStr = "";
		if (!empty($params)) foreach($params as $v) { $paramsStr .= $v . '/'; }
		if ($page === NULL ) {$page = $this->page; }
		if ($pageMode === NULL) { $pageMode = $this->pageMode; }
		$jumpposStr = "";
		if ( $jumppos !== NULL ) { $jumpposStr = '#' . $jumppos; }
		$namePath = (empty($this->name)) ? '' : $this->name . '/';
		return $_SERVER['SCRIPT_NAME'].'/'.$namePath . $action . '/' . $paramsStr . $pageMode . '=' . $page . $jumpposStr;
	}
	
	/*
	* this is used from every print form and index
	*/
	function walkItemCallback($itemStruct, $callback, $obj=NULL, $path=NULL) {
		$objFlag = is_object($obj);	// it has two case, object or array
		foreach ($itemStruct as $k=>$v) {
			if(is_array($v)) {
				$s_path = $path;
				$s_path[] = $k;
				$s_obj = ($objFlag) ? $obj->{$k} : $obj[$k];
				$this->walkItemCallback($v, $callback, $s_obj, $s_path);
			} else {
				$pathStr = (empty($path))? $k : implode('/',$path). '/' . $k;
				$val = ($objFlag) ? $obj->{$k} : $obj[$k];
				$attr = ($objFlag) ? $obj->{$k}->attributes() : array();
				if(is_array($val)) { $val = implode(',',$val); }
				$params = array(&$pathStr, &$k, &$val, $attr);
				call_user_func_array(array(&$this,$callback),$params);
			}
		}
	}
	

	/*
	* Display Index and Paging
	*/
	function index($item, &$objects) {
		$keys = array_keys($item);
		$itemName = $keys[0];
		$numPerPage = ($this->pageMode == 'list') ? $this->line_per_page : $this->column_per_page;
		$inc  = (count($objects) % $numPerPage == 0) ? 0 : 1;
		$numOfPage = intval(count($objects) / $numPerPage) + $inc;
		if (($this->page < 0) || ($this->page > $numOfPage)) {  // ($this->page == -1) means 'last page'
			$this->page = $numOfPage - 1; 
		}
		if ($this->sort_mode == "descending") {
			$inc = -1;
			$pos = count($objects)-($numPerPage * $this->page)-1;
			$end = ($pos - $numPerPage) > 0 ? $pos - $numPerPage: 0;
		} else {
			$inc = 1;
			$pos = ($numPerPage * $this->page);
			$end = ($pos + $numPerPage) < count($objects) ? $pos + $numPerPage : count($objects)-1;
		}
		
		// Page Start
		// Page Header -- page title
		if ($this->pageMode == 'list') {
			echo '<div class="list">';
			echo '<h2>' . __("LIST MODE") . '</h2>';
		} else {
			echo '<div class="columns">';
			echo '<h2>' . __("COLUMN MODE") . '</h2>';
		}
		// Page Header -- navigation controler
		echo '<div class="nav actions">';
		echo '<ul>';
		if ($this->edit_mode) {
			echo '<li><a href="' . $this->make_url('add', array()) . '">' . __("ADD") . '</a></li>';
		}
		echo '<li><a href="' . $this->make_url('index', array(), NULL, 'page') . '">' . __("COLUMN MODE") . '</a></li>';
		echo '<li><a href="' . $this->make_url('index', array(), NULL, 'list') . '">' . __("LIST MODE") . '</a></li>';
		echo '</ul>';
		echo '</div>';
		
		// Print Index or List
		if ($this->pageMode == 'list') {
			// list mode
			echo '<table class="list"><tr>';
			$this->walkItemCallback($item[$itemName], "printTableHeader",NULL,array($itemName));
			if ($this->edit_mode) { echo '<th>&nbsp;</th>'; }
			echo '</tr>';
			if (count($objects) > 0) {
				for($i=0; $i < $numPerPage; ++$i) {
					echo '<tr>';
					$this->walkItemCallback($item[$itemName], "printItemList", $objects[$pos],array($itemName));
					if ($this->edit_mode) { echo '<td class="ope">'; $this->printAction($pos,$objects); echo '</td>';}
					echo '</tr>';
					if ($pos == $end) break;
					$pos += $inc;
				}
			}
			echo '</table>';
		} else {
			// column mode
			if (count($objects) > 0) {
				for($i=0; $i < $numPerPage; ++$i) {
					echo '<a name="' . $pos . '"></a>';
					echo '<div class="column">';
					if ($this->edit_mode) { $this->printAction($pos,$objects); }
					echo '<dl>';
					$this->walkItemCallback($item[$itemName], "printItemColumn", $objects[$pos],array($itemName));
					echo '</dl>';
					echo '</div> <!-- /column -->';
					if ($pos == $end) break;
					$pos += $inc;
				}
			}
		}
		// Page Footer -- page control
		echo '<div class="pager actions">';
		echo '<ul>';
		if ($this->page > 0) {
			echo '<li class="go_top"><a href="' . $this->make_url('index',array(),0) . '">' . __('|&lt;TOP') . '</a></li>';
			echo '<li class="go_next"><a href="' . $this->make_url('index',array(),($this->page - 1)) . '">' . __('&lt;PREV') . '</a></li>';
		}
		echo '<li class="nombre">' . __('[%d/%d]',$this->page + 1, $numOfPage) . '</li>';
		if ($this->page < ($numOfPage - 1)) {
			echo '<li class="go_prev"><a href="' . $this->make_url('index',array(),($this->page + 1)) . '">' . __('NEXT&gt;') . '</a></li>';
			echo '<li class="go_last"><a href="' . $this->make_url('index',array(),-1) . '">' . __('LAST&gt;|') . '</a></li>';
		}
		echo '</ul>';
		echo '</div> <!-- /pager -->';
		//
		echo '</div> <!-- /columns /list -->';
	}
		
	/*
	* Print Action Buttons
	*/
	function printAction($pos,$objects) {
		$obj = $objects[$pos];
		$params = array($pos, hashObject($obj));
		echo '<div class="ope actions">';
		echo '<ul>';
		echo '<li><a href="' . $this->make_url('edit',$params) . '">' . __('EDIT') . '</a></li>';
		echo '<li><a href="' . $this->make_url('confirm/delete',$params) . '">' . __('DELETE') . '</a></li>';
		if ($this->pageMode == 'list') {
			echo '<li><a href="' . $this->make_url('view',$params) . '">' . __('VIEW') . '</a></li>';
		}
		if ($pos < count($objects)-1) {
			echo '<li><a href="' . $this->make_url('up',$params) . '">' . __('UP') . '</a></li>';
		}
		if ($pos > 0) {
			echo '<li><a href="' . $this->make_url('down',$params) . '">' . __('DOWN') . '</a></li>';
		}
		echo '</ul>';
		echo '</div>';
	}
		
	/*
	* Print Index Columns
	*/
	function printItemColumn($pathStr,$key,$val,$attr) {
		$style = $this->item_styles[$pathStr];
		$hidden = false;
		switch ($style['type']) {
			case "hidden":
				$hidden = true;
			case "textarea":
				$val = nl2br($val);
				break;
			case "select":
			case "radio":
			case "checkbox":
				if(!empty($val)) {
					$res = '';
					$vals = preg_split('/,/',$val);
					foreach($vals as $v) { $res .= __($style['options'][$v]) . ','; }
					$val = substr($res,0, strlen($res)-1);
				}
				break;
			default:	// text or none or unrecognize
				break;
		}
		if(!$hidden) {
			if ($val=='') $val = '-';
			if (!empty($style['label'])) {$label=$style['label'];} else { $label=$key; }
			if ($attr['type'] != 'html') { $val = htmlspecialchars($val); }
			echo '<dt>' . __($label) . '</dt>';
			echo '<dd>' . $val . '</dd>';
		}
	}

	/*
	* Print Index List Header
	*/
	function printTableHeader($pathStr, $key, $dummy=NULL) {
		$style = $this->item_styles[$pathStr];
		if(!empty($style['list']) && $style['list']) {
			if (!empty($style['label'])) {$label=$style['label'];} else {$label=$key;}
			echo '<th>' . __($label) . '</th>';
		}
	}
	
	/*
	* Print Index List
	*/
	function printItemList($pathStr,$key,$val, $attr) {
		$style = $this->item_styles[$pathStr];
		$hidden = false;
		switch ($style['type']) {
			case "hidden":
				$hidden = true;
			case "select":
			case "radio":
			case "checkbox":
				if(!empty($val)) {
					$res = '';
					$vals = preg_split('/,/',$val);
					foreach($vals as $v) { $res .= __($style['options'][$v]) . ','; }
					$val = substr($res,0, strlen($res)-1);
				}
				break;
			default:	// text or textarea or none or unrecognize
				if (mb_strlen($val, 'UTF-8') > 20) {
					$val = mb_substr($val, 0, 20, 'UTF-8') . "...";	// max length 20 chars
				}
				break;
		}
		if(!$hidden) {
			if(!empty($style['list']) && $style['list']) {
				if ($val=='') $val = '-';
				if ($attr['type'] != 'html') { $val = htmlspecialchars($val); }
				echo '<td>' . $val . '</td>';
			}
		}
	}
	
		
	/*
	* Display Edit Form
	*/
	function edit($item, &$obj, $errors=array()) {
		$keys = array_keys($item);
		$itemName = $keys[0];
		if (empty($obj)) {
			echo '<h2>' . __("ADD") . '</h2>';
		} else {
			echo '<h2>' . __("EDIT") . '</h2>';
		}
		if(!empty($errors)) {
			echo '<div class="alert">';
			echo '<ul>';
			foreach($errors as $msg) { echo '<li>' . $msg . '</li>'; }
			echo '</ul>';
			echo '</div>';
		}
		echo '<div class="form">';
		echo '<form name="'.$this->name.'" id="'.$this->name.'" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
		if (!is_object($obj)) { $obj = $obj[$itemName]; } // if this is not object then it is $data array.
		$this->walkItemCallback($item[$itemName], "printForm", $obj, array($itemName));
		echo '<div class="actions">';
		echo '<ul>';
		echo '<li><input type="submit" value="' . __('Submit') .'" /></li>';
		echo '<li><a href="' . $this->make_url('index', array()) . '">' . __('INDEX') . '</a></li>';
		echo '</ul>';
		echo '</div>';
		echo '</form>';
		echo '</div>';
	}
	
	function printForm($pathStr, $key, $val, $attr) {
		$dataName = 'data';
		$path = preg_split('/\//', $pathStr);
		foreach($path as $dn) { $dataName .= "[" . $dn . "]"; }	
		foreach($path as $dn) { $dataID .= ucfirst(strtolower($dn)); }
		$validation = $this->item_validations[$pathStr];
		$style = $this->item_styles[$pathStr];
		$hidden = false;
		$readonly = ($style['readonly'] == 'readonly') ? ' readonly="readonly"' : '';
		$class = "";
		if ($style['readonly'] == 'readonly') { $class .= "readonly ";}
		if (!empty($class)) { $class = 'class="' . $class . '" '; }
		switch ($style['type']) {
			case "hidden":
				$hidden = true;
				$input = sprintf('<input type="hidden" name="%s" id="%s" value="%s" %s/>',$dataName,$dataID,$val,$class);
				break;
			case "select":
				$options = "";
				if(!empty($style['options'])) {
					$vals = preg_split('/,/',$val);
					foreach($style['options'] as $k=>$v) {
						$selected = (in_array($k, $vals)) ? ' selected' : '';
						$options .= "<option value='".$k."'". $selected .">". __($v) ."</option>";
					}
				}
				$input = sprintf('<select name="%s" id="%s" %s>',$dataName,$dataID,$class);
				$input .= $options . '</select>';
				break;
			case "checkbox":
				$options = '<fieldset>';
				$pos = 0;
				if(!empty($style['options'])) {
					$vals = preg_split('/,/',$val);
					foreach($style['options'] as $k=>$v) {
						$checked = (in_array($k, $vals)) ? ' checked' : '';
						$dataItemID = $dataID . strval($pos++);
						$options .= sprintf('<input type="checkbox" name="%s[]" id="%s" value="%s" %s %s/><label for="%s">%s</label>',$dataName,$dataItemID,$k,$checked,$class,$dataItemID,__($v));
					}
				}
				$options .= '</fieldset>';
				$input =  $options;
				break;
			case "radio":
				$options = '<fieldset>';
				$pos = 0;
				if(!empty($style['options'])) {
					foreach($style['options'] as $k=>$v) {
						$dataItemID = $dataID . strval($pos++);
						$checked = ($k == $val) ? ' checked' : '';
						$options .= sprintf('<input type="radio" name="%s" id="%s" value="%s" %s %s/><label for="%s">%s</label>',$dataName,$dataItemID,$k,$checked,$class,$dataItemID,__($v));
					}
				}
				$options .= '</fieldset>';
				$input =  $options;
				break;
			case "textarea":
				$input = sprintf('<textarea name="%s" id="%s" %s %s>',$dataName,$dataID,$readonly,$class);
				$input .= htmlspecialchars($val). '</textarea>';
				break;
			default:	// text or none or unrecognize
				$input = sprintf('<input type="text" name="%s" id="%s" %s value="%s" %s/>',$dataName,$dataID,$readonly,htmlspecialchars($val),$class);
				break;
		}
		if (!empty($style['label'])) {$label=$style['label'];} else {$label=$key;}
		if(!$hidden) echo '<div class="input '.$style['type'].' ' . $key . '"><label for="' . $dataID . '">' . __($label) . '</label>';
		echo $input;
		if(!$hidden) echo '</div>';
	}
	
	/*
	* Display View
	*/
	function view($item, &$obj) {
		$keys = array_keys($item);
		$itemName = $keys[0];
		echo '<h2>' . __("VIEW") . '</h2>';
		echo '<div class="columns">';
		echo '<div class="column">';
		echo '<dl>';
		$this->walkItemCallback($item[$itemName], "printItemColumn", $obj,array($itemName));
		echo '</dl>';
		echo '<div class="actions">';
		echo '<ul>';
		echo '<li><a href="' . $this->make_url('index', array()) . '">' . __('INDEX') . '</a></li>';
		echo '</ul>';
		echo '</div> <!-- /actions -->';
		echo '</div> <!-- /column -->';
		echo '</div> <!-- /columns -->';
	}
		
	
	/*
	* Confirm
	*/
	function confirm($url) {
		$action = array_shift($url);
		echo '<div class="alert">';
		echo '<div class="note">';
		echo '<p>' . __('Are you sure?') . '</p>';
		echo '<div class="actions">';
		echo '<ul>';
		echo '<li><a href="' . $this->make_url($action, $url) . '">' . __('OK') . '</a></li>';
		echo '<li><a href="' . $this->make_url('index', array()) . '">' . __('Cancel') . '</a></li>';
		echo '</ul>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/*
	* Alert
	*/
	function alert($msg) {
		echo '<div class="alert">';
		echo '<div class="note">';
		echo '<p>' . __($msg) . '</p>';
		echo '<div class="actions">';
		echo '<ul>';
		echo '<li><a href="' . $this->make_url('index', array()) . '">' . __('OK') . '</a></li>';
		echo '</ul>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

}