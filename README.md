# Sitemap Bundle for Symfony

Bundle which creates a dynamic sitemap in XML format.

## Installation

This bundle can be installed by Composer:

```shell
composer require m-adamski/symfony-sitemap-bundle
```

## How to use it?

Bundle provides the Sitemap Controller, and a configured routes file that you just need to import into your local
configuration. You can do that by adding sitemap section into ``config/routes.yaml`` file:

```yaml
sitemap:
    resource: '@SitemapBundle/Resources/config/routes.yaml'
```

The next step is to mark the routes to be added to the sitemap file. The bundle supports several configurations:

```yaml
index:
    path: /
    methods: [ GET ]
    controller: App\Controller\DefaultController::index
    defaults:
        _sitemap: 1.00
```

```yaml
index:
    path: /
    methods: [ GET ]
    controller: App\Controller\DefaultController::index
    defaults:
        _sitemap: true
```

```yaml
index:
    path: /
    methods: [ GET ]
    controller: App\Controller\DefaultController::index
    defaults:
        _sitemap:
            priority: 1.00
            change_frequency: 'monthly'
            last_modification: '2021-01-01 12:00:00'
```

What about dynamically generated routes? Sometimes the URLs we want to include in the sitemap are dynamically generated
with one or more parameters. In this case, it is possible to use the ``generator`` parameter:

```yaml
city:
    path: /city/{cityName}
    methods: [ GET ]
    controller: App\Controller\DefaultController::city
    defaults:
        _sitemap:
            generator: App\Model\SitemapGenerator::generateCity
```

```php
<?php

namespace App\Model;

use Adamski\Symfony\SitemapBundle\Model\SitemapGeneratorInterface;

class SitemapGenerator implements SitemapGeneratorInterface {

    public function generateCity(): array {
        return [
            ["cityName" => "New York"],
            ["cityName" => "Oslo"],
            ["cityName" => "Warsaw"]
        ];
    }
}
```

Important: The SitemapGenerator object must be marked as public in DI.

```yaml
App\Model\SitemapGenerator:
    public: true
```

As the result there should be generated three additional items in sitemap file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://localhost/city/New%20York</loc>
    </url>
    <url>
        <loc>http://localhost/city/Oslo</loc>
    </url>
    <url>
        <loc>http://localhost/city/Warsaw</loc>
    </url>
</urlset>
```

Bundle also supports the internationalized routing:

```yaml
home:
    path:
        pl: /pl
        en: /en
    methods: [ GET ]
    controller: App\Controller\DefaultController::staticPage
    defaults:
        _sitemap: true
```

Generated Sitemap XML:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>http://localhost/pl</loc>
        <xhtml:link rel="alternate" hreflang="pl" href="http://localhost/pl"/>
        <xhtml:link rel="alternate" hreflang="en" href="http://localhost/en"/>
    </url>
    <url>
        <loc>http://localhost/en</loc>
        <xhtml:link rel="alternate" hreflang="en" href="http://localhost/en"/>
        <xhtml:link rel="alternate" hreflang="pl" href="http://localhost/pl"/>
    </url>
</urlset>
```

## License

MIT
