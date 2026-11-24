# Laravel Breadcrumbs

[![Tests](https://github.com/rivalex/laravel-breadcrumbs/actions/workflows/tests.yml/badge.svg)](https://github.com/rivalex/laravel-breadcrumbs/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/rivalex/laravel-breadcrumbs/v/stable)](https://packagist.org/packages/rivalex/laravel-breadcrumbs)
[![Total Downloads](https://poser.pugx.org/rivalex/laravel-breadcrumbs/downloads)](https://packagist.org/packages/rivalex/laravel-breadcrumbs)
[![License](https://poser.pugx.org/rivalex/laravel-breadcrumbs/license)](https://packagist.org/packages/rivalex/laravel-breadcrumbs)

A simple, [Laravel](https://laravel.com/)-style way to define and render breadcrumbs, with ready-made templates for the most popular CSS frameworks and optional [Font Awesome](https://fontawesome.com) icons.

## Table of Contents

- [Requirements](#requirements)
- [Getting Started](#getting-started)
- [Defining Breadcrumbs](#defining-breadcrumbs)
- [Custom Templates](#custom-templates)
- [Outputting Breadcrumbs](#outputting-breadcrumbs)
- [Structured Data](#structured-data)
- [Route-Bound Breadcrumbs](#route-bound-breadcrumbs)
- [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
- [FAQ](#faq)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [Credits](#credits)
- [License](#license)

## Requirements

| Package version | Laravel      | PHP  |
|-----------------|--------------|------|
| **2.x**         | 12.71+, 13.40+ | 8.2+ |
| 1.x             | 5.x - 9.x    | 7.2+ |

Laravel 13 requires PHP 8.3 or higher.

## Getting Started

### 1. Install

```bash
composer require rivalex/laravel-breadcrumbs
```

The service provider and the `Breadcrumbs` facade alias are registered automatically through package auto-discovery.

### 2. Define your breadcrumbs

Create a file called `routes/breadcrumbs.php`:

```php
<?php

use App\Models\Category;
use App\Models\Post;
use Rivalex\Breadcrumbs\BreadcrumbsGenerator as Trail;

// Home
Breadcrumbs::for('home', function (Trail $trail) {
    $trail->push('Home', route('home'));
});

// Home > About
Breadcrumbs::for('about', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('About', route('about'));
});

// Home > Blog
Breadcrumbs::for('blog', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});

// Home > Blog > [Category]
Breadcrumbs::for('category', function (Trail $trail, Category $category) {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category));
});

// Home > Blog > [Category] > [Post]
Breadcrumbs::for('post', function (Trail $trail, Post $post) {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post));
});
```

See [Defining Breadcrumbs](#defining-breadcrumbs) for more details.

### 3. Choose a template

The default template is [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/components/breadcrumb/), wrapped in an accessible `<nav aria-label="breadcrumb">`. If that is what you use, skip this step.

Otherwise publish the config file:

```bash
php artisan vendor:publish --tag=breadcrumbs-config
```

Then edit `config/breadcrumbs.php`:

```php
'view' => 'breadcrumbs::bootstrap5',
```

Available values:

| View                           | Framework                                                                     |
|--------------------------------|-------------------------------------------------------------------------------|
| `breadcrumbs::bootstrap5`      | [Bootstrap 5](https://getbootstrap.com/docs/5.3/components/breadcrumb/)       |
| `breadcrumbs::bootstrap5icons` | Bootstrap 5 with [Font Awesome](https://fontawesome.com) icons                |
| `breadcrumbs::bootstrap4`      | [Bootstrap 4](https://getbootstrap.com/docs/4.6/components/breadcrumb/)       |
| `breadcrumbs::bootstrap4icons` | Bootstrap 4 with Font Awesome icons                                           |
| `breadcrumbs::bootstrap3`      | [Bootstrap 3](https://getbootstrap.com/docs/3.4/components/#breadcrumbs)      |
| `breadcrumbs::bootstrap2`      | [Bootstrap 2](https://getbootstrap.com/2.3.2/components.html#breadcrumbs)     |
| `breadcrumbs::bulma`           | [Bulma](https://bulma.io/documentation/components/breadcrumb/)                |
| `breadcrumbs::foundation6`     | [Foundation 6](https://get.foundation/sites/docs/breadcrumbs.html)            |
| `breadcrumbs::materialize`     | [Materialize](https://materializecss.com/breadcrumbs.html)                    |
| `breadcrumbs::uikit`           | [UIkit](https://getuikit.com/docs/breadcrumb)                                 |
| `breadcrumbs::json-ld`         | [JSON-LD structured data](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb) (a `<script>` tag, no visible output) |
| `partials.breadcrumbs`         | The path to your own view (see [Custom Templates](#custom-templates))         |

#### Font Awesome icons

With one of the `*icons` views, pass the icon classes through the `faIcon` data key:

```php
Breadcrumbs::for('home', function (Trail $trail) {
    $trail->push('Home', route('home'), ['faIcon' => 'fas fa-home']);
});
```

Font Awesome itself is not bundled: load it in your layout.

> **Note:** all the built-in templates escape titles. If you need markup in a title, write a [custom template](#custom-templates) and print it with `{!! !!}` yourself, making sure the value is trusted.

### 4. Output the breadcrumbs

Call `Breadcrumbs::render()` in your views, passing the breadcrumb name and any parameters:

```blade
{{ Breadcrumbs::render('home') }}

{{ Breadcrumbs::render('category', $category) }}
```

See [Outputting Breadcrumbs](#outputting-breadcrumbs) for other options, and [Route-Bound Breadcrumbs](#route-bound-breadcrumbs) to link breadcrumb names to route names automatically.

## Defining Breadcrumbs

A breadcrumb has a name, a title and, optionally, a URL. Titles and URLs are usually dynamic, so you define them in a closure that receives the parameters you need.

### Static pages

```php
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});
```

Inside the closure call `$trail->push($title, $url)`. To build the URL you can use any Laravel helper (`url()`, `secure_url()`, `route()`, `action()`) or a plain string.

```blade
{{ Breadcrumbs::render('home') }}
```

> Home

### Parent links

```php
Breadcrumbs::for('blog', function ($trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});
```

`parent()` runs the closure of another breadcrumb first.

> [Home](#) / Blog

The default templates never link the last breadcrumb (the current page), even when it has a URL. Create your own template to change that.

### Dynamic titles and links

```php
Breadcrumbs::for('post', function ($trail, $post) {
    $trail->parent('blog');
    $trail->push($post->title, route('post', $post));
});
```

```blade
{{ Breadcrumbs::render('post', $post) }}
```

> [Home](#) / [Blog](#) / Post Title

You can pass as many parameters as you need.

### Nested categories

Call `push()` several times:

```php
Breadcrumbs::for('category', function ($trail, $category) {
    $trail->parent('blog');

    foreach ($category->ancestors as $ancestor) {
        $trail->push($ancestor->title, route('category', $ancestor));
    }

    $trail->push($category->title, route('category', $category));
});
```

Or recurse through `parent()`:

```php
Breadcrumbs::for('category', function ($trail, $category) {
    if ($category->parent) {
        $trail->parent('category', $category->parent);
    } else {
        $trail->parent('blog');
    }

    $trail->push($category->title, route('category', $category));
});
```

> [Home](#) / [Blog](#) / [Grandparent Category](#) / [Parent Category](#) / Category Title

## Custom Templates

### Create a view

Create a view, e.g. `resources/views/partials/breadcrumbs.blade.php`:

```blade
@if (count($breadcrumbs))

    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)

            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
            @endif

        @endforeach
    </ol>

@endif
```

The built-in templates live in the [`views/`](views) directory.

The view receives a `$breadcrumbs` collection. Each item is an object with:

- `title`: the breadcrumb title
- `url`: the breadcrumb URL, or `null`
- one property for each entry of the `$data` array (see [Custom data](#custom-data))

### Update the config

```php
'view' => 'partials.breadcrumbs', // resources/views/partials/breadcrumbs.blade.php
```

### Skipping the view

Call `Breadcrumbs::generate()` to get the [collection](https://laravel.com/docs/collections) directly:

```blade
@foreach (Breadcrumbs::generate('post', $post) as $breadcrumb)
    {{-- ... --}}
@endforeach
```

## Outputting Breadcrumbs

### With Blade

```blade
{{ Breadcrumbs::render('home') }}

{{ Breadcrumbs::render('category', $category) }}
```

### With layouts and `@section`

In the page:

```blade
@extends('layout.name')

@section('breadcrumbs', Breadcrumbs::render('home'))
```

In the layout:

```blade
@yield('breadcrumbs')
```

### Pure PHP (without Blade)

```php
<?= Breadcrumbs::render('home') ?>
```

## Structured Data

To output breadcrumbs as JSON-LD [structured data](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb), render the `breadcrumbs::json-ld` template with `Breadcrumbs::view()` in addition to the normal one:

```blade
<html>
    <head>
        ...
        {{ Breadcrumbs::view('breadcrumbs::json-ld', 'category', $category) }}
        ...
    </head>
    <body>
        ...
        {{ Breadcrumbs::render('category', $category) }}
        ...
    </body>
</html>
```

To add an image, put it in the `$data` parameter of `push()`:

```php
Breadcrumbs::for('post', function ($trail, $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post), ['image' => asset($post->image)]);
});
```

For Microdata or RDFa, write a [custom template](#custom-templates).

## Route-Bound Breadcrumbs

Instead of calling `Breadcrumbs::render($name, ...$params)` on every page, you can name your breadcrumbs after your routes and let the package pick the right one.

### Name your routes

`routes/web.php`:

```php
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Home > [Post]
Route::get('/post/{post}', [PostController::class, 'show'])->name('post');
```

### Name your breadcrumbs to match

`routes/breadcrumbs.php`:

```php
// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Home > [Post]
Breadcrumbs::for('post', function ($trail, Post $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

For a custom 404 page, use the name `errors.404`:

```php
Breadcrumbs::for('errors.404', function ($trail) {
    $trail->parent('home');
    $trail->push('Page Not Found');
});
```

### Output them in your layout

Call `render()` with no parameters, for instance in `resources/views/app.blade.php`:

```blade
{{ Breadcrumbs::render() }}
```

The same works for `Breadcrumbs::generate()` and `Breadcrumbs::view()`:

```php
$breadcrumbs = Breadcrumbs::generate();
```

```blade
{{ Breadcrumbs::view('breadcrumbs::json-ld') }}
```

### Route binding exceptions

If the matching breadcrumb does not exist, an `InvalidBreadcrumbException` is thrown, so you notice missing pages. To disable it, publish the config file and set:

```php
'missing-route-bound-breadcrumb-exception' => false,
```

Likewise, to avoid an `UnnamedRouteException` when the current route has no name:

```php
'unnamed-route-exception' => false,
```

### Route model binding

Breadcrumbs receive the same model instances as your controller, so the model is only loaded once:

```php
// routes/web.php
Route::get('/post/{post}', [PostController::class, 'show'])->name('post');

// app/Http/Controllers/PostController.php
public function show(Post $post) // <-- implicit binding happens here
{
    return view('post.show', ['post' => $post]);
}

// routes/breadcrumbs.php
Breadcrumbs::for('post', function ($trail, Post $post) { // <-- the same model is injected here
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

### Resourceful controllers

Laravel names the routes of a resource controller (`photo.index`, `photo.show`, ...), so you can reuse the names:

```php
// routes/web.php
Route::resource('photo', PhotoController::class);
```

```php
// routes/breadcrumbs.php

// Photos
Breadcrumbs::for('photo.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Photos', route('photo.index'));
});

// Photos > Upload Photo
Breadcrumbs::for('photo.create', function ($trail) {
    $trail->parent('photo.index');
    $trail->push('Upload Photo', route('photo.create'));
});

// Photos > [Photo Name]
Breadcrumbs::for('photo.show', function ($trail, $photo) {
    $trail->parent('photo.index');
    $trail->push($photo->title, route('photo.show', $photo));
});

// Photos > [Photo Name] > Edit Photo
Breadcrumbs::for('photo.edit', function ($trail, $photo) {
    $trail->parent('photo.show', $photo);
    $trail->push('Edit Photo', route('photo.edit', $photo));
});
```

See also [Why is there no `Breadcrumbs::resource()` method?](#why-is-there-no-breadcrumbsresource-method)

## Advanced Usage

### Breadcrumbs with no URL

The second parameter of `push()` is optional:

```php
$trail->push('Sample');
```

`$breadcrumb->url` will be `null`. The built-in templates render it like the last item (the `active` class).

### Custom data

`push()` accepts a third parameter, an array of arbitrary data merged into the breadcrumb as properties:

```php
$trail->push('Home', '/', ['icon' => 'home.png']);
```

```blade
<li><a href="{{ $breadcrumb->url }}">
    <img src="/images/icons/{{ $breadcrumb->icon }}">
    {{ $breadcrumb->title }}
</a></li>
```

Do not use the keys `title` or `url`: they would be overwritten.

### Before and after callbacks

Register callbacks that add breadcrumbs at the start or at the end of every trail. For example, to append the page number:

```php
Breadcrumbs::after(function ($trail) {
    $page = (int) request('page', 1);

    if ($page > 1) {
        $trail->push("Page $page");
    }
});
```

### Getting the current page breadcrumb

`Breadcrumbs::current()` returns the last breadcrumb of the current page, handy for the page title:

```blade
<title>{{ ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Fallback Title' }}</title>
```

To skip a breadcrumb (e.g. pagination), add `'current' => false` to its data:

```php
Breadcrumbs::after(function ($trail) {
    $page = (int) request('page', 1);

    if ($page > 1) {
        $trail->push("Page $page", null, ['current' => false]);
    }
});
```

For more advanced filtering use `Breadcrumbs::generate()` and the collection methods:

```php
$current = Breadcrumbs::generate()->where('current', '!==', false)->last();
```

### Switching views at runtime

Use `Breadcrumbs::view()` instead of `render()` to pick another template:

```blade
{{ Breadcrumbs::view('partials.breadcrumbs2', 'category', $category) }}
```

Or change the config value for all following `render()` calls:

```php
Config::set('breadcrumbs.view', 'partials.breadcrumbs2');
```

Or load the view yourself:

```blade
@include('partials.breadcrumbs2', ['breadcrumbs' => Breadcrumbs::generate('category', $category)])
```

### Overriding the current route

Without parameters, `render()` and `generate()` use the current route name and parameters. Override them with:

```php
Breadcrumbs::setCurrentRoute($name, $param1, $param2);
Breadcrumbs::clearCurrentRoute();
```

### Checking if a breadcrumb exists

```php
Breadcrumbs::exists('name'); // bool
Breadcrumbs::exists();       // for the current route
```

### Defining breadcrumbs in a different file

Publish the config file and edit `files`:

```php
'files' => base_path('routes/breadcrumbs.php'),
```

It accepts a path or an array of paths, so `glob()` works too:

```php
'files' => [
    base_path('breadcrumbs/admin.php'),
    base_path('breadcrumbs/frontend.php'),
],

'files' => glob(base_path('breadcrumbs/*.php')),
```

Use an empty array `[]` to disable the loading.

### Defining breadcrumbs in another package

Load your file from the `boot()` method of your service provider:

```php
public function boot(): void
{
    if (class_exists(\Breadcrumbs::class)) {
        require __DIR__ . '/breadcrumbs.php';
    }
}
```

### Dependency injection

Inject the manager instead of using the facade:

```php
use Rivalex\Breadcrumbs\BreadcrumbsManager;

public function boot(BreadcrumbsManager $breadcrumbs): void
{
    $breadcrumbs->for('name', function ($trail) {
        // ...
    });
}
```

### Macros

`BreadcrumbsManager` is [macroable](https://laravel.com/api/master/Illuminate/Support/Traits/Macroable.html):

```php
Breadcrumbs::macro('pageTitle', function () {
    $title = ($breadcrumb = Breadcrumbs::current()) ? "{$breadcrumb->title} – " : '';

    if (($page = (int) request('page')) > 1) {
        $title .= "Page $page – ";
    }

    return $title . 'Demo App';
});
```

```blade
<title>{{ Breadcrumbs::pageTitle() }}</title>
```

### Advanced customisations

Subclass `BreadcrumbsManager` and/or `BreadcrumbsGenerator` and point the config file to your classes:

```php
'manager-class' => App\Breadcrumbs\Manager::class,
'generator-class' => App\Breadcrumbs\Generator::class,
```

Anything that is not part of the public API described below may change between releases, so cover your subclasses with tests.

### Livewire

Breadcrumbs work with [Livewire](https://livewire.laravel.com) 3 and 4; Livewire is not a dependency of this package.

Render them in your layout, or inside a full-page component, as usual:

```blade
{{ Breadcrumbs::render() }}
```

When a component re-renders, the request goes to Livewire's update endpoint, not to the page. The package detects this and resolves the breadcrumbs of the page that rendered the component, route model bindings included, so route-bound breadcrumbs (`Breadcrumbs::render()` with no parameters) keep working inside components.

Notes:

- With `wire:navigate` the whole page is swapped, so breadcrumbs placed in the layout update with it.
- This only applies on Livewire's own update endpoint (the route name ends with `livewire.update`), whose snapshot Livewire has verified. On any other route the client-supplied Livewire data is ignored.
- If the original page URL cannot be matched to a route, or a bound model no longer exists, the package falls back to the current route.
- A breadcrumb that depends on component state (not on the route) should be rendered with an explicit name and parameters: `Breadcrumbs::render('post', $this->post)`.

### Error solutions

When [Spatie's error solutions](https://github.com/spatie/error-solutions) are available (Laravel's default error page includes them), the package exceptions suggest how to fix the problem, e.g. which code to add to `routes/breadcrumbs.php`.

## API Reference

### `Breadcrumbs` facade

| Method                                                              | Returns    |
|---------------------------------------------------------------------|------------|
| `Breadcrumbs::for(string $name, Closure $callback)`                 | void       |
| `Breadcrumbs::register(string $name, Closure $callback)`            | void       |
| `Breadcrumbs::before(Closure $callback)`                            | void       |
| `Breadcrumbs::after(Closure $callback)`                             | void       |
| `Breadcrumbs::exists(?string $name = null)`                         | bool       |
| `Breadcrumbs::generate(?string $name = null, mixed ...$params)`     | Collection |
| `Breadcrumbs::render(?string $name = null, mixed ...$params)`       | HtmlString |
| `Breadcrumbs::view(string $view, ?string $name = null, mixed ...$params)` | HtmlString |
| `Breadcrumbs::current()`                                            | object / null |
| `Breadcrumbs::setCurrentRoute(string $name, mixed ...$params)`      | void       |
| `Breadcrumbs::clearCurrentRoute()`                                  | void       |

`register()` is an alias of `for()`. [Source](classes/BreadcrumbsManager.php)

### Defining breadcrumbs

```php
use App\Models\Post;
use Rivalex\Breadcrumbs\BreadcrumbsGenerator;

Breadcrumbs::before(function (BreadcrumbsGenerator $trail) {
    // ...
});

Breadcrumbs::for('name', function (BreadcrumbsGenerator $trail, Post $post) {
    // ...
});

Breadcrumbs::after(function (BreadcrumbsGenerator $trail) {
    // ...
});
```

| Method                                                       | Returns |
|--------------------------------------------------------------|---------|
| `$trail->push(string $title, ?string $url = null, array $data = [])` | void |
| `$trail->parent(string $name, mixed ...$params)`             | void    |

[Source](classes/BreadcrumbsGenerator.php)

### In the view (template)

| Variable                             | Type          |
|--------------------------------------|---------------|
| `$breadcrumb->title`                 | string        |
| `$breadcrumb->url`                   | string / null |
| `$breadcrumb->custom_attribute_name` | mixed         |

### Configuration file

`config/breadcrumbs.php` ([source](config/breadcrumbs.php))

| Setting                                    | Type           |
|--------------------------------------------|----------------|
| `view`                                     | string         |
| `files`                                    | string / array |
| `unnamed-route-exception`                  | bool           |
| `missing-route-bound-breadcrumb-exception` | bool           |
| `invalid-named-breadcrumb-exception`       | bool           |
| `manager-class`                            | string         |
| `generator-class`                          | string         |

## FAQ

### A new version of Laravel is out. Is it supported?

The supported range is declared in [composer.json](composer.json) and tested in CI against every supported PHP and Laravel combination. If a new major release breaks something, open an issue or a pull request.

### Why is there no `Breadcrumbs::resource()` method?

No implementation is flexible enough to cover translations and nested resources without becoming overly complex. Define your own with a macro. This is a starting point:

```php
Breadcrumbs::macro('resource', function (string $name, string $title) {
    // Home > Blog
    Breadcrumbs::for("$name.index", function ($trail) use ($name, $title) {
        $trail->parent('home');
        $trail->push($title, route("$name.index"));
    });

    // Home > Blog > New
    Breadcrumbs::for("$name.create", function ($trail) use ($name) {
        $trail->parent("$name.index");
        $trail->push('New', route("$name.create"));
    });

    // Home > Blog > Post 123
    Breadcrumbs::for("$name.show", function ($trail, $model) use ($name) {
        $trail->parent("$name.index");
        $trail->push($model->title, route("$name.show", $model));
    });

    // Home > Blog > Post 123 > Edit
    Breadcrumbs::for("$name.edit", function ($trail, $model) use ($name) {
        $trail->parent("$name.show", $model);
        $trail->push('Edit', route("$name.edit", $model));
    });
});

Breadcrumbs::resource('blog', 'Blog');
Breadcrumbs::resource('photos', 'Photos');
```

It assumes every model has a `title` attribute and does not handle translations or nested resources: adapt it to your needs.

## Troubleshooting

#### Class "Breadcrumbs" not found

- Run `composer update rivalex/laravel-breadcrumbs`.
- Run `php artisan package:discover` to make sure the service provider is detected.

#### Breadcrumb not found with name ...

- Check that the breadcrumbs are registered in the right place (`routes/breadcrumbs.php` by default).
    - Put `dd(__FILE__)` in the file to make sure it is loaded.
    - If the path is wrong, run `php artisan config:clear` or update `config/breadcrumbs.php`.
- Check that the breadcrumb name is correct. With route-bound breadcrumbs it must match the route name exactly.
- To allow pages without breadcrumbs, either register an empty closure for them or set [`missing-route-bound-breadcrumb-exception` to `false`](#route-binding-exceptions).

#### Failed opening required ...

- Check the path configured in `files`, then the file ownership and permissions.
- Run `php artisan config:clear` to drop a stale cached configuration.

#### Undefined variable: breadcrumbs

- Use `{{ Breadcrumbs::render() }}` or `{{ Breadcrumbs::view() }}`, not `@include()`, to render the built-in views.

#### Method `for` does not exist

- You are running a very old 1.x release: use `Breadcrumbs::register()` or upgrade.

## Contributing

Bug reports, fixes and documentation improvements are welcome through [issues](https://github.com/rivalex/laravel-breadcrumbs/issues) and [pull requests](https://github.com/rivalex/laravel-breadcrumbs/pulls). New features need a clear use case, documentation and tests.

```bash
git clone https://github.com/rivalex/laravel-breadcrumbs.git
cd laravel-breadcrumbs
composer install
composer test
```

Code coverage (requires Xdebug or PCOV):

```bash
scripts/test-coverage.sh
```

### Pull request workflow

`master` is protected: changes go through a pull request, merged with a merge commit once the GitHub Actions checks pass. Direct pushes and force pushes are not allowed.

### Supporting a new Laravel or PHP version

1. Update the PHP and Laravel matrix in [`.github/workflows/tests.yml`](.github/workflows/tests.yml) (including the matching `orchestra/testbench` version and the minimum Laravel constraint). The matrix also runs with `--prefer-lowest`, so the minimums in `composer.json` must be versions that really work.
2. Update the `require` and `require-dev` constraints in [`composer.json`](composer.json).
3. Update the [Requirements](#requirements) table and the [Changelog](#changelog).

The branch ruleset requires the single `Tests passed` job, which depends on the whole matrix, so it does not need updating when the matrix changes.

### Releasing

*For maintainers.*

1. Make sure the [Requirements](#requirements) table and the [Changelog](#changelog) are up to date, and that the checks on `master` are green.
2. Tag the release (`git tag -a 2.1.0 -m "2.1.0"`) and push it (`git push origin 2.1.0`). Release tags are protected: they cannot be moved or deleted once pushed, so tag only final code.
3. Create the GitHub release with the changelog entry as notes. Packagist picks the tag up automatically.

## Changelog

This project uses [Semantic Versioning](https://semver.org/).

### 2.0.1 (2026-11-24)

- No changes to the package code. CI now has a single `Tests passed` gate job, Dependabot updates for GitHub Actions and Composer, and the README documents the pull request and release workflow.

### 2.0.0 (2026-11-24)

- **Breaking:** require PHP 8.2+ and Laravel 12.71+ or 13.40+ (dropped Laravel 5.6 to 11 and PHP < 8.2). The minimums are the first releases that include the fixes for all known `laravel/framework` security advisories; Laravel 11 is out of security support and has no patches for them.
- Add Livewire 3 and 4 support: route-bound breadcrumbs now work when a component re-renders in an update request.
- **Breaking:** all built-in templates now escape breadcrumb titles (`{{ }}` instead of `{!! !!}`), fixing a potential XSS when titles come from user data. HTML in titles is no longer interpreted.
- Bootstrap 5 templates: wrap the list in `<nav aria-label="breadcrumb">` (Bootstrap 5.3 markup).
- Replace the abandoned `facade/ignition-contracts` with `spatie/error-solutions` for the suggested solutions.
- Add explicit nullable types, removing the PHP 8.4 deprecations.
- Move the test suite to PHPUnit 11-13 and Orchestra Testbench 9-11.
- Replace Travis CI with GitHub Actions.

Older releases are listed on the [releases page](https://github.com/rivalex/laravel-breadcrumbs/tags).

## Credits

This package started in 2020 as a fork of the archived [davejamesmiller/laravel-breadcrumbs](https://github.com/davejamesmiller/laravel-breadcrumbs) and has since been maintained independently. Thanks to Dave James Miller for the original work.

## License

Released under the [MIT License](LICENSE).
