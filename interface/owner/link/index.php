<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.link");

$links = getLinks($blogid);
require ROOT . '/lib/piece/owner/header.php';
require ROOT . '/lib/piece/owner/contentMenu.php';
?>
						<script type="text/javascript">
							//<![CDATA[
								function deleteLink(id) {
									if (!confirm("<?php echo _t('링크를 삭제하시겠습니까?');?>"))
										return;
									var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/owner/link/delete/" + id);
									request.onSuccess = function () {
										PM.removeRequest(this);
										PM.showMessage("<?php echo _t('링크가 삭제되었습니다.');?>", "center", "bottom");
										var node = document.getElementById("link_" + id);
										node.parentNode.removeChild(node);
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request, "<?php echo _t('링크를 삭제하고 있습니다.');?>");
									request.send();
								}
								
								function setLinkVisibility(id, command) {
									var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/link/visibility/" + id);
									request.onSuccess = function () {
										PM.removeRequest(this);
										var visibility = parseInt(this.getText("/response/visibility"));
										switch(visibility) {
											/* visibility := 0: invisible, 1: member-visible, 2: public-visible */
											case 0:
												document.getElementById("privateIcon_" + id).className = 'private-on-icon';
												document.getElementById("protectedIcon_" + id).className = 'protected-off-icon';
												document.getElementById("publicIcon_" + id).className = 'public-off-icon';

												document.getElementById("privateIcon_" + id).innerHTML = '<span class="text"><?php echo _t('비공개');?><\/span>';
												document.getElementById("protectedIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=protect" onclick="setLinkVisibility('+id+', 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?><\/span><\/a>';
												document.getElementById("publicIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=public" onclick="setLinkVisibility('+id+', 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?><\/span><\/a>';
												break;
											case 1:
												document.getElementById("privateIcon_" + id).className = 'private-off-icon';
												document.getElementById("protectedIcon_" + id).className = 'protected-on-icon';
												document.getElementById("publicIcon_" + id).className = 'public-off-icon';

												document.getElementById("privateIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=private" onclick="setLinkVisibility('+id+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?><\/span><\/a>';
												document.getElementById("protectedIcon_" + id).innerHTML = '<span class="text"><?php echo _t('보호');?><\/span>';
												document.getElementById("publicIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=public" onclick="setLinkVisibility('+id+', 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?><\/span><\/a>';
												break;
											case 2:
											default :
												document.getElementById("privateIcon_" + id).className = 'private-off-icon';
												document.getElementById("protectedIcon_" + id).className = 'protected-off-icon';
												document.getElementById("publicIcon_" + id).className = 'public-on-icon';
												
												document.getElementById("privateIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=private" onclick="setLinkVisibility('+id+', 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?><\/span><\/a>';
												document.getElementById("protectedIcon_" + id).innerHTML = '<a href="<?php echo $blogURL;?>/owner/link/visibility/' + id + '?command=protect" onclick="setLinkVisibility('+id+', 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?><\/span><\/a>';
												document.getElementById("publicIcon_" + id).innerHTML = '<span class="text"><?php echo _t('공개');?><\/span>';
												break;
										}
									}
									request.onError= function () {
										PM.removeRequest(this);
										switch(parseInt(this.getText("/response/error")))
										{
											default:
												alert("<?php echo _t('알 수 없는 에러가 발생했습니다.');?>");
										}
									}
									PM.addRequest(request);
									request.send("visibility="+command);
								}
							//]]>
						</script>
						
						<div id="part-link-list" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('링크 목록입니다');?></span></h2>
							
							<table class="data-inbox" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="homepage"><span class="text"><?php echo _t('홈페이지 이름');?></span></th>
										<th class="status"><span class="text"><?php echo _t('상태');?></span></th>
										<th class="address"><span class="text"><?php echo _t('사이트 주소');?></span></th>
										<th class="edit"><span class="text"><?php echo _t('수정');?></span></th>
										<th class="delete"><span class="text"><?php echo _t('삭제');?></span></th>
									</tr>
								</thead>
<?php
if (sizeof($links) > 0) echo "									<tbody>";
for ($i=0; $i<sizeof($links); $i++) {
	$link = $links[$i];
	$visible_class = ( $link['visibility'] ? "visible-link" : "invisible-link" ) . " visible-button";
	$visible_class .= ' button';
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($links) - 1) ? ' last-line' : '';
?>
									<tr id="link_<?php echo $link['id'];?>" class="<?php echo $className;?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
										<td class="homepage"><a href="<?php echo htmlspecialchars($link['name']);?>" onclick="window.open(this.href); return false;" title="<?php echo _t('이 링크에 연결합니다.');?>"><?php echo htmlspecialchars($link['name']);?></a></td>									
										<td class="status">
											
											<span id="privateIcon_<?php echo $link['id'];?>" class="private-<?php echo (($link['visibility'] == 0) ? 'on' : 'off');?>-icon">
<?php 
	if($link['visibility'] != 0) {
?>
												<a href="<?php echo $blogURL;?>/owner/link/visibility/<?php echo $link['id'];?>?command=protect" onclick="setLinkVisibility(<?php echo $link['id'];?>, 0); return false;" title="<?php echo _t('현재 상태를 비공개로 전환합니다.');?>"><span class="text"><?php echo _t('비공개');?></span></a>
<?php
	} else {
?>
												<span class="text"><?php echo _t('비공개');?></span>
<?php
	}
?>
											</span>

											
											<span id="protectedIcon_<?php echo $link['id'];?>" class="protected-<?php echo (($link['visibility'] == 1) ? 'on' : 'off');?>-icon">
<?php 
	if($link['visibility'] != 1) {
?>
												<a href="<?php echo $blogURL;?>/owner/link/visibility/<?php echo $link['id'];?>?command=protect" onclick="setLinkVisibility(<?php echo $link['id'];?>, 1); return false;" title="<?php echo _t('현재 상태를 보호로 전환합니다.');?>"><span class="text"><?php echo _t('보호');?></span></a>
<?php
	} else {
?>
												<span class="text"><?php echo _t('보호');?></span>
<?php
	}
?>
											</span>
											<span id="publicIcon_<?php echo $link['id'];?>" class="public-<?php echo (($link['visibility'] == 2) ? 'on' : 'off');?>-icon">
<?php 
	if($link['visibility'] != 2) {
?>
												<a href="<?php echo $blogURL;?>/owner/link/visibility/<?php echo $link['id'];?>?command=public" onclick="setLinkVisibility(<?php echo $link['id'];?>, 2); return false;" title="<?php echo _t('현재 상태를 공개로 전환합니다.');?>"><span class="text"><?php echo _t('공개');?></span></a>
<?php
	} else {
?>
												<span class="text"><?php echo _t('공개');?></span>
<?php
	}
?>
											</span>
										</td>
										
										<td class="address"><a href="<?php echo $blogURL;?>/owner/link/edit/<?php echo $link['id'];?>" title="<?php echo _t('이 링크 정보를 수정합니다.');?>"><?php echo htmlspecialchars($link['url']);?></a></td>
										<td class="edit"><a class="edit-button button" href="<?php echo $blogURL;?>/owner/link/edit/<?php echo $link['id'];?>" title="<?php echo _t('링크 정보를 수정합니다.');?>"><span><?php echo _t('수정');?></span></a></td>
										<td class="delete"><a class="delete-button button" href="<?php echo $blogURL;?>/owner/link/delete/<?php echo $link['id'];?>" onclick="deleteLink(<?php echo $link['id'];?>); return false;" title="<?php echo _t('링크 정보를 삭제합니다.');?>"><span class="text"><?php echo _t('삭제');?></span></a></td>
									</tr>
<?php
}
if (sizeof($links) > 0) echo "									</tbody>";
?>
							</table>
						</div>
<?php
require ROOT . '/lib/piece/owner/footer.php';
?>
