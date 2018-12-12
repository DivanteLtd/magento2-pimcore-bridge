<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Divante\PimcoreIntegration\Exception\InvalidDataStructureException;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\PropertyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LinkConfigurableListener
 */
class LinkConfigurableListener implements ObserverInterface
{
    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var OptionFactory
     */
    private $optionsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LinkConfigurableListener constructor.
     *
     * @param LinkManagementInterface $linkManagement
     * @param ProductRepositoryInterface $productRepository
     * @param Config $eavConfig
     * @param OptionFactory $optionsFactory
     */
    public function __construct(
        LinkManagementInterface $linkManagement,
        ProductRepositoryInterface $productRepository,
        Config $eavConfig,
        OptionFactory $optionsFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->linkManagement = $linkManagement;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->optionsFactory = $optionsFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws InvalidDataStructureException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        $parentId = $product->getData('parent_id');

        if (!$parentId) {
            return;
        }

        try {
            /** @var Product $parent */
            $parent = $this->productRepository->getByPimId($parentId);
        } catch (NoSuchEntityException $e) {
            $this->productRepository->delete($product);

            return;
        }

        /** @var PropertyInterface $optionsProperty */
        $optionsProperty = $product->getOptionsProperty();

        if (null === $optionsProperty || '' === $optionsProperty->getPropData()) {
            $this->throwInvalidDataStructureException($product);
        }

        $attrCodes = explode(',', $optionsProperty->getPropData());

        if (empty($attrCodes)) {
            $this->throwInvalidDataStructureException($product);
        }

        foreach ($attrCodes as $code) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $code);
            $options = $attribute->getOptions();
            array_shift($options);

            $attributeValues = [];
            foreach ($options as $option) {
                $attributeValues[] = [
                    'label'        => $option->getLabel(),
                    'attribute_id' => $attribute->getId(),
                    'value_index'  => $option->getValue(),
                ];
            }

            $configurableAttributesData[] =
                [
                    'attribute_id' => $attribute->getId(),
                    'code'         => $attribute->getAttributeCode(),
                    'label'        => $attribute->getStoreLabel(),
                    'position'     => '0',
                    'values'       => $attributeValues,
                ];
        }

        $configurableOptions = $this->optionsFactory->create($configurableAttributesData);
        $extensionAttributes = $parent->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductOptions($configurableOptions);

        $links = $extensionAttributes->getConfigurableProductLinks();
        $links[$product->getId()] = $product->getId();

        $extensionAttributes->setConfigurableProductLinks($links);

        $parent->setExtensionAttributes($extensionAttributes);

        $this->productRepository->save($parent);
    }

    /**
     * @param ProductInterface $product
     *
     * @throws InvalidDataStructureException
     *
     * @return void
     */
    private function throwInvalidDataStructureException(ProductInterface $product)
    {
        throw new InvalidDataStructureException(
            __(
                'Unable to import variant product %1. Variant product must have defined configurable attributes in properties.',
                $product->getPimId()
            )
        );
    }
}
