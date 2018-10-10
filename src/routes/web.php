<?php 

$namespace = 'Puzzle\Wordpuzzle\Controllers';

Route::group([
	'namespace' =>	$namespace,
], function(){

	Route::get('puzzle', [
		'as'	=>	'show.generate.puzzle',
		'uses'	=>	'PuzzleController@index'
	]);
	Route::post('puzzle', [
		'as'	=>	'generate.puzzle',
		'uses'	=>	'PuzzleController@generate'
	]);
	
});