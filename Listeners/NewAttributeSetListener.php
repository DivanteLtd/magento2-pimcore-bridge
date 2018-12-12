<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Divante\PimcoreIntegration\Api\Data\AttributeSetInterface;
use Divante\PimcoreIntegration\Api\Data\AttributeSetInterfaceFactory;
use Divante\PimcoreIntegration\Exception\InvalidChecksumException;
use Divante\PimcoreIntegration\Listeners\AttributeSet\NameResolverFactory;
use Divante\PimcoreIntegration\Listeners\AttributeSet\NameResolverInterface;
use Magento\Catalog\Api\AttributeSetManagementInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class NewAttributeSetListener
 */
class NewAttributeSetListener implements ObserverInterface
{
    /**
     * @var AttributeSetManagementInterface
     */
    private $attributeSetManagement;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var int
     */
    private $defaultAttrSetId;

    /**
     * @var AttributeSetInterfaceFactory
     */
    private $attributeSetFactory;

    /**
     * @var NameResolverInterface
     */
    private $nameResolver;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * AttributeSetHandler constructor.
     *
     * @param AttributeSetManagementInterface $attributeSetManagement
     * @param Config $eavConfig
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AttributeSetInterfaceFactory $attributeSetFactory
     * @param NameResolverFactory $nameResolverFactory
     * @param Registry $registry
     */
    public function __construct(
        AttributeSetManagementInterface $attributeSetManagement,
        Config $eavConfig,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeSetInterfaceFactory $attributeSetFactory,
        NameResolverFactory $nameResolverFactory,
        Registry $registry
    ) {
        $this->attributeSetManagement = $attributeSetManagement;
        $this->eavConfig = $eavConfig;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->nameResolver = $nameResolverFactory->create();
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(Observer $observer)
    {
        $productsData = $observer->getData('products');

        if (empty($productsData)) {
            return;
        }

        foreach ($productsData as $pimId => $data) {
            try {
                if (!$this->isChecksumValid($data)) {
                    throw new InvalidChecksumException(__('Product response does not contain checksum information.'));
                }

                $this->attributeSetRepository->getByChecksum($data['attr_checksum']['value']);
            } catch (NoSuchEntityException $ex) {
                /** @var AttributeSetInterface $newSet */
                $newSet = $this->attributeSetFactory->create();

                $newSet->setEntityTypeId($this->eavConfig->getEntityType(Product::ENTITY)->getId())
                    ->setChecksum($data['attr_checksum']['value'])
                    ->setAttributeSetName($this->nameResolver->getNextPimcoreAttributeSetName());

                $this->attributeSetManagement->create($newSet, $this->getDefaultAttrSetId());
                $this->registry->register('is_attribute_set_new', true, true);
            }
        }
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function isChecksumValid(array $data): bool
    {
        return !empty($data['attr_checksum']['value']);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return int
     */
    private function getDefaultAttrSetId(): int
    {
        if (!$this->defaultAttrSetId) {
            $this->defaultAttrSetId = $this->eavConfig
                ->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getDefaultAttributeSetId();
        }

        return $this->defaultAttrSetId;
    }
}
