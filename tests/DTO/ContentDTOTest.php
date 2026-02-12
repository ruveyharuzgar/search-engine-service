<?php

namespace App\Tests\DTO;

use App\DTO\ContentDTO;
use PHPUnit\Framework\TestCase;

class ContentDTOTest extends TestCase
{
    public function testCreateContentDTO(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test Video',
            type: 'video',
            metrics: ['views' => 1000, 'likes' => 100],
            publishedAt: new \DateTime('2024-03-15'),
            tags: ['test', 'video']
        );

        $this->assertEquals('v1', $content->id);
        $this->assertEquals('Test Video', $content->title);
        $this->assertEquals('video', $content->type);
        $this->assertIsArray($content->metrics);
        $this->assertInstanceOf(\DateTime::class, $content->publishedAt);
        $this->assertIsArray($content->tags);
        $this->assertCount(2, $content->tags);
    }

    public function testToArray(): void
    {
        $publishedAt = new \DateTime('2024-03-15 10:00:00');
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test Video',
            type: 'video',
            metrics: ['views' => 1000, 'likes' => 100],
            publishedAt: $publishedAt,
            tags: ['test']
        );
        $content->score = 42.5;
        $array = $content->toArray();
        $this->assertIsArray($array);
        $this->assertEquals('v1', $array['id']);
        $this->assertEquals('Test Video', $array['title']);
        $this->assertEquals('video', $array['type']);
        $this->assertIsArray($array['metrics']);
        $this->assertEquals($publishedAt->format('Y-m-d H:i:s'), $array['published_at']);
        $this->assertIsArray($array['tags']);
        $this->assertEquals(42.5, $array['score']);
    }

    public function testScoreProperty(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test',
            type: 'video',
            metrics: [],
            publishedAt: new \DateTime(),
            tags: []
        );

        $content->score = 99.9;
        $this->assertEquals(99.9, $content->score);
    }

    public function testEmptyTags(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test',
            type: 'video',
            metrics: [],
            publishedAt: new \DateTime(),
            tags: []
        );

        $this->assertIsArray($content->tags);
        $this->assertEmpty($content->tags);
    }

    public function testEmptyMetrics(): void
    {
        $content = new ContentDTO(
            id: 'v1',
            title: 'Test',
            type: 'video',
            metrics: [],
            publishedAt: new \DateTime(),
            tags: []
        );

        $this->assertIsArray($content->metrics);
        $this->assertEmpty($content->metrics);
    }
}
