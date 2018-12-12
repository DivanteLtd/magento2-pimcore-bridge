<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Importer;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Builder\QueueBuilderInterface;
use Divante\PimcoreIntegration\Queue\QueueFactory;
use Divante\PimcoreIntegration\System\Config;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Divante\PimcoreIntegration\Webapi\Response;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\AbstractValidator;
use Monolog\Logger;

/**
 * Class Import
 */
abstract class AbstractImporter
{
    /**
     * Action for insert/update request
     */
    const ACTION_INSERT_UPDATE = 'insert/update';

    /**
     * Action for delete request
     */
    const ACTION_DELETE = 'delete';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var AbstractValidator
     */
    protected $validator;

    /**
     * @var QueueBuilderInterface
     */
    protected $queueBuilder;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * Import constructor.
     *
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param Config|ConfigInterface $config
     * @param ManagerInterface $eventManager
     * @param AbstractValidator $validator
     * @param QueueBuilderInterface $queueBuilder
     * @param QueueFactory $queueFactory
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        BridgeLoggerFactory $bridgeLoggerFactory,
        ConfigInterface $config,
        ManagerInterface $eventManager,
        AbstractValidator $validator,
        QueueBuilderInterface $queueBuilder,
        QueueFactory $queueFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->logger = $bridgeLoggerFactory->getLoggerInstance();
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->validator = $validator;
        $this->queueBuilder = $queueBuilder;
        $this->queueFactory = $queueFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @param string $message
     *
     * @return array
     */
    protected function success(string $message): array
    {
        $this->logger->addInfo(Logger::INFO, [$message]);

        return [
            'data' => [
                'code'    => Response\CodesInterface::CODE_SUCCESS,
                'status'  => 'Accepted',
                'message' => $message,
            ],
        ];
    }

    /**
     * Add published product in Pimcore to Magento import queue
     *
     * @param DataObject $dto
     * @param string $action
     *
     * @return array
     */
    protected function prepareRequest(DataObject $dto, string $action): array
    {
        if (!$this->config->isEnabled()) {
            return $this->critical(['Pimcore bridge is currently disabled.']);
        }

        $dto->setData('action', $action);
        $this->logger->addRecord(Logger::INFO, 'Pimcore data received: ', $dto->toArray());

        $this->eventManager->dispatch('add_to_queue_before', ['data' => $dto->toArray()]);

        return $this->addToQueue($dto);
    }

    /**
     * @param array $errors
     *
     * @return array
     */
    protected function critical(array $errors): array
    {
        $this->logger->addCritical(Logger::CRITICAL, $errors);

        return [
            'data' => [
                'code'   => Response\CodesInterface::CODE_FAILURE,
                'status' => 'Bad Request',
                'errors' => $errors,
            ],
        ];
    }

    /**
     * Add published product in Pimcore to Magento import queue
     *
     * @param DataObject $dto
     *
     * @return array
     */
    abstract protected function addToQueue(DataObject $dto): array;

    /**
     * @param DataObject $dto
     *
     * @return bool
     */
    abstract public function isAlreadyQueued(DataObject $dto): bool;
}
