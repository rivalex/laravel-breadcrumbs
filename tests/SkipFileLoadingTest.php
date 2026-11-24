<?php

namespace BreadcrumbsTests;

use PHPUnit\Framework\Attributes\CoversMethod;
use Rivalex\Breadcrumbs\BreadcrumbsServiceProvider;

#[CoversMethod(BreadcrumbsServiceProvider::class, 'registerBreadcrumbs')]
class SkipFileLoadingTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->config->set('breadcrumbs.files', []);
    }

    public function testLoading()
    {
        // I can't think of a way to actually test this since nothing is loaded -
        // see code coverage (if (!$files) { return; })
        $this->assertTrue(true);
    }
}
