<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Queue\Action;

use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Http\Request\RequestClient;
use Divante\PimcoreIntegration\Model\CategoryRepository;
use Divante\PimcoreIntegration\Queue\Action\UpdateCategoryAction;
use Divante\PimcoreIntegration\Test\FakeResponseGenerator;
use Magento\TestFramework\ObjectManager;
use Zend\Http\Response;

/**
 * UpdateCategoryActionTest
 */
class UpdateCategoryActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockResponse;

    /**
     * @var UpdateCategoryAction
     */
    private $updateCategoryAction;

    /**
     * @var RequestClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var int
     */
    private $rootCat = 2;

    /**
     * @var int
     */
    private $lvl1CatPimId = 17;

    /**
     * @var int
     */
    private $lvl2CatPimId = 18;

    /**
     * @var int
     */
    private $lvl3CatPimId = 19;

    public function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->mockRequest = $this->getMockBuilder(RequestClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['send', 'setQueryData'])
            ->getMock();

        $this->mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();

        $this->mockRequest->expects($this->any())
            ->method('send')
            ->willReturn($this->mockResponse);

        $this->updateCategoryAction = $this->objectManager->create(UpdateCategoryAction::class, [
            'requestClient' => $this->mockRequest,
        ]);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testInsertNewCategory()
    {
        $this->mockResponse->expects($this->once())
            ->method('getBody')
            ->willReturn(FakeResponseGenerator::getLvl1CatResponse());

        /** @var CategoryQueueInterface $queue */
        $queue = $this->createQueue($this->lvl1CatPimId);

        $this->assertTrue($this->updateCategoryAction->execute($queue));

        /** @var CategoryRepository $catRepo */
        $catRepo = $this->objectManager->create(CategoryRepository::class);
        $cat = $catRepo->getByPimId($this->lvl1CatPimId);
        $this->assertNotNull($cat->getId());
        $this->assertEquals($this->rootCat, $cat->getParentId());
    }

    /**
     * @param $pimId
     * @param string $action
     * @param int $storeViewId
     *
     * @return CategoryQueueInterface
     */
    private function createQueue($pimId, string $action = 'insert/update', $storeViewId = 0)
    {
        /** @var CategoryQueueInterface $queue */
        $queue = $this->objectManager->create(CategoryQueueInterface::class);
        $queue->setAction($action)
            ->setCategoryId($pimId)
            ->setStoreViewId($storeViewId);

        return $queue;
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/category.php
     */
    public function testUpdateExistingCategory()
    {

        $description = 'description';

        $this->mockResponse->expects($this->once())
            ->method('getBody')
            ->willReturn(FakeResponseGenerator::getLvl1CatResponse());

        /** @var CategoryQueueInterface $queue */
        $queue = $this->createQueue($this->lvl1CatPimId);

        $this->assertTrue($this->updateCategoryAction->execute($queue));

        /** @var CategoryRepository $catRepo */
        $catRepo = $this->objectManager->create(CategoryRepository::class);
        $cat = $catRepo->getByPimId($this->lvl1CatPimId);
        $this->assertEquals($description, $cat->getDescription());
        $this->assertEquals($this->lvl1CatPimId, $cat->getPimcoreId());
    }

    public function testCreationOfTreeOfCategoriesAndMoveOne()
    {
        $this->mockResponse->expects($this->exactly(4))
            ->method('getBody')
            ->willReturnOnConsecutiveCalls(
                FakeResponseGenerator::getLvl1CatResponse(),
                FakeResponseGenerator::getLvl2CatResponse(),
                FakeResponseGenerator::getLvl3CatResponse(),
                FakeResponseGenerator::getLvl3CatMovedToLvl2Response()
            );

        $queue1 = $this->createQueue($this->lvl1CatPimId);
        $queue2 = $this->createQueue($this->lvl2CatPimId);
        $queue3 = $this->createQueue($this->lvl3CatPimId);

        $this->assertTrue($this->updateCategoryAction->execute($queue1));
        $this->assertTrue($this->updateCategoryAction->execute($queue2));
        $this->assertTrue($this->updateCategoryAction->execute($queue3));

        /** @var CategoryRepository $catRepo */
        $catRepo = $this->objectManager->create(CategoryRepository::class);
        $catLv1 = $catRepo->getByPimId($this->lvl1CatPimId);
        $catLv2 = $catRepo->getByPimId($this->lvl2CatPimId);
        $catLv3 = $catRepo->getByPimId($this->lvl3CatPimId);

        $this->assertEquals($this->rootCat, $catLv1->getParentId());
        $this->assertEquals($catLv1->getId(), $catLv2->getParentId());
        $this->assertEquals($catLv2->getId(), $catLv3->getParentId());

        $queue4 = $this->createQueue($this->lvl3CatPimId);
        $this->assertTrue($this->updateCategoryAction->execute($queue4));

        /** @var CategoryRepository $catRepo */
        $catRepo = $this->objectManager->create(CategoryRepository::class);
        $catLv3 = $catRepo->getByPimId($this->lvl3CatPimId);
        $this->assertEquals($catLv1->getId(), $catLv3->getParentId());
    }
}
