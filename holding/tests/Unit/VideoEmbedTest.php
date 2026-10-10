<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    public function test_youtube_and_vimeo_urls_become_safe_embeds(): void
    {
        $youtube = VideoEmbed::fromUrl('https://www.youtube.com/watch?v=abc_def123');
        $short = VideoEmbed::fromUrl('https://youtu.be/abc_def123');
        $vimeo = VideoEmbed::fromUrl('https://vimeo.com/123456789');

        $this->assertSame('https://www.youtube-nocookie.com/embed/abc_def123?autoplay=0&rel=0', $youtube['src']);
        $this->assertSame($youtube['src'], $short['src']);
        $this->assertSame('https://player.vimeo.com/video/123456789?autoplay=0', $vimeo['src']);
    }

    public function test_other_urls_are_rejected(): void
    {
        $this->assertNull(VideoEmbed::fromUrl('https://example.com/watch?v=abc_def123'));
        $this->assertNull(VideoEmbed::fromUrl('javascript:alert(1)'));
        $this->assertNull(VideoEmbed::fromUrl(''));
    }
}
