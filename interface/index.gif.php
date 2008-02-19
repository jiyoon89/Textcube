<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require ROOT . '/lib/includeForIcon.php';

$icon_path = ROOT . "/attach/$blogid/index.gif";
if( !file_exists($icon_path) ) {
	$icon_path = ROOT . '/image/icon_blogIcon_default.png';
}
dumpWithEtag( $icon_path );
?>
