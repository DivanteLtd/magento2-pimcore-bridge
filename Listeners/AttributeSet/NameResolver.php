<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners\AttributeSet;

use Magento\Framework\App\ResourceConnection;

/**
 * Class NameResolver
 */
class NameResolver implements NameResolverInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * NameResolver constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns new viable pimcore attribute set name
     *
     * @return string
     */
    public function getNextPimcoreAttributeSetName(): string
    {
        $connection = $this->resource->getConnection();
        $query = $connection->select()
            ->from(
                'information_schema.TABLES',
                'AUTO_INCREMENT'
            )->where('TABLE_NAME = ?', $connection->getTableName('eav_attribute_set')
            )->where('TABLE_SCHEMA = ?', $this->resource->getSchemaName(ResourceConnection::DEFAULT_CONNECTION));

        return sprintf('pimcore-set-%s', (int)$connection->fetchOne($query));
    }
}
