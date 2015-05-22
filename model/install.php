<?php
$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'twenga` (
	`hash_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';