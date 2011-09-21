<?php



// Inclusion des fichiers de la librairie
require_once 'lib/DB.php';
require_once 'lib/Model.php';



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
class Book extends DbModel {
	static $table_name  = 'books';
	static $primary_key = 'book_id';
	
	static $before_save = 'before_save_fct';
	public function before_save_fct() {
		echo '>>> BEFORE-SAVE' . '<br>';
	}
	
	static $after_save = 'after_save_fct';
	public function after_save_fct() {
		echo '>>> AFTER-SAVE' . '<br>';
	}
}



// C'est parti !
echo '<pre>';

$book = Book::find_first();

echo 'Avant: ' . $book->name . '<br>';

$book->name = 'New book name';

echo 'Apres: ' . $book->name . '<br>';

$book->save();



?>