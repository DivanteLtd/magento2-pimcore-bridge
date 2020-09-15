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
 * Class TextareaStrategy
 */
class TextareaStrategy extends AbstractStrategy
{
    /**
     * @return int
     */
    public function execute(): int
    {
        $eavSetup = $this->eavSetupFactory->create();

        $attributeConfiguration = $this->getAttributeConfiguration($eavSetup);

        $eavSetup->addAttribute(
            Product::ENTITY,
            $this->code,
            array_merge($attributeConfiguration, [
                'type'                     => 'text',
                'label'                    => $this->attrData['label'],
                'input'                    => 'textarea',
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
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
