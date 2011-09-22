<?php


error_reporting(-1);
ini_set('display_errors', true);



// Inclusion des fichiers de la librairie
require_once '../DbModel.php';



// Initialisation de la DB
// (à faire dans un fichier config-database.php par ex)
DB::Construct(array(
	'host'     => 'localhost',
	'user'     => 'root',
	'pass'     => '',
	'database' => 'test',
	'prefix'   => '',
	'charset'  => 'utf8',
	'debug'    => true,
));



// Déclaration d'un Model
// (à placer dans un fichier spécifique)
class Author extends DbModel {
	
	static $primary_key = 'author_id';
	
	static $validations = array(
		'name' => array(
/*
			'presence'  => array('message' => "{FIELD_NAME} est obligatoire"),
			'length'    => array(
					array('message' => "{FIELD_NAME} est trop court (min {MIN} car.)", 'min' => 5),
					array('message' => "{FIELD_NAME} est trop long", 'max' => 15),
				),
			'inclusion' => array('in' => array('John', 'Jean')),
			'exclusion' => array('in' => array('Admin', 'Administrator', 'Administrateur')),
*/
		),
	);
	
}

class Book extends DbModel {}



// C'est parti !
echo '<pre>';


var_dump(array(
	Author::table_name(),
	Book::table_name(),
));



?>