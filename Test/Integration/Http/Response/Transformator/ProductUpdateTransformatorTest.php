<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Http\Response\Transformator;

use Divante\PimcoreIntegration\Http\Response\Transformator\ProductUpdateTransformator;
use Divante\PimcoreIntegration\Test\FakeResponseGenerator;
use Magento\TestFramework\ObjectManager;
use Zend\Http\Response;

/**
 * Class ProductUpdateTransformatorTest
 */
class ProductUpdateTransformatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductUpdateTransformator
     */
    private $transformator;

    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    public function setUp()
    {
        $this->response = $this->mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();

        $this->transformator = ObjectManager::getInstance()->create(ProductUpdateTransformator::class);
    }

    public function testTransformationResult()
    {
        $expectedMinimumDataStructure = [
            'sku'              => 'Zabawka nr 2',
            'name'             => 'Toy nb 2',
            'media_gallery'    => [
                3 => ['media_gallery'],
                5 => ['thumbnail'],
            ],
            'category_ids'     => [],
            'pimcore_id'       => 13,
            'related_products' => [6],
        ];

        $productId = 13;

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn(FakeResponseGenerator::getProductWithoutCategories());

        $result = $this->transformator->transform($this->response);

        $this->assertArraySubset($expectedMinimumDataStructure, $result->getData($productId)->getData());
    }
}
