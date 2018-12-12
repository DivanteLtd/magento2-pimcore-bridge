<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;


use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\Api\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class PathResolver
 */
class PathResolver
{
    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * PathResolver constructor.
     *
     * @param Filesystem $filesystem
     * @param MediaConfig $mediaConfig
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(Filesystem $filesystem, MediaConfig $mediaConfig)
    {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getBaseProductAssetPath(string $filename): string
    {
        $path = $this->mediaDirectory->getAbsolutePath(
            $this->mediaConfig->getBaseMediaPath()
            . Uploader::getDispretionPath($filename)
            . DIRECTORY_SEPARATOR
            . $filename
        );

        return $path;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getTmpProductAssetPath(string $filename): string
    {
        $path = $this->mediaDirectory->getAbsolutePath(
            $this->mediaConfig->getBaseTmpMediaPath()
            . Uploader::getDispretionPath($filename)
            . DIRECTORY_SEPARATOR
            . $filename
        );

        return $path;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getCategoryAssetPath(string $filename): string
    {
        $path = $this->mediaDirectory->getAbsolutePath(sprintf('/catalog/category/%s', $filename));

        return $path;
    }

    /**
     * @return string
     */
    public function getCategoryMediaRootDir()
    {
        return $this->mediaDirectory->getAbsolutePath('/catalog/category');
    }
}
