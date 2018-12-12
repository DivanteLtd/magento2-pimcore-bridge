<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api;

use Magento\Framework\Webapi\Rest\Request;
use Zend\Http\Response;

/**
 * Interface RequestClientIntegration
 *
 * @api
 */
interface RequestClientInterface
{
    /**
     * @return Response
     */
    public function send(): Response;

    /**
     * @return int
     */
    public function getStoreViewId(): int;

    /**
     * @param int $storeViewId
     *
     * @return RequestClientInterface
     */
    public function setStoreViewId(int $storeViewId): RequestClientInterface;

    /**
     * Get request url
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $uri
     *
     * @return RequestClientInterface
     */
    public function setUri(string $uri): RequestClientInterface;

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     *
     * @return RequestClientInterface
     */
    public function setMethod(string $method = Request::HTTP_METHOD_GET): RequestClientInterface;

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setPostData(array $data): RequestClientInterface;

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setQueryData(array $data): RequestClientInterface;

    /**
     * @param string $prefix
     *
     * @return RequestClientInterface
     */
    public function setEventPrefix(string $prefix): RequestClientInterface;
}
