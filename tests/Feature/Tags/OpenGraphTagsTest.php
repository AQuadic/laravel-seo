<?php

use RalphJSmit\Laravel\SEO\Tests\Fixtures\Page;

use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    if ( ! file_exists($dir = public_path('images/foo')) ) {
        mkdir($dir, 0777, true);
    }

    copy(__DIR__ . '/../../Fixtures/images/test-image.jpg', public_path('images/foo/test-image.jpg'));
});

it('can correctly render OpenGraph tags', function () {
    config()->set('seo.title.suffix', ' | Laravel SEO');
    config()->set('seo.description.fallback', 'Fallback description');
    config()->set('seo.image.fallback', 'images/foo/test-image.jpg');
    config()->set('seo.site_name', 'My Sitename');

    get($url = route('seo.test-plain'))
        ->assertSee('<meta name="og:title" content="Test Plain | Laravel SEO">', false)
        ->assertSee('<meta name="og:description" content="Fallback description">', false)
        ->assertSee('<meta name="og:image" content="' . secure_url('images/foo/test-image.jpg') . '">', false)
        ->assertSee('<meta name="og:image:width" content="1451">', false)
        ->assertSee('<meta name="og:image:height" content="258">', false)
        ->assertSee('<meta name="og:url" content="' . $url . '">', false)
        ->assertSee('<meta name="og:site_name" content="My Sitename">', false)
        ->assertSee('<meta name="og:type" content="website">', false)
        ->assertSee('<meta name="og:locale" content="en">', false)
        ->assertDontSee('og:updated_time')
        ->assertDontSee('article:published_time')
        ->assertDontSee('article:modified_time');
});

it('can correctly render OpenGraph tags for a post or page', function () {
    config()->set('seo.title.suffix', ' | Laravel SEO');
    config()->set('seo.description.fallback', 'Fallback description');
    config()->set('seo.image.fallback', 'images/foo/test-image.jpg');
    config()->set('seo.site_name', 'My Sitename');

    testTime()->freeze();

    $page = Page::create();

    $page->seo->update([
        'description' => 'My great description, set by the SEO model.',
    ]);

    $page::$overrides = [
        'title' => 'My great title',
        'description' => 'My great description, set by a model on a per-page basis.',
        'type' => 'article',
        'locale' => 'nl',
    ];

    $page->refresh();

    get($url = route('seo.test-page', ['page' => $page]))
        ->assertSee('<meta name="og:title" content="My great title | Laravel SEO">', false)
        ->assertSee('<meta name="og:description" content="My great description, set by a model on a per-page basis.">', false)
        ->assertSee('<meta name="og:image" content="' . secure_url('images/foo/test-image.jpg') . '">', false)
        ->assertSee('<meta name="og:image:width" content="1451">', false)
        ->assertSee('<meta name="og:image:height" content="258">', false)
        ->assertSee('<meta name="og:url" content="' . $url . '">', false)
        ->assertSee('<meta name="og:site_name" content="My Sitename">', false)
        ->assertSee('<meta name="og:type" content="article">', false)
        ->assertSee('<meta name="og:locale" content="nl">', false)
        ->assertSee('<meta name="article:published_time" content="' . $page->created_at->toIso8601String() . '">', false)
        ->assertSee('<meta name="article:modified_time" content="' . $page->updated_at->toIso8601String() . '">', false);
});

it('can correctly render OpenGraph tags for a post or page with a few additional overrides', function () {
    config()->set('seo.title.suffix', ' | Laravel SEO');
    config()->set('seo.description.fallback', 'Fallback description');
    config()->set('seo.image.fallback', 'images/foo/test-image.jpg');
    config()->set('seo.site_name', 'My Sitename');

    testTime()->freeze();

    $page = Page::create();

    $page->seo->update([
        'description' => 'My great description, set by the SEO model.',
    ]);

    $page::$overrides = [
        'title' => 'My great title',
        'description' => 'My great description, set by a model on a per-page basis.',
        'type' => 'article',
        'published_time' => now()->subDays(2),
        'modified_time' => now()->subDay(),
        'section' => 'Laravel',
        'tags' => [
            'PHP',
            'Laravel',
        ],
    ];

    $page->refresh();

    get($url = route('seo.test-page', ['page' => $page]))
        ->assertSee('<meta name="og:title" content="My great title | Laravel SEO">', false)
        ->assertSee('<meta name="og:description" content="My great description, set by a model on a per-page basis.">', false)
        ->assertSee('<meta name="og:image" content="' . secure_url('images/foo/test-image.jpg') . '">', false)
        ->assertSee('<meta name="og:image:width" content="1451">', false)
        ->assertSee('<meta name="og:image:height" content="258">', false)
        ->assertSee('<meta name="og:url" content="' . $url . '">', false)
        ->assertSee('<meta name="og:site_name" content="My Sitename">', false)
        ->assertSee('<meta name="og:type" content="article">', false)
        ->assertSee('<meta name="article:published_time" content="' . now()->subDays(2)->toIso8601String() . '">', false)
        ->assertSee('<meta name="article:modified_time" content="' . now()->subDay()->toIso8601String() . '">', false)
        ->assertSee('<meta name="article:section" content="Laravel">', false)
        ->assertSee('<meta name="article:tag" content="PHP">', false)
        ->assertSee('<meta name="article:tag" content="Laravel">', false);
});