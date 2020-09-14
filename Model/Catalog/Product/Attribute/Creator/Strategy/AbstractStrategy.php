<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class AbstractStrategy
 */
abstract class  AbstractStrategy implements AttributeCreationStrategyInterface
{
    /**
     * @var eavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $attrData;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var array
     */
    protected static $defaultAttrConfig = [
        'backend'                 => '',
        'frontend'                => '',
        'input'                   => 'text',
        'class'                   => '',
        'source'                  => '',
        'global'                  => ScopedAttributeInterface::SCOPE_STORE,
        'visible'                 => true,
        'required'                => false,
        'user_defined'            => true,
        'searchable'              => true,
        'filterable'              => true,
        'comparable'              => true,
        'visible_on_front'        => true,
        'used_in_product_listing' => true,
        'unique'                  => false,
    ];

    /**
     * AbstractStrategy constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $attrData
     * @param string $code
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $attrData,
        string $code
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attrData = $attrData;
        $this->code = $code;
    }

    /**
     * @param array $existingAttribute
     * @return array
     */
    public function getExistingAttributeOptions(array $existingAttribute): array
    {
        return [
            'backend'                  => $existingAttribute['backend_model'],
            'frontend'                 => $existingAttribute['frontend_model'],
            'input'                    => $existingAttribute['frontend_input'],
            'class'                    => $existingAttribute['frontend_class'],
            'source'                   => $existingAttribute['source_model'],
            'global'                   => $existingAttribute['is_global'],
            'visible'                  => $existingAttribute['is_visible'],
            'required'                 => $existingAttribute['is_required'],
            'user_defined'             => $existingAttribute['is_user_defined'],
            'searchable'               => $existingAttribute['is_searchable'],
            'filterable'               => $existingAttribute['is_searchable'],
            'comparable'               => $existingAttribute['is_comparable'],
            'visible_on_front'         => $existingAttribute['is_visible_on_front'],
            'used_in_product_listing'  => $existingAttribute['used_in_product_listing'],
            'unique'                   => $existingAttribute['is_unique'],
            'used_for_promo_rules'     => $existingAttribute['is_used_for_promo_rules'],
            'is_html_allowed_on_front' => $existingAttribute['is_html_allowed_on_front'],
            'used_for_sort_by'         => $existingAttribute['used_for_sort_by'],
            'is_used_in_grid'          => $existingAttribute['is_used_in_grid'],
            'is_filterable_in_grid'    => $existingAttribute['is_filterable_in_grid']
        ];
    }
}
