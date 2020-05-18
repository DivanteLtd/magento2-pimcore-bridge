<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Divante\PimcoreIntegration\Listeners\Validator\AttributeCodeValidatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\StrategyFactoryInterface;
use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\LabelManager;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ConfigFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ConfigFactory as EavConfigFactory;

/**
 * Class NewAttributeListener
 */
class NewAttributeListener implements ObserverInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StrategyFactoryInterface
     */
    private $strategyFactory;

    /**
     * @var array
     */
    private $excludedAttributes;

    /**
     * @var LabelManager
     */
    private $labelManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var Config
     */
    private $configFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AbstractValidator
     */
    private $attrCodeValidator;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var EavConfigFactory
     */
    private $eavConfigFactory;

    /**
     * NewAttributesListener constructor.
     *
     * @param ResourceConnection $resource
     * @param StrategyFactoryInterface $strategyFactory
     * @param LabelManager $labelManager
     * @param StoreManagerInterface $storeManager
     * @param AttributeManagementInterface $attributeManagement
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AttributeRepository $attributeRepository
     * @param ConfigFactory $configFactory
     * @param BridgeLoggerFactory $loggerFactory
     * @param Registry $registry
     * @param AttributeCodeValidatorInterface $attrCodeValidator
     * @param EavConfigFactory $eavConfigFactory
     * @param array $excludedAttributes Attributes that should be excluded to not break magento
     */
    public function __construct(
        ResourceConnection $resource,
        StrategyFactoryInterface $strategyFactory,
        LabelManager $labelManager,
        StoreManagerInterface $storeManager,
        AttributeManagementInterface $attributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeRepository $attributeRepository,
        ConfigFactory $configFactory,
        BridgeLoggerFactory $loggerFactory,
        Registry $registry,
        AttributeCodeValidatorInterface $attrCodeValidator,
        EavConfigFactory $eavConfigFactory,
        array $excludedAttributes = []
    ) {
        $this->resource = $resource;
        $this->strategyFactory = $strategyFactory;
        $this->excludedAttributes = $excludedAttributes;
        $this->labelManager = $labelManager;
        $this->storeManager = $storeManager;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->configFactory = $configFactory;
        $this->logger = $loggerFactory->getLoggerInstance();
        $this->registry = $registry;
        $this->attrCodeValidator = $attrCodeValidator;
        $this->attributeRepository = $attributeRepository;
        $this->eavConfigFactory = $eavConfigFactory;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws \Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $productsData = $observer->getData('products');

        if (empty($productsData)) {
            return;
        }

        foreach ($productsData as $pimId => $data) {
            $attrSet = $this->attributeSetRepository->getByChecksum($data['attr_checksum']['value']);

            $config = $this->configFactory->create();
            $groupId = $config->getAttributeGroupId($attrSet->getAttributeSetId(), 'Pimcore');

            $elements = $data['elements'];
            $configurables = $this->extractConfigurableCodes($data);

            $this->validateAttributes($elements);

            foreach ($elements as $code => $attrData) {
                if (\in_array($code, $this->excludedAttributes)) {
                    continue;
                }

                $isLabelUpdated = $this->tryToUpdateAttributeLabels($code, $attrData);

                if (!$this->isToProcessAttribute($attrData)) {
                    continue;
                }

                if (\in_array($code, $configurables)) {
                    $attrData['is_configurable'] = true;
                }

                $strategy = $this->strategyFactory->create($code, $attrData);

                try {
                    $attrId = $strategy->execute();
                } catch (\Exception $ex) {
                    if ($this->registry->registry('is_attribute_set_new')) {
                        $this->attributeSetRepository->delete($attrSet);
                    }
                    throw $ex;
                }

                if ($attrId) {
                    try {
                        $this->attributeManagement->assign(
                            Product::ENTITY,
                            $attrSet->getAttributeSetId(),
                            $groupId,
                            $code,
                            1
                        );
                    } catch (NoSuchEntityException $ex) {
                        $this->logger->critical($ex->getMessage());
                    }

                    if (!$isLabelUpdated) {
                        $this->labelManager->saveLabelsForAttribute(
                            $code,
                            [$this->storeManager->getStore()->getId() => $attrData['label']]
                        );
                    }
                }
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function extractConfigurableCodes(array $data): array
    {
        $configurables = [];

        if (!empty($data['properties'])) {
            foreach ($data['properties'] as $property) {
                if (!empty($property['name']) && $property['name'] === 'configurable_attributes') {
                    $configurables = explode(',', $property['data']);
                }
            }
        }

        return $configurables;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isSelectType(string $type): bool
    {
        return ($type === 'select' || $type === 'multiselect' || $type === 'visualswatch');
    }

    /**
     * @param $elements
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validateAttributes($elements)
    {
        foreach ($elements as $code => $attrData) {
            if (!$this->attrCodeValidator->isValid($code)) {
                $errorMessage = __(
                    'An attribute code "%3" must not be less than %1 and more than %2 characters.',
                    $this->attrCodeValidator->getMinLength(),
                    $this->attrCodeValidator->getMaxLength(),
                    $code
                );

                throw new LocalizedException($errorMessage);
            }
        }
    }

    /**
     * @param $attrData
     *
     * @return bool
     */
    public function isToProcessAttribute($attrData): bool
    {
        return ($this->registry->registry('is_attribute_set_new') || $this->isSelectType($attrData['type']));
    }

    /**
     * @param $code
     * @param $attrData
     * @throws StateException
     * @throws LocalizedException
     *
     * @return bool
     */
    public function tryToUpdateAttributeLabels($code, $attrData): bool
    {
        try {
            /** @var EavConfig $eavConfig */
            $eavConfig = $this->eavConfigFactory->create();
            $attr = $eavConfig->getAttribute(Product::ENTITY, $code);

            if (!$attr->getId()) {
                return false;
            }

            $this->labelManager->saveLabelsForAttribute(
                $code,
                [$this->storeManager->getStore()->getId() => $attrData['label']]
            );

            return true;
        } catch (NoSuchEntityException $ex) {
            return false;
        }
    }
}
