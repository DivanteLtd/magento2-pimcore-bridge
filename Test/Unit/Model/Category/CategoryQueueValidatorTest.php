<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model\Category;

use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueue;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueueValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryQueueValidatorTest
 */
class CategoryQueueValidatorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var CategoryQueueValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = $this->getObjectManager()->getObject(CategoryQueueValidator::class);
    }

    /**
     * @return ObjectManager
     */
    private function getObjectManager(): ObjectManager
    {
        if (!$this->om) {
            $this->om = new ObjectManager($this);
        }

        return $this->om;
    }

    /**
     * @dataProvider valuesToValidateProvider
     *
     * @param $value
     * @param $expected
     */
    public function testIsValid($value, $expected)
    {
        $this->assertSame($expected, $this->validator->isValid($value));
    }

    /**
     * @return array
     */
    public function valuesToValidateProvider(): array
    {
        return [
            [true, false],
            [$this->getDataObject(), false],
            [$this->getDataObject('1'), false],
            [$this->getDataObject('1', '2'), true],
        ];
    }

    /**
     * @param string|null $categoryId
     * @param string|null $store
     *
     * @return CategoryQueue
     */
    protected function getDataObject($categoryId = null, $store = null)
    {
        /** @var CategoryQueue $dto */
        $dto = $this->getObjectManager()->getObject(CategoryQueue::class);

        if (null !== $categoryId) {
            $dto->setCategoryId((string) $categoryId);
        }

        if (null !== $store) {
            $dto->setStoreViewId((string) $store);
        }

        return $dto;
    }
}
