<?php

namespace App\Tests\Provider;

use App\Provider\XmlProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class XmlProviderTest extends TestCase
{
    private function createMockLogger(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }

    public function testFetchContentsSuccess(): void
    {
        $xmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed>
    <items>
        <item>
            <id>v1</id>
            <headline>Test Video</headline>
            <type>video</type>
            <stats>
                <views>22000</views>
                <likes>1800</likes>
                <duration>25:15</duration>
            </stats>
            <publication_date>2024-03-15</publication_date>
            <categories>
                <category>devops</category>
                <category>containers</category>
            </categories>
        </item>
    </items>
</feed>
XML;

        $mockResponse = new MockResponse($xmlData);
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new XmlProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertCount(1, $contents);
        $this->assertEquals('v1', $contents[0]->id);
        $this->assertEquals('Test Video', $contents[0]->title);
        $this->assertEquals('video', $contents[0]->type);
        $this->assertIsArray($contents[0]->tags);
        $this->assertContains('devops', $contents[0]->tags);
    }

    public function testFetchContentsEmptyResponse(): void
    {
        $xmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed>
    <items></items>
</feed>
XML;

        $mockResponse = new MockResponse($xmlData);
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new XmlProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertEmpty($contents);
    }

    public function testFetchContentsInvalidXml(): void
    {
        $mockResponse = new MockResponse('invalid xml');
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new XmlProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertIsArray($contents);
        $this->assertEmpty($contents);
    }

    public function testFetchContentsMultipleItems(): void
    {
            $xmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed>
    <items>
        <item>
            <id>v1</id>
            <headline>Video 1</headline>
            <type>video</type>
            <stats>
                <views>1000</views>
                <likes>100</likes>
                <duration>10:00</duration>
            </stats>
            <publication_date>2024-03-15</publication_date>
            <categories>
                <category>tag1</category>
            </categories>
        </item>
        <item>
            <id>a1</id>
            <headline>Article 1</headline>
            <type>article</type>
            <stats>
                <reading_time>10</reading_time>
                <reactions>500</reactions>
                <comments>25</comments>
            </stats>
            <publication_date>2024-03-14</publication_date>
            <categories>
                <category>tag2</category>
            </categories>
        </item>
    </items>
</feed>
XML;

        $mockResponse = new MockResponse($xmlData);
        $httpClient = new MockHttpClient($mockResponse);
        $provider = new XmlProvider($httpClient, $this->createMockLogger(), 'http://test.com/api');
        $contents = $provider->fetchContents();
        $this->assertCount(2, $contents);
        $this->assertEquals('v1', $contents[0]->id);
        $this->assertEquals('a1', $contents[1]->id);
        $this->assertEquals('video', $contents[0]->type);
        $this->assertEquals('article', $contents[1]->type);
    }
}
