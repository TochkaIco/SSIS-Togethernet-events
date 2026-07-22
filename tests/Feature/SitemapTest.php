<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('returns sitemap xml', function () {
    $response = get('/sitemap.xml');
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('events');
});
