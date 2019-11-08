<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author azu tripuls.de
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Magento\Framework\Event\Observer;

/**
 * Class RelatedProductsLinkerListener
 */
class CrossSellLinkerListener extends  AbstractLinkerListener
{
    const PIMCORE_FIELDNAME_CROSSSELL = 'cross_sell_products';
    const MAGENTO_PRODUCT_LINKTYPE_CROSSSELL = 'crosssell';



    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $pimcoreProduct = $observer->getData('pimcore');
        $product = $observer->getData('product');

            $this->setNewProductLinks($pimcoreProduct,$product,
            self::MAGENTO_PRODUCT_LINKTYPE_CROSSSELL,
            self::PIMCORE_FIELDNAME_CROSSSELL);
    }


}
