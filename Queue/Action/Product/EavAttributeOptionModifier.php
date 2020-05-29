<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\Model\Eav\Entity\OptionResolver;
use Magento\Catalog\Model\Product;

/**
 * Class EavAttributeOptionModifier
 */
class EavAttributeOptionModifier implements DataModifierInterface
{
    /**
     * @var OptionResolver
     */
    private $optionResolver;

    /**
     * EavAttributeOptionModifier constructor.
     *
     * @param OptionResolver $optionResolver
     */
    public function __construct(OptionResolver $optionResolver)
    {
        $this->optionResolver = $optionResolver;
    }

    /**
     * @param Product $product
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @return array
     */
    public function handle(Product $product, PimcoreProductInterface $pimcoreProduct): array
    {
        foreach ($pimcoreProduct->getData() as $code => $attr) {
            if (empty($attr['type'])
                || ($attr['type'] !== 'select' && $attr['type'] !== 'multiselect' && $attr['type'] !== 'visualswatch')
                || !is_array($attr)
            ) {
                continue;
            }

            $value = false;

            if ($attr['type'] === 'select') {
                $value = $this->optionResolver->resolveOptionId($attr['value'], $code);
            } elseif ($attr['type'] === 'multiselect') {
                $value = $this->optionResolver->resolveMultipleOptionIds($attr['value'], $code);
            } elseif ($attr['type'] === 'visualswatch') {
                $value = $this->optionResolver->resolveOptionId($attr['value'], $code);
            }

            if (!$value) {
                $pimcoreProduct->unsetData($code);
            } else {
                $pimcoreProduct->setData($code, $value);
            }
        }

        return [$product, $pimcoreProduct];
    }
}
