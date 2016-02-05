<?php 

namespace Media24si\ResponseCache\Facades;

use Illuminate\Support\Facades\Facade;

class ResponseCacheManagerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Media24si\ResponseCache\ResponseCacheManager';
    }
}
