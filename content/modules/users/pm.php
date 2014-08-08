<?php
/**
 *
 * uCMS User Messaging Class
 * @package Users Messaging
 * @since uCMS 1.2
 * @version uCMS 1.3
 *
*/
class Messaging extends uSers{

	function __construct($user){
		$this->id 			= $user->id;
		$this->login 		= $user->login;
		$this->password 	= $user->password;
		$this->group 		= $user->group;
		$this->avatar 		= $user->avatar;
		$this->email 		= $user->email;
		$this->activation 	= $user->activation;
		$this->date 		= $user->date;
		$this->session_hash = $user->session_hash;
		$this->regip 		= $user->regip;
		$this->logip 		= $user->logip;
		$this->online 		= $user->online;
		$this->lastlogin 	= $user->lastlogin;
	}

	/**
	 *
	 * Get inbox messages data
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_inbox_messages(){
		global $udb, $ucms, $profile_login, $user_id;
		if(!USER_MESSAGES) return false;
		$id = $this->get_user_id();
		$where = '';
		if(!$this->is_profile()){
			$where = "AND `author` = '$user_id'";
		}
		$inbox = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `receiver` = '$id' OR `receiver` = '0' $where");
		if($inbox and count($inbox) > 0){
			$authors = "";
			$receivers = "";
			for($i = 0; $i < count($inbox); $i++){
				$authors .= $inbox[$i]['author'];
				$receivers .= $inbox[$i]['receiver'];
				if($i+1 < count($inbox)) $authors .= "','";
				if($i+1 < count($inbox)) $receivers .= "','";
			}
			$authors = "'".$authors."'";
			$receivers = "'".$receivers."'";
			$logins = $udb->get_rows("SELECT `id`, `login`, `group`, `avatar` FROM `".UC_PREFIX."users` WHERE `id` in ($authors) ");
			$logins2 = $udb->get_rows("SELECT `id`, `login`, `group`, `avatar` FROM `".UC_PREFIX."users` WHERE `id` in ($receivers) ");

			$nicknames = $udb->get_rows("SELECT `user_id`, `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` in ($authors) AND `name` = 'nickname'");
			$nicknames2 = $udb->get_rows("SELECT `user_id`, `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` in ($receivers) AND `name` = 'nickname'");
			for($i = 0; $i < count($inbox); $i++){
				for($j = 0; $j < count($logins); $j++){
					if(isset($inbox[$i]['author_login']) and ($inbox[$i]['author'] === $logins[$j]['id'])) break;
					if($inbox[$i]['author'] === $logins[$j]['id']){
						$inbox[$i]['author_login'] = $logins[$j]['login'];
						$inbox[$i]['author_group'] = $logins[$j]['group'];
						$inbox[$i]['author_avatar'] = $logins[$j]['avatar'];
						break;
					}
				}

				for($j = 0; $j < count($logins2); $j++){
					if($inbox[$i]['receiver'] == 0){
						$inbox[$i]['receiver_login'] = $ucms->cout("module.users.pm.all_users.label", true);
						$inbox[$i]['receiver_group'] = USER_GROUP_ID;
						$inbox[$i]['receiver_avatar'] = 'no-avatar.jpg';
					}
					if(isset($inbox[$i]['receiver_login']) and ($inbox[$i]['receiver'] === $logins2[$j]['id'])) break;
					if($inbox[$i]['receiver'] === $logins2[$j]['id']){
						$inbox[$i]['receiver_login'] = $logins2[$j]['login'];
						$inbox[$i]['receiver_group'] = $logins2[$j]['group'];
						$inbox[$i]['receiver_avatar'] = $logins2[$j]['avatar'];
						break;
					}
				}

				for($j = 0; $j < count($nicknames); $j++){
					if(!empty($inbox[$i]['author_nickname']) and ($check_id === $nicknames[$j]['user_id'])) break;
					if($inbox[$i]['author'] === $nicknames[$j]['user_id']){
						$inbox[$i]['author_nickname'] = $nicknames[$j]['value'];
						$check_id = $nicknames[$j]['user_id'];
						break;
					}else{
						$inbox[$i]['author_nickname'] = "";
						$check_id = $nicknames[$j]['user_id'];
					}
				}

				for($j = 0; $j < count($nicknames2); $j++){
					if(!empty($inbox[$i]['receiver_nickname']) and ($check_id === $nicknames2[$j]['user_id'])) break;
					if($inbox[$i]['receiver'] === $nicknames2[$j]['user_id']){
						$inbox[$i]['receiver_nickname'] = $nicknames2[$j]['value'];
						$check_id = $nicknames2[$j]['user_id'];
						break;
					}else{
						$inbox[$i]['receiver_nickname'] = "";
						$check_id = $nicknames2[$j]['user_id'];
					}
				}
			}
			return $inbox;
		}
		return false;
	}

	/**
	 *
	 * Get outbox messages data
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_outbox_messages(){
		global $udb, $ucms, $profile_login;
		if(!USER_MESSAGES) return false;
		$id = $this->get_user_id();
		$where = '';
		if(!$this->is_profile()){
			$user_id = $this->get_user_id($profile_login);
			$where = "AND `receiver` = '$user_id'";
		}
		$outbox = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `author` = '$id' $where");
		if($outbox and count($outbox) > 0){
			$authors = "";
			$receivers = "";
			for($i = 0; $i < count($outbox); $i++){
				$authors .= $outbox[$i]['author'];
				$receivers .= $outbox[$i]['receiver'];
				if($i+1 < count($outbox)) $authors .= "','";
				if($i+1 < count($outbox)) $receivers .= "','";
			}
			$authors = "'".$authors."'";
			$receivers = "'".$receivers."'";
			$logins = $udb->get_rows("SELECT `id`, `login`, `group`, `avatar` FROM `".UC_PREFIX."users` WHERE `id` in ($authors) ");
			$logins2 = $udb->get_rows("SELECT `id`, `login`, `group`, `avatar` FROM `".UC_PREFIX."users` WHERE `id` in ($receivers) ");

			$nicknames = $udb->get_rows("SELECT `user_id`, `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` in ($authors) AND `name` = 'nickname'");
			$nicknames2 = $udb->get_rows("SELECT `user_id`, `value` FROM `".UC_PREFIX."usersinfo` WHERE `user_id` in ($receivers) AND `name` = 'nickname'");
			for($i = 0; $i < count($outbox); $i++){
				for($j = 0; $j < count($logins); $j++){
					if(isset($outbox[$i]['author_login']) and ($outbox[$i]['author'] === $logins[$j]['id'])) break;
					if($outbox[$i]['author'] === $logins[$j]['id']){
						$outbox[$i]['author_login'] = $logins[$j]['login'];
						$outbox[$i]['author_group'] = $logins[$j]['group'];
						$outbox[$i]['author_avatar'] = $logins[$j]['avatar'];
						break;
					}
				}

				for($j = 0; $j < count($logins2); $j++){
					if($outbox[$i]['receiver'] == 0){
						$outbox[$i]['receiver_login'] = $ucms->cout("module.users.pm.all_users.label", true);
						$outbox[$i]['receiver_group'] = USER_GROUP_ID;
						$outbox[$i]['receiver_avatar'] = 'no-avatar.jpg';
					}
					if(isset($outbox[$i]['receiver_login']) and ($outbox[$i]['receiver'] === $logins2[$j]['id'])) break;
					if($outbox[$i]['receiver'] === $logins2[$j]['id']){
						$outbox[$i]['receiver_login'] = $logins2[$j]['login'];
						$outbox[$i]['receiver_group'] = $logins2[$j]['group'];
						$outbox[$i]['receiver_avatar'] = $logins2[$j]['avatar'];
						break;
					}
				}

				for($j = 0; $j < count($nicknames); $j++){
					if(!empty($outbox[$i]['author_nickname']) and ($check_id === $nicknames[$j]['user_id'])) break;
					if($outbox[$i]['author'] === $nicknames[$j]['user_id']){
						$outbox[$i]['author_nickname'] = $nicknames[$j]['value'];
						$check_id = $nicknames[$j]['user_id'];
						break;
					}else{
						$outbox[$i]['author_nickname'] = "";
						$check_id = $nicknames[$j]['user_id'];
					}
				}

				for($j = 0; $j < count($nicknames2); $j++){
					if(!empty($outbox[$i]['receiver_nickname']) and ($check_id === $nicknames2[$j]['user_id'])) break;
					if($outbox[$i]['receiver'] === $nicknames2[$j]['user_id']){
						$outbox[$i]['receiver_nickname'] = $nicknames2[$j]['value'];
						$check_id = $nicknames2[$j]['user_id'];
						break;
					}else{
						$outbox[$i]['receiver_nickname'] = "";
						$check_id = $nicknames2[$j]['user_id'];
					}
				}
			}
			return $outbox;
		}
		return false;
	}

	/**
	 *
	 * List messages for user
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function list_messages(){
		global $udb, $ucms, $profile_login;
		if(!USER_MESSAGES) return false;
		$id = $this->get_user_id();
		$where1 = '';
		$where2 = '';
		$inbox_empty = $ucms->cout("module.users.pm.inbox_empty.local.label", true);
		if(!$this->is_profile()){
			$user_id = $this->get_user_id($profile_login);
			$where1 = "AND `author` = '$user_id'";
			$where2 = "AND `receiver` = '$user_id'";
			$inbox_empty = $ucms->cout("module.users.pm.inbox_empty.user.label", true);
		}

		$inbox = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `receiver` = '$id' $where1");
		$sent = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `author` = '$id' $where2");
		$num1 = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `receiver` = '$id' $where1");
		$num2 = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `author` = '$id' $where2");
		$ucms->cout("module.users.pm.inbox.label");
		if($num1 > 0){
			echo '<table style="border-spacing: 10px; width: 300px; padding: 10px; text-align: center;">';
			for($i = 0; $i < $num1; $i++){
				$author = $this->get_user_login($inbox[$i]['author']);
				$author_link = NICE_LINKS ? UCMS_DIR.'/user/'.$author : UCMS_DIR.'/?action=profile&amp;id='.$inbox[$i]['author']; 
				echo '<tr>';
				echo '<th>'.$ucms->cout("module.users.pm.table.header.author", true).'</th>';
				echo '<th>'.$ucms->cout("module.users.pm.table.header.message", true).'</th>';
				echo '</tr>';
				echo '<tr>';
				echo '<td><a href="'.$author_link.'">'.$author.'</a></td>';
				echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($inbox[$i]['text']).'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}else echo $inbox_empty;
		$ucms->cout("module.users.pm.outbox.label");
		if($num2 > 0){
			echo '<table style="border-spacing: 10px; width: 300px; padding: 10px;  text-align: center;">';
			for($i = 0; $i < $num2; $i++){
				$receiver = $this->get_user_login($sent[$i]['receiver']);
				$receiver_link = NICE_LINKS ? UCMS_DIR.'/user/'.$receiver : UCMS_DIR.'/?action=profile&amp;id='.$sent[$i]['receiver']; 
				echo '<tr>';
				echo '<th>'.$ucms->cout("module.users.pm.table.header.receiver", true).'</th>';
				echo '<th>'.$ucms->cout("module.users.pm.table.header.message", true).'</th>';
				echo '</tr>';
				echo '<tr>';
				echo '<td><a href="'.$receiver_link.'">'.$receiver.'</a></td>';
				echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($sent[$i]['text']).'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}else $ucms->cout("module.users.pm.no_outbox.label");
	}

	/**
	 *
	 * Print form to send messages
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function send_message_form(){
		global $profile_login, $ucms;
		if(!USER_MESSAGES) return false;
		$from = NICE_LINKS ? UCMS_DIR."/user/".$this->get_user_login() : UCMS_DIR."/?action=profile&amp;id=".$this->get_user_id();
		if($this->get_user_group() != BANNED_GROUP_ID){
			$ucms->template(get_module("path", "users")."forms/send_message_form.php", true, $from, $profile_login);
		}else{
			echo "<div class=\"error\">".$ucms->cout("module.users.pm.error.no_allowed_to_send", true)."</a>";
		}
	}

	/**
	 *
	 * Handle of sending messages
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function send_message($p){
		global $user, $udb, $ucms, $event;
		if(!USER_MESSAGES) return false;
		if(isset($p['message']) and isset($p['receiver']) and $this->get_user_group() != BANNED_GROUP_ID){
			if($this->logged()){
				$author = $this->get_user_id();
				
				$receiver_array = explode(", " , $udb->parse_value($p['receiver']));
				if(isset($receiver_array[1]) and $receiver_array[1] != ''){
					for($i = 0; $i < count($receiver_array); $i++){
						$receiver_array[$i] = $this->get_user_id($receiver_array[$i]);
						if($receiver_array[$i]){
							$text = $udb->parse_value($p['message']);				
							if($author != $receiver_array[$i]){
								if($this->get_user_info('pm_alert', $receiver_array[$i])){
									$author_login = $this->get_user_login($author);
									$receiver_login = $this->get_user_login($receiver_array[$i]);
									$author_login_link = NICE_LINKS ? "<a href=\"".UCMS_URL."user/$author_login/messages\">$author_login</a>" : "<a href=\"".UCMS_URL."?action=profile&amp;id=$author&amp;messages\">$author_login</a>";
									$receiver_login_link = NICE_LINKS ? 
									"<a href=$receiver_array[$i]\"".UCMS_URL."user/$receiver_login/messages\">".$ucms->cout("module.users.pm.receiver_login_link", true)."</a>"
									 : "<a href=\"".UCMS_URL."?action=profile&amp;id=$author&amp;messages\">".$ucms->cout("module.users.pm.receiver_login_link", true)."</a>";
									$login = $this->get_user_login($receiver_array[$i]);
									$domain2 = preg_replace('#(http://)#', '', SITE_DOMAIN);
									$headers = "Content-type:text/html; charset=utf-8\r\n";
									$subject = $ucms->cout("module.users.pm.email.subject.label", true);
									$message = $ucms->cout("module.users.pm.email.message", true, $login, $author_login, $receiver_login_link, $text, $author_login_link, SITE_NAME);
									$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain2.'>'."\r\n";
									$sent = mail($this->get_user_email($receiver_array[$i]), '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
								}
								$send = $udb->query("INSERT INTO `".UC_PREFIX."messages` (`id`, `author`, `receiver`, `date`, `text`, `readed`)
									VALUES (null, '$author', '$receiver_array[$i]', NOW(), '$text', '0')");
								
							}else{
								$ucms->cout("module.users.pm.error.message_to_yourself");
							}
						}else{
							$ucms->cout("module.users.pm.error.no_such_user");
						}
					}
					if($send){
						$event->do_actions("user.message.sent");
						header("Location: ".$ucms->get_back_url());
						$_SESSION['success_sent'] = true;
					}
				}elseif($p['receiver'] === '*' and $user->has_access("users", 5)){
					$receiver = 0;
					$text = $udb->parse_value($p['message']);
					$send = $udb->query("INSERT INTO `".UC_PREFIX."messages` (`id`, `author`, `receiver`, `date`, `text`, `readed`)
					VALUES (null, '$author', '$receiver', NOW(), '$text', '0')");
					$event->do_actions("user.message.sent");
					header("Location: ".$ucms->get_back_url());
					$_SESSION['success_sent'] = true;
				}else{
					$receiver = $this->get_user_id($udb->parse_value($p['receiver']));					
					if($receiver){
						$text = $udb->parse_value($p['message']);				
						if($author != $receiver){
							if($this->get_user_info('pm_alert', $receiver)){
								$author_login = $this->get_user_login($author);
								$receiver_login = $this->get_user_login($receiver);
								$author_login_link = NICE_LINKS ? "<a href=\"".UCMS_URL."user/$author_login/messages\">$author_login</a>" : "<a href=\"".UCMS_URL."?action=profile&amp;id=$author&amp;messages\">$author_login</a>";
								$receiver_login_link = NICE_LINKS ?
								 "<a href=\"".UCMS_URL."user/$receiver_login/messages\">".$ucms->cout("module.users.pm.receiver_login_link", true)."</a>"
								 : "<a href=\"".UCMS_URL."?action=profile&amp;id=$author&amp;messages\">".$ucms->cout("module.users.pm.receiver_login_link", true)."</a>";
								$login = $this->get_user_login($receiver);
								$domain2 = preg_replace('#(http://)#', '', SITE_DOMAIN);
								$headers = "Content-type:text/html; charset=utf-8\r\n";
								$subject = $ucms->cout("module.users.pm.email.subject.label", true);
								$message = $ucms->cout("module.users.pm.email.message", true, $login, $author_login, $receiver_login_link, $text, $author_login_link, SITE_NAME);
								$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain2.'>'."\r\n";
								$sent = mail($this->get_user_email($receiver), '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
							}
							$send = $udb->query("INSERT INTO `".UC_PREFIX."messages` (`id`, `author`, `receiver`, `date`, `text`, `readed`)
								VALUES(null, '$author', '$receiver', NOW(), '$text', '0')");
							$event->do_actions("user.message.sent");
							header("Location: ".$ucms->get_back_url());
							$_SESSION['success_sent'] = true;
						}else{
							$ucms->cout("module.users.pm.error.message_to_yourself");
						}
					}else{
						$ucms->cout("module.users.pm.error.no_such_user");
					}
				
				}
			}
		}else{
			$ucms->cout("module.users.pm.error.empty_fields");
		}
	
	}

	/**
	 *
	 * Alert user of message status
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function header_messages(){
		global $ucms;
		if(isset($_POST['message']) and isset($_POST['receiver'])){
			echo '<div class="error">';
			$this->send_message($_POST);
			echo '</div>';
		}elseif(isset($_SESSION['success_sent'])){
			echo '<div class="success">'.$ucms->cout("module.users.pm.alert.success.sent", true).'</div>';
			unset($_SESSION['success_sent']);
		}
		if(isset($_POST['delete_message_id'])){
			$this->delete_message($_POST['delete_message_id']);
		}elseif (isset($_SESSION['success_del'])) {
			echo '<div class="success">'.$ucms->cout("module.users.pm.alert.success.deleted", true).'</div>';
			unset($_SESSION['success_del']);
		}
	}

	/**
	 *
	 * Delete user message
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function delete_message($id){
		global $event;
		$id = (int) $id;
		if($id <= 0) return false;

		global $udb, $user_id, $user, $ucms;
		$test = $udb->get_row("SELECT `author` FROM `".UC_PREFIX."messages` WHERE `id` = '$id'");
		if($test and isset($test['author'])){
			$accessLVL = $user->is_admin($test['author']) ? 7 : 5;
			if($test['author'] == $user_id || $user->has_access("users", $accessLVL)){
				$del = $udb->query("DELETE FROM `".UC_PREFIX."messages` WHERE `id` = '$id'");
				if($del){
					$event->do_actions("user.message.deleted");
					header("Location:".$ucms->get_back_url());
					$_SESSION['success_del'] = true;
				}
			}
		} 
	}

	/**
	 *
	 * Get number of unread messages
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function count_unread($user_id){
		global $udb;
		$user_id = (int) $user_id;
		$messages = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."messages` WHERE `receiver` = '$user_id' AND `readed` = '0'");
 		return (int) $messages;
	}

	/**
	 *
	 * Check is there new messages for current user
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function is_new_messages($user_id){
		$messages = $this->count_unread($user_id);
		if($messages > 0) return true;
		else return false;
	}

	/**
	 *
	 * Show alert on new messages for current user
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function show_alert($user_id){
		global $udb, $ucms, $user_messages_page;
		$messages = $this->count_unread($user_id);
		if($messages > 0){
			echo '<div class="warning">'.$ucms->cout("module.users.pm.alert.warning.unread_messages", true, $messages).'</div>';
		}
		if(!empty($user_messages_page))
			$udb->query("UPDATE `".UC_PREFIX."messages` SET `readed` = '1' WHERE `receiver` = '$user_id'");
	}

	/**
	 *
	 * Check if current user have inbox messages
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function have_inbox_messages(){
		global $messages_inbox;
		if($messages_inbox and count($messages_inbox) > 0){
			return true;
		}
	}

	/**
	 *
	 * Check if current user have outbox messages
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function have_outbox_messages(){
		global $messages_outbox;
		if($messages_outbox and count($messages_outbox) > 0){
			return true;
		}
	}

	/**
	 *
	 * Get amount of inbox messages for current user
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function inbox_count(){
		global $messages_inbox;
		if($messages_inbox and count($messages_inbox) > 0){
			return count($messages_inbox);
		}
		return 0;
	}

	/**
	 *
	 * Get amount of outbox messages for current user
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function outbox_count(){
		global $messages_outbox;
		if($messages_outbox and count($messages_outbox) > 0){
			return count($messages_outbox);
		}
		return 0;
	}

	/**
	 *
	 * Get $column from array of $messages_outbox at current iterator $message
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_outbox_message($column, $message = -1, $messages_outbox = array()){
		$columns = array("id", "author", "receiver", "date", "text", "readed", "author_login", "author_avatar", "author_group", "receiver_login", "receiver_avatar", "receiver_group",
			"author_nickname", "receiver_nickname");
		if($message == -1)
			global $message;
		if(!isset($messages_outbox[0]))
			global $messages_outbox;
		if(in_array($column, $columns) and isset($messages_outbox[$message][$column]))
			return $messages_outbox[$message][$column];
		else return "null";
	}

	/**
	 *
	 * Get $column from array of $messages_inbox at current iterator $message
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_inbox_message($column, $message = -1, $messages_inbox = array()){
		$columns = array("id", "author", "receiver", "date", "text", "readed", "author_login", "author_avatar", "author_group", "receiver_login", "receiver_avatar", "receiver_group",
			"author_nickname", "receiver_nickname");
		if($message == -1)
			global $message;
		if(!isset($messages_inbox[0]))
			global $messages_inbox;
		if(in_array($column, $columns) and isset($messages_inbox[$message][$column]))
			return $messages_inbox[$message][$column];
		else return "null";
	}
}
?>