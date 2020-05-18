<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\Swatch\VisualSwatchManager;
use Divante\PimcoreIntegration\Model\Eav\Entity\OptionResolver;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Class VisualswatchStrategy
 */
class VisualswatchStrategy extends AbstractOptionTypeStrategy
{
    /**
     * @var VisualSwatchManager
     */
    protected $swatchManager;

    /**
     * VisualswatchStrategy constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param StoreManagerInterface $storeManager
     * @param Attribute $eavAttribute
     * @param OptionResolver $optionResolver
     * @param ResourceConnection $resource
     * @param AttributeFactory $attributeFactory
     * @param VisualSwatchManager $visualSwatchManager
     * @param array $attrData
     * @param string $code
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionManagementInterface $attributeOptionManagement,
        StoreManagerInterface $storeManager,
        Attribute $eavAttribute,
        OptionResolver $optionResolver,
        ResourceConnection $resource,
        AttributeFactory $attributeFactory,
        VisualSwatchManager $visualSwatchManager,
        array $attrData,
        string $code
    ) {
        parent::__construct(
            $eavSetupFactory,
            $attributeRepository,
            $attributeOptionManagement,
            $storeManager,
            $eavAttribute,
            $optionResolver,
            $resource,
            $attributeFactory,
            $attrData,
            $code
        );

        $this->swatchManager = $visualSwatchManager;
    }


    /**
     * @return int
     */
    public function execute(): int
    {
        if (empty($this->attrData['value'])) {
            return 0;
        }

        $options = $this->prepareOptions();
        $swatches = $this->prepareSwatches();
        $eavAttribute = $this->getEavAttribute();

        if (!$eavAttribute->getId() && $eavAttribute->getFrontendInput() !== 'select') {
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
                $this->insertSwatchValues($optionId, $swatches['swatch'][$pimcoreId]);
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
     * @return array
     */
    protected function prepareSwatches(): array
    {
        foreach ($this->attrData['value'] as $option) {
            if (!isset($option['key'])) {
                continue;
            }

            $value = $option['swatch_value'];

            if ($option['swatch_type'] == 1) {
                $value = $this->swatchManager->createVisualSwatchFromBase64($option['key'], $value);
            }

            $swatch = ['type' => $option['swatch_type'], 'value' => $value];
            $swatches['swatch'][$option['value']] = [0 => $swatch];
        }

        return $swatches;
    }

    /**
     * @param string $optionId
     * @param array $swatches
     */
    protected function insertSwatchValues(string $optionId, array $swatches)
    {
        $connection = $this->resource->getConnection();

        $tableName = $connection->getTableName('eav_attribute_option_swatch');
        foreach ($swatches as $storeId => $swatch) {
            $connection->insertOnDuplicate(
                $tableName,
                [
                    'option_id' => $optionId,
                    'store_id' => $storeId,
                    'value' => $swatch['value'],
                    'type' => $swatch['type'],
                ],
                ['option_id', 'store_id', 'value', 'type']
            );
        }
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
            'type' => 'varchar',
            'label' => $this->attrData['label'],
            'input' => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            'user_defined' => true,
            'source' => Table::class,
        ];
    }
}
