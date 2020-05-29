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
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
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

        foreach ($options['value'] as $pimcoreId => $option) {
            $optionId = $this->getOptionId($eavAttribute->getId(), $pimcoreId);

            if (!$optionId) {
                $optionId = $this->createAttributeOption($eavAttribute->getId(), $pimcoreId);
            }

            $optionValues = ['value' => [$pimcoreId => $options['value'][$pimcoreId]]];
            $this->insertOptionValues($optionId, $optionValues);
            $this->insertSwatchValues($optionId, $swatches);
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
                sprintf($value) => [0 => $label, $this->storeManager->getStore()->getId() => $label],
            ],
        ];

        return $options;
    }

    /**
     * @return array
     */
    protected function prepareSwatches(): array
    {
        $value = $this->attrData['value']['swatch_value'];

        if ($this->attrData['value']['swatch_type'] == 2) {
            $value = $this->swatchManager->createVisualSwatchFromBase64($this->attrData['value']['key'], $value);
        }

        $swatch = ['type' => $this->attrData['value']['swatch_type'], 'value' => $value];

        return $swatch;
    }

    /**
     * @param string $optionId
     * @param array $swatch
     */
    protected function insertSwatchValues(string $optionId, array $swatch)
    {
        $connection = $this->resource->getConnection();

        $tableName = $connection->getTableName('eav_attribute_option_swatch');
        $connection->insertOnDuplicate(
            $tableName,
            [
                'option_id' => $optionId,
                'store_id' => 0, // is always saved in default
                'value' => $swatch['value'],
                'type' => $swatch['type'],
            ],
            ['option_id', 'store_id', 'value', 'type']
        );
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

        $eavAttribute = $this->attributeRepository->get('catalog_product', $this->code);
        $eavAttribute->setData('swatch_input_type', 'visual');
        $this->attributeRepository->save($eavAttribute);
    }

    /**
     * @return array
     */
    public function getBaseAttrConfig(): array
    {
        $data = [
            'type' => 'varchar',
            'label' => $this->attrData['label'],
            'user_defined' => true,
            'source' => Table::class,
            'swatch_input_type' => 'visual',
            'input' => 'select',
        ];

        if ($this->isConfigurable()) {
            $data = array_merge($data, [
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]);
        }

        return $data;
    }

    /**
     * @return bool
     */
    private function isConfigurable(): bool
    {
        return (!empty($this->attrData['is_configurable']) && true === $this->attrData['is_configurable']);
    }
}
