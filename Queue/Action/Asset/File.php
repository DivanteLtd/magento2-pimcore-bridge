<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

use Magento\Framework\File\Uploader;

/**
 * Class File
 */
class File implements FileInterface
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string|null
     */
    private $ext;

    /**
     * File constructor.
     *
     * @param string $suffix
     * @param string|null $ext
     */
    public function __construct(string $suffix = '_pim', string $ext = null)
    {
        $this->suffix = $suffix;
        $this->ext = $ext;
    }

    /**
     * @param string $base
     *
     * @return string
     */
    public function getFilename(string $base): string
    {
        $filename = sprintf('%s%s', $base, $this->suffix);
        $filename = $this->ext ? sprintf('%s.%s', $filename, $this->ext) : $filename;

        return $filename;
    }

    /**
     * @param string $base
     *
     * @return string
     */
    public function getFilenameWithDispretionPath(string $base): string
    {
        return sprintf('%s/%s', Uploader::getDispretionPath($this->getFilename($base)), $this->getFilename($base));
    }
}
