<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function addTeamUser($email, $name, $comment, $senderName, $senderEmail) {
	requireModel('blog.user');
	requireModel('blog.blogSetting');
	global $database,$service,$blogURL,$hostURL,$user,$blog;

	$blogid = getBlogId();
	if(empty($email))
		return 1;
	if(!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/',$email))
		return array( 2, _t('이메일이 바르지 않습니다.') );
	
	$isUserExists = User::getUserIdByEmail($email);
	if(empty($isUserExists)) { // If user is not exist
		User::add($email,$name);
	}
	$userid = User::getUserIdByEmail($email);
	$result = addBlog(getBlogId(), $userid, null);
	if($result === true) {
		return sendInvitationMail(getBlogId(), $userid, User::getName($userid), $comment, $senderName, $senderEmail);
	}
	return $result;
}

function changeACLonBlog($blogid, $ACLtype, $userid, $switch) {  // Change user priviledge on the blog.
	global $database;
	if(empty($ACLtype) || empty($userid))
		return false;

	$acl = POD::queryCell("SELECT acl
			FROM {$database['prefix']}Teamblog 
			WHERE blogid='$blogid' and userid='$userid'");

	if( $acl === null ) { // If there is no ACL, add user into the blog.
		$name = User::getName($userid);
		POD::query("INSERT INTO `{$database['prefix']}Teamblog`  
				VALUES('$blogid', '$userid', '0', UNIX_TIMESTAMP(), '0')");
		$acl = 0;
	}

	$bitwise = null;
	switch( $ACLtype ) {
		case 'admin':
			$bitwise = BITWISE_ADMINISTRATOR;
			break;
		case 'editor':
			$bitwise = BITWISE_EDITOR;
			break;
		default:
			return false;
	}

	if( $switch ) {
		$acl |= $bitwise;
	} else {
		$acl &= ~$bitwise;
	}

	$sql = "UPDATE `{$database['prefix']}Teamblog` 
		SET acl = ".$acl." 
		WHERE blogid = ".$blogid." and userid = ".$userid;
	return POD::execute($sql);
}

function deleteTeamblogUser($userid ,$blogid = null, $clean = true) {
	global $database;
	if ($blogid == null) {
		$blogid = getBlogId();
	}
	POD::execute("UPDATE `{$database['prefix']}Entries` 
		SET userid = ".User::getBlogOwner($blogid)." 
		WHERE blogid = ".$blogid." AND userid = ".$userid);

	// Delete ACL relation.
	if(!POD::execute("DELETE FROM `{$database['prefix']}Teamblog` WHERE blogid='$blogid' and userid='$userid'"))
		return false;
	// And if there is no blog related to the specific user, delete user.
	if($clean && !POD::queryAll("SELECT * FROM `{$database['prefix']}Teamblog` WHERE userid = '$userid'")) {
		User::removePermanent($userid);
	}
	return true;
}

function changeBlogOwner($blogid,$userid) {
	global $database;
	$sql = "UPDATE `{$database['prefix']}Teamblog` SET acl = 3 WHERE blogid = ".$blogid." and acl = " . BITWISE_OWNER;
	POD::execute($sql);

	$acl = POD::queryCell("SELECT acl FROM {$database['prefix']}Teamblog WHERE blogid='$blogid' and userid='$userid'");

	if( $acl === null ) { // If there is no ACL, add user into the blog.
		POD::query("INSERT INTO `{$database['prefix']}Teamblog`  
			VALUES('$blogid', '$userid', '".BITWISE_OWNER."', UNIX_TIMESTAMP(), '0')");
	}
	else {
		$sql = "UPDATE `{$database['prefix']}Teamblog` SET acl = ".BITWISE_OWNER." 
			WHERE blogid = ".$blogid." and userid = " . $userid;
		POD::execute($sql);
	}

	return true;
}
?>