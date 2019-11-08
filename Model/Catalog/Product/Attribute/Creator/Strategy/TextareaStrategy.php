<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;

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
        // check if we can load the attribute
        $attribute = $eavSetup->getAttribute(Product::ENTITY,$this->code);

        // getAttribute returns array if attribute doesnt exists
        if(is_array($attribute) && empty($attribute)){
            $eavSetup->addAttribute(
                Product::ENTITY,
                $this->code,
                array_merge(self::$defaultAttrConfig, [
                    'type' => 'text',
                    'label' => $this->attrData['label'],
                    'input' => 'textarea',
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => false,
                    'filterable' => false,
                    'is_filterable' => false,
                    'filterable_in_search' => false,
                    'is_filterable_in_search' => false,
                ])
            );
        }
        return $eavSetup->getAttributeId(Product::ENTITY, $this->code);
    }
}
