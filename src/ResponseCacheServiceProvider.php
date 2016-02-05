<?php

namespace Media24si\ResponseCache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Media24si\ResponseCache\Cache\ResponseMemcachedStore;
use Illuminate\Support\Arr;

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
