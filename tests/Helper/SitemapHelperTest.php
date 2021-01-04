<?php

namespace Helper;

use Adamski\Symfony\SitemapBundle\Helper\SitemapHelper;
use Adamski\Symfony\SitemapBundle\Model\SitemapGeneratorInterface;
use Adamski\Symfony\SitemapBundle\Model\SitemapItem;
use Adamski\Symfony\SitemapBundle\Model\SitemapItemAlternate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class SitemapHelperTest extends TestCase {

    /**
     * Test Simple Sitemap.
     */
    public function testSimpleSitemap() {
        $routerStub = $this->createMock(Router::class);
        $containerStub = $this->createMock(ContainerInterface::class);

        // Generate Route Collection
        $routeCollection = new RouteCollection();
        $routeCollection->add("index", new Route("/", ["_sitemap" => 1.0]));
        $routeCollection->add("home", new Route("/home", ["_sitemap" => true]));
        $routeCollection->add("other", new Route("/other"));

        // Configure Stubs
        $routerStub->method("getRouteCollection")->willReturn($routeCollection);
        $routerStub->method("generate")
            ->withConsecutive(["index"], ["home"], ["other"])
            ->willReturnOnConsecutiveCalls("/", "/home", "/other");

        // Create instance of the Sitemap Helper
        $sitemapHelper = new SitemapHelper($containerStub, $routerStub);
        $sitemapItems = $sitemapHelper->getSitemapItems();

        // Generate expected result
        $expectedResult[] = new SitemapItem("/", 1.0);
        $expectedResult[] = new SitemapItem("/home");

        $this->assertEquals($expectedResult, $sitemapItems);
    }

    /**
     * Test Sitemap with Internationalized routing
     */
    public function testLocaleSitemap() {
        $routerStub = $this->createMock(Router::class);
        $containerStub = $this->createMock(ContainerInterface::class);

        // Generate Route Collection
        $routeCollection = new RouteCollection();
        $routeCollection->add("about-us.pl", new Route("/pl/o-nas", ["_locale" => "pl", "_canonical_route" => "about-us", "_sitemap" => true]));
        $routeCollection->add("about-us.en", new Route("/en/about-us", ["_locale" => "en", "_canonical_route" => "about-us", "_sitemap" => true]));

        $routerStub->method("getRouteCollection")->willReturn($routeCollection);
        $routerStub->method("generate")->willReturnCallback(function (string $name, array $parameters) {
            return $parameters["_locale"] === "pl" ? "/pl/o-nas" : "/en/about-us";
        });

        // Create instance of the Sitemap Helper
        $sitemapHelper = new SitemapHelper($containerStub, $routerStub);
        $sitemapItems = $sitemapHelper->getSitemapItems();

        // Generate expected result
        $expectedResult[] = new SitemapItem("/pl/o-nas");
        $expectedResult[] = new SitemapItem("/en/about-us");

        // Add Sitemap Items Alternates
        $expectedResult[0]->setAlternates([
            new SitemapItemAlternate("/pl/o-nas", "pl"),
            new SitemapItemAlternate("/en/about-us", "en")
        ]);

        $expectedResult[1]->setAlternates([
            new SitemapItemAlternate("/pl/o-nas", "pl"),
            new SitemapItemAlternate("/en/about-us", "en")
        ]);

        $this->assertEquals($expectedResult, $sitemapItems);
    }

    /**
     * Test Sitemap with dynamic generated routes.
     */
    public function testGeneratedSitemap() {
        $routerStub = $this->createMock(Router::class);
        $containerStub = $this->createMock(ContainerInterface::class);
        $sitemapGeneratorStub = $this->getMockBuilder(SitemapGeneratorInterface::class)->addMethods(["generateName"])->getMock();

        // Generate Route Collection
        $routeCollection = new RouteCollection();
        $routeCollection->add("name", new Route("/name/{name}", ["_sitemap" => ["generator" => "SitemapGeneratorInterface::generateName"]]));

        // Configure Stubs
        $sitemapGeneratorStub->method("generateName")->willReturn([["name" => "Susan"], ["name" => "Matt"]]);
        $containerStub->method("get")->willReturn($sitemapGeneratorStub);
        $routerStub->method("getRouteCollection")->willReturn($routeCollection);
        $routerStub->method("generate")->willReturnCallback(function (string $name, array $parameters) {
            return $parameters["name"] === "Susan" ? "/name/Susan" : "/name/Matt";
        });

        // Create instance of the Sitemap Helper
        $sitemapHelper = new SitemapHelper($containerStub, $routerStub);
        $sitemapItems = $sitemapHelper->getSitemapItems();

        // Generate expected result
        $expectedResult[] = new SitemapItem("/name/Susan", null, null, null, ["name" => "Susan"]);
        $expectedResult[] = new SitemapItem("/name/Matt", null, null, null, ["name" => "Matt"]);

        $this->assertEquals($expectedResult, $sitemapItems);
    }
}
