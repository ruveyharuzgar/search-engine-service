<?php

namespace App\Service;

use App\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;

class ProviderManager
{
    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(
        private iterable $providers,
        private LoggerInterface $logger
    ) {}

    /**
     * Tüm provider'lardan içerikleri toplar
     */
    public function fetchAllContents(): array
    {
        $allContents = [];

        foreach ($this->providers as $provider) {
            try {
                $this->logger->info("Provider'dan veri çekiliyor: " . $provider->getName());
                $contents = $provider->fetchContents();
                $allContents = array_merge($allContents, $contents);
                $this->logger->info(sprintf(
                    "%s provider'dan %d içerik alındı",
                    $provider->getName(),
                    count($contents)
                ));
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    "Provider hatası (%s): %s",
                    $provider->getName(),
                    $e->getMessage()
                ));
            }
        }

        return $allContents;
    }
}
