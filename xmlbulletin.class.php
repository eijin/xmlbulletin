<?php
/* =========================================================================
Xmlbulletin is to control XML file for Bulletin Board System
Copyright (C) 2011  Eiji Nakai www.smallmake.com

xmlbulletin.class.php: version 1.0.0 - May 8, 2011.

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
include_once("xmlbulletin.model.php");
include_once("xmlbulletin.view.php");

class Xmlbulletin {
	public $model;				// Model
	public $view; 				// View
	public $action;				// Action
	public $params;				// Action's paramaters
	public $data;					// from $_REQUEST['data']
	
	public  $error = "";
	public  $html_head = "";		// CSS file link etc.
	
	private $actionNames = array('index','add','edit','view','delete','up','down','confirm');
	
	function __construct() {
		// load $_REQUEST especialy 'data' arrays
		$this->data =& $_REQUEST['data'];

		// default css link tag for html
		$script_path = $_SERVER['SCRIPT_NAME'];
		if ($pos = strrpos($script_path,'/')) { $script_path = substr($script_path, 0, $pos); } else { $script_path = ''; }
		$css = '<link rel="stylesheet" type="text/css" href="' . $script_path . '/xmlbulletin.css" />';

		// read PATH_INFO
		$params = preg_split("/\//",$_SERVER['PATH_INFO']);
		// param 0
		array_shift($params);	// skip script name
		// param 1
		$extendedClassName = array_shift($params);
		if (array_search($extendedClassName, $this->actionNames)!==false) {
			$this->action = $extendedClassName;
			$extendedClassName = '';
		} else {
			// param 2
			$this->action = array_shift($params);
		}
		if (empty($this->action)) $this->action = "index";
		// the rest of params put into $this->params
		$this->params = $params;

		// load extended class of model and view
		if (empty($extendedClassName)) { 
			// using default model and view
			$this->model = new xmlbulletinModel('');
			$this->view = new xmlbulletinView('');
		} else {
			if (!file_exists($extendedClassName.'.php')) {
				$this->error = __('You indicated %s, but there is not %s.php file.', $extendedClassName, $extendedClassName);
				return;
			} else {
				include_once($extendedClassName .'.php');	// this must have "class model" and "class view"
				$this->model = new model($extendedClassName);
				$this->view = new view($extendedClassName);
			}
			if (file_exists($extendedClassName .'.css')) {
				// if exist then replace to a default css link
				$css = '<link rel="stylesheet" type="text/css" href="' . $script_path . '/' . $extendedClassName . '.css" />';
			}
		}
		// check error
		if ($this->model->error) {
			$this->error = $this->model->error;
			return;
		}
		if ($this->view->error) {
			$this->error = $this->view->error;
			return;
		}
		
		$this->html_head .= $css;
		
		// map and hash check 'params'
		$objs = $this->model->get_item_objects();
		switch ($this->action) {
			case "add":
				$pos = NULL;
				break;
			case "edit":
			case "delete":
			case "up":
			case "down":
				$pos = intval($this->params[0]);
				$hash = $this->params[1];
				if ($this->action == "edit") { if(empty($this->data)) break; }
				if (hashObject($objs[$pos]) != $hash) {
					$this->error = __("ERROR: not match hash.");
				}
				break;
			default:
				break;
		}
		
		// deal with 'model'
		if (empty($this->error)) {
			switch ($this->action) {
				case "add":
					if(!empty($this->data)) {
						if ($this->model->save($this->data)) {
							$pos = count($objs);
							$page = ($this->view->sort_mode == "descending") ? 0 : -1; 	// -1 means the last page
							$this->redirect('index',$pos, $page);
						}
					}
					break;
				case "edit":
					if(!empty($this->data)) {
						if ($this->model->save($this->data, $pos)) {
							$this->redirect('index',$pos);
						}
					}
					break;
				case "delete":
					$this->model->delete($pos);
					$this->redirect('index',$pos);
					break;
				case "up":
					$this->model->up($pos);
					$this->redirect('index',$pos+1);
					break;
				case "down":
					$this->model->down($pos);
					$this->redirect('index',$pos-1);
					break;
				default:
					break;
			}
		}
	}
	
	/*
	* Control View
	*/
	function display($action=NULL) {
		if (empty($this->error)) {
			if ($action===NULL) { $action = $this->action; }
			$objs = $this->model->get_item_objects();
			if ($action == "edit" || $action == "view") {
				$pos = intval($this->params[0]);
				$hash = $this->params[1];
			}
			switch ($action) {
				case "index":
					$this->view->index($this->model->item, $objs);
					break;
				case "add":
					$this->view->edit($this->model->item, $this->data,$this->model->validation_errors);
					break;
				case "edit":
					if (empty($this->model->validation_errors)) {
						$this->view->edit($this->model->item, $objs[$pos]);
					} else {
						// reload $data not $object in this case
						$this->view->edit($this->model->item, $this->data,$this->model->validation_errors);
					}
					break;
				case "view":
					$this->view->view($this->model->item, $objs[$pos]);
					break;
				case "confirm":
					$this->view->confirm($this->params);
					break;
				default:
					$this->error = __('There is not the action - %s.', $action);
			}
		}
		if ($this->error) { $this->view->alertForm($this->error); }
	}
		
	
	function redirect($action, $jumppos=NULL, $page=NULL) {
		header("Location: ".$this->view->make_url($action,array(), $page, NULL, $jumppos));
	}
		
}
?>
