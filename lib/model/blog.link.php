<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

global $__gCacheLink;
$__gCacheLink = array();

function getLinks($blogid) {
	global $database, $__gCacheLink;
	if(empty($__gCacheLink)) {
		if ($result = POD::queryAll("SELECT * 
			FROM {$database['prefix']}Links 
			WHERE blogid = $blogid 
			ORDER BY name")) {
			$__gCacheLink = array();
			foreach($result as $link) {
				array_push($__gCacheLink, $link);
			}
		}
	}
	return $__gCacheLink;
}

function getLinksWithPagingForOwner($blogid, $page, $count) {
	global $database;
	return fetchWithPaging( "SELECT * FROM {$database['prefix']}Links WHERE blogid = $blogid ORDER BY name", $page, $count );
}

function getLink($blogid, $id) {
	global $database, $__gCacheLink;
	return POD::queryRow("SELECT * FROM {$database['prefix']}Links WHERE blogid = $blogid and id = $id");
}

function deleteLink($blogid, $id) {
	global $database;
	$result = POD::execute("DELETE FROM {$database['prefix']}Links WHERE blogid = $blogid AND id = $id");
	return ($result) ? true : false;
}

function toggleLinkVisibility($blogid, $id, $visibility) {
	global $database;
	$result = POD::execute("UPDATE {$database['prefix']}Links SET visibility = $visibility WHERE blogid = $blogid AND id = $id");
	return array( ($result) ? true : false, $visibility );
}

function addLink($blogid, $link) {
	global $database;
	$name = UTF8::lessenAsEncoding(trim($link['name']), 255);
	$url = UTF8::lessenAsEncoding(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return - 1;
	$name = POD::escapeString($name);
	$url = POD::escapeString($url);
	$rss = isset($link['rss']) ? POD::escapeString(UTF8::lessenAsEncoding(trim($link['rss']), 255)) : '';
	if (POD::queryCell("SELECT id FROM {$database['prefix']}Links WHERE blogid = $blogid AND url = '$url'"))
		return 1;
	if (POD::execute("INSERT INTO {$database['prefix']}Links (blogid,name,url,rss,written) VALUES ($blogid, '$name', '$url', '$rss', UNIX_TIMESTAMP())"))
		return 0;
	else
		return - 1;
}

function updateLink($blogid, $link) {
	global $database;
	$id = $link['id'];
	$name = UTF8::lessenAsEncoding(trim($link['name']), 255);
	$url = UTF8::lessenAsEncoding(trim($link['url']), 255);
	if (empty($name) || empty($url))
		return false;
	$name = POD::escapeString($name);
	$url = POD::escapeString($url);
	$rss = isset($link['rss']) ? POD::escapeString(UTF8::lessenAsEncoding(trim($link['rss']), 255)) : '';
	return POD::execute("update {$database['prefix']}Links
				set
					name = '$name',
					url = '$url',
					rss = '$rss',
					written = UNIX_TIMESTAMP()
				where
					blogid = $blogid and id = {$link['id']}");
}

function updateXfn($blogid, $links) {
	global $database;
	$ids = Array();
	foreach( $links as $k => $v ) {
		if( substr($k,0,3) == 'xfn' ) {
			$id = substr( $k, 3 );
			$xfn = POD::escapeString($v);
			POD::execute("update {$database['prefix']}Links
				set
					xfn = '$xfn',
					written = UNIX_TIMESTAMP()
				where
					blogid = $blogid and id = $id");
		}
	}
}
?>
