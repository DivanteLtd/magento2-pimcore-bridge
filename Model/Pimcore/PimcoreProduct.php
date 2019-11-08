<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Pimcore;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreAttributeMapperInterface;
use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\Exception\InvalidTypeException;
use Divante\PimcoreIntegration\Model\Pimcore\Mapper\ComplexMapperInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Product
 */
class PimcoreProduct extends DataObject implements PimcoreProductInterface
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var PimcoreAttributeMapperInterface
     */
    private $pimcoreAttributeMapper;

    /**
     * @var ComplexMapperInterface[]
     */
    private $complexMappers;

    /**
     * PimcoreProduct constructor.
     *
     * @param EntityType $entityType
     * @param PimcoreAttributeMapperInterface $pimcoreAttributeMapper
     * @param array $complexMappers
     * @param array $data
     */
    public function __construct(
        EntityType $entityType,
        PimcoreAttributeMapperInterface $pimcoreAttributeMapper,
        array $complexMappers = [],
        array $data = []
    ) {
        parent::__construct($data);

        $this->entityType = $entityType;
        $this->pimcoreAttributeMapper = $pimcoreAttributeMapper;
        $this->complexMappers = $complexMappers;
    }

    /**
     * @param array $elements
     *
     * @return PimcoreProductInterface
     *
     * @throws LocalizedException
     */
    public function setElements(array $elements): PimcoreProductInterface
    {
        foreach ($elements as $attributeCode => $attributeData) {

            if(strlen($attributeCode) > 60){
                $attributeCode = 'CS'.md5($attributeCode);
            }

            if (array_key_exists($attributeCode, $this->complexMappers)) {
                if (!$this->complexMappers[$attributeCode] instanceof ComplexMapperInterface) {
                    throw new InvalidTypeException(
                        __('Mapper must implement %1 interface', ComplexMapperInterface::class)
                    );
                }

                $mappedAttributeData = $this->complexMappers[$attributeCode]->map($attributeData);
            } else {
                $mappedAttributeData = $this->pimcoreAttributeMapper->mapUsingType($attributeData);
            }

            $this->setData($attributeCode, $mappedAttributeData);
        }

        $this->setAttributeSetId($this->getDefaultProductAttributeSetId());

        return $this;
    }

    /**
     * @param string|int $attributeSet
     *
     * @return PimcoreProductInterface
     */
    public function setAttributeSetId($attributeSet): PimcoreProductInterface
    {
        return $this->setData(self::ATTRIBUTE_SET_ID, $attributeSet);
    }

    /**
     * @return null|string
     */
    private function getDefaultProductAttributeSetId()
    {
        return $this->entityType->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE)->getDefaultAttributeSetId();
    }
}
