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
        Route::name('test.livewire.update')->post('/test/update', fn () => Breadcrumbs::generate()->pluck('title')->all());

        // A normal page: must never honour a (forged) Livewire snapshot
        Route::name('forged')->post('/test/forged', fn () => Breadcrumbs::generate()->pluck('title')->all());
    }

    private function livewireUpdate(string $path, string $method = 'GET', bool $isLivewire = true, string $uri = '/test/update')
    {
        $snapshot = json_encode(['memo' => ['path' => $path, 'method' => $method]]);

        return $this->postJson(
            $uri,
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

        // No X-Livewire header: the "test.livewire.update" route is the current one, and it has no breadcrumb
        $this->livewireUpdate('post/5', 'GET', false);
    }

    public function testForgedSnapshotIsIgnoredOutsideLivewiresUpdateRoute()
    {
        // The X-Livewire header and snapshot are client-controlled: on a normal route they must not select the route
        $this->expectException(InvalidBreadcrumbException::class);

        $this->livewireUpdate('post/5', 'GET', true, '/test/forged');
    }

    public function testFallsBackToTheCurrentRouteIfABoundModelDoesNotExist()
    {
        Route::bind('post', fn ($id) => throw new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $this->expectException(InvalidBreadcrumbException::class);

        $this->livewireUpdate('post/5');
    }

    public function testFallsBackToTheCurrentRouteIfTheOriginalPageDoesNotExist()
    {
        $this->expectException(InvalidBreadcrumbException::class);

        $this->livewireUpdate('does/not/exist');
    }
}
