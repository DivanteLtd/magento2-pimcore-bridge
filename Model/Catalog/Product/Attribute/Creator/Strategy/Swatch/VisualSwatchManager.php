<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\Swatch;

use Magento\Framework\Filesystem;
use Magento\Swatches\Helper\Media;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class VisualSwatchManager
 */
class VisualSwatchManager implements SwatchManagerInterface
{
    /**
     * @var Media
     */
    protected $swatchHelper;

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * VisualSwatchManager constructor.
     *
     * @param Media $swatchHelper
     * @param Config $mediaConfig
     */
    public function __construct(Media $swatchHelper, Config $mediaConfig, Filesystem $filesystem)
    {
        $this->swatchHelper = $swatchHelper;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $file
     * @param string $base64
     *
     * @return string
     */
    public function createVisualSwatchFromBase64(string $file, string $base64): string
    {
        $content = base64_decode($base64);
        $filename = $this->resolveFilename($content, $file);
        $path = $this->resolveFullTmpPath($filename);

        $this->storeTmpFile($path, $content);
        $finalFile = $this->fixGeneratingSwatchVariation($this->swatchHelper->moveImageFromTmp($filename));

        $this->swatchHelper->generateSwatchVariations($finalFile);

        return $finalFile;
    }

    /**
     * @param string $path
     * @param string $content
     *
     * @return void
     */
    protected function storeTmpFile(string $path, string $content)
    {
        $file = fopen($path, "wb");
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * @param string $newFile
     *
     * @return false|string
     */
    private function fixGeneratingSwatchVariation(string $newFile)
    {
        if (substr($newFile, 0, 1) == '.') {
            $newFile = substr($newFile, 1); // Fix generating swatch variations for files beginning with ".".
        }

        return $newFile;
    }

    /**
     * @param string $content
     * @param string $file
     *
     * @return string
     */
    private function resolveFilename(string $content, string $file): string
    {
        list($type, $mime) = explode('/', finfo_buffer(finfo_open(), $content, FILEINFO_MIME_TYPE));
        $filename = $file . '.' . $mime;

        return $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function resolveFullTmpPath(string $filename): string
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $tmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
        $fullTmpMediaPath = $mediaDirectory->getAbsolutePath($tmpMediaPath);
        $path = $fullTmpMediaPath . '/' . $filename;

        return $path;
    }
}
