<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Cron;

use Divante\PimcoreIntegration\Cron\ProductPublisher;
use Divante\PimcoreIntegration\System\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * Class ProductPublisher
 */
class ProductPublisherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfig;

    /**
     * @var SearchCriteriaBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteriaBuilderFactory;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStore;

    /**
     * @var StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStoreManager;

    /**
     * @var ProductRepositoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRepositoryFactory;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteria;

    /**
     * @var ProductPublisher
     */
    private $publisher;

    /**
     * @var SearchResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchResults;

    /**
     * @var  ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteriaBuilder;

    /**
     * @var ObjectManager
     */
    private $om;

    public function setUp()
    {
        $this->mockSearchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();

        $this->mockSearchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->mockSearchCriteriaBuilderFactory = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockSearchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockSearchCriteria);

        $this->mockSearchCriteriaBuilderFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->mockSearchCriteriaBuilder);

        $this->mockProductRepositoryFactory = $this->getMockBuilder(ProductRepositoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockProductRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'save'])
            ->getMock();

        $this->mockProductRepositoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->mockProductRepository);

        $this->mockStoreManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStores', 'setCurrentStore'])
            ->getMock();

        $this->mockStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->mockSearchResults = $this->getMockBuilder(SearchResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();

        $this->mockStoreManager->expects($this->any())
            ->method('getStores')
            ->willReturn([$this->mockStore]);

        $this->mockConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsProductPublishActive'])
            ->getMock();

        $this->om = new ObjectManager($this);

        $this->publisher = $this->om->getObject(ProductPublisher::class, [
            'criteriaBuilderFactory' => $this->mockSearchCriteriaBuilderFactory,
            'repositoryFactory'      => $this->mockProductRepositoryFactory,
            'storeManager'           => $this->mockStoreManager,
            'config'                 => $this->mockConfig,
        ]);
    }

    public function testIsPublicationProcessDisabled()
    {
        $this->mockConfig->expects($this->once())
            ->method('getIsProductPublishActive')
            ->willReturn(false);

        $this->assertNull($this->publisher->execute());
    }

    public function testEmptyResults()
    {
        $this->mockConfig->expects($this->once())
            ->method('getIsProductPublishActive')
            ->willReturn(true);

        $this->mockSearchCriteriaBuilder->expects($this->at(0))
            ->method('addFilter')
            ->with('quantity_and_stock_status', '1');

        $this->mockSearchCriteriaBuilder->expects($this->at(1))
            ->method('addFilter')
            ->with('price', 0, 'gt');

        $this->mockSearchCriteriaBuilder->expects($this->at(2))
            ->method('addFilter')
            ->with('status', Status::STATUS_DISABLED);

        $this->mockSearchCriteriaBuilder->expects($this->at(3))
            ->method('addFilter')
            ->with('is_active_in_pim', 1);

        $this->mockProductRepository->expects($this->any())
            ->method('getList')
            ->willReturn($this->mockSearchResults);

        $this->mockSearchResults->expects($this->any())
            ->method('getItems')
            ->willReturn([]);

        $this->publisher->execute();
    }

    public function testProcessingProductWithNoCategories()
    {
        $this->mockProductRepository->expects($this->any())
            ->method('getList')
            ->willReturn($this->mockSearchResults);

        $mockProduct = $this->mockProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryIds', 'setStatus'])
            ->getMock();

        $mockProduct->expects($this->any())
            ->method('getCategoryIds')
            ->willReturn([]);

        $this->mockSearchResults->expects($this->any())
            ->method('getItems')
            ->willReturn([$mockProduct]);

        $mockProduct->expects($this->never())
            ->method('setStatus');

        $this->publisher->execute();
    }

    public function testProcessingProductWithAssignedCategories()
    {
        $this->mockProductRepository->expects($this->any())
            ->method('getList')
            ->willReturn($this->mockSearchResults);

        $mockProduct = $this->mockProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryIds', 'setStatus', 'getImage'])
            ->getMock();

        $mockProduct->expects($this->any())
            ->method('getCategoryIds')
            ->willReturn([1, 3, 5]);

        $this->mockSearchResults->expects($this->any())
            ->method('getItems')
            ->willReturn([$mockProduct]);

        $mockProduct->expects($this->any())
            ->method('setStatus')
            ->with(Status::STATUS_ENABLED);

        $this->mockProductRepository
            ->expects($this->any())
            ->method('save');

        $this->assertNull($this->publisher->execute());
    }
}
