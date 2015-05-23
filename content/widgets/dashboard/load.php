<?php
$users = new Query("{users}");
$this->usersAmount = $users->countRows()->execute();
$groups = new Query("{groups}");
$this->groupsAmount = $groups->countRows()->execute();
$this->extensionsAmount = count(Extensions::getAll());
?>