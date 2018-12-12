<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Action\Asset;

use Divante\PimcoreIntegration\Exception\InvalidAssetTypeException;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * TypeMetadataBuilderTest
 */
class TypeMetadataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return array
     */
    public function validDataProvider()
    {
        return [
            ['catalog_product', ['image'], 'catalog_product/image'],
            ['catalog_product', ['image', 'thumbnail'], 'catalog_product/image,thumbnail'],
            ['catalog_product', ['image', '  thumbnail  '], 'catalog_product/image,thumbnail'],
        ];
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param string $entityType
     * @param array $assetTypes
     * @param $result
     */
    public function testGetMetadataStringForValidData(string $entityType, array $assetTypes, $result)
    {
        /** @var TypeMetadataBuilder $metadataBuilder */
        $metadataBuilder = $this->objectManager->getObject(TypeMetadataBuilder::class, [
            'entityType' => $entityType,
            'assetTypes' => $assetTypes,
        ]);

        if (false === $result) {
            $this->expectException(new InvalidAssetTypeException(__('as')));
        }

        $this->assertSame($result, $metadataBuilder->getTypeMetadataString());
    }

    /**
     * @return array
     */
    public function invalidDataProvider()
    {
        return [
            ['', []],
            ['', ['image']],
            ['catalog_product', []],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @expectedException \Divante\PimcoreIntegration\Exception\InvalidAssetMetadataException
     * @expectedExceptionMessage Neither entity type or asset type can be empty.
     *
     * @param string $entityType
     * @param array $assetTypes
     */
    public function testGetMetadataStringForInvalidData(string $entityType, array $assetTypes)
    {
        $this->objectManager->getObject(TypeMetadataBuilder::class, [
            'entityType' => $entityType,
            'assetTypes' => $assetTypes,
        ]);
    }
}
