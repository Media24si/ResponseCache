<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Media24si\ResponseCache\ResponseCacheManager;

class ResponseCacheManagerTest extends PHPUnit_Framework_TestCase
{
    private $testUrl = 'http://foo.com/bar';

    private $manager;
    private $cache;

    public function setUp()
    {
        $this->cache = new Repository(new ArrayStore());
        $this->cache->flush();
        $this->manager = new ResponseCacheManager($this->cache);
    }

    public function test_save_response_not_for_private()
    {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);

        $data = $this->cache->get('responseCache:' . $this->testUrl);
        
        $this->assertNull($data);
    }

    public function test_save_response_for_public()
    {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);

        $data = $this->cache->get('responseCache:' . $this->testUrl);
        
        $this->assertNotNull($data);
        $this->assertEquals('foo', $data['content']->getContent());
    }

    public function test_save_response_sets_tag() {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->header('cache-tags', 'testTag')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);        

        $tags = $this->cache->get('responseCache:tag:testTag');
        $this->assertArrayHasKey( 'responseCache:' . $this->testUrl, $tags );
    }

    public function test_save_response_sets_multiple_tags() {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->header('cache-tags', 'testTag,fooBar')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);        

        $tags = $this->cache->get('responseCache:tag:testTag');
        $this->assertArrayHasKey( 'responseCache:' . $this->testUrl, $tags );

        $tags = $this->cache->get('responseCache:tag:fooBar');
        $this->assertArrayHasKey( 'responseCache:' . $this->testUrl, $tags );
    }

    public function test_get_returns_response() {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);        

        $content = $this->manager->get($this->testUrl);

        $this->assertInstanceOf('Illuminate\Http\Response', $content);
        $this->assertEquals('foo', $content->getContent());
    }

    public function test_get_tags_return_array() {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->header('cache-tags', 'testTag,fooBar')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);        

        $tags = $this->manager->getTag('testTag');
        $this->assertArrayHasKey( 'responseCache:' . $this->testUrl, $tags );

        $tags = $this->manager->getTag('fooBar');
        $this->assertArrayHasKey( 'responseCache:' . $this->testUrl, $tags );
    }

    public function test_flush_tags_clears_tag() {
        $response = new \Illuminate\Http\Response();
        $response->setContent('foo')
                ->header('cache-tags', 'testTag,fooBar')
                ->setPublic()
                ->setMaxAge('600');

        $this->manager->saveResponse($this->testUrl, $response);        

        $tags = $this->manager->flushTag('testTag');
        $data = $this->cache->get('responseCache:' . $this->testUrl);        
        $this->assertNull($data);
    }
}
