<?php

namespace App\Tests\Provider;

use App\Provider\JsonProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class JsonProviderTest extends TestCase
{
    private function createMockLogger(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }

    public function testFetchContentsSuccess(): void
    {
        $mockData = [
            'contents' => [
                [
                    'id' => 'v1',
                    'title' => 'Test Video',
                    'type' => 'video',
                    'metrics' => [
                        'views' => 15000,
                        'likes' => 1200,
                        'duration' => '15:30'
                    ],
                    'published_at' => '2024-03-15T10:00:00Z',
                    'tags' => ['programming', 'tutorial']
                ]
            ]
        ];

        $mockResponse = new MockResponse(json_encode($mockData));
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new JsonProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertCount(1, $contents);
        $this->assertEquals('v1', $contents[0]->id);
        $this->assertEquals('Test Video', $contents[0]->title);
        $this->assertEquals('video', $contents[0]->type);
        $this->assertIsArray($contents[0]->tags);
        $this->assertContains('programming', $contents[0]->tags);
    }

    public function testFetchContentsEmptyResponse(): void
    {
        $mockData = ['contents' => []];
        $mockResponse = new MockResponse(json_encode($mockData));
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new JsonProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertEmpty($contents);
    }

    public function testFetchContentsInvalidJson(): void
    {
        $mockResponse = new MockResponse('invalid json');
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new JsonProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertEmpty($contents);
    }

    public function testFetchContentsHttpError(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new JsonProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertEmpty($contents);
    }

    public function testFetchContentsMultipleItems(): void
    {
        $mockData = [
            'contents' => [
                [
                    'id' => 'v1',
                    'title' => 'Video 1',
                    'type' => 'video',
                    'metrics' => ['views' => 1000, 'likes' => 100, 'duration' => '10:00'],
                    'published_at' => '2024-03-15T10:00:00Z',
                    'tags' => ['tag1']
                ],
                [
                    'id' => 'v2',
                    'title' => 'Video 2',
                    'type' => 'video',
                    'metrics' => ['views' => 2000, 'likes' => 200, 'duration' => '20:00'],
                    'published_at' => '2024-03-14T10:00:00Z',
                    'tags' => ['tag2']
                ]
            ]
        ];

        $mockResponse = new MockResponse(json_encode($mockData));
        $httpClient = new MockHttpClient($mockResponse);   
        $provider = new JsonProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertCount(2, $contents);
        $this->assertEquals('v1', $contents[0]->id);
        $this->assertEquals('v2', $contents[1]->id);
    }
}
