<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class DeleteProductAction
 */
class DeleteProductAction implements ActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * DeleteProductAction constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ManagerInterface $eventManager
     * @param ActionResultFactory $actionResultFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ManagerInterface $eventManager,
        ActionResultFactory $actionResultFactory
    ) {
        $this->productRepository = $productRepository;
        $this->eventManager = $eventManager;
        $this->actionResultFactory = $actionResultFactory;
    }

    /**
     * @param QueueInterface|ProductQueueInterface $queue
     * @param mixed $data
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        try {
            $product = $this->productRepository->getByPimId($queue->getProductId());
            $this->eventManager->dispatch('pimcore_product_delete_before', ['product' => $product]);
            $this->productRepository->delete($product);
            $this->eventManager->dispatch('pimcore_product_delete_after', ['product' => $product]);
        } catch (\Exception $ex) {
            // Fail gracefully
        }

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }
}
