<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Entity\Attribute\Backend;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;

/**
 * Class IsActiveInPimcore
 */
class IsActiveInPimcore extends AbstractBackend
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * IsActiveInPimcore constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param DataObject|Product $object
     *
     * @return AbstractBackend
     */
    public function afterSave($object)
    {
        //if (!$object->getData('is_active_in_pim')) {
        //    $object->setStatus(Status::STATUS_DISABLED);
        //}

        return parent::afterSave($object);
    }
}
