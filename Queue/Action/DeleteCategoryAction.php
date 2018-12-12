<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class DeleteCategoryAction
 */
class DeleteCategoryAction implements ActionInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var ManagerInterface
     */
    private $event;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * UpdateCategoryAction constructor.
     *
     * @param CategoryRepositoryInterface $repository
     * @param ManagerInterface $eventManager
     * @param ActionResultFactory $actionResultFactory
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        ManagerInterface $eventManager,
        ActionResultFactory $actionResultFactory
    ) {
        $this->repository = $repository;
        $this->event = $eventManager;
        $this->actionResultFactory = $actionResultFactory;
    }

    /**
     * @param QueueInterface $queue
     * @param mixed $data
     *
     * @throws InputException
     * @throws StateException
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        try {
            /** @var Category $category */
            $category = $this->repository->getByPimId($queue->getCategoryId());
            $this->event->dispatch('pimcore_category_delete_before', ['category' => $category]);
            $this->repository->delete($category);
            $this->event->dispatch('pimcore_category_delete_after', ['category' => $category]);
        } catch (NoSuchEntityException $ex) {
            // Fail gracefully
        }

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }
}
