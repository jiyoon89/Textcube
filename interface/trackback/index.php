<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'url' => array('url', 'default' => ''),
		'title' => array('string', 'default' => ''),
		'excerpt' => array('string', 'default' => ''),
		'blog_name' => array('string', 'default' => '')
	),
	'SERVER' => array(
		'CONTENT_TYPE' => array('string', 'default' => '')
	)
);
require ROOT . '/library/includeForBlog.php';
$url = $_POST['url'];
$title = !empty($_POST['title']) ? $_POST['title'] : '';
$excerpt = !empty($_POST['excerpt']) ? $_POST['excerpt'] : '';
$blog_name = !empty($_POST['blog_name']) ? $_POST['blog_name'] : '';
if (!empty($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'charset') > 0) {
	$charsetPos = strpos($_SERVER["CONTENT_TYPE"], 'charset');
	$charsetArray = explode('=', substr($_SERVER["CONTENT_TYPE"], $charsetPos));
	$charset = $charsetArray[1];
	$ary[] = trim($charset);
}
/*if(!isset($suri['id'])) $suri['id'] = getEntryIdBySlogan($blogid, $suri['value']);
if(empty($suri['id'])) {
	respond::PrintResult(array('error' => 1, 'message' => 'URL is not exist or invalid'));
	exit;
}*/
$result = receiveTrackback($blogid, $suri['id'], $title, $url, $excerpt, $blog_name);
if ($result == 0) {
	if($row = POD::queryRow("SELECT * 
		FROM {$database['prefix']}Entries
		WHERE blogid = $blogid 
			AND id = {$suri['id']} 
			AND draft = 0 
			AND visibility = 3 
			AND acceptComment = 1"))
		sendTrackbackPing($suri['id'], "$defaultURL/".($blog['useSloganOnPost'] ? "entry/{$row['slogan']}": $suri['id']), $url, $blog_name, $title);
	respond::ResultPage(0);
} else {
	if ($result == 1) {
		respond::PrintResult(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 2) {
		respond::PrintResult(array('error' => 1, 'message' => 'Could not receive'));
	} else if ($result == 3) {
		respond::PrintResult(array('error' => 1, 'message' => 'The entry is not accept trackback'));
	} else if ($result == 4) {
		respond::PrintResult(array('error' => 1, 'message' => 'already exists trackback'));
	} else if ($result == 5) {
		respond::PrintResult(array('error' => 1, 'message' => 'URL is not exist or invalid'));
	}
}
?> 
