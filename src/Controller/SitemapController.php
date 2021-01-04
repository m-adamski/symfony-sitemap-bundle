<?php

namespace Adamski\Symfony\SitemapBundle\Controller;

use Adamski\Symfony\SitemapBundle\Helper\SitemapHelper;
use Adamski\Symfony\SitemapBundle\Model\SitemapItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends AbstractController {

    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * SitemapController constructor.
     *
     * @param SitemapHelper $sitemapHelper
     */
    public function __construct(SitemapHelper $sitemapHelper) {
        $this->sitemapHelper = $sitemapHelper;
    }

    /**
     * Render Sitemap XML.
     *
     * @return Response
     */
    public function generate(): Response {
        $sitemapItems = $this->sitemapHelper->getSitemapItems();
        $withAlternates = array_filter($sitemapItems, function (SitemapItem $item) {
            return count($item->getAlternates()) > 0;
        });

        // Generate XML Response
        $xmlResponse = new Response();
        $xmlResponse->headers->set("Content-Type", "text/xml");
        $xmlResponse->setContent(
            $this->renderView("@Sitemap/sitemap.xml.twig", [
                "has_alternates" => count($withAlternates) > 0,
                "sitemap_items"  => $this->sitemapHelper->getSitemapItems()
            ])
        );

        return $xmlResponse;
    }
}
