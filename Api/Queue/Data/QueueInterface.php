<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue\Data;

/**
 * Interface QueueInterface
 */
interface QueueInterface
{
    /**
     * ID of transaction via REST Api. Model PK
     */
    const TRANSACTION_ID = 'transaction_id';

    /**
     * ID of published pimcore product's store view
     */
    const STORE_VIEW_ID = 'store_view_id';

    /**
     * Status of processing import
     */
    const STATUS = 'status';

    /**
     * Type of importer action
     */
    const ACTION = 'action';

    /**
     * Date when product was published
     */
    const CREATED_AT = 'created_at';

    /**
     * Date when status of import has been changed
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Date of complete product import to Magento
     */
    const FINISHED_AT = 'finished_at';

    /**
     * Status for new published products in queue
     */
    const STATUS_NEW = 0;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getStoreViewId();

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @return string|null
     */
    public function getAction();

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @return string|null
     */
    public function getFinishedAt();

    /**
     * @param int $transactionId
     *
     * @return $this
     */
    public function setId($transactionId);

    /**
     * @param int $store
     *
     * @return $this
     */
    public function setStoreViewId(int $store);

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status);

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action);

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * @param string $finishedAt
     *
     * @return $this
     */
    public function setFinishedAt(string $finishedAt);

    /**
     * @return int|null
     */
    public function getPimcoreId();

    /**
     * @return string
     */
    public function getQueueType(): string;

    /**
     * @param array $keys
     *
     * @return array
     */
    public function toArray(array $keys = []);
}
