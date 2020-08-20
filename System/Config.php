<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Configuration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string $scope
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, string $scope)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->scope = $scope;
    }

    /**
     * @return bool
     */
    public function isConfigurationValid(): bool
    {
        return $this->isEnabled() && (bool) $this->getPimcoreApiKey();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_MODULE_ENABLED, $this->scope);
    }

    /**
     * @return string|null
     */
    public function getPimcoreApiKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_PIMCORE_API_KEY, $this->scope);
    }

    /**
     * @return int
     */
    public function getLoggerType(): int
    {
        return (int) $this->scopeConfig->getValue(static::XML_PATH_LOGGER_TYPE, $this->scope);
    }

    /**
     * @return string
     */
    public function getPimcoreEndpoint(): string
    {
        return (string) $this->scopeConfig->getValue(static::XML_PATH_PIMCORE_ENDPOINT, $this->scope);
    }

    /**
     * @return int
     */
    public function getCategoryQueueProcess(): int
    {
        return (int) $this->scopeConfig->getValue(static::XML_PATH_CAT_QUEUE_PROCESS, $this->scope);
    }

    /**
     * @return int
     */
    public function getProductQueueProcess(): int
    {
        return (int) $this->scopeConfig->getValue(static::XML_PATH_PROD_QUEUE_PROCESS, $this->scope);
    }

    /**
     * @return int
     */
    public function getAssetQueueProcess(): int
    {
        return (int) $this->scopeConfig->getValue(static::XML_PATH_ASSET_QUEUE_PROCESS, $this->scope);
    }

    /**
     * @return string
     */
    public function getInstanceUrl(): string
    {
        $confValue = $this->scopeConfig->getValue(static::XML_PATH_INSTANCE_URL, $this->scope);

        if (!$confValue) {
            /** @var Store $store */
            $store = $this->storeManager->getStore();
            $confValue = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        }

        return trim($confValue, '/');
    }

    /**
     * @return string
     */
    public function getQueueOutdatedValue(): string
    {
        return (string) $this->scopeConfig->getValue(static::XML_PATH_QUEUE_OUTDATED, $this->scope);
    }

    /**
     * @return bool
     */
    public function getIsProductPublishActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_CRON_PUBLISH_IS_ACTIVE, $this->scope);
    }

    /**
     * @return bool
     */
    public function getIsPriceOverride(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_PRICES_OVERRIDE, $this->scope);
    }
}
