<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
$backup = null;
if (file_exists(ROOT . "/cache/backup/$owner.xml.gz"))
	$backup = filemtime(ROOT . "/cache/backup/$owner.xml.gz");
else if (file_exists(ROOT . "/cache/backup/$owner.xml"))
	$backup = filemtime(ROOT . "/cache/backup/$owner.xml");
require ROOT . '/lib/piece/owner/header5.php';
require ROOT . '/lib/piece/owner/contentMenu54.php';
?>
							<script type="text/javascript">
								//<![CDATA[
									var dialog = null
									function showDialog($name) {
										if (dialog)
											dialog.style.display = "none";
										dialog = document.getElementById($name + "Dialog");
										PM.showPanel(dialog);
									}
									function hideDialog() {
										if (dialog) {
											dialog.style.display = "none";
											dialog = null;
										}
									}
									function correctData() {
										document.getElementById("correctingIndicator").style.width = "0%";
										PM.showPanel("correctingDataDialog");
										document.getElementById("dataCorrector").submit();
									}
									function backupData() {
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/data/backup?includeFileContents=" + document.getElementById("includeFileContents-yes").checked);
										PM.addRequest(request, "<?=_t('백업을 저장하고 있습니다...')?>");
										request.onSuccess = function () {
											PM.removeRequest(this);
											PM.showMessage("<?=_t('백업이 저장되었습니다.')?>", "center", "bottom");
										}
										request.onError = function () {
											PM.removeRequest(this);
											alert("<?=_t('백업을 저장하지 못했습니다.')?>");
										}
										request.send();
										hideDialog();
									}
									function exportData() {
										window.location.href = "<?=$blogURL?>/owner/data/export?includeFileContents=" + document.getElementById("includeFileContents-yes").checked;
										hideDialog();
									}
									function downloadBackup() {
										window.location.href = "<?=$blogURL?>/owner/data/download";
										hideDialog();
									}
									function importData() {
										var dataImporter = document.getElementById("dataImporter");
										if (document.getElementById("importFromUploaded").checked) {
											if (!dataImporter.elements["backupPath"].value) {
												alert("<?=_t('백업파일을 선택하십시오.')?>");
												dataImporter.elements["backupPath"].focus();
												return false;
											}
											document.getElementById("progressText").innerHTML = "<?=_t('백업파일을 올리고 있습니다.')?>";
										} else if (document.getElementById("importFromWeb").checked) {
											if (!dataImporter.elements["backupURL"].value) {
												alert("<?=_t('백업파일 URL을 입력하십시오.')?>");
												dataImporter.elements["backupURL"].focus();
												return false;
											}
											document.getElementById("progressText").innerHTML = "<?=_t('백업파일을 가져오고 있습니다.')?>";
										} else {
											document.getElementById("progressText").innerHTML = "";
										}
										hideDialog();
										document.getElementById("progressIndicator").style.width = "0%";
										PM.showPanel("progressDialog");
										dataImporter.submit();
									}
									function removeData() {
										var removeAttachments = document.getElementById("removeAttachments-yes");
										var confirmativePassword = document.getElementById("confirmativePassword");
										if (confirmativePassword.value.length < 6) {
											alert("<?=_t('비밀번호를 입력하십시오.')?>");
											confirmativePassword.focus();
											return false;
										}
										var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/data/remove");
										PM.addRequest(request, "<?=_t('데이터를 삭제하고 있습니다...')?>");
										request.onSuccess = function () {
											PM.removeRequest(this);
											PM.showMessage("<?=_t('데이터가 삭제되었습니다.')?>", "center", "bottom");
										}
										request.onError = function () {
											PM.removeRequest(this);
											alert("<?=_t('비밀번호가 일치하지 않습니다.')?>");
										}
										request.send("removeAttachments=" + (removeAttachments.checked ? "1" : "0") + "&confirmativePassword=" + encodeURIComponent(confirmativePassword.value));
										hideDialog();
									}
								//]]>
							</script>
							
							<div id="part-data-correct" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('데이터 교정')?></span></h2>
								
								<div class="data-inbox">
									<div class="image" onclick="correctData()">
										<img src="<?=$service['path']?>/style/image/dbCorrect.gif" alt="<?=_t('데이터 교정 이미지')?>" />
										<div class="title"><?=_t('CORRECT')?></div>
									</div>
									<div class="explain">
										<?=_t('비정상적인 데이터를 교정합니다.<br />동적인 캐쉬 데이터는 재계산하여 저장합니다.')?>
									</div>
									<div class="clear"></div>
								</div>
								
								<form id="dataCorrector" name="dataCorrector" method="get" action="<?=$blogURL?>/owner/data/correct" target="blackhole"></form>
								
								<div id="correctingDataDialog" class="system-dialog" style="position: absolute; display: none; z-index: 10;">
									<h3><?=_t('데이터를 교정하고 있습니다. 잠시만 기다려 주십시오...')?></h3>
									<div class="messege-sub">
										<span id="correctingText"></span>
										<span id="correctingTextSub"></span>
									</div>
									<div id="correctingIndicator" class="progressBar" style="width: 0%; height: 18px; margin-top: 5px; background-color: #66DDFF;"></div>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<div id="part-data-backup" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('데이터 백업')?></span></h2>
								
								<div class="data-inbox">
									<div class="image" onclick="showDialog('DBExport')">
										<img src="<?=$service['path']?>/style/image/dbExport.gif" alt="<?=_t('데이터 백업 이미지')?>" />
										<div class="title"><?=_t('EXPORT')?></div>
									</div>
									<div class="explain">
										<?=_t('현재의 모든 데이터를 백업파일로 보관합니다.<br />첨부파일을 포함시킬 수 있으며, 복원할 경우 자동으로 첨부파일이 처리됩니다.<br />백업파일은 서버에 저장하거나 다운받으실 수 있습니다.')?>
									</div>
									<div class="clear"></div>
								</div>
								
								<div id="DBExportDialog" class="dialog" style="position: absolute; display: none; z-index: 10;">
									<h3><?=_t('데이터 백업을 시작합니다')?></h3>
									<div class="messege-body">
										<div class="messege">
											<span class="asterisk">*</span><span><?=_t('첨부파일을 포함하시겠습니까?')?></span>
										</div>
										<div class="selection">
											<div class="select-yes" title="<?=_t('첨부 파일이 포함된 백업파일을 사용하여 복원할 경우, 첨부 파일의 내용은 백업파일의 내용으로 다시 작성됩니다.')?>"><input type="radio" id="includeFileContents-yes" class="radio" name="includeFileContents" value="1" /> <label for="includeFileContents-yes"><?=_t('첨부파일을 포함합니다.')?></label></div>
											<div class="select-no" title="<?=_t('첨부 파일이 포함되지 않는 백업파일을 사용하여 복원하여도 기존 첨부 파일을 삭제하거나 훼손시키지 않습니다.')?>"><input type="radio" id="includeFileContents-no" class="radio" name="includeFileContents" value="0" checked="checked" /> <label for="includeFileContents-no"><?=_t('첨부파일을 포함하지 않습니다.')?></label></div>
										</div>
									</div>
									<div class="button-box">
										<a class="server-button button" href="#void" onclick="backupData()" title="<?=_t('서버에 백업파일을 저장하여 복원에 사용할 수 있습니다.')?>"><span><?=_t('서버에 저장')?></span></a>
										<span class="hidden">|</span>
										<a class="local-button button" href="#void" onclick="exportData()" title="<?=_t('현재 상태의 데이터를 백업하여 다운로드합니다. 서버에 저장된 백업파일은 갱신되지 않습니다.')?>"><span><?=_t('다운로드')?></span></a>
										<span class="hidden">|</span>
										<a class="close-button button" href="#void" onclick="hideDialog()" title="<?=_t('명령을 취소하고 이 대화상자를 닫습니다.')?>"><span><?=_t('취소하기')?></span></a>
 									</div>
 									<div class="clear"></div>
 								</div>
 								<div class="clear"></div>
 							</div>
 							
 							<hr class="hidden" />
 							
							<div id="part-data-restore" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('데이터 복원')?></span></h2>
								
								<div class="data-inbox">
									<div class="image" onclick="showDialog('DBImport')">
										<img src="<?=$service['path']?>/style/image/dbImport.gif" alt="<?=_t('데이터 복원 이미지')?>" />
										<div class="title"><?=_t('IMPORT')?></div>
									</div>
									<div class="explain">
										<?=_t('백업파일을 읽어서 데이터를 복원합니다.<br />백업파일에 첨부파일이 포함되어 있으면 첨부파일도 자동으로 복원됩니다.<br />마이그레이션 데이터도 복원을 통해 가져올 수 있습니다.')?>
									</div>
									<div class="clear"></div>
								</div>

								<div id="DBImportDialog" class="dialog" style="position: absolute; display: none; z-index: 10;">
									<form id="dataImporter" name="dataImporter" method="post" action="<?=$blogURL?>/owner/data/import" enctype="multipart/form-data" target="blackhole">
										<h3><?=_t('데이터 복원을 시작합니다')?></h3>
										<div class="messege-body">
											<div class="explain">
												<span class="asterisk">*</span><?=_f('이 계정의 업로드 허용 용량은 <strong>%1</strong> 바이트로 백업파일의 크기가 이를 초과하는 경우 <acronym title="file transfer protocol">FTP</acronym> 등으로 원하시는 사이트에 업로드하신 후 이 파일의 웹 주소를 입력해서 진행하십시오. 이 경우, 보안을 위해 복원이 끝나면 반드시 그 백업파일을 웹 상에서 지우실 것을 권장합니다.', (getNumericValue(ini_get('post_max_size')) < getNumericValue(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize')))?>
											</div>
<?
if ($backup) {
?>
											<div class="messege">
												<span class="asterisk">*</span><?=_f('서버에 <em>%1</em>에 저장된 백업파일이 있습니다.', Timestamp::format5($backup))?>
											</div>
<?
}
?>
											<div class="selection">
<?
if ($backup) {
?>
												<div id="select-server" title="<?=_t('데이터 백업 기능을 통해 서버에 저장해 두었던 기존 파일을 이용해 데이터베이스를 복원합니다. 데이터 파일에 대해서는 위의 정보를 참고하십시오.')?>"><input type="radio" id="importFromServer" class="radio" name="importFrom" value="server" checked="checked" onclick="if (this.checked) {hideLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = true;}" /> <label for="importFromServer"><?=_t('서버에 저장된 백업파일.')?></label></div>
<?
}
?>
												<div id="select-upload" title="<?=_t('백업파일을 자신의 하드디스크로부터 직접 선택하여 데이터베이스를 복원합니다. 백업파일의 용량이 업로드 허용용량을 초과하지 않는지 주의하십시오.')?>"><input type="radio" id="importFromUploaded" class="radio" name="importFrom" value="uploaded"<?=($backup ? '' : ' checked="checked"')?> onclick="if (this.checked) {showLayer('uploadBackup'); hideLayer('remoteBackup'); document.getElementById('backupPath').disabled = false; document.getElementById('backupURL').disabled = true;}" /> <label for="importFromUploaded"><?=_t('백업파일 올리기.')?></label></div>
												<div id="select-web" title="<?=_t('백업파일의 크기가 업로드 허용 용량을 초과하는 경우, FTP 등을 이용하여 계정의 홈페이지에 직업 업로드한 후 이 파일의 위치를 지정하여 데이터베이스를 복원할 수 있습니다.')?>"><input type="radio" id="importFromWeb" class="radio" name="importFrom" value="web" onclick="if (this.checked) {hideLayer('uploadBackup'); showLayer('remoteBackup'); document.getElementById('backupPath').disabled = true; document.getElementById('backupURL').disabled = false;}" /> <label for="importFromWeb"><?=_t('웹에서 백업파일 가져오기.')?></label></div>
												<div id="select-correct" title="<?=_t('백업파일에 비정상적인 글자가 포함된 경우 복원에 실패할 수 있습니다. 비정상적인 글자를 교정하여 복원이 가능하도록 합니다. 이를 사용할 경우 복원에 많은 시간이 소요될 수 있습니다.')?>"><input type="checkbox" id="correctData" class="checkbox" name="correctData" value="on" /> <label for="correctData"><?=_t('백업파일에 포함된 비정상적인 글자를 교정합니다.')?></label></div>
											</div>
											<div id="uploadBackup" style="display: <?=($backup ? 'none' : 'block')?>;">
												<label for="backupPath"><?=_t('백업파일 경로')?></label><span class="divider"> : </span><input type="file" id="backupPath" class="file-input" name="backupPath" <?=($backup ? 'disabled="disabled"' : '')?> />
											</div>
											<div id="remoteBackup" style="display: none;">
												<label for="backupURL"><?=_t('백업파일 <acronym title="Uniform Resource Locator">URL</acronym>')?></label><span class="divider"> : </span><input type="text" id="backupURL" class="text-input" name="backupURL" value="http://" disabled="disabled" onkeydown="if (event.keyCode == 13) { importData(); return false; }" />
											</div>
										</div>
										<div class="button-box">
											<a class="restore-button button" href="#void" onclick="importData()"><span><?=_t('복원하기')?></span></a>
											<span class="hidden">|</span>
											<a class="close-button button" href="#void" onclick="hideDialog()" title="<?=_t('명령을 취소하고 이 대화상자를 닫습니다.')?>"><span><?=_t('취소하기')?></span></a>
	 									</div>
	 								</form>
	 								<div class="clear"></div>
 								</div>
								
								<div id="progressDialog" class="system-dialog" style="position: absolute; display: none; z-index: 10;">
									<h3><?=_t('데이터를 복원하고 있습니다. 잠시만 기다려 주십시오...')?></h3>
									<div class="messege-sub">
										<span id="progressText"><?=_t('백업파일을 올리고 있습니다.')?></span>
										<span id="progressTextSub"></span>
									</div>
									<div id="progressIndicator" class="progressBar" style="width: 10%; height: 18px; margin-top: 5px; background-color:#66DDFF;"></div>
									<div class="clear"></div>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<div id="part-data-remove" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('데이터 삭제')?></span></h2>
								
								<div class="data-inbox">
									<div class="image" onclick="showDialog('DBRemove')">
										<img src="<?=$service['path']?>/style/image/dbClear.gif" alt="<?=_t('데이터 삭제 이미지')?>" />
										<div class="title"><?=_t('REMOVE')?></div>
									</div>
									<div class="explain">
										<?=_t('태터툴즈의 모든 데이터를 삭제합니다.<br />첨부파일의 삭제 여부를 선택하실 수 있습니다.<br />데이터의 복원은 백업파일로만 가능하므로 먼저 백업을 하시기 바랍니다.')?>
									</div>
									<div class="clear"></div>
								</div>
								
								<div id="DBRemoveDialog" class="dialog" style="position: absolute; display: none; z-index: 10;">
									<h3><?=_t('데이터 삭제를 시작합니다')?></h3>
									<div class="messege-body">
										<div class="explain">
<?
if ($backup) {
?>
											<span class="asterisk">*</span><?=_f('서버에 <em>%1</em>에 저장된 백업파일이 있습니다. 삭제후 복원에는 이 파일을 이용하실 수 있습니다.', Timestamp::format5($backup))?><br />
<?
}
?>
										</div>
										<div class="messege">
											<span class="asterisk">*</span><?=_t('첨부파일을 포함하여 삭제하시겠습니까?')?>
										</div>
										<div class="selection">
											<div class="select-yes"><input type="radio" id="removeAttachments-yes" class="radio" name="removeAttachments" value="1" /> <label for="removeAttachments-yes"><?=_t('첨부파일을 포함합니다.')?></label></div>
											<div class="select-no"><input type="radio" id="removeAttachments-no" class="radio" name="removeAttachments" value="0" checked="checked" /> <label for="removeAttachments-no"><?=_t('첨부파일을 포함하지 않습니다.')?></label></div>
										</div>
										<div id="admin-password">
											<label for="confirmativePassword"><span class="asterisk">*</span><?=_t('데이터를 삭제하시려면 관리자 비밀번호를 입력하십시오.')?></label>
											<input type="password" id="confirmativePassword" class="text-input" name="confirmativePassword" onkeydown="if (event.keyCode == 13) { removeData(); return false; }" />
										</div>
									</div>
									<div class="button-box">
										<a class="remove-button button" href="#void" onclick="removeData()"><span><?=_t('삭제하기')?></span></a>
										<span class="hidden">|</span>
										<a class="close-button button" href="#void" onclick="hideDialog()" title="<?=_t('명령을 취소하고 이 대화상자를 닫습니다.')?>"><span><?=_t('취소하기')?></span></a>
 									</div>
 									<div class="clear"></div>
 								</div>
 								<div class="clear"></div>
 							</div>
 							
			 				<iframe id="blackhole" name="blackhole" style="display: none;"></iframe>
<?
require ROOT . '/lib/piece/owner/footer2.php';
?>