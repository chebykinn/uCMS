<?php
	header('Content-type: application/xml');
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n";
	echo "<rss version=\"2.0\"
	xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"
	xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\"
	xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
	xmlns:atom=\"http://www.w3.org/2005/Atom\"
	xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\"
	xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\">
	\n\n";
	$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR."/rss" : SITE_DOMAIN.UCMS_DIR."/?action=rss";
	echo "<channel>\n";
	echo "<title>".SITE_TITLE."</title>\n";
	echo "<link>$link</link>\n";
	echo "<description>".SITE_DESCRIPTION."</description>\n";
	echo "<copyright>© ".SITE_AUTHOR."," . date("Y") . "</copyright>\n";
	echo "<language>ru</language>\n\n";
	
	$post_sql = "SELECT * FROM `".UC_PREFIX."posts` WHERE `publish` > 0 ORDER BY `date` DESC, `id` DESC LIMIT 0, 50";
	require 'include/posts.php';
	if (is_posts()) {
		for ($post = 0; $post < posts_count(); $post++) {
			if(preg_match("#(<!--more-->)#", $posts[$post]['body'])){
				$da_post = explode('<!--more-->', $posts[$post]['body']);
				$body = $da_post[0];
			}elseif(preg_match("#(@-more-@)#", $posts[$post]['body'])){
				$da_post = explode('@-more-@', $posts[$post]['body']);
				if(isset($da_post[1])){
					$body = $da_post[0];
				}
				
			}else $body = $posts[$post]['body'];
			$body = str_replace("\n", "<br>", $body);
			$body = '<![CDATA['.$body.']]>';
			$date_string = date($posts[$post]['date']);
			$link = NICE_LINKS ? SITE_DOMAIN.UCMS_DIR.get_post_alias() : SITE_DOMAIN.UCMS_DIR."?id=".$posts[$post]['id'];
			$more = htmlspecialchars('<a href="'.$link.'">Читать далее</a>');

			echo "<item>\n";
			echo "<title>".$posts[$post]['title']."</title>\n";
			echo "<link>".$link."</link>\n";
			echo "<description>".$body."...".$more."</description>\n";
			echo "<pubDate>".$date_string."</pubDate>\n";
			echo "<guid isPermaLink=\"true\">".$link."</guid>\n";
			echo "<dc:creator>".get_post_author()."</dc:creator>\n";
			echo "</item>\n\n";
		}
	}
	echo "</channel>\n";
	echo "</rss>\n";
?>