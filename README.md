# Response cache

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Laravel 5 response cache.

## Install

Require this package with Composer (Packagist), using the following command:

``` bash
$ composer require media24si/response-cache
```

Register the ResponseCacheServiceProvider to the providers array in `config/app.php`

``` php
Media24si\ResponseCache\ResponseCacheServiceProvider::class,
```

Publish vendor files (config file):
``` bash
$ php artisan vendor:publish
```

## Usage

If you return response which is `public` it will be cached for `max-age` seconds.
``` php
return response()->json(['name' => 'Taylor'])->setPublic()->setMaxAge(600);
```

## Config

Check `responseCache.php` for all possible configurations.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[manual]: http://guzzle.readthedocs.org/en/latest/
