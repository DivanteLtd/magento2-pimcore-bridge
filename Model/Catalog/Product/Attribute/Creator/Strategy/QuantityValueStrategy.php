<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;

/**
 * Class QuantityValueStrategy
 */
class QuantityValueStrategy extends AbstractStrategy
{
    /**
     * @return int
     */
    public function execute(): int
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            Product::ENTITY,
            $this->code,
            $this->getMergedConfig($this->getBaseAttrConfig())
        );

        return $eavSetup->getAttributeId(Product::ENTITY, $this->code);
    }

    /**
     * @return array
     */
    public function getBaseAttrConfig(): array
    {
        return [
            'type' => 'decimal',
            'label' => $this->getLabel(),
            'input' => 'text',
        ];
    }

    /**
     * @return string
     */
    private function getLabel(): string
    {
        return $this->attrData['label'] . ' (' . $this->attrData['unit'] . ')';
    }
}
