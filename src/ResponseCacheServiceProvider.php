<?php

namespace Media24si\ResponseCache;

use Illuminate\Support\ServiceProvider;

class ResponseCacheServiceProvider extends ServiceProvider  {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/../config/responseCache.php' => config_path('responseCache.php'),
		]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{	
	}

}
