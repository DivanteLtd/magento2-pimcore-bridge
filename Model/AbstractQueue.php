<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model;

use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractQueue
 */
abstract class AbstractQueue extends AbstractModel implements QueueInterface
{
    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(QueueInterface::TRANSACTION_ID);
    }

    /**
     * @return string|null
     */
    public function getStoreViewId()
    {
        return $this->getData(QueueInterface::STORE_VIEW_ID);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(QueueInterface::STATUS);
    }

    /**
     * @return null|string
     */
    public function getAction()
    {
        return $this->getData(QueueInterface::ACTION);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(QueueInterface::CREATED_AT);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(QueueInterface::UPDATED_AT);
    }

    /**
     * @return string|null
     */
    public function getFinishedAt()
    {
        return $this->getData(QueueInterface::FINISHED_AT);
    }

    /**
     * @param int $transactionId
     *
     * @return $this
     */
    public function setId($transactionId)
    {
        $this->setData(QueueInterface::TRANSACTION_ID, $transactionId);

        return $this;
    }

    /**
     * @param int $store
     *
     * @return $this
     */
    public function setStoreViewId(int $store)
    {
        return $this->setData(QueueInterface::STORE_VIEW_ID, $store);
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status)
    {
        return $this->setData(QueueInterface::STATUS, $status);
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action)
    {
        return $this->setData(QueueInterface::ACTION, $action);
    }

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(string $createdAt)
    {
        return $this->setData(QueueInterface::CREATED_AT, $createdAt);
    }

    /**
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt)
    {
        return $this->setData(QueueInterface::UPDATED_AT, $updatedAt);
    }

    /**
     * @param string $finishedAt
     *
     * @return $this
     */
    public function setFinishedAt(string $finishedAt)
    {
        return $this->setData(QueueInterface::FINISHED_AT, $finishedAt);
    }
}
