			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 글관리')?></span></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li class="add selected"><a href="#void" onclick="window.location.href = '<?=$blogURL?>/owner/entry/post'<?=(getDraftEntryId() ? "+(confirm('" . _t('임시 저장본을 보시겠습니까?\t') . "') ? '?draft' : '')" : '')?>"><span class="text"><?php echo _t('새 글을 씁니다')?></span></a></li>
						<li class="list"><a href="<?=$blogURL?>/owner/entry"><span class="text"><?php echo _t('글을 봅니다')?></span></a></li>
						<li class="thread"><a href="<?=$blogURL?>/owner/entry/comment"><span class="text"><?php echo _t('댓글을 봅니다')?></span></a></li>
						<li class="notify"><a href="<?=$blogURL?>/owner/entry/notify"><span class="text"><?php echo _t('댓글 알리미')?></span></a></li>
						<li class="trackback"><a href="<?=$blogURL?>/owner/entry/trackback"><span class="text"><?php echo _t('트랙백을 봅니다')?></span></a></li>
						<li class="category"><a href="<?=$blogURL?>/owner/entry/category"><span class="text"><?php echo _t('분류를 관리합니다')?></span></a></li>
						<li class="helper"><a href="http://www.tattertools.com/doc/3" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-outbox">
					<div id="psuedo-inbox">
						<form method="post" action="<?=$blogURL?>/owner/entry">
							<input type="hidden" name="page" value="<?=$suri['page']?>" />
							
							<div id="data-outbox">
