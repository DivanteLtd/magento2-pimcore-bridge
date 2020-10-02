<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;

/**
 * Class MultiselectStrategy
 */
class MultiselectStrategy extends AbstractOptionTypeStrategy
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return int
     */
    public function execute(): int
    {
        if (empty($this->attrData['value'])) {
            return 0;
        }

        $options = $this->prepareOptions();
        $eavAttribute = $this->getEavAttribute();

        if (!$eavAttribute->getId() && $eavAttribute->getFrontendInput() !== 'multiselect') {
            $this->createNewAttribute($options);
            $eavAttribute = $this->getEavAttribute();
        }

        $options['value'] = array_filter($options['value'], function ($option) {
            $value = $option[$this->storeManager->getStore()->getId()];

            //TODO this is a good place to refactor to collect and filter array with one request
            return !$this->getOptionId($this->getAttributeId(), $value);
        });

        if (!empty($options['value'])) {
            foreach ($options['value'] as $pimcoreId => $option) {
                $optionId = $this->getOptionId($eavAttribute->getId(), $pimcoreId);

                if (!$optionId) {
                    $optionId = $this->createAttributeOption($eavAttribute->getId(), $pimcoreId);
                }

                $optionValues = ['value' => [$pimcoreId => $options['value'][$pimcoreId]]];
                $this->insertOptionValues($optionId, $optionValues);
            }
        }

        return $eavAttribute->getAttributeId();
    }

    /**
     * @return array
     */
    protected function prepareOptions(): array
    {
        $options = [];

        foreach ($this->attrData['value'] as $option) {
            if (!isset($option['key'])) {
                continue;
            }
            $label = $option['key'];
            $options['value'][$option['value']] = [0 => $label, $this->storeManager->getStore()->getId() => $label];
        }

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
     * @return array
     */
    public function getBaseAttrConfig(): array
    {
        return [
            'type'         => 'varchar',
            'label'        => $this->attrData['label'],
            'input'        => 'multiselect',
            'user_defined' => true,
            'backend'      => ArrayBackend::class,
        ];
    }
}
