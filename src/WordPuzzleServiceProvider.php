<?php

namespace Puzzle\Wordpuzzle;

use Illuminate\Support\ServiceProvider;

class WordPuzzleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->loadRoutesFrom( __DIR__ . '/routes/web.php');
		$this->loadViewsFrom( __DIR__ . '/views', 'majid/wordpuzzle');
		$this->mergeConfigFrom( __DIR__ . '/config/puzzle.php', 'majid/wordpuzzle');
	}

	public function register()
	{
		 
	}
}