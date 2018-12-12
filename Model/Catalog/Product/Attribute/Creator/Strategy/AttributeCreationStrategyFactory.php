<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class AttributeCreationStrategyFactory
 */
class AttributeCreationStrategyFactory implements StrategyFactoryInterface
{
    /**
     * @var string
     */
    public static $classNamespaceRoot = 'Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * AttributeCreationStrategyFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $code
     * @param array $attrData
     *
     * @throws LocalizedException
     * @return AttributeCreationStrategyInterface
     */
    public function create(string $code, array $attrData): AttributeCreationStrategyInterface
    {
        try {
            $class = sprintf('%s\%sStrategy', self::$classNamespaceRoot, ucfirst($attrData['type']));

            return $this->objectManager->create($class, ['attrData' => $attrData, 'code' => $code]);
        } catch (\Exception $ex) {
            throw new LocalizedException(__('Invalid attribute type: "%1"', $attrData['type']));
        }
    }
}
