# Response cache

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/Media24si/ResponseCache.svg?branch=master)](https://travis-ci.org/Media24si/ResponseCache)

Laravel 5 response cache.

## Install

Require this package with Composer (Packagist), using the following command:

``` bash
$ composer require media24si/response-cache
```

Register the ResponseCacheServiceProvider to the providers array in `config/app.php`

``` php
Media24si\ResponseCache\ResponseCacheServiceProvider::class
```

Publish vendor files (config file):
``` bash
$ php artisan vendor:publish
```

To access cache manager register facade in `config/app.php`
``` php
'ResponseCacheManager' => Media24si\ResponseCache\Facades\ResponseCacheManagerFacade::class
```

## Usage

Register middleware as a global in `app/Http/Kernel.php`
``` php
\Media24si\ResponseCache\Http\Middleware\CacheMiddleware::class
```

To cache response, mark response as public and set max-age (TTL):
``` php
return response()->json(['name' => 'John'])
		->setPublic()
		->setMaxAge(600);
```

## Tag usage

Many times you want to assing tags to URI. With assigned tags it's simple to clear more cached URIs.

To assign tag to caching response, set `cache-tags` header. To assign more tags, seperate them with comma (,).

To cache response, mark response as public and set max-age (TTL):
``` php
return response()->json(['name' => 'John'])
		->setPublic()
		->setMaxAge(600)
		->header('cache-tags', 'foo,bar,john,doe');
```

To flush all keys for tag:
``` php
ResponseCacheManager::flushTag('foo')
```

## Config

Check `responseCache.php` for all possible configurations.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[manual]: http://guzzle.readthedocs.org/en/latest/
