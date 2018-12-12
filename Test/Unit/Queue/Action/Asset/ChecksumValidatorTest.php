<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Action\Asset;

use Divante\PimcoreIntegration\Http\Response\Transformator\Data\Checksum;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\ChecksumInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\ChecksumValidator;
use Divante\PimcoreIntegration\Queue\Action\Asset\ChecksumValidatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * ChecksumValidatorTest
 */
class ChecksumValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChecksumValidatorInterface
     */
    private $checksumValidator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->checksumValidator = $this->objectManager->getObject(ChecksumValidator::class);
    }

    /**
     * Image and data provider
     *
     * @return array
     */
    public function checksumAndImageDataProvider(): array
    {
        return [
            [
                'alg'     => 'sha1',
                'image'   => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9TDwADngGMcF/ZFQAAAABJRU5ErkJggg=='),
                'value'   => 'a2dc24e1f09f3279fb0ffa2b571e6f2052e58fee',
                'isValid' => true,

            ],
            [
                'alg'     => 'sha256',
                'image'   => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9TDwADngGMcF/ZFQAAAABJRU5ErkJggg=='),
                'value'   => 'f8eb5b397eb3740247f3585936cb3365df8057479775f47dface156f251b0f11',
                'isValid' => true,

            ],
            [
                'alg'     => 'sha1',
                'image'   => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9TDwADngGMcF/ZFQAAAABJRU5ErkJggg=='),
                'value'   => 'invalid_encoded_data',
                'isValid' => false,

            ],
        ];
    }

    /**
     * @dataProvider checksumAndImageDataProvider
     */
    public function testIsValid(string $alg, string $image, string $value, bool $isValid)
    {
        /** @var ChecksumInterface $checksum */
        $checksum = $this->objectManager->getObject(Checksum::class);
        $checksum->setAlgorithm($alg)->setValue($value);

        $this->assertSame($isValid, $this->checksumValidator->isValid($checksum, $image));
    }
}
