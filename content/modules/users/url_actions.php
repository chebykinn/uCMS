<?php

add_url_action('logout', USERS_MODULE_PATH, 'logout.php');
add_url_action('login', USERS_MODULE_PATH);
add_url_action('profile', USERS_MODULE_PATH);
add_url_action('registration', USERS_MODULE_PATH, 'registration.php');
add_url_action('reset', USERS_MODULE_PATH, 'reset.php');
add_url_action('activation', USERS_MODULE_PATH, 'activation.php');
add_url_action('user', USERS_MODULE_PATH, 'profile-load.php');
add_url_action('users', USERS_MODULE_PATH, 'userlist.php');


?>