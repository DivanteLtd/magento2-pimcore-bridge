<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Magento\Catalog\Model\Product;

/**
 * Class NewsToModifier
 */
class NewsToModifier implements DataModifierInterface
{
    /**
     * @param Product $product
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @return array
     */
    public function handle(Product $product, PimcoreProductInterface $pimcoreProduct): array
    {
        $format = 'Y-m-d H:i:s';
        $newsFromDate = new \DateTime($pimcoreProduct->getData('news_from_date'));
        $newsToDate = new \DateTime($pimcoreProduct->getData('news_to_date'));

        if ($newsFromDate > $newsToDate) {
            $pimcoreProduct->setData('news_from_date', $newsFromDate->format($format));
            $pimcoreProduct->setData('news_to_date', $newsFromDate->format($format));
        }

        return [$product, $pimcoreProduct];
    }
}
