<?php

namespace Adamski\Symfony\SitemapBundle\Helper;

use Adamski\Symfony\SitemapBundle\Model\SitemapGeneratorInterface;
use Adamski\Symfony\SitemapBundle\Model\SitemapItem;
use Adamski\Symfony\SitemapBundle\Model\SitemapItemAlternate;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class SitemapHelper {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * SitemapHelper constructor.
     *
     * @param ContainerInterface $container
     * @param RouterInterface    $router
     */
    public function __construct(ContainerInterface $container, RouterInterface $router) {
        $this->container = $container;
        $this->router = $router;
    }

    /**
     * Generate collection of the Sitemap Items.
     *
     * @return SitemapItem[]
     */
    public function getSitemapItems(): array {
        $sitemapItems = [];

        foreach ($this->router->getRouteCollection() as $index => $route) {
            if (null !== $sitemapRoute = $route->getDefault("_sitemap")) {

                // Check if current Route is internationalized
                if ($route->hasDefault("_canonical_route") && $route->hasDefault("_locale")) {
                    $localeRoute = $route->getDefault("_locale");
                    $canonicalRoute = $route->getDefault("_canonical_route");

                    // Generate Sitemap Item
                    foreach ($this->generateItems($canonicalRoute, $sitemapRoute, $localeRoute) as $item) {
                        $alternates = $this->generateAlternates($canonicalRoute, $item->getPayload());

                        if (count($alternates) > 1) {
                            $item->setAlternates(
                                $this->generateAlternates($canonicalRoute, $item->getPayload())
                            );
                        }

                        $sitemapItems[] = $item;
                    }
                } else {
                    $sitemapItems = array_merge($sitemapItems, $this->generateItems($index, $sitemapRoute));
                }
            }
        }

        return $sitemapItems;
    }

    /**
     * Generate Sitemap Items.
     *
     * @param string      $canonicalRoute
     * @param             $sitemapConf
     * @param string|null $locale
     * @return array|SitemapItem[]
     */
    private function generateItems(string $canonicalRoute, $sitemapConf, ?string $locale = null): array {
        if (is_array($sitemapConf)) {
            $itemPriority = $this->getValue($sitemapConf, "priority");
            $itemChangeFrequency = $this->getValue($sitemapConf, "change_frequency");
            $itemModificationDate = $this->getValue($sitemapConf, "last_modification");
            $itemModificationDate = null !== $itemModificationDate ? new \DateTime($itemModificationDate) : null;

            if (null !== $generatorConf = $this->getValue($sitemapConf, "generator")) {
                list($genClass, $genMethod) = explode("::", $generatorConf);

                if (null !== ($generator = $this->container->get($genClass))) {
                    if ($generator instanceof SitemapGeneratorInterface) {
                        if (is_callable([$generator, $genMethod])) {
                            $generationResult = $generator->{$genMethod}();

                            if (is_array($generationResult) && count($generationResult) > 0) {
                                return array_map(function (array $payload) use ($canonicalRoute, $locale, $itemPriority, $itemChangeFrequency, $itemModificationDate) {
                                    $parameters = array_merge($payload, null !== $locale ? ["_locale" => $locale] : []);

                                    return new SitemapItem(
                                        $this->generateUrl($canonicalRoute, $parameters),
                                        $itemPriority,
                                        $itemChangeFrequency,
                                        $itemModificationDate,
                                        $payload
                                    );
                                }, $generationResult);
                            }
                        }
                    }
                }

                return [];
            } else {
                return [new SitemapItem(
                    $this->generateUrl($canonicalRoute, null !== $locale ? ["_locale" => $locale] : []),
                    $itemPriority,
                    $itemChangeFrequency,
                    $itemModificationDate
                )];
            }
        } else if (is_double($sitemapConf) || is_int($sitemapConf)) {
            return [new SitemapItem(
                $this->generateUrl($canonicalRoute, null !== $locale ? ["_locale" => $locale] : []),
                $sitemapConf
            )];
        }

        return [new SitemapItem(
            $this->generateUrl($canonicalRoute, null !== $locale ? ["_locale" => $locale] : [])
        )];
    }

    /**
     * Generate collection of Sitemap Item Alternates.
     *
     * @param string $canonicalRoute
     * @param array  $payload
     * @return SitemapItemAlternate[]
     */
    private function generateAlternates(string $canonicalRoute, array $payload = []): array {
        $alternates = [];

        foreach ($this->router->getRouteCollection() as $index => $route) {
            if ($canonicalRoute === $route->getDefault("_canonical_route") && null !== $localRoute = $route->getDefault("_locale")) {
                $parameters = array_merge($payload, ["_locale" => $localRoute]);

                $alternates[] = new SitemapItemAlternate(
                    $this->generateUrl($canonicalRoute, $parameters), $localRoute
                );
            }
        }

        return $alternates;
    }

    /**
     * Get item with specified key from provided array.
     * Default value will be returned if provided key does not exists in array.
     *
     * @param array  $array
     * @param string $key
     * @param null   $default
     * @return mixed|null
     */
    private function getValue(array $array, string $key, $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Generate absolute url for provided canonical route.
     *
     * @param string $canonicalRoute
     * @param array  $parameters
     * @return string
     */
    private function generateUrl(string $canonicalRoute, array $parameters = []): string {
        return $this->router->generate($canonicalRoute, $parameters, RouterInterface::ABSOLUTE_URL);
    }
}
