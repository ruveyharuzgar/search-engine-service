<?php

namespace App\Tests\Service;

use App\Service\CacheManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheManagerTest extends TestCase
{
    private CacheManager $cacheManager;
    private TagAwareAdapter $cache;

    protected function setUp(): void
    {
        // Use TagAwareAdapter with ArrayAdapter for testing (in-memory cache)
        $arrayAdapter = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($arrayAdapter);
        $this->cacheManager = new CacheManager($this->cache, $this->cache, 3600);
    }

    public function testGenerateKey(): void
    {
        $prefix = 'test';
        $params = ['key1' => 'value1', 'key2' => 'value2'];
        $key1 = $this->cacheManager->generateKey($prefix, $params);
        $key2 = $this->cacheManager->generateKey($prefix, $params);
        $this->assertIsString($key1);
        $this->assertEquals($key1, $key2); 
        $this->assertStringStartsWith($prefix . '_', $key1);
    }

    public function testGenerateKeyDifferentParams(): void
    {
        $prefix = 'test';
        $params1 = ['key1' => 'value1'];
        $params2 = ['key1' => 'value2'];
        $key1 = $this->cacheManager->generateKey($prefix, $params1);
        $key2 = $this->cacheManager->generateKey($prefix, $params2);
        $this->assertNotEquals($key1, $key2); 
    }

    public function testGetWithCallback(): void
    {
        $key = 'test_key';
        $expectedValue = ['data' => 'test'];
        $callbackExecuted = false;

        $callback = function () use ($expectedValue, &$callbackExecuted) {
            $callbackExecuted = true;
            return $expectedValue;
        };

        $result1 = $this->cacheManager->get($key, $callback);
        $this->assertTrue($callbackExecuted);
        $this->assertEquals($expectedValue, $result1);
        $callbackExecuted = false;
        $result2 = $this->cacheManager->get($key, $callback);
        $this->assertFalse($callbackExecuted); 
        $this->assertEquals($expectedValue, $result2);
    }

    public function testClear(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test'];
        $this->cacheManager->get($key, fn() => $value);
        $this->cacheManager->clear();
        $callbackExecuted = false;
        $this->cacheManager->get($key, function () use ($value, &$callbackExecuted) {
            $callbackExecuted = true;
            return $value;
        });
        $this->assertTrue($callbackExecuted);
    }

    public function testCacheWithComplexData(): void
    {
        $key = 'complex_key';
        $complexData = [
            'array' => [1, 2, 3],
            'nested' => ['key' => 'value'],
            'number' => 42,
            'string' => 'test'
        ];
        $result = $this->cacheManager->get($key, fn() => $complexData);
        $this->assertEquals($complexData, $result);
    }
}
