<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Http;

use Divante\PimcoreIntegration\Http\EndpointUrlBuilder;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\System\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class EndpointUrlBuilderTest
 */
class EndpointUrlBuilderTest extends TestCase
{
    /**
     * @var UrlBuilderInterface
     */
    private $endpointUrlBuilder;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $endpoint = 'http://pimcore.com/';

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPimcoreEndpoint'])
            ->getMock();

        $this->endpointUrlBuilder = $this->objectManager->getObject(EndpointUrlBuilder::class, [
            'config' => $this->mockConfig,
        ]);
    }

    public function urlPartsDataProvider()
    {
        return [
            ['', $this->endpoint],
            ['path', $this->endpoint . 'path'],
        ];
    }

    /**
     * @param string $path
     * @param string $result
     *
     * @dataProvider urlPartsDataProvider
     *
     * @return void
     */
    public function testBuild(string $path, string $result)
    {
        $this->mockConfig->expects($this->once())
            ->method('getPimcoreEndpoint')
            ->willReturn($this->endpoint);

        $this->assertSame($result, $this->endpointUrlBuilder->build($path));
    }
}
