<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Divante\PimcoreIntegration\Model\Eav\Entity\OptionResolver;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractOptionTypeStrategy
 */
abstract class AbstractOptionTypeStrategy extends AbstractStrategy
{
    /**
     * @var AttributeOptionManagementInterface
     */
    protected $attributeOptionManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var OptionResolver
     */
    protected $optionResolver;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * SelectStrategy constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param StoreManagerInterface $storeManager
     * @param Attribute $eavAttribute
     * @param OptionResolver $optionResolver
     * @param ResourceConnection $resource
     * @param AttributeFactory $attributeFactory
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
        array $attrData,
        string $code
    ) {
        parent::__construct($eavSetupFactory, $attributeRepository, $attrData, $code);

        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->storeManager = $storeManager;
        $this->eavAttribute = $eavAttribute;
        $this->optionResolver = $optionResolver;
        $this->resource = $resource;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @return array
     */
    abstract protected function prepareOptions(): array;

    /**
     *
     * @return string
     */
    protected function getAttributeId(): string
    {
        return $this->eavAttribute->getIdByCode(Product::ENTITY, $this->code);
    }

    /**
     * @return bool
     */
    protected function getEavAttribute(): \Magento\Eav\Model\Entity\Attribute
    {
        $attribute = $this->attributeFactory->create();
        try {
            $attribute->loadByCode(Product::ENTITY, $this->code);
        } catch (LocalizedException $e) {
            // Fail gracefully
        }

        return $attribute;
    }

    /**
     * @param array $options
     *
     * @return void
     */
    abstract protected function createNewAttribute(array $options);

    /**
     * @param string $attributeId
     * @param string $value
     *
     * @return string
     */
    protected function getOptionId(string $attributeId, string $value): string
    {
        return $this->optionResolver->getOptionId($attributeId, $value);
    }

    /**
     * @param string $optionId
     * @param array $options
     *
     * @return void
     */
    protected function insertOptionValues(string $optionId, array $options)
    {
        $connection = $this->resource->getConnection();

        $tableName = $connection->getTableName('eav_attribute_option_value');
        foreach ($options['value'] as $pimcoreId => $option) {
            foreach ($option as $storeId => $value) {
                $connection->insertOnDuplicate(
                    $tableName,
                    ['option_id' => $optionId, 'store_id' => $storeId, 'value' => $value],
                    ['option_id', 'store_id', 'value']
                );
            }
        }
    }

    /**
     * @param string $attributeId
     * @param array $options
     *
     * @return string
     */
    protected function createAttributeOption($attributeId, $pimcoreId): string
    {
        $connection = $this->resource->getConnection();

        $connection->insert(
            $connection->getTableName('eav_attribute_option'),
            ['attribute_id' => $attributeId, 'pimcore_id' => $pimcoreId]
        );

        return $connection->lastInsertId();
    }
}
