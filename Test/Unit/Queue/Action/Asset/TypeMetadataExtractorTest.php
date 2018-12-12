<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Action\Asset;

use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * TypeMetadataExtractorTest
 */
class TypeMetadataExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypeMetadataExtractor
     */
    private $typeMetadataExtractor;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function validationDataProvider(): array
    {
        return [
            ['category_product/image', true],
            ['category_product/image,thumbnail', true],
            ['category_product/', false],
            ['category_product', false],
            ['/image', false],
            ['/', false],
        ];
    }

    /**
     * @param string $typeString
     * @param bool $isValidExpectation
     *
     * @dataProvider validationDataProvider
     */
    public function testIsValid(string $typeString, bool $isValidExpectation)
    {
        $this->typeMetadataExtractor = $this->objectManager->getObject(TypeMetadataExtractor::class, [
            'typeString' => $typeString,
        ]);

        $this->assertSame($isValidExpectation, $this->typeMetadataExtractor->isValid());
    }

    /**
     * @return array
     */
    public function typesDataProvider(): array
    {
        return [
            ['category_product/image', ['image']],
            ['category_product/image,thumbnail', ['image', 'thumbnail']],
            ['category_product/image, thumbnail', ['image', 'thumbnail']],
            ['category_product/image,    thumbnail    ', ['image', 'thumbnail']],
        ];
    }

    /**
     * @param string $typeString
     * @param array $types
     *
     * @dataProvider typesDataProvider
     */
    public function testGetAssetTypes(string $typeString, array $types)
    {
        $this->typeMetadataExtractor = $this->objectManager->getObject(TypeMetadataExtractor::class, [
            'typeString' => $typeString,
        ]);

        $this->assertSame($types, $this->typeMetadataExtractor->getAssetTypes());
    }

    /**
     * @return array
     */
    public function entitiesDataProvider(): array
    {
        return [
            ['category_product/image', 'category_product'],
            ['/image', 'image'],
            [false, ''],
        ];
    }

    /**
     * @param string $typeString
     *
     * @dataProvider entitiesDataProvider
     */
    public function testGetEntityType(string $typeString, string $entity)
    {
        $this->typeMetadataExtractor = $this->objectManager->getObject(TypeMetadataExtractor::class, [
            'typeString' => $typeString,
        ]);

        $this->assertSame($entity, $this->typeMetadataExtractor->getEntityType());
    }
}
