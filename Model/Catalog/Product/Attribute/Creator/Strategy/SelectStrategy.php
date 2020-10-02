<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SelectStrategy
 */
class SelectStrategy extends AbstractOptionTypeStrategy
{
    /**
     * @throws LocalizedException
     *
     * @return int
     */
    public function execute(): int
    {
        if (empty($this->attrData['value'])
            || (empty($this->attrData['value']['value']) && $this->attrData['value']['value'] !== '0')
            || empty($this->attrData['value']['key'])
        ) {
            return 0;
        }

        $options = $this->prepareOptions();
        $eavAttribute = $this->getEavAttribute();

        if (!$eavAttribute->getId() && ($eavAttribute->getFrontendInput() !== 'select')) {
            $this->createNewAttribute($options);
            $eavAttribute = $this->getEavAttribute();
        }

        foreach ($options['value'] as $pimcoreId => $option) {
            $optionId = $this->getOptionId($eavAttribute->getId(), $pimcoreId);

            if (!$optionId) {
                $optionId = $this->createAttributeOption($eavAttribute->getId(), $pimcoreId);
            }

            $optionValues = ['value' => [$pimcoreId => $options['value'][$pimcoreId]]];
            $this->insertOptionValues($optionId, $optionValues);
        }

        return $eavAttribute->getAttributeId();
    }

    /**
     * @return array
     */
    protected function prepareOptions(): array
    {

        $value = $this->attrData['value']['value'];
        $label = $this->attrData['value']['key'];

        $options = [
            'value' => [
                $value => [0 => $label, $this->storeManager->getStore()->getId() => $label],
            ],
        ];

        return $options;
    }

    /**
     * @param array $options
     *
     * @return void
     */
    protected function createNewAttribute(array $options)
    {
        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute(
            Product::ENTITY,
            $this->code,
            $this->getMergedConfig($this->getBaseAttrConfig())
        );
    }

    /**
     * @return bool
     */
    private function isConfigurable(): bool
    {
        return (!empty($this->attrData['is_configurable']) && true === $this->attrData['is_configurable']);
    }

    /**
     * @return array
     */
    public function getBaseAttrConfig(): array
    {
        $data = [
            'type'         => 'int',
            'label'        => $this->attrData['label'],
            'input'        => 'select',
            'user_defined' => true,
        ];

        if ($this->isConfigurable()) {
            $data = array_merge($data, [
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL
            ]);
        }

        return $data;
    }
}
