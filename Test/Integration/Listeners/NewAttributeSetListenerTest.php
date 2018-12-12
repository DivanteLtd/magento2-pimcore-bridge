<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Listeners;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Divante\PimcoreIntegration\Listeners\NewAttributeSetListener;
use Magento\Framework\Event\Observer;
use Magento\TestFramework\ObjectManager;

/**
 * Class NewAttributeSetListenerTest
 */
class NewAttributeSetListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NewAttributeSetListener
     */
    private $listener;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $om;

    public function setUp()
    {
        $this->om = ObjectManager::getInstance();

        $this->observer = $this->om->create(Observer::class);
        $this->listener = $this->om->create(NewAttributeSetListener::class);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @magentoDbIsolation enabled
     */
    public function testCreationOfNewAttributeSet()
    {
        $this->observer->setData([
            'products' => [
                1 => [
                    'attr_checksum' => [
                        'value' => '5174165b4fb9577d07462cb1e6b6b1bc',
                        'algo'  => 'md5',
                    ],
                ],
            ],
        ]);

        $this->listener->execute($this->observer);
        /** @var AttributeSetRepositoryInterface $repo */
        $repo = $this->om->create(AttributeSetRepositoryInterface::class);
        $set = $repo->getByChecksum('5174165b4fb9577d07462cb1e6b6b1bc');
        $this->assertNotNull($set->getAttributeSetId());
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @expectedException \Divante\PimcoreIntegration\Exception\InvalidChecksumException
     */
    public function testInvalidChecksumException()
    {
        $this->observer->setData([
            'products' => [
                1 => [
                    'attr_checksum' => [
                        'algo'  => 'md5',
                    ],
                ],
            ],
        ]);

        $this->listener->execute($this->observer);
    }
}
