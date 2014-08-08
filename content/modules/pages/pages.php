<?php 
/**
 *
 * Pages API
 * @package uCMS Pages
 * @since uCMS 1.0
 * @version uCMS 1.3
 *
*/
	/**
	 *
	 * Check if it is page
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function page(){
		global $page_id, $user;
		if ($page_id != '' and $user->has_access("pages", 1)){
			return true;
		}
		return false;	
	}

	/**
	 *
	 * Print current page id
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_id(){
		global $page_id;
		if ($page_id != ''){
			echo $page_id;
		}
	}
	
	/**
	 *
	 * Print current page title from prepared array or from $id_page
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_title($id_page = ""){
		if($id_page == ""){
			global $id_page;
		}
		global $ucms;
		echo stripcslashes($id_page['title']);
		if($id_page['publish'] < 1) echo " (".$ucms->cout("module.pages.draft.label", true).")";
	}

	/**
	 *
	 * Print current page content from prepared array or from $id_page
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_content($id_page = ""){
		if($id_page == ""){
			global $id_page;
		}
		echo stripcslashes($id_page['body']);
	}

	/**
	 *
	 * Print current page author link or $plain author name from prepared array or from $id_page
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_author($plain = false, $id_page = ""){
		global $user;
		if($id_page == ""){
			global $id_page;
		}
		if($plain or (int) $id_page['author'] == 0){
			echo $id_page['author'];
		}else{
			if($id_page['author'] != $user->get_user_id()){
				$user_login = $user->get_user_login($id_page['author']);
				$user_nickname = $user->get_user_nickname($id_page['author']);
			}
			else {
				$user_login = $user->get_user_login();
				$user_nickname = $user->get_user_nickname();
			}
			$link = NICE_LINKS ? UCMS_DIR."/user/$user_login" : UCMS_DIR."/?action=profile&amp;id=$id_page[author]";
			echo "<a href=\"$link\">".(!empty($user_nickname) ? $user_nickname : $user_login)."</a>";
		}
	}

	/**
	 *
	 * Print current page date from prepared array or from $id_page
	 * @package uCMS Pages
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_date(){
		global $id_page, $ucms;
		echo $ucms->date_format($id_page['date'], DATE_FORMAT);
	}

	/**
	 *
	 * Print manage tools for current page from prepared array or from $id_page
	 * @package uCMS Pages
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function page_admin($page_id = 0, $id_page = ""){
		global $user, $ucms;
		if($id_page == ""){
			global $id_page;
		}
		if($page_id == 0){
			global $page_id;
		}
		if($user->has_access("pages", 2)){
			if($id_page['author'] == $user->get_user_id())
				$accessLVL = 2;
			elseif($user->get_user_group($id_page['author']) == 1){
				$accessLVL = 6;
			}else
				$accessLVL = 4;
			if ($page_id != '' and $user->has_access("pages", $accessLVL)){
				echo '<a href="'.UCMS_DIR.'/admin/manage.php?module=pages&amp;action=update&amp;id='.$page_id.'" >'.$ucms->cout("module.pages.site.edit.button", true).'</a>';
			}
		}
	}
?>