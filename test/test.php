<?php



// Inclusion des fichiers de la librairie
require_once '../DbModel.php';



// Initialisation de la DB
// (à faire dans un fichier config-database.php par ex)
DB::Construct(array(
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
}



// C'est parti !
echo '<pre>';

$author = Author::find_first();
echo $author;



?>