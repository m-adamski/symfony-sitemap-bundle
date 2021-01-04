<?php

namespace Adamski\Symfony\SitemapBundle\Model;

use DateTime;

class SitemapItem {

    /**
     * @var string
     */
    protected $loc;

    /**
     * @var double|null
     */
    protected $priority;

    /**
     * @var string|null
     */
    protected $changeFrequency;

    /**
     * @var DateTime|null
     */
    protected $modificationDate;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var SitemapItemAlternate[]
     */
    protected $alternates;

    /**
     * SitemapItem constructor.
     *
     * @param string        $loc
     * @param float|null    $priority
     * @param string|null   $changeFrequency
     * @param DateTime|null $modificationDate
     * @param array         $payload
     */
    public function __construct(string $loc, ?float $priority = null, ?string $changeFrequency = null, ?DateTime $modificationDate = null, array $payload = []) {
        $this->loc = $loc;
        $this->priority = $priority;
        $this->changeFrequency = $changeFrequency;
        $this->modificationDate = $modificationDate;
        $this->payload = $payload;
        $this->alternates = [];
    }

    /**
     * @return string
     */
    public function getLoc(): string {
        return $this->loc;
    }

    /**
     * @param string $loc
     */
    public function setLoc(string $loc): void {
        $this->loc = $loc;
    }

    /**
     * @return float|null
     */
    public function getPriority(): ?float {
        return $this->priority;
    }

    /**
     * @param float|null $priority
     */
    public function setPriority(?float $priority): void {
        $this->priority = $priority;
    }

    /**
     * @return string|null
     */
    public function getChangeFrequency(): ?string {
        return $this->changeFrequency;
    }

    /**
     * @param string|null $changeFrequency
     */
    public function setChangeFrequency(?string $changeFrequency): void {
        $changeFrequency = strtolower($changeFrequency);

        if (in_array($changeFrequency, ["always", "hourly", "daily", "weekly", "monthly", "yearly", "never"])) {
            $this->changeFrequency = $changeFrequency;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getModificationDate(): ?DateTime {
        return $this->modificationDate;
    }

    /**
     * @param DateTime|null $modificationDate
     */
    public function setModificationDate(?DateTime $modificationDate): void {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return array
     */
    public function getPayload(): array {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload): void {
        $this->payload = $payload;
    }

    /**
     * @return SitemapItemAlternate[]
     */
    public function getAlternates(): array {
        return $this->alternates;
    }

    /**
     * @param SitemapItemAlternate[] $alternates
     */
    public function setAlternates(array $alternates): void {
        $this->alternates = $alternates;
    }
}
