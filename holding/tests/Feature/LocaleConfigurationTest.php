<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleConfigurationTest extends TestCase
{
    public function test_the_holding_site_uses_the_four_required_locales(): void
    {
        $this->assertSame('holding', config('karako.site'));
        $this->assertSame('fa', config('app.locale'));
        $this->assertSame('en', config('app.fallback_locale'));
        $this->assertSame(
            ['fa', 'en', 'ru', 'ar'],
            array_keys(config('karako.locales'))
        );
        $this->assertSame('rtl', config('karako.locales.fa.dir'));
        $this->assertSame('rtl', config('karako.locales.ar.dir'));
        $this->assertSame('ltr', config('karako.locales.en.dir'));
        $this->assertSame('ltr', config('karako.locales.ru.dir'));
    }
}
