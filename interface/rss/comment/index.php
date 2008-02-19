<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);

require ROOT . '/lib/includeForBlog.php';
requireModel("blog.rss");
requireModel("blog.entry");

requireStrictBlogURL();
if (false) {
	fetchConfigVal();
}
$cache = new pageCache;
if(!empty($suri['id'])) {
	$cache->name = 'commentRSS_'.$suri['id'];
	if(!$cache->load()) {
		$result = getCommentRSSByEntryId(getBlogId(),$suri['id']);
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
} else {
	$cache->name = 'commentRSS';
	if(!$cache->load()) {
		$result = getCommentRSSTotal(getBlogId());
		if($result !== false) {
			$cache->contents = $result;
			$cache->update();
		}
	}
}
header('Content-Type: text/xml; charset=utf-8');
echo fireEvent('ViewCommentRSS', $cache->contents);
?>
