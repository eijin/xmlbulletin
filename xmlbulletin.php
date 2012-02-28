<?php
/* 
* This is a simple sample for Xmlbulletin Project.
*/
	include_once("utility.php");
	include_once("xmlbulletin.class.php");
	$xb = new Xmlbulletin;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $xb->error ? __('ERROR') : __($xb->model->title); ?></title>
<?php echo $xb->html_head; ?>
<style>
#content { font-size: 90%; }
</style>
</head>

<body>
<div id="content">
<?php
	if($xb->error) {
		echo __($xb->error);
	} else {
		$xb->display();
	}
?>
</div>
</body>
</html>
