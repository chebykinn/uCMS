<?php
$event->bind_action("comment.added", "comment_added");
$event->bind_action("post.added", "post_added");
$event->bind_action("page.added", "page_added");
$event->bind_action("user.registered", "user_registered");
$event->bind_action("user.logged_in", "user_logged_in");

function comment_added($author, $comment, $guest, $email){
	global $user, $ucms;
	$group = $user->get_user_group($author);
	$observed_user_groups = explode(",", COMMENTS_OBSERVED_USER_GROUPS);
	if(COMMENTS_EMAIL != '' and COMMENTS_NOTIFICATION and (in_array($group, $observed_user_groups) or $guest)){
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$headers = "Content-type:text/html; charset=utf-8\r\n";
		$subject = $ucms->cout("plugin.notifications.comment_added.subject", true);
		$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$user_link = NICE_LINKS ? "".UCMS_URL."user/".$user->get_user_login($author) : "".UCMS_URL."?action=profile&amp;id=".$author;
		if(!$guest)
			$message = $ucms->cout("plugin.notifications.comment_added.message.user", true, SITE_NAME, $user_link, $user->get_user_login($author), $ucms->get_date(), $comment, UCMS_URL);
		else
			$message = $ucms->cout("plugin.notifications.comment_added.message.guest", true, SITE_NAME, $author, $email, $ucms->get_date(), $comment, UCMS_URL);
		mail(COMMENTS_EMAIL, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers."Content-type:text/html; Charset=utf-8\r\n");
	}
}

function post_added($id, $title){
	global $ucms, $user;
	$group = $user->get_user_group($id);
	$observed_user_groups = explode(",", POSTS_OBSERVED_USER_GROUPS);
	if(POSTS_EMAIL != '' and POSTS_NOTIFICATION and in_array($group, $observed_user_groups)){
		$date = $ucms->get_date();
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$group_name = $user->get_group_name($group);
		$login = $user->get_user_login($id);
		$email = $user->get_user_email($id);
		$a_headers = "Content-type:text/html; charset=utf-8\r\n";
		$a_subject = $ucms->cout("plugin.notifications.post_added.subject", true);
		$a_message = $ucms->cout("plugin.notifications.post_added.message", true, SITE_NAME, $date, $title, $login, $group_name, $email, UCMS_URL);
		$a_headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$a_sent = mail(POSTS_EMAIL, '=?UTF-8?B?'.base64_encode($a_subject).'?=', $a_message, $a_headers);
	}
}

function page_added($id, $title){
	global $ucms, $user;
	$group = $user->get_user_group($id);
	$observed_user_groups = explode(",", PAGES_OBSERVED_USER_GROUPS);
	if(PAGES_EMAIL != '' and PAGES_NOTIFICATION and in_array($group, $observed_user_groups)){
		$date = $ucms->get_date();
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$group_name = $user->get_group_name($group);
		$login = $user->get_user_login($id);
		$email = $user->get_user_email($id);
		$a_headers = "Content-type:text/html; charset=utf-8\r\n";
		$a_subject = $ucms->cout("plugin.notifications.page_added.subject", true);
		$a_message = $ucms->cout("plugin.notifications.page_added.message", true, SITE_NAME, $date, $title, $login, $group_name, $email, UCMS_URL);
		$a_headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$a_sent = mail(PAGES_EMAIL, '=?UTF-8?B?'.base64_encode($a_subject).'?=', $a_message, $a_headers);
	}
}

function user_registered($login, $email){
	global $ucms;
	if(NEW_USER_EMAIL != '' and NEW_USERS_NOTIFICATION){
		$date = $ucms->get_date();
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$a_headers = "Content-type:text/html; charset=utf-8\r\n";
		$a_subject = $ucms->cout("plugin.notifications.user_registered.subject", true, $login);
		$a_message = $ucms->cout("plugin.notifications.user_registered.message", true, SITE_NAME, $date, $login, $email, UCMS_URL);
		$a_headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$a_sent = mail(NEW_USER_EMAIL, '=?UTF-8?B?'.base64_encode($a_subject).'?=', $a_message, $a_headers);
	}
}

function user_logged_in($id){
	global $ucms, $user;
	$group = $user->get_user_group($id);
	$observed_user_groups = explode(",", OBSERVED_USER_GROUPS);
	if(USER_LOGGED_IN_EMAIL != '' and USER_LOGGED_IN_NOTIFICATIONS and in_array($group, $observed_user_groups)){
		$date = $ucms->get_date();
		$domain = preg_replace("#(http://)#", '', SITE_DOMAIN);
		$group_name = $user->get_group_name($group);
		$login = $user->get_user_login($id);
		$email = $user->get_user_email($id);
		$a_headers = "Content-type:text/html; charset=utf-8\r\n";
		$a_subject = $ucms->cout("plugin.notifications.user_logged_in.subject", true, $login);
		$a_message = $ucms->cout("plugin.notifications.user_logged_in.message", true, SITE_NAME, $date, $login, $group_name, $email);
		$a_headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain.'>'."\r\n";
		$a_sent = mail(USER_LOGGED_IN_EMAIL, '=?UTF-8?B?'.base64_encode($a_subject).'?=', $a_message, $a_headers);
	}
}

?>