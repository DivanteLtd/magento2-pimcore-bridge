<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Notification;

/**
 * Interface PimcoreNotificationInterface
 */
interface PimcoreNotificatorInterface
{
    /**
     * @return bool
     */
    public function send(): bool;

    /**
     * @param string $pimId
     *
     * @return PimcoreNotificatorInterface
     */
    public function setPimId(string $pimId): PimcoreNotificatorInterface;

    /**
     * @param string $message
     *
     * @return PimcoreNotificatorInterface
     */
    public function setMessage(string $message): PimcoreNotificatorInterface;

    /**
     * @param string $uriPath
     *
     * @return PimcoreNotificatorInterface
     */
    public function setUriPath(string $uriPath): PimcoreNotificatorInterface;

    /**
     * @param string $status
     *
     * @return PimcoreNotificatorInterface
     */
    public function setStatus(string $status): PimcoreNotificatorInterface;

    /**
     * @param string $id
     *
     * @return PimcoreNotificatorInterface
     */
    public function setStoreViewId(string $id): PimcoreNotificatorInterface;
}
