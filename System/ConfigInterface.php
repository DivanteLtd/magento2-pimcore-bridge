<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\System;

/**
 * Class ConfigInterface
 */
interface ConfigInterface
{
    /**
     * Configuration path for module status
     */
    const XML_PATH_MODULE_ENABLED = 'configuration/basic/is_enabled';

    /**
     * Configuration for monolog handler
     */
    const XML_PATH_LOGGER_TYPE = 'configuration/basic/logger_type';

    /**
     * Configuration for queue outdated value
     */
    const XML_PATH_QUEUE_OUTDATED = 'configuration/basic/queue_outdated';

    /**
     * Configuration path for rices override settings
     */
    const XML_PATH_PRICES_OVERRIDE = 'configuration/prices/is_override_enabled';

    /**
     * Configuration path for Pimcore API Key used for request authorization
     */
    const XML_PATH_PIMCORE_API_KEY = 'pimcore/integration/api_key';

    /**
     * Configuration path for Pimcore Endpoint
     */
    const XML_PATH_PIMCORE_ENDPOINT = 'pimcore/integration/endpoint';

    /**
     * Configuration path for Pimcore Endpoint
     */
    const XML_PATH_INSTANCE_URL = 'pimcore/integration/instance_url';

    /**
     * Configuration path for Category Queue Process
     */
    const XML_PATH_CAT_QUEUE_PROCESS = 'pimcore/integration/category_queue_process';

    /**
     * Configuration path for Product Queue Process
     */
    const XML_PATH_PROD_QUEUE_PROCESS = 'pimcore/integration/product_queue_process';

    /**
     * Configuration path for Asset Queue Process
     */
    const XML_PATH_ASSET_QUEUE_PROCESS = 'pimcore/integration/asset_queue_process';

    /**
     * Configuration path for Asset Queue Process
     */
    const XML_PATH_CRON_PUBLISH_IS_ACTIVE = 'cron/enable_products/is_active';


    /**
     * @return bool
     */
    public function isConfigurationValid(): bool;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string|null
     */
    public function getPimcoreApiKey();

    /**
     * @return int
     */
    public function getLoggerType(): int;

    /**
     * @return string
     */
    public function getPimcoreEndpoint(): string;

    /**
     * @return int
     */
    public function getCategoryQueueProcess(): int;

    /**
     * @return int
     */
    public function getProductQueueProcess(): int;

    /**
     * @return int
     */
    public function getAssetQueueProcess(): int;

    /**
     * @return string
     */
    public function getInstanceUrl(): string;

    /**
     * @return string
     */
    public function getQueueOutdatedValue(): string;

    /**
     * @return bool
     */
    public function getIsProductPublishActive(): bool;

    /**
     * @return bool
     */
    public function getIsPriceOverride(): bool;
}
