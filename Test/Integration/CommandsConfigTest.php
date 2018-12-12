<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration;

use Magento\Framework\Console\CommandList;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\ObjectManager\Config;

/**
 * Class CommandsConfigTest
 */
class CommandsConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var array
     */
    private $registeredCommands = [
        'divante_bridge_asset_import',
        'divante_bridge_product_import',
        'divante_bridge_category_import',
    ];

    public function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->objectManager->configure(
            [
                'preferences' => [
                    ltrim(ConfigInterface::class, '\\') => ltrim(Config::class, '\\'),
                ],
            ]
        );
    }

    /**
     * Test proper configuration of cli commands
     */
    public function testCommandsConfiguration()
    {
        /** @var CommandList $commandList */
        $commandList = $this->objectManager->create(CommandList::class);
        $list = $commandList->getCommands();

        foreach ($this->registeredCommands as $expectedCommand) {
            $this->assertArrayHasKey($expectedCommand, $list);
        }
    }
}
