<?php
add_url_action("index", POSTS_MODULE_PATH, 'posts-load.php');
add_url_action("category", POSTS_MODULE_PATH, 'posts-load.php');
add_url_action("archive", POSTS_MODULE_PATH);
add_url_action("rss", POSTS_MODULE_PATH, 'rss.php');
add_url_action("other", POSTS_MODULE_PATH, 'posts-load.php');
add_url_action(TAG_SEF_PREFIX, POSTS_MODULE_PATH, 'tags-load.php');
?>