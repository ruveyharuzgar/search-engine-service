<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{
    public function testSearchEndpointReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testSearchWithKeyword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', ['keyword' => 'programming']);
        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function testSearchWithTypeFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', ['type' => 'video']);
        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        foreach ($data['data'] as $item) {
            $this->assertEquals('video', $item['type']);
        }
    }

    public function testSearchWithSortBy(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', ['sortBy' => 'score']);
        $this->assertResponseIsSuccessful();
        
        $scoreData = json_decode($client->getResponse()->getContent(), true);
        $client->request('GET', '/api/search', ['sortBy' => 'date']);
        $this->assertResponseIsSuccessful();
        
        $dateData = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($scoreData['success']);
        $this->assertTrue($dateData['success']);
    }

    public function testSearchPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', [
            'page' => 1,
            'perPage' => 2
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['page']);
        $this->assertEquals(2, $data['pagination']['per_page']);
        $this->assertLessThanOrEqual(2, count($data['data']));
    }

    public function testSearchReturnsCorrectStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertArrayHasKey('total', $data['pagination']);
        $this->assertArrayHasKey('page', $data['pagination']);
        $this->assertArrayHasKey('per_page', $data['pagination']);
        $this->assertArrayHasKey('total_pages', $data['pagination']);
        
        if (!empty($data['data'])) {
            $item = $data['data'][0];
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('metrics', $item);
            $this->assertArrayHasKey('published_at', $item);
            $this->assertArrayHasKey('tags', $item);
            $this->assertArrayHasKey('score', $item);
        }
    }

    public function testSyncEndpoint(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/sync');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('synced_count', $data);
        $this->assertIsInt($data['synced_count']);
    }

    public function testInvalidSortByParameter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', ['sortBy' => 'invalid']);
        $this->assertResponseIsSuccessful();
    }

    public function testNegativePageNumber(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', ['page' => -1]);
        $this->assertResponseIsSuccessful();
    }
}
