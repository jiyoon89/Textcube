<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function initPaging($url, $prefix = '?page=') {
	return array('url' => rtrim($url,'?'), 'prefix' => $prefix, 'postfix' => '', 'total' => 0, 'pages' => 0, 'page' => 0, 'before' => array(), 'after' => array());
}

function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	global $folderURL, $service;
	if ($url === null)
		$url = $folderURL;
	$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '');
	if (empty($sql))
		return array(array(), $paging);
	if (preg_match('/\s(FROM.*)(ORDER BY.*)$/si', $sql, $matches))
		$from = $matches[1];
	else
		return array(array(), $paging);
	$paging['total'] = POD::queryCell("SELECT COUNT(*) $from");
	if ($paging['total'] === null)
		return array(array(), $paging);
	if (empty($count)) $count = 1;
	$paging['pages'] = intval(ceil($paging['total'] / $count));
	$paging['page'] = is_numeric($page) ? $page : 1;
	if ($paging['page'] > $paging['pages']) {
		$paging['page'] = $paging['pages'];
		if ($paging['pages'] > 0)
			$paging['prev'] = $paging['pages'] - 1;
		//return array(array(), $paging);
	}
	if ($paging['page'] > 1)
		$paging['prev'] = $paging['page'] - 1;
	if ($paging['page'] < $paging['pages'])
		$paging['next'] = $paging['page'] + 1;
	$offset = ($paging['page'] - 1) * $count;
	if ($offset < 0) $offset = 0;
	if ($countItem !== null) $count = $countItem;
	return array(POD::queryAll("$sql LIMIT $offset, $count"), $paging);
}
?>
