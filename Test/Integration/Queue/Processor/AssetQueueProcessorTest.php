<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Queue\Processor;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueue;
use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Divante\PimcoreIntegration\Queue\Processor\AssetQueueProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\ObjectManager;

/**
 * Class AssetQueueProcessorTest
 */
class AssetQueueProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssetQueueProcessor
     */
    private $processor;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var string
     */
    private $imgName = '117_pim.jpg';

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->pathResolver = ObjectManager::getInstance()->create(PathResolver::class);
        $this->processor = ObjectManager::getInstance()->create(AssetQueueProcessor::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/product_with_image.php
     */
    public function testDeleteOfAssetForAProduct()
    {
        $this->createDeleteAssetRequestQueue();

        $this->assertFileExists($this->pathResolver->getBaseProductAssetPath($this->imgName));

        /** @var ProductRepository $repository */
        $repository = ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $product = $repository->get('simple');

        $this->assertNotNull($product->getThumbnail());
        $this->assertNotNull($product->getImage());
        $this->assertNotNull($product->getSmallImage());

        $this->processor->process();

        $repository->cleanCache();
        $product = $repository->get('simple', false, null, true);

        $this->assertFileNotExists($this->pathResolver->getBaseProductAssetPath($this->imgName));
        $this->assertNull($product->getThumbnail());
        $this->assertNull($product->getImage());
        $this->assertNull($product->getSmallImage());
    }

    /**
     * @return void
     */
    private function createDeleteAssetRequestQueue()
    {
        /** @var AssetQueue $queue */
        $queue = ObjectManager::getInstance()->create(AssetQueue::class);
        $queue->setAssetId(117)
            ->setAction('delete')
            ->setStoreViewId(0)
            ->save();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/category_with_image.php
     */
    public function testDeleteOfAssetForACategory()
    {
        $this->createDeleteAssetRequestQueue();

        $this->assertFileExists($this->pathResolver->getCategoryAssetPath($this->imgName));
        $this->processor->process();

        /** @var CategoryRepositoryInterface $repository */
        $repository = ObjectManager::getInstance()->create(CategoryRepositoryInterface::class);
        $category = $repository->get(333);

        $this->assertFileNotExists($this->pathResolver->getCategoryAssetPath($this->imgName));
        $this->assertNull($category->getImage());
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        if (file_exists($this->pathResolver->getBaseProductAssetPath($this->imgName))) {
            unlink($this->pathResolver->getBaseProductAssetPath($this->imgName));
        }

        if (file_exists($this->pathResolver->getCategoryAssetPath($this->imgName))) {
            unlink($this->pathResolver->getCategoryAssetPath($this->imgName));
        }
    }
}
