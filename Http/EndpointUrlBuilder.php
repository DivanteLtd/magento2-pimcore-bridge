<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http;

use Divante\PimcoreIntegration\System\ConfigInterface;

/**
 * Class EndpointUrlBuilder
 */
class EndpointUrlBuilder implements UrlBuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * EndpointUrlBuilder constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Builds endpoint url based on configuration
     *
     * @param string $path
     *
     * @return string
     */
    public function build(string $path): string
    {
        return sprintf('%s/%s', trim($this->config->getPimcoreEndpoint(), '/'), $path);
    }
}
