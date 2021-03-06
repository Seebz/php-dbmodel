<?php


error_reporting(-1);
ini_set('display_errors', true);



// Inclusion des fichiers de la librairie
require_once '../DbModel.php';



// Initialisation de la DB
// (à faire dans un fichier config-database.php par ex)
DbConnection::add('default', array(
	'host'     => 'localhost',
	'user'     => 'root',
	'pass'     => 'root',
	'database' => 'test',
	'prefix'   => '',
	'charset'  => 'utf8',
	'debug'    => true,
));



// Déclaration d'un Model
// (à placer dans un fichier spécifique)
class Author extends DbModel {
	
	static $primary_key = 'author_id';
	
/*
	static $validations = array(
		'name' => array(
			'presence'  => array('message' => "{FIELD_NAME} est obligatoire"),
			'length'    => array(
					array('message' => "{FIELD_NAME} est trop court (min {MIN} car.)", 'min' => 5),
					array('message' => "{FIELD_NAME} est trop long", 'max' => 15),
				),
			'inclusion' => array('in' => array('John', 'Jean')),
			'exclusion' => array('in' => array('Admin', 'Administrator', 'Administrateur')),
			'uniqueness' => array(),
		),
		'email' => array(
			'format' => array('type' => 'email'),
		),
	);
*/
	
}

class Book extends DbModel {
	static $primary_key = 'book_id';
}



// C'est parti !
echo '<pre>';


$author = Author::last();
$author->save();

var_dump( $author->to_array() );



?>