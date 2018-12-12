<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Model\Queue\Category;

use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueueRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryQueueRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var CategoryQueueRepository
     */
    protected $repository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function setUp()
    {
        $this->om = ObjectManager::getInstance();
        $this->repository = $this->om->create(CategoryQueueRepository::class);
        $this->searchCriteriaBuilder = $this->om->create(SearchCriteriaBuilder::class);
    }

    /**
     * Test get list with empty table
     */
    public function testGetListWithEmptyData()
    {
        $criteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->repository->getList($criteria);

        $this->assertEmpty($searchResults->getItems());
        $this->assertEquals(0, $searchResults->getTotalCount());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/multiple_category_queue.php
     */
    public function testGetAllItemsWithEmptyCriteria()
    {
        $queuesCountCreatedInFixtures = 3;
        $criteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->repository->getList($criteria);

        $this->assertNotEmpty($searchResults->getItems());
        $this->assertEquals($queuesCountCreatedInFixtures, $searchResults->getTotalCount());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/multiple_category_queue.php
     */
    public function testGetListWithSomeCriterias()
    {
        $expectedResultsCount = 1;
        $this->searchCriteriaBuilder->addFilter('category_id', 1);
        $criteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->repository->getList($criteria);

        $this->assertNotEmpty($searchResults->getItems());
        $this->assertEquals($expectedResultsCount, $searchResults->getTotalCount());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage CategoryQueue with transaction_id "1" does not exist.
     */
    public function testGetByIdForNonExistentEntity()
    {
        $this->repository->getById(1);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/multiple_category_queue.php
     */
    public function testGetByIdForExistingEntity()
    {
        $searchResults = $this->repository->getList($this->searchCriteriaBuilder->create());
        $items = $searchResults->getItems();
        $id = end($items)->getId();
        $queue = $this->repository->getById($id);

        $this->assertTrue($queue instanceof CategoryQueueInterface);
        $this->assertEquals($id, $queue->getId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveNewQueueEntity()
    {
        $categoryId = '99';

        /** @var CategoryQueueInterface|AbstractModel $queue */
        $queue = $this->om->create(CategoryQueueInterface::class);

        $queue->setAction('insert/update')->setStoreViewId('0')->setCategoryId($categoryId);
        $queue = $this->repository->save($queue);

        $this->searchCriteriaBuilder->addFilter('category_id', $categoryId);
        $criteria = $this->searchCriteriaBuilder->create();
        $results = $this->repository->getList($criteria);

        $this->assertEquals(1, $results->getTotalCount());
        $items = $results->getItems();
        $this->assertTrue(end($items)->getId() === $queue->getId());
    }
}
