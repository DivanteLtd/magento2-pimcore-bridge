<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model;

use Divante\PimcoreIntegration\Model\CategoryRepository;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryRepositoryTest
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * @var Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCategory;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCollection;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCollectionFactory;

    /**
     * @var CategoryRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'addAttributeToSelect', 'getSize', 'getFirstItem', 'setStore'])
            ->getMock();

        $this->mockCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->repository = $this->objectManager->getObject(CategoryRepository::class, [
            'collectionFactory' => $this->mockCollectionFactory,
        ]);
    }

    public function testGetByPimId()
    {
        $pimId = '100';
        $size = 2;

        $this->mockCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->mockCollection);

        $this->mockCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('pimcore_id', ['eq' => $pimId]);

        $this->mockCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->mockCollection->expects($this->once())
            ->method('getSize')
            ->willReturn($size);

        $this->mockCollection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->mockCategory);

        $this->repository->getByPimId($pimId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetByPimNoSuchEntityException()
    {
        $this->mockCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->mockCollection);

        $this->repository->getByPimId('0');
    }
}
