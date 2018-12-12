<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Model\Product\Attribute;

use Divante\PimcoreIntegration\Api\Data\AttributeSetInterface;
use Magento\Catalog\Model\Product\Attribute\SetRepository;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\ObjectManager;

/**
 * Class AttributeSetTest
 */
class AttributeSetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testAttributeSetIsSavedProperly()
    {
        /** @var SetRepository $repo */
        $repo = ObjectManager::getInstance()->create(SetRepository::class);
        /** @var SetFactory $setFactory */
        $setFactory = ObjectManager::getInstance()->create(SetFactory::class);

        /** @var AttributeSetInterface $setToSave */
        $setToSave = $setFactory->create();
        $data = [
            'checksum'           => 'test-checksum',
            'attribute_set_name' => 'test-name',
            'entity_type_id'     => '4',
        ];
        $setToSave->setData($data);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $savedSet = $repo->save($setToSave);
        $setToTest = $repo->get($savedSet->getAttributeSetId());

        $this->assertArraySubset($data, $setToTest->getData());
    }
}
