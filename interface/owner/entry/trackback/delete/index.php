<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array('list', 'default' => '')
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");
requireComponent('Textcube.Function.Respond');

requireStrictRoute();

if(isset($suri['id'])) {
	
	$isAjaxRequest = checkAjaxRequest();
	
	if (trashTrackback($blogid, $suri['id']) !== false)
		$isAjaxRequest ? respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);
	else
		$isAjaxRequest ? respond::ResultPage(-1) : header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	foreach(explode(',', $_POST['targets']) as $target)
		trashTrackback($blogid, $target);
	respond::ResultPage(0);
}
?>
