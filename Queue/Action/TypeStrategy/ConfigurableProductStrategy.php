<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\TypeStrategy;

use Divante\PimcoreIntegration\Exception\InvalidDataStructureException;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\PropertyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;

/**
 * Class ConfigurableProductStrategy
 */
class ConfigurableProductStrategy implements ProductTypeCreationStrategyInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var OptionsFactory
     */
    private $optionsFactory;

    /**
     * ConfigurableProductStrategy constructor.
     *
     * @param Config $eavConfig
     * @param OptionsFactory $optionsFactory
     */
    public function __construct(Config $eavConfig, OptionsFactory $optionsFactory)
    {
        $this->eavConfig = $eavConfig;
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * @param ProductInterface $product
     *
     * @throws InvalidDataStructureException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return ProductInterface
     */
    public function execute(ProductInterface $product): ProductInterface
    {
        /** @var PropertyInterface|null $optionsProperty */
        $optionsProperty = $product->getOptionsProperty();

        if (null === $optionsProperty || empty($optionsProperty->getPropData())) {
            $this->throwInvalidDataStructureException($product);
        }

        $attrCodes = explode(',', $optionsProperty->getPropData());

        if (empty($attrCodes)) {
            $this->throwInvalidDataStructureException($product);
        }

        $invalidConfigurableAttrs = [];
        foreach ($attrCodes as $code) {
            if (!$product->getData($code)) {
                $invalidConfigurableAttrs[] = $code;
            }
        }

        if (!empty($invalidConfigurableAttrs)) {
            throw new InvalidDataStructureException(
                __(
                    'Value of configurable attributes [%1] must be set for product with pimcore_id "%2"',
                    implode(',', $invalidConfigurableAttrs),
                    $product->getData('pimcore_id')
                )
            );
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
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductOptions($configurableOptions);
        $product->setExtensionAttributes($extensionAttributes);
        $product->setTypeId(Configurable::TYPE_CODE);

        return $product;
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
