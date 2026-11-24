<?php

namespace BreadcrumbsTests;

use Breadcrumbs;
use Livewire\LivewireServiceProvider;
use Rivalex\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Route;

class LivewireTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [LivewireServiceProvider::class]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Breadcrumbs::for('home', function ($trail) {
            $trail->push('Home', '/');
        });

        Breadcrumbs::for('post', function ($trail, $post) {
            $trail->parent('home');
            $trail->push($post->title, "/post/{$post->id}");
        });

        Route::name('home')->get('/', fn () => '');

        Route::bind('post', fn ($id) => (object) ['id' => $id, 'title' => "Post $id"]);
        Route::name('post')->get('/post/{post}', fn () => '');

        // Stand-in for Livewire's update endpoint: the router's "current" route during a component update
        Route::name('test.update')->post('/test/update', fn () => Breadcrumbs::generate()->pluck('title')->all());
    }

    private function livewireUpdate(string $path, string $method = 'GET', bool $isLivewire = true)
    {
        $snapshot = json_encode(['memo' => ['path' => $path, 'method' => $method]]);

        return $this->postJson(
            '/test/update',
            ['components' => [['snapshot' => $snapshot]]],
            $isLivewire ? ['X-Livewire' => 'true'] : []
        );
    }

    public function testRouteBoundBreadcrumbsUseTheOriginalPageDuringALivewireUpdate()
    {
        $this->livewireUpdate('post/5')->assertExactJson(['Home', 'Post 5']);
    }

    public function testStaticOriginalPage()
    {
        $this->livewireUpdate('/')->assertExactJson(['Home']);
    }

    public function testNormalRequestsKeepUsingTheCurrentRoute()
    {
        $this->expectException(InvalidBreadcrumbException::class);

        // No X-Livewire header: the "test.update" route is the current one, and it has no breadcrumb
        $this->livewireUpdate('post/5', 'GET', false);
    }

    public function testFallsBackToTheCurrentRouteIfTheOriginalPageDoesNotExist()
    {
        $this->expectException(InvalidBreadcrumbException::class);

        $this->livewireUpdate('does/not/exist');
    }
}
