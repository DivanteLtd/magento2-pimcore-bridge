<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\System\Config;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class AbstractFrequencyConfig
 */
abstract class AbstractFrequencyConfig extends Value
{
    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @throws \Exception
     *
     * @return Value
     */
    public function afterSave()
    {
        $time = $this->getData($this->getTimeConfigValuePath());
        $frequency = $this->getData($this->getFrequencyConfigValuePath());

        $cronExprArray = [
            (int) $time[1], //Minute
            (int) $time[0], //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        $cronExprString = implode(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                $this->getCronStringPath(),
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                $this->getCronStringPath()
            )->save();
            $this->configValueFactory->create()->load(
                $this->getCronModelPath(),
                'path'
            )->setValue(
                $this->runModelPath
            )->setPath(
                $this->getCronModelPath()
            )->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }

    /**
     * @return string
     */
    abstract protected function getCronStringPath(): string;

    /**
     * @return string
     */
    abstract protected function getCronModelPath(): string;

    /**
     * @return string
     */
    abstract protected function getTimeConfigValuePath(): string;

    /**
     * @return string
     */
    abstract protected function getFrequencyConfigValuePath(): string;
}
