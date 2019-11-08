<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ConfigFactory as EavConfigFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;

/**
 * Class LabelManager
 */
class LabelManager
{
    /**
     * @var AttributeRepositoryInterfaceFactory
     */
    private $attrRepositoryFactory;

    /**
     * @var Config|EavConfigFactory
     */
    private $configFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * LabelManager constructor.
     *
     * @param AttributeRepositoryInterfaceFactory $attrRepositoryFactory
     * @param EavConfigFactory $configFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        AttributeRepositoryInterfaceFactory $attrRepositoryFactory,
        EavConfigFactory $configFactory,
        ResourceConnection $resource
    ) {
        $this->attrRepositoryFactory = $attrRepositoryFactory;
        $this->configFactory = $configFactory;
        $this->resource = $resource;
    }

    /**
     * @param string $attrCode
     * @param array $labels
     *
     * @throws StateException
     *
     * @return void
     */
    public function saveLabelsForAttribute(string $attrCode, array $labels)
    {
        try {
            $attrRepository = $this->attrRepositoryFactory->create();

            /** @var Config $eavConfig */
            $eavConfig = $this->configFactory->create();
            /** @var AttributeInterface $attribute */
            $attr = $eavConfig->getAttribute(Product::ENTITY, $attrCode);
        } catch (LocalizedException $e) {
            return;
        }

        $frontEndLabels = $attr->getFrontendLabels();
        // if no store-view-specific labels are set, check and update the default-label
        if( is_array($frontEndLabels ) && count( $frontEndLabels)  == 0) {
            $newDefaultLabel = reset($labels);
            if($attr->getDefaultFrontendLabel() !== $newDefaultLabel){
                $attr->setDefaultFrontendLabel($newDefaultLabel);

                $attrRepository->save($attr);
                return;
            }
        }

        $currentLabels = $this->getStoreLabels($attr->getId());
        $labelsToSave = $currentLabels;

        foreach ($labels as $key => $label) {
            $labelsToSave[$key] = $label;
        }

        if (!$this->isLabelsChanged($currentLabels, $labelsToSave)) {
            return;
        }


        /** @var  $attr2 */
        $attr2 = $attrRepository->get(Product::ENTITY, $attrCode);
        $labelsX = $attr2->getFrontEndLabels();
        foreach ($labelsX as $label) {
            foreach ($labels as $key => $newLabelText) {
                if($key === $label->getStoreId()){
                    $label->setLabel($newLabelText );
                }
            }
        }
        $attr2->setFrontendLabels($labelsX);
        $attrRepository->save($attr2);
    }

    /**
     * @param $currentLabels
     * @param $labelsToSave
     *
     * @return bool
     */
    private function isLabelsChanged($currentLabels, $labelsToSave): bool
    {
        return ($currentLabels !== $labelsToSave);
    }

    /**
     * @param int $attrId
     * @return array
     */
    private function getStoreLabels(int $attrId)
    {
        $connection = $this->resource->getConnection();
        $query = $connection->select()->from(
            $connection->getTableName('eav_attribute_label'),
            ['value', 'store_id']
        )->where("attribute_id = ?", $attrId);

        $result = $connection->fetchAll($query);
        $labels = [];
        foreach ($result as $labelData) {
            $labels[$labelData['store_id']] = $labelData['value'];
        }

        return $labels;
    }
}
