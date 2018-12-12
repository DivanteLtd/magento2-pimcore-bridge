<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Setup;

use Divante\PimcoreIntegration\Model\Entity\Attribute\Backend\IsActiveInPimcore;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->addPimcoreIdAttr($eavSetup);
        }

        if (version_compare($context->getVersion(), '1.0.2.3') < 0) {
            $this->addIsActiveInPimAttr($eavSetup);
        }

    }

    /**
     * @param $eavSetup
     *
     * @return void
     */
    protected function addPimcoreIdAttr(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(Category::ENTITY, 'pimcore_id', [
            'type'     => 'int',
            'label'    => 'Pimcore ID',
            'input'    => 'text',
            'visible'  => true,
            'default'  => '',
            'required' => false,
            'global'   => ScopedAttributeInterface::SCOPE_STORE,
        ]);
    }

    /**
     * @param $eavSetup
     *
     * @return void
     */
    protected function addIsActiveInPimAttr(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(Product::ENTITY, 'is_active_in_pim', [
            'type'                    => 'int',
            'label'                   => 'Is active in pimcore',
            'input'                   => 'select',
            'visible'                 => false,
            'global'                  => ScopedAttributeInterface::SCOPE_WEBSITE,
            'group'                   => 'Pimcore',
            'source'                  => Boolean::class,
            'backend'                 => IsActiveInPimcore::class,
            'required'                => true,
            'user_defined'            => false,
            'default'                 => '0',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => false,
            'unique'                  => false,
        ]);
    }
}
