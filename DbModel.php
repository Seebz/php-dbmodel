<?php


// Vérification de la version PHP
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('PHP DbModel nécessite au minimum PHP 5.3');
}


// Inclusion des fichiers de la librairie
require_once __DIR__ . '/lib/DB.php';
require_once __DIR__ . '/lib/DbTable.php';
require_once __DIR__ . '/lib/Model.php';
require_once __DIR__ . '/lib/DbModel.php';
require_once __DIR__ . '/lib/Validator.php';
require_once __DIR__ . '/lib/Inflector.php';


?>