<?php

namespace Divante\PimcoreIntegration\Block\Adminhtml\System\Config\Form\Queue;

use Divante\PimcoreIntegration\Queue\Processor\AssetQueueProcessor;
use Divante\PimcoreIntegration\Queue\Processor\CategoryQueueProcessor;
use Divante\PimcoreIntegration\Queue\Processor\ProductQueueProcessor;

class Status extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var CategoryQueueProcessor
     */
    private $categoryQueueProcessor;

    /**
     * @var ProductQueueProcessor
     */
    private $productQueueProcessor;

    /**
     * @var AssetQueueProcessor
     */
    private $assetQueueProcessor;


    /**
     * Status constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CategoryQueueProcessor $categoryQueueProcessor
     * @param ProductQueueProcessor $productQueueProcessor
     * @param AssetQueueProcessor $assetQueueProcessor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CategoryQueueProcessor $categoryQueueProcessor,
        ProductQueueProcessor $productQueueProcessor,
        AssetQueueProcessor $assetQueueProcessor,
        array $data = []
    ){
        $this->categoryQueueProcessor = $categoryQueueProcessor;
        $this->productQueueProcessor = $productQueueProcessor;
        $this->assetQueueProcessor = $assetQueueProcessor;
        parent::__construct($context, $data);
    }


    /**
     * Return element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $countCategory = $this->categoryQueueProcessor->predictQueueLength();
        $countProduct = $this->productQueueProcessor->predictQueueLength();
        $countAsset = $this->assetQueueProcessor->predictQueueLength();
        $status =  $countCategory . '/' . $countProduct . '/' . $countAsset;

        return 'Predicted item-count for the next queue proccess (Category/Product/Asset): <strong>' .$status . '</strong>';
    }

}