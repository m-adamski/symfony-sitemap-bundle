<?php

namespace Adamski\Symfony\SitemapBundle\Model;

class SitemapItemAlternate {

    /**
     * @var string
     */
    protected $href;

    /**
     * @var string
     */
    protected $hrefLang;

    /**
     * SitemapItemAlternate constructor.
     *
     * @param string $href
     * @param string $hrefLang
     */
    public function __construct(string $href, string $hrefLang) {
        $this->href = $href;
        $this->hrefLang = $hrefLang;
    }

    /**
     * @return string
     */
    public function getHref(): string {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref(string $href): void {
        $this->href = $href;
    }

    /**
     * @return string
     */
    public function getHrefLang(): string {
        return $this->hrefLang;
    }

    /**
     * @param string $hrefLang
     */
    public function setHrefLang(string $hrefLang): void {
        $this->hrefLang = $hrefLang;
    }
}
