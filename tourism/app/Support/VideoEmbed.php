<?php

namespace App\Support;

class VideoEmbed
{
    /**
     * @return array{provider: string, src: string}|null
     */
    public static function fromUrl(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = (string) ($parts['path'] ?? '');

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = $query['v'] ?? null;
            if (! is_string($id) && preg_match('#^/(embed|shorts)/([A-Za-z0-9_-]{6,20})$#', $path, $matches)) {
                $id = $matches[2];
            }
            if (is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id)) {
                return [
                    'provider' => 'youtube',
                    'src' => 'https://www.youtube-nocookie.com/embed/'.$id.'?autoplay=0&rel=0',
                ];
            }
        }

        if ($host === 'youtu.be' && preg_match('#^/([A-Za-z0-9_-]{6,20})$#', $path, $matches)) {
            return [
                'provider' => 'youtube',
                'src' => 'https://www.youtube-nocookie.com/embed/'.$matches[1].'?autoplay=0&rel=0',
            ];
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('#/(?:video/)?([0-9]{6,12})#', $path, $matches)) {
                return [
                    'provider' => 'vimeo',
                    'src' => 'https://player.vimeo.com/video/'.$matches[1].'?autoplay=0',
                ];
            }
        }

        return null;
    }
}
