<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Action\Asset;

use Divante\PimcoreIntegration\Queue\Action\Asset\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * FileTest
 */
class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return array
     */
    public function fileDataProvider(): array
    {
        return [
            ['', '', 'name', 'name', '/n/a/name'],
            ['_suffix', '', 'name', 'name_suffix', '/n/a/name_suffix'],
            ['_suffix', 'jpeg', 'name', 'name_suffix.jpeg', '/n/a/name_suffix.jpeg'],
            ['', 'jpeg', 'name', 'name.jpeg', '/n/a/name.jpeg'],
        ];
    }

    /**
     * @param string $suffix
     * @param string $ext
     * @param string $base
     * @param string $filename
     * @param string $expected
     *
     * @dataProvider fileDataProvider
     */
    public function testGetFilenameWithDispretionPath(
        string $suffix,
        string $ext,
        string $base,
        string $filename,
        string $expected
    ) {
        /** @var File $file */
        $file = $this->objectManager->getObject(File::class, [
            'suffix' => $suffix,
            'ext'    => $ext,
        ]);

        $this->assertSame($expected, $file->getFilenameWithDispretionPath($base));
    }

    /**
     * @param string $suffix
     * @param string $ext
     * @param string $base
     * @param string $expected
     *
     * @dataProvider fileDataProvider
     */
    public function testGetFilename(string $suffix, string $ext, string $base, string $expected)
    {
        /** @var File $file */
        $file = $this->objectManager->getObject(File::class, [
            'suffix' => $suffix,
            'ext'    => $ext,
        ]);

        $this->assertSame($expected, $file->getFilename($base));
    }
}
