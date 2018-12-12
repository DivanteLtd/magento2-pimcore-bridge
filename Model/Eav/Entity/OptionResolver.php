<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Eav\Entity;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OptionResolver
 */
class OptionResolver
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Attribute
     */
    private $eavAttribute;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * OptionResolver constructor.
     *
     * @param ResourceConnection $resource
     * @param Attribute $eavAttribute
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resource,
        Attribute $eavAttribute,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->eavAttribute = $eavAttribute;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $value
     * @param int|string $attr
     *
     * @return string
     */
    public function resolveOptionId(string $value, $attr): string
    {
        if (!\is_int($attr)) {
            $attr = $this->eavAttribute->getIdByCode(Product::ENTITY, (string) $attr);
        }

        $connection = $this->resource->getConnection();
        $query = $connection->select()->from(
            ['v' => $connection->getTableName('eav_attribute_option_value')],
            ['v.option_id']
        )->joinLeft(
            ['o' => $connection->getTableName('eav_attribute_option')],
            'v.option_id=o.option_id',
            ''
        )->where('v.value = ?', $value)
            ->where('o.attribute_id = ?', $attr)
            ->where('v.store_id = ?', $this->storeManager->getStore()->getId())
            ->group('v.value');

        return $connection->fetchOne($query);
    }

    /**
     * @param array $values
     * @param int|string $attr
     *
     * @return array
     */
    public function resolveMultipleOptionIds(array $values, $attr): array
    {
        if (!\is_int($attr)) {
            $attr = $this->eavAttribute->getIdByCode(Product::ENTITY, (string) $attr);
        }

        $connection = $this->resource->getConnection();
        $query = $connection->select()->from(
            ['v' => $connection->getTableName('eav_attribute_option_value')],
            ['v.option_id']
        )->joinLeft(
            ['o' => $connection->getTableName('eav_attribute_option')],
            'v.option_id=o.option_id',
            ''
        )->where('v.value IN (?)', $values)
            ->where('o.attribute_id = ?', $attr)
            ->where('v.store_id = ?', $this->storeManager->getStore()->getId())
            ->group('v.value');

        return $connection->fetchCol($query);
    }

    /**
     * @param string $attributeId
     * @param string $value
     *
     * @return string
     */
    public function getOptionId(string $attributeId, string $value): string
    {
        $connection = $this->resource->getConnection();
        $query = $connection->select()
            ->from(
                ['e' => $connection->getTableName('eav_attribute_option')],
                ['option_id']
            )->where('e.attribute_id = ?', $attributeId)
            ->where('e.pimcore_id = ?', $value);

        return $connection->fetchOne($query);
    }
}
