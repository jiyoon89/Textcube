			<div id="layout-body">
				<h2><span class="text"><?php echo _t('서브메뉴 : 환경설정')?></span></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-blog"><a href="<?php echo $blogURL?>/owner/setting/blog"><span class="text"><?php echo _t('블로그 환경을 관리합니다')?></span></a></li>
						<li id="sub-menu-account"><a href="<?php echo $blogURL?>/owner/setting/account"><span class="text"><?php echo _t('계정정보를 관리합니다')?></span></a></li>
						<li id="sub-menu-plugind"><a href="<?php echo $blogURL?>/owner/setting/plugins"><span class="text"><?php echo _t('플러그인을 관리합니다')?></span></a></li>
						<li id="sub-menu-data" class="selected"><a href="<?php echo $blogURL?>/owner/data"><span class="text"><?php echo _t('데이터를 관리합니다')?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/22" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<div id="data-outbox">
