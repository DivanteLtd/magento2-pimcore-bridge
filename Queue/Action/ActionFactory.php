<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Exception\InvalidTypeException;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ActionFactory
 */
class ActionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ActionFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     *
     * @throws InvalidTypeException
     *
     * @return ActionInterface
     */
    public function createByType(string $type): ActionInterface
    {
        switch ($type) {
            case ActionInterface::UPDATE_PRODUCT_ACTION:
                return $this->createUpdateProductAction();
            case ActionInterface::UPDATE_CATEGORY_ACTION:
                return $this->createUpdateCategoryAction();
            case ActionInterface::DELETE_PRODUCT_ACTION:
                return $this->createDeleteProductAction();
            case ActionInterface::DELETE_CATEGORY_ACTION:
                return $this->createDeleteCategoryAction();
            case ActionInterface::UPDATE_ASSET_ACTION:
                return $this->createUpdateAssetAction();
            case ActionInterface::DELETE_ASSET_ACTION:
                return $this->createDeleteAssetAction();
            default:
                throw new InvalidTypeException(__('Invalid action type "%1"', $type));
        }
    }

    /**
     * @return UpdateCategoryAction
     */
    public function createUpdateCategoryAction(): ActionInterface
    {
        return $this->objectManager->create(UpdateCategoryAction::class);
    }

    /**
     * @return DeleteCategoryAction
     */
    public function createDeleteCategoryAction(): ActionInterface
    {
        return $this->objectManager->create(DeleteCategoryAction::class);
    }

    /**
     * @return UpdateAssetAction
     */
    public function createUpdateAssetAction(): ActionInterface
    {
        return $this->objectManager->create(UpdateAssetAction::class);
    }

    /**
     * @return DeleteAssetAction
     */
    public function createDeleteAssetAction(): ActionInterface
    {
        return $this->objectManager->create(DeleteAssetAction::class);
    }

    /**
     * @return UpdateProductAction
     */
    public function createUpdateProductAction(): ActionInterface
    {
        return $this->objectManager->create(UpdateProductAction::class);
    }

    /**
     * @return DeleteProductAction
     */
    public function createDeleteProductAction(): ActionInterface
    {
        return $this->objectManager->create(DeleteProductAction::class);
    }
}
