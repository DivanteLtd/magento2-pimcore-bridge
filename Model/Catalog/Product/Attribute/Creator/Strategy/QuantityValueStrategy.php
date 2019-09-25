<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author azu tripuls.de
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;/**
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
            array_merge(self::$defaultAttrConfig, [
                'type'  => 'decimal',
                'label' => $this->attrData['label'] . ' ['. $this->attrData['unit'] . ']',
                'input' => 'text',
            ])
        );        return $eavSetup->getAttributeId(Product::ENTITY, $this->code);
    }
}