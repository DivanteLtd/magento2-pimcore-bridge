<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Queue\Action;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Http\Request\RequestClient;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueue;
use Divante\PimcoreIntegration\Queue\Action\UpdateAssetAction;
use Divante\PimcoreIntegration\Queue\Importer\AssetQueueImporter;
use Divante\PimcoreIntegration\Test\FakeResponseGenerator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Zend\Http\Response;

class UpdateAssetActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpdateAssetAction
     */
    private $updateAssetAction;

    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockResponse;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

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

        $this->updateAssetAction = $this->objectManager->create(UpdateAssetAction::class, [
            'requestClient' => $this->mockRequest,
        ]);
    }

    public function queuesDataProvider()
    {
        return [
            [
                [
                    'asset_id'         => '117',
                    'target_entity_id' => 1,
                    'store_view_id'    => '0',
                    'action'           => AssetQueueImporter::ACTION_INSERT_UPDATE,
                    'type'             => 'small_image',
                    'entity'           => 'catalog_product',
                ],
            ],
            [
                [
                    'asset_id'         => '118',
                    'target_entity_id' => 1,
                    'store_view_id'    => '0',
                    'action'           => AssetQueueImporter::ACTION_INSERT_UPDATE,
                    'type'             => 'thumbnail',
                    'entity'           => 'catalog_product',

                ],
            ],
            [
                [
                    'asset_id'         => '119',
                    'target_entity_id' => 1,
                    'store_view_id'    => '0',
                    'action'           => AssetQueueImporter::ACTION_INSERT_UPDATE,
                    'type'             => 'image',
                    'entity'           => 'catalog_product',
                ],
            ],
        ];
    }

    /**
     * @throws \Divante\PimcoreIntegration\Exception\InvalidQueueTypeException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/product_simple.php
     */
    public function testUpdatingAssetForProduct()
    {
        $this->mockResponse->expects($this->any())
            ->method('getBody')
            ->willReturn(FakeResponseGenerator::getAssetResponse());

        /** @var AssetQueue $queue */
        $queue = $this->objectManager->create(AssetQueueInterface::class);

        $queue->setAssetId(117)
            ->setTargetEntityId(1)
            ->setStoreViewId(0)
            ->setAction(AssetQueueImporter::ACTION_INSERT_UPDATE)
            ->setType('catalog_product/image');

        $this->updateAssetAction->execute($queue);

        $queue->setAssetId(117)
            ->setTargetEntityId(1)
            ->setStoreViewId(0)
            ->setAction(AssetQueueImporter::ACTION_INSERT_UPDATE)
            ->setType('catalog_product/image');

        $this->updateAssetAction->execute($queue);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $galleryEntries = $product->getMediaGalleryEntries();

        $this->assertCount(2, $galleryEntries);
        $this->assertContains('image', $galleryEntries[1]->getTypes());
    }

    /**
     * @param array $data
     *
     * @throws \Divante\PimcoreIntegration\Exception\InvalidQueueTypeException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider queuesDataProvider
     *
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/product_simple.php
     */
    public function testCreatingAssetForProduct(array $data)
    {

        $this->mockResponse->expects($this->once())
            ->method('getBody')
            ->willReturn(FakeResponseGenerator::getAssetResponse());
        /** @var AssetQueue $queue */
        $queue = $this->objectManager->create(AssetQueueInterface::class);

        $queue->setAssetId($data['asset_id'])
            ->setTargetEntityId($data['target_entity_id'])
            ->setStoreViewId($data['store_view_id'])
            ->setAction($data['action'])
            ->setType(sprintf('%s/%s', $data['entity'], $data['type']));

        $this->updateAssetAction->execute($queue);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $galleryEntries = $product->getMediaGalleryEntries();

        $this->assertCount(2, $galleryEntries);
        $this->assertContains($data['type'], $galleryEntries[1]->getTypes());
    }
}
