<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
	
if (isset($_GET['category'])) $_POST['category'] = $_GET['category'];
if (isset($_GET['site'])) $_POST['site'] = $_GET['site'];
if (isset($_GET['ip'])) $_POST['ip'] = $_GET['ip'];
if (isset($_GET['withSearch'])) $_POST['withSearch'] = $_GET['withSearch'];
if (isset($_GET['search'])) $_POST['search'] = $_GET['search'];
if (isset($_GET['status'])) $_POST['status'] = $_GET['status'];

$IV = array(
	'GET' => array(
		'site' => array('string', 'mandatory' => false),
		'ip' => array('ip', 'mandatory' => false),
		'page' => array('int', 1, 'default' => 1)
	),
	'POST' => array(
		'category' => array('int', 'default' => 0),
		'site' => array('string', 'mandatory' => false),
		'ip' => array('ip', 'mandatory' => false),
		'withSearch' => array(array('on'), 'mandatory' => false),
		'search' => array('string', 'default' => ''),
		'perPage' => array('int', 1, 'mandatory' => false),
		'status' => array('string', 'mandatory' => false)
	)
);	
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");

$categoryId = empty($_POST['category']) ? 0 : $_POST['category'];
$site = isset($_GET['site']) && !empty($_GET['site']) ? $_GET['site'] : '';
$site = isset($_POST['site']) && !empty($_POST['site']) ? $_POST['site'] : $site;
$ip = isset($_GET['ip']) && !empty($_GET['ip']) ? $_GET['ip'] : '';
$ip = isset($_POST['ip']) && !empty($_POST['ip']) ? $_POST['ip'] : $ip;
$search = empty($_POST['withSearch']) || empty($_POST['search']) ? '' : trim($_POST['search']);
$perPage = getBlogSetting('rowsPerPage', 10);
if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])) {
	$perPage = $_POST['perPage'];
	setBlogSetting('rowsPerPage', $_POST['perPage']);
}

$tabsClass = array();
$tabsClass['postfix'] = null;
$tabsClass['postfix'] .= isset($_POST['category']) ? '&category='.$_POST['category'] : '';
$tabsClass['postfix'] .= isset($_POST['name']) ? '&name='.$_POST['name'] : '';
$tabsClass['postfix'] .= isset($_POST['ip']) ? '&ip='.$_POST['ip'] : '';
$tabsClass['postfix'] .= isset($_POST['search']) ? '&search='.$_POST['search'] : '';
if(!empty($tabsClass['postfix'])) $tabsClass['postfix'] = ltrim($tabsClass['postfix'],'/'); 

if (isset($_POST['status'])) {
	if($_POST['status']=='received') {
		$tabsClass['received'] = true;
		$visibilityText = _t('걸린 글');
	} else if($_POST['status']=='sent') {
		$tabsClass['sent'] = true;
		$visibilityText = _t('건 글');
	}
} else {
	$tabsClass['received'] = true;
	$visibilityText = _t('걸린 글');
}

if(isset($tabsClass['received']) && $tabsClass['received'] == true) {
	list($trackbacks, $paging) = getTrackbacksWithPagingForOwner($blogid, $categoryId, $site, $ip, $search, $suri['page'], $perPage);
} else {
	list($trackbacks, $paging) = getTrackbackLogsWithPagingForOwner($blogid, $categoryId, $site, $ip, $search, $suri['page'], $perPage);
}

require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';

?>
						<script type="text/javascript">
							//<![CDATA[
<?php
if($tabsClass['received'] == true) {
?>
							function changeState(caller, value, mode) {
									try {			
										if (caller.className == 'block-icon bullet') {
											var command 	= 'unblock';
										} else {
											var command 	= 'block';
										}
										var name 		= caller.id.replace(/\-[0-9]+$/, '');
										param  	=  '?value='	+ encodeURIComponent(value);
										param 	+= '&mode=' 	+ mode;
										param 	+= '&command=' 	+ command;
										
										var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/setting/filter/change/" + param);
										var iconList = document.getElementsByTagName("a");	
										for (var i = 0; i < iconList.length; i++) {
											icon = iconList[i];
											if(icon.id == null || icon.id.replace(/\-[0-9]+$/, '') != name) {
												continue;
											} else {
												if (command == 'block') {
													icon.className = 'block-icon bullet';
													icon.innerHTML = '<span class="text"><?php echo _t('[차단됨]');?><\/span>';
													icon.setAttribute('title', "<?php echo _t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>");
												} else {
													icon.className = 'unblock-icon bullet';
													icon.innerHTML = '<span class="text"><?php echo _t('[허용됨]');?><\/span>';
													icon.setAttribute('title', "<?php echo _t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>");
												}
											}
										}
										request.send();
									} catch(e) {
										alert(e.message);
									}
								}
								
								function trashTrackback(id) {
									if (!confirm("<?php echo _t('선택된 걸린글을 휴지통으로 옮깁니다. 계속 하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/communication/trackback/delete/" + id);
									request.onSuccess = function() {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('걸린글을 삭제하였습니다.');?>","center", "bottom");
										document.getElementById('list-form').submit();
									}
									request.onError = function() {
										PM.removeRequest(this);
										PM.showErrorMessage("<?php echo _t('걸린글을 삭제하지 못하였습니다.');?>","center", "bottom");
									}
									PM.addRequest(request, "<?php echo _t('걸린글을 삭제하고 있습니다.');?>");
									request.send();
								}
								
								function trashTrackbacks() {
									try {
										if (!confirm("<?php echo _t('선택된 걸린글을 지웁니다. 계속 하시겠습니까?');?>"))
											return false;
										var oElement;
											var targets = new Array();
										for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
											oElement = document.getElementById('list-form').elements[i];
											if ((oElement.name == "entry") && oElement.checked)
												targets[targets.length] = oElement.value;
										}
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/communication/trackback/delete/");
										request.onSuccess = function() {
											document.getElementById('list-form').submit();
										}
										request.send("targets=" + targets.join(","));
									} catch(e) {
										alert(e.message);
									}
								}
<?php
} else {
?>
								function removeTrackbackLog(id) {
									if (confirm("선택된 글걸기 기록을 지웁니다. 계속 하시겠습니까?")) {
										var request = new HTTPRequest("<?php echo $blogURL;?>/owner/communication/trackback/log/remove/" + id);
										request.onSuccess = function () {
											document.getElementById('list-form').submit();
										}
										request.onError = function () {
											alert("글걸기 기록을 지우지 못했습니다.");
										}
										request.send();
									}
								}
								
								function trashTrackbacks() {
									try {
										if (!confirm("<?php echo _t('선택된 걸린글 기록을 지웁니다. 계속 하시겠습니까?');?>"))
											return false;
										var oElement;
											var targets = new Array();
										for (i = 0; document.getElementById('list-form').elements[i]; i ++) {
											oElement = document.getElementById('list-form').elements[i];
											if ((oElement.name == "entry") && oElement.checked)
												targets[targets.length] = oElement.value;
										}
										var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/communication/trackback/log/remove/");
										request.onSuccess = function() {
											document.getElementById('list-form').submit();
										}
										alert(targets.join(","));
										request.send("targets=" + targets.join(","));
									} catch(e) {
										alert(e.message);
									}
								}
<?php
}
?>
								
								function checkAll(checked) {
									for (i = 0; document.getElementById('list-form').elements[i]; i++) {
										if (document.getElementById('list-form').elements[i].name == "entry") {
											if (document.getElementById('list-form').elements[i].checked != checked) {
												document.getElementById('list-form').elements[i].checked = checked;
												toggleThisTr(document.getElementById('list-form').elements[i]);
											}
										}
									}
								}
								
								window.addEventListener("load", execLoadFunction, false);
								function execLoadFunction() {
									document.getElementById('allChecked').disabled = false;
									removeItselfById('category-move-button');
								}
								
								function toggleThisTr(obj) {
									objTR = getParentByTagName("TR", obj);
									
									if (objTR.className.match('inactive')) {
										objTR.className = objTR.className.replace('inactive', 'active');
									} else {
										objTR.className = objTR.className.replace('active', 'inactive');
									}
								}
							//]]>
						</script>
						
						<div id="part-post-trackback" class="part">
							<h2 class="caption">
								<span class="main-text"><?php echo (isset($tabsClass['received']) ? _t('걸린글 목록입니다') : _t('건 글 목록입니다'));?></span>
<?php
if (strlen($site) > 0 || strlen($ip) > 0) {
	if (strlen($site) > 0) {
?>
								<span class="filter-condition"><?php echo htmlspecialchars($site);?></span>
<?php
	}
	
	if (strlen($ip) > 0) {
?>
								<span class="filter-condition"><?php echo htmlspecialchars($ip);?></span>
<?php
	}
}
?>
							</h2>
<?php
require ROOT . '/lib/piece/owner/communicationTab.php';
?>


							<form id="category-form" class="category-box" method="post" action="<?php echo $blogURL;?>/owner/communication/trackback">
								<div class="section">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
<?php
	if(!isset($tabsClass['received'])) {
?>
									<input type="hidden" name="status" value="sent" />
<?php } ?>
									<select id="category" name="category" onchange="document.getElementById('category-form').page.value=1; document.getElementById('category-form').submit()">
										<option value="0"><?php echo _t('전체');?></option>
<?php
foreach (getCategories($blogid) as $category) {
?>
										<option value="<?php echo $category['id'];?>"<?php echo ($category['id'] == $categoryId ? ' selected="selected"' : '');?>><?php echo htmlspecialchars($category['name']);?></option>
<?php
	foreach ($category['children'] as $child) {
?>
										<option value="<?php echo $child['id'];?>"<?php echo ($child['id'] == $categoryId ? ' selected="selected"' : '');?>>&nbsp;― <?php echo htmlspecialchars($child['name']);?></option>
<?php
	}
}
?>
									</select>
									<input type="submit" id="category-move-button" class="move-button input-button" value="<?php echo _t('이동');?>" />
								</div>
							</form>

							<form id="list-form" method="post" action="<?php echo $blogURL;?>/owner/communication/trackback">
<?php
	if(!isset($tabsClass['received'])) {
?>
								<input type="hidden" name="status" value="sent" />
<?php } ?>
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="selection"><input type="checkbox" id="allChecked" class="checkbox" onclick="checkAll(this.checked);" disabled="disabled" /></th>
											<th class="date"><span class="text"><?php echo _t('등록일자');?></span></th>
											<th class="site"><span class="text"><?php echo (isset($tabsClass['received']) ? _t('사이트 이름') : _t('보낸 주소'));?></span></th>
											<th class="category"><span class="text"><?php echo _t('분류');?></span></th>
											<th class="title"><span class="text"><?php echo (isset($tabsClass['received']) ? _t('받은 글 제목') : _t('보낸 글 제목'));?></span></th>
<?php if(isset($tabsClass['received'])) {
?>
											<th class="ip"><acronym title="Internet Protocol">ip</acronym></th>
<?php } ?>
											<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
										</tr>
									</thead>
<?php
if (sizeof($trackbacks) > 0) echo "									<tbody>";
$siteNumber = array();
for ($i=0; $i<sizeof($trackbacks); $i++) {
	$trackback = $trackbacks[$i];
	requireComponent('Textcube.Data.Filter');
	$isFilterURL = Filter::isFiltered('url', $trackback['url']);
	$filteredURL = getURLForFilter($trackback['url']);

	$filter = new Filter();
	if (isset($trackback['ip']) && Filter::isFiltered('ip', $trackback['ip'])) {
		$isIpFiltered = true;
	} else {
		$isIpFiltered = false;
	}
	if(isset($trackback['site'])) {
		if (!isset($siteNumber[$trackback['site']])) {
			$siteNumber[$trackback['site']] = $i;
			$currentSite = $i;
		} else {
			$currentSite = $siteNumber[$trackback['site']];
		}
	} else {
		$currentSite = $i;
	}
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($trackbacks) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="selection"><input type="checkbox" class="checkbox" name="entry" value="<?php echo $trackback['id'];?>" onclick="document.getElementById('allChecked').checked=false; toggleThisTr(this);" /></td>
											<td class="date"><?php echo Timestamp::formatDate($trackback['written']);?></td>
											<td class="site">
<?php
	if(isset($tabsClass['received'])) {
		if ($isFilterURL) {
?>
												<a id="urlFilter<?php echo $currentSite;?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $blogURL;?>/owner/setting/filter/change/?value=<?php echo urlencode($filteredURL);?>&amp;mode=url&amp;command=unblock" onclick="changeState(this,'<?php echo $filteredURL;?>','url'); return false;" title="<?php echo _t('이 사이트는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
		} else {
?>
												<a id="urlFilter<?php echo $currentSite;?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $blogURL;?>/owner/setting/filter/change/?value=<?php echo urlencode($filteredURL);?>&amp;mode=url&amp;command=block" onclick="changeState(this,'<?php echo $filteredURL;?>','url'); return false;" title="<?php echo _t('이 사이트는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
		}
?>
												<a href="?site=<?php echo urlencode(escapeJSInAttribute($trackback['site']));?>" title="<?php echo _t('이 사이트에서 건 글 목록을 보여줍니다.');?>"><?php echo htmlspecialchars($trackback['site']);?></a>
<?php
	} else {
?>
												<a href="<?php echo htmlspecialchars($trackback['url']);?>"><?php echo link_cut(htmlspecialchars($trackback['url']),30);?></a>
<?php
	}
?>
											</td>
											<td class="category">
<?php
	if (!empty($trackback['categoryName'])) {
?>
											<span class="categorized"><?php echo htmlspecialchars($trackback['categoryName']);?></span>
<?php
	} else {
?>
											<span class="uncategorized"><?php echo _t('분류 없음');?></span>
<?php
	}
?>
											</td>
											<td class="title">
												<a href="<?php echo $trackback['url'];?>" onclick="window.open(this.href); return false;" title="<?php echo _t('글을 건 글을 보여줍니다.');?>"><?php echo htmlspecialchars($trackback['subject']);?></a>
											</td>
<?php
	if(isset($tabsClass['received'])) {
?>
											<td class="ip">
<?php
		if ($isIpFiltered) {
?>
												<a id="ipFilter<?php echo urlencode($trackback['ip']);?>-<?php echo $i;?>" class="block-icon bullet" href="<?php echo $blogURL;?>/owner/setting/filter/change/?value=<?php echo urlencode($trackback['ip']);?>&amp;mode=ip&amp;command=unblock" onclick="changeState(this,'<?php echo urlencode($trackback['ip']);?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되었습니다. 클릭하시면 차단을 해제합니다.');?>"><span class="text"><?php echo _t('[차단됨]');?></span></a>
<?php
		} else {
?>
												<a id="ipFilter<?php echo urlencode($trackback['ip']);?>-<?php echo $i;?>" class="unblock-icon bullet" href="<?php echo $blogURL;?>/owner/setting/filter/change/?value=<?php echo urlencode($trackback['ip']);?>&amp;mode=ip&amp;command=block" onclick="changeState(this,'<?php echo urlencode($trackback['ip']);?>', 'ip'); return false;" title="<?php echo _t('이 IP는 차단되지 않았습니다. 클릭하시면 차단합니다.');?>"><span class="text"><?php echo _t('[허용됨]');?></span></a>
<?php
		}
?>

												<a href="?ip=<?php echo urlencode(escapeJSInAttribute($trackback['ip']));?>" title="<?php echo _t('이 IP로 등록된 걸린글 목록을 보여줍니다.');?>"><?php echo $trackback['ip'];?></a>
											</td>
<?php
	}
?>
											<td class="delete">
<?php
	if(isset($tabsClass['received'])) {
?>
												<a class="delete-button button" href="<?php echo $blogURL;?>/owner/communication/trackback/delete/<?php echo $trackback['id'];?>" onclick="trashTrackback(<?php echo $trackback['id'];?>); return false;" title="<?php echo _t('이 걸린글을 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a>
<?php
	} else {
?>
												<a class="delete-button button" href="<?php echo $blogURL;?>/owner/communication/trackback/log/remove/<?php echo $trackback['id'];?>" onclick="removeTrackbackLog(<?php echo $trackback['id'];?>); return false;" title="<?php echo _t('이 글걸기 기록을 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a>
<?php
	}
?>
											</td>
										</tr>
<?php
}

if (sizeof($trackbacks) > 0) echo "									</tbody>";
?>
								</table>
								
								<hr class="hidden" />
								
								<div class="data-subbox">
									<input type="hidden" name="page" value="<?php echo $suri['page'];?>" />
									<input type="hidden" name="site" value="" />
									<input type="hidden" name="ip" value="" />
									
									<div id="delete-section" class="section">
										<span class="label"><?php echo _t('선택한 걸린글을');?></span>
										<input type="button" class="delete-button input-button" value="<?php echo _t('삭제');?>" onclick="trashTrackbacks();" />
									</div>
									
									<div id="page-section" class="section">
										<div id="page-navigation">
											<span id="page-list">
<?php
//$paging['url'] = 'document.getElementById('list-form').page.value=';
//$paging['prefix'] = '';
//$paging['postfix'] = '; document.getElementById('list-form').submit()';
$pagingTemplate = '[##_paging_rep_##]';
$pagingItemTemplate = '<a [##_paging_rep_link_##]>[[##_paging_rep_link_num_##]]</a>';
print getPagingView($paging, $pagingTemplate, $pagingItemTemplate);
?>
											</span>
											<span id="total-count"><?php echo _f('총 %1건', empty($paging['total']) ? "0" : $paging['total']);?></span>
										</div>
										<div class="page-count">
											<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 0);?>
											<select name="perPage" onchange="document.getElementById('list-form').page.value=1; document.getElementById('list-form').submit()">
<?php
for ($i = 10; $i <= 30; $i += 5) {
	if ($i == $perPage) {
?>
												<option value="<?php echo $i;?>" selected="selected"><?php echo $i;?></option>
<?php
	} else {
?>
												<option value="<?php echo $i;?>"><?php echo $i;?></option>
<?php
	}
}
?>
											</select>
											<?php echo getArrayValue(explode('%1', _t('한 페이지에 글 %1건 표시')), 1);?>
										</div>
									</div>
								</div>
							</form>
							
							<hr class="hidden" />
							
							<form id="search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/communication/trackback">
								<h2><?php echo _t('검색');?></h2>
								
								<div class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('사이트명');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($search);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
<?php
if(!isset($tabsClass['received'])) {
	?>
									<input type="hidden" name="status" value="sent" />
<?php } ?>
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>