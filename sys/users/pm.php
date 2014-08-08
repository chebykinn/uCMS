<?php
class pm extends uSers{
	function list_messages(){
		global $udb, $ucms, $profile_login;
		if(!USER_MESSAGES) return false;
		$id = $this->get_user_id();
		$where1 = '';
		$where2 = '';
		$inbox_empty = "<br><br>Вам еще никто не писал.<br>";
		if(!$this->is_profile()){
			$user_id = $this->get_user_id($profile_login);
			$where1 = "AND `author` = '$user_id'";
			$where2 = "AND `receiver` = '$user_id'";
			$inbox_empty = "<br><br>Пользователь вам еще не писал.<br>";
		}

		$inbox = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `receiver` = '$id' $where1");
		$sent = $udb->get_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `author` = '$id' $where2");
		$num1 = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `receiver` = '$id' $where1");
		$num2 = $udb->num_rows("SELECT * FROM `".UC_PREFIX."messages` WHERE `author` = '$id' $where2");
		echo '<br><b>Входящие:</b>';
		if($num1 > 0){
			echo '<table style="border-spacing: 10px; width: 300px; padding: 10px; text-align: center;">';
			for($i = 0; $i < $num1; $i++){
				$author = $this->get_user_login($inbox[$i]['author']);
				$author_link = NICE_LINKS ? UCMS_DIR.'/user/'.$author : UCMS_DIR.'/?action=profile&amp;id='.$inbox[$i]['author']; 
				echo '<tr>';
				echo '<th>Отправитель</th>';
				echo '<th>Текст сообщения</th>';
				echo '</tr>';
				echo '<tr>';
				echo '<td><a href="'.$author_link.'">'.$author.'</a></td>';
				echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($inbox[$i]['text']).'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}else echo $inbox_empty;
		echo '<br><b>Отправленные:</b>';
		if($num2 > 0){
			echo '<table style="border-spacing: 10px; width: 300px; padding: 10px;  text-align: center;">';
			for($i = 0; $i < $num2; $i++){
				$receiver = $this->get_user_login($sent[$i]['receiver']);
				$receiver_link = NICE_LINKS ? UCMS_DIR.'/user/'.$receiver : UCMS_DIR.'/?action=profile&amp;id='.$sent[$i]['receiver']; 
				echo '<tr>';
				echo '<th>Получатель</th>';
				echo '<th>Текст сообщения</th>';
				echo '</tr>';
				echo '<tr>';
				echo '<td><a href="'.$receiver_link.'">'.$receiver.'</a></td>';
				echo '<td style="word-wrap: break-word; width: 200px;">'.htmlspecialchars($sent[$i]['text']).'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}else echo '<br><br>Вы еще не отправляли сообщений.<br>';
	}

	function send_message_form(){
		global $profile_login;
		if(!USER_MESSAGES) return false;
		$from = NICE_LINKS ? UCMS_DIR."/user/".$this->get_user_login() : UCMS_DIR."/?action=profile&amp;id=".$this->get_user_id();
		if($this->get_user_group() != 5){
		?>
		<form action="<?php echo $from ?>" method="post">
			<?php if($this->is_profile()){ ?><br><b>Кому:</b><br>
			<input type="text" name="receiver" style="width: 200px;" required><?php }else{ ?>
			<input type="hidden" name="receiver" value="<?php echo $profile_login ?>"><?php } ?>
			<br><b>Текст:</b><br>
			<textarea name="message" style="width: 350px; height: 150px;" required></textarea>
			<br><input type="submit" value="Отправить">
		</form>
		<?php
		}else{
			echo "<div class=\"error\">Вы не можете писать сообщения, так как вы забанены.</a>";
		}
	}

	function send_message($p){
		global $user, $udb, $ucms;
		if(!USER_MESSAGES) return false;
		if(isset($p['message']) and isset($p['receiver']) and $this->get_user_group() != 5){
			if($this->logged()){
				$author = $this->get_user_id();
				
				$receiver_array = explode(", " , $udb->parse_value($p['receiver']));
				if(isset($receiver_array[1]) and $receiver_array[1] != ''){
					for($i = 0; $i < count($receiver_array); $i++){
						$receiver_array[$i] = $this->get_user_id($receiver_array[$i]);
						if($receiver_array[$i]){
							$text = $udb->parse_value($p['message']);				
							if($author != $receiver_array[$i]){
								if($this->get_user_pm_subscription($receiver_array[$i])){
									$author_login = $this->get_user_login($author);
									$receiver_login = $this->get_user_login($receiver_array[$i]);
									$author_login_link = NICE_LINKS ? "<a href=\"".UCMS_URL."/user/$author_login\">$author_login</a>" : "<a href=\"".UCMS_URL."/?action=profile&amp;id=$author\">$author_login</a>";
									$receiver_login_link = NICE_LINKS ? "<a href=$receiver_array[$i]\"".UCMS_URL."/user/$receiver_login\">Прочитать</a>" : "<a href=\"".UCMS_URL."/?action=profile&amp;id=$author\">Прочитать</a>";
									$login = $this->get_user_login($receiver_array[$i]);
									$domain2 = preg_replace('#(http://)#', '', SITE_DOMAIN);
									$headers = "Content-type:text/html; charset=utf-8\r\n";
									$subject = "Вам пришло личное сообщение";
									$message = "Здравствуйте, $login! <br>
										Вам пришло личное сообщение от $author_login:<br>
										$receiver_login_link
										<hr>
										$text
										<hr>
										<br>Написать в ответ $author_login_link.
										<br> <br> С уважением, Администрация ".SITE_NAME.".";
									$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <uCMS@'.$domain2.'>'."\r\n";
									$sent = mail($this->get_user_email($receiver_array[$i]), '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
								}
								$send = $udb->query("INSERT INTO `".UC_PREFIX."messages` VALUES(null, '$author', '$receiver_array[$i]', NOW(), '$text', '0')");
								
							}else{
								echo 'Вы не можете отправлять сообщения самому себе.';
							}
						}else{
							echo 'Такого пользователя не существует.';
						}
					}
					if($send){
						header("Location: ".$ucms->get_back_url());
						$_SESSION['success_sent'] = true;
					}
				}else{
					$receiver = $this->get_user_id($udb->parse_value($p['receiver']));					
					if($receiver){
						$text = $udb->parse_value($p['message']);				
						if($author != $receiver){
							if($this->get_user_pm_subscription($receiver)){
								$author_login = $this->get_user_login($author);
								$receiver_login = $this->get_user_login($receiver);
								$author_login_link = NICE_LINKS ? "<a href=\"".UCMS_URL."/user/$author_login\">$author_login</a>" : "<a href=\"".UCMS_URL."/?action=profile&amp;id=$author\">$author_login</a>";
								$receiver_login_link = NICE_LINKS ? "<a href=\"".UCMS_URL."/user/$receiver_login\">Прочитать</a>" : "<a href=\"".UCMS_URL."/?action=profile&amp;id=$author\">Прочитать</a>";
								$login = $this->get_user_login($receiver);
								$domain2 = preg_replace('#(http://)#', '', SITE_DOMAIN);
								$headers = "Content-type:text/html; charset=utf-8\r\n";
								$subject = "Вам пришло личное сообщение";
								$message = "Здравствуйте, $login! <br>
									Вам пришло личное сообщение от $author_login:<br>
									$receiver_login_link
									<hr>
									$text
									<hr>
									<br>Написать в ответ $author_login_link.
									<br> <br> С уважением, Администрация ".SITE_NAME.".";
								$headers .= 'From: '.'=?UTF-8?B?'.base64_encode(SITE_NAME).'?='.' <noreply@'.$domain2.'>'."\r\n";
								$sent = mail($this->get_user_email($receiver), '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
							}
							$send = $udb->query("INSERT INTO `".UC_PREFIX."messages` VALUES(null, '$author', '$receiver', NOW(), '$text', '0')");
							header("Location: ".$ucms->get_back_url());
							$_SESSION['success_sent'] = true;
						}else{
							echo 'Вы не можете отправлять сообщения самому себе.';
						}
					}else{
						echo 'Такого пользователя не существует.';
					}
				
				}
			}
		}else{
			echo 'Вы заполнили не все поля.';
		}
	
	}

	function header_messages(){
		if(isset($_POST['message']) and isset($_POST['receiver'])){
			echo '<div class="error">';
			$this->send_message($_POST);
			echo '</div>';
		}elseif(isset($_SESSION['success_sent'])){
			echo '<div class="success">Ваше сообщение было успешно отправлено.</div>';
			unset($_SESSION['success_sent']);
		}
	}

	function count_unreaded($user_id){
		global $udb;
		$user_id = (int) $user_id;
		$messages = $udb->num_rows("SELECT `id` FROM `".UC_PREFIX."messages` WHERE `receiver` = '$user_id' AND `readed` = '0'");
 		return $messages;
	}

	function is_new_messages($user_id){
		$messages = $this->count_unreaded($user_id);
		if($messages > 0) return true;
		else return false;
	}

	function show_alert($user_id){
		global $udb;
		$messages = $this->count_unreaded($user_id);
		if($messages > 0){
			echo '<div class="warning">Непрочитанных сообщений: '.$messages.'</div>';
		}
		$udb->query("UPDATE `".UC_PREFIX."messages` SET `readed` = '1' WHERE `receiver` = '$user_id'");
	}
}
?>