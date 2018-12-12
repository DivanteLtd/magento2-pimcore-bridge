<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\SetRepository as CoreSetRepository;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SetRepository
 */
class SetRepository extends CoreSetRepository implements AttributeSetRepositoryInterface
{
    /**
     * @param string $checksum
     *
     * @throws NoSuchEntityException
     *
     * @return AttributeSetInterface
     */
    public function getByChecksum(string $checksum): AttributeSetInterface
    {
        $filter = $this->filterBuilder
            ->setField('checksum')
            ->setValue($checksum)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $result = $this->getList($this->searchCriteriaBuilder->create());

        if (!$result->getTotalCount()) {
            throw NoSuchEntityException::singleField('checksum', $checksum);
        }

        $items = $result->getItems();

        return reset($items);
    }
}
