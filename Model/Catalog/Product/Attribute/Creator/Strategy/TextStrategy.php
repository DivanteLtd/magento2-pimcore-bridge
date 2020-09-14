<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Setup\EavSetup;

/**
 * Class TextStrategy
 */
class TextStrategy extends AbstractStrategy
{
    /**
     * @return int
     */
    public function execute(): int
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $attributeConfiguration = $this->getAttributeConfiguration($eavSetup);

        $eavSetup->addAttribute(
            Product::ENTITY,
            $this->code,
            array_merge($attributeConfiguration, [
                'type'  => 'varchar',
                'label' => $this->attrData['label'],
                'input' => 'text',
            ])
        );

        return $eavSetup->getAttributeId(Product::ENTITY, $this->code);
    }

    /**
     * @param EavSetup $eavSetup
     * @return array
     */
    public function getAttributeConfiguration(EavSetup $eavSetup): array
    {
        $existingAttribute = $eavSetup->getAttribute(CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID, $this->code);

        if (!$existingAttribute) {
            return self::$defaultAttrConfig;
        }

        return $this->getExistingAttributeOptions($existingAttribute);
    }
}
