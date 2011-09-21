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
	
	static $validations = array(
		'name' => array(
			'presence_of' => array('message' => "{FIELD_NAME} est obligatoire."),
			'length_of'   => array(
					array('message' => "{FIELD_NAME} est trop court.", 'min' => 5),
					array('message' => "{FIELD_NAME} est trop long.",  'max' => 15),
				),
		),
	);
	
}



// C'est parti !
echo '<pre>';

$author = Author::find_first();

$author->name = 'aa';
//$author->name = '';

//var_dump( $author->is_valid() );
var_dump( $author->save() );

print_r( $author->errors );


?>