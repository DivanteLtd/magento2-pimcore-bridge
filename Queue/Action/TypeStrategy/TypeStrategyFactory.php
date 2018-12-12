<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\TypeStrategy;

use Divante\PimcoreIntegration\Exception\InvalidStrategyException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class TypeStrategyFactory
 */
class TypeStrategyFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $strategies;

    /**
     * ConfigurableProductStrategy constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $strategies
     */
    public function __construct(ObjectManagerInterface $objectManager, array $strategies = [])
    {
        $this->objectManager = $objectManager;
        $this->strategies = $strategies;
    }

    /**
     * @param string $type
     *
     * @throws InvalidStrategyException
     *
     * @return ProductTypeCreationStrategyInterface
     */
    public function create(string $type): ProductTypeCreationStrategyInterface
    {
        if (!isset($this->strategies[$type])) {
            throw new InvalidStrategyException(__("Strategy '%1' is not configured."));
        }

        return $this->objectManager->create($this->strategies[$type]);
    }
}
