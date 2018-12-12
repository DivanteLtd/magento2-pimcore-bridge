<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\System;

use Divante\PimcoreIntegration\System\Config;
use Magento\Framework\App\Config as CoreConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStore;

    /**
     * @var  StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStoreManager;

    /**
     * @var CoreConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockScopeConfig;

    /**
     * @var string
     */
    private $baseUrl = 'http://base-url.com';

    /**
     * @var string
     */
    private $scope = 'default';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockScopeConfig = $this->getMockBuilder(CoreConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $this->mockStoreManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();

        $this->mockStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMock();

        $this->mockStoreManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->mockStore);

        $this->config = $this->objectManager->getObject(Config::class, [
            'scopeConfig'  => $this->mockScopeConfig,
            'storeManager' => $this->mockStoreManager,
            'scope'        => $this->scope,
        ]);
    }

    /**
     * @return array
     */
    public function instanceUrlConfigDataProvider(): array
    {
        return [
            ['', $this->baseUrl],
            [null, $this->baseUrl],
            ['some-url', 'some-url'],
            ['some-url-with-trailing-slash/', 'some-url-with-trailing-slash'],
        ];
    }

    /**
     * @dataProvider instanceUrlConfigDataProvider
     *
     * @param $configValue
     * @param $expected
     */
    public function testGetInstanceUrl($configValue, $expected)
    {
        $this->mockScopeConfig->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSTANCE_URL)
            ->willReturn($configValue);

        if (!$configValue) {
            $this->mockStore->expects($this->once())
                ->method('getBaseUrl')
                ->willReturn($this->baseUrl);
        }

        $this->assertSame($expected, $this->config->getInstanceUrl());
    }
}
