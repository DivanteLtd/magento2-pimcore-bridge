<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Queue\Action\Asset\Strategy;

use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Divante\PimcoreIntegration\Queue\Action\Asset\Strategy\ReplaceImages;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractor;
use Magento\Framework\DataObject;
use Magento\TestFramework\ObjectManager;

/**
 * Class ReplaceImagesTest
 */
class ReplaceImagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataObject|AssetInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAssetData;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var string
     */
    private $filename = 'file.jpg';

    /**
     * @var string
     */
    private $name = 'file';

    /**
     * @var string
     */
    private $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    public function setUp()
    {
        $this->pathResolver = ObjectManager::getInstance()->create(PathResolver::class);

        $this->mockAssetData = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNameWithExt', 'getDecodedImage', 'getName','getPimId'])
            ->getMock();

        $this->mockAssetData->expects($this->any())
            ->method('getNameWithExt')
            ->willReturn($this->filename);

        $this->mockAssetData->expects($this->any())
            ->method('getName')
            ->willReturn($this->name);

        $this->mockAssetData->expects($this->any())
            ->method('getDecodedImage')
            ->willReturn(base64_decode($this->base64Image));

        $this->mockAssetData->expects($this->any())
            ->method('getPimId')
            ->willReturn(1);

        if (file_exists($this->pathResolver->getTmpProductAssetPath($this->filename))) {
            unlink($this->pathResolver->getTmpProductAssetPath($this->filename));
        }

        if (file_exists($this->pathResolver->getBaseProductAssetPath($this->filename))) {
            unlink($this->pathResolver->getBaseProductAssetPath($this->filename));
        }

        if (file_exists($this->pathResolver->getCategoryAssetPath($this->filename))) {
            unlink($this->pathResolver->getCategoryAssetPath($this->filename));
        }
    }

    public function testExecute()
    {
        $metadataExtractor = ObjectManager::getInstance()->create(TypeMetadataExtractor::class, [
            'typeString' => '',
        ]);

        /** @var ReplaceImages $replaceImages */
        $replaceImages = ObjectManager::getInstance()->create(ReplaceImages::class);

        $replaceImages->execute($this->mockAssetData, $metadataExtractor);
        $this->assertFileExists($this->pathResolver->getBaseProductAssetPath($this->filename));
        $this->assertFileExists($this->pathResolver->getCategoryAssetPath($this->filename));
    }
}
