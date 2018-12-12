<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset\Strategy;

use Divante\PimcoreIntegration\Exception\InvalidStrategyException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class StrategyFactory
 */
class AssetHandlerStrategyFactory
{
    /**
     * Available strategies
     */
    const PRODUCT_IMAGE_IMPORT = 'product_image';
    const CATEGORY_IMAGE_IMPORT = 'category_image';
    const REPLACE_ASSET = 'replace_asset';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * StrategyFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $strategy
     *
     * @throws InvalidStrategyException
     *
     * @return AssetHandlerStrategyInterface
     */
    public function create(string $strategy): AssetHandlerStrategyInterface
    {
        switch ($strategy) {
            case self:: PRODUCT_IMAGE_IMPORT:
                return $this->objectManager->create(ProductImage::class);
            case self:: REPLACE_ASSET:
                return $this->objectManager->create(ReplaceImages::class);
            case self:: CATEGORY_IMAGE_IMPORT:
                return $this->objectManager->create(CategoryImages::class);
            default:
                throw new InvalidStrategyException(__('"%1" is not valid strategy.', $strategy));
        }
    }
}
