<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Model;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryQueueRepositoryTest
 */
class CategoryQueueRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    public function setUp()
    {
        $this->om = ObjectManager::getInstance();
        $this->repository = $this->om->create(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Divante/PimcoreIntegration/Test/Integration/_files/category.php
     */
    public function testGetByPimId()
    {
        $this->assertTrue($this->repository->getByPimId(17) instanceof CategoryInterface);
    }

    public function testGetByPimIdNoSuchEntityException()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getByPimId('0');
    }
}
