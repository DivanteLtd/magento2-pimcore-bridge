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
 * Class YesnoStrategy
 */
class YesnoStrategy extends AbstractStrategy
{
    /**
     * @return int
     */
    public function execute(): int
    {
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
            'type' => 'int',
            'label'  => $this->attrData['label'],
            'input'  => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
        ];
    }
}
