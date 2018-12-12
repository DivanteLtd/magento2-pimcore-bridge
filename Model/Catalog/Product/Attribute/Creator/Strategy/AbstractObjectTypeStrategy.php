<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Event\Manager;

/**
 * Class AbstractObjectTypeStrategy
 */
abstract class AbstractObjectTypeStrategy implements AttributeCreationStrategyInterface
{
    /**
     * @var array
     */
    protected $attrData;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var Manager
     */
    protected $eventManager;

    /**
     * @var eavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * AbstractObjectTypeStrategy constructor.
     *
     * @param Manager $eventManager
     * @param array $attrData
     * @param string $code
     */
    public function __construct(Manager $eventManager, array $attrData, string $code, EavSetupFactory $eavSetupFactory)
    {
        $this->attrData = $attrData;
        $this->code = $code;
        $this->eventManager = $eventManager;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return int
     */
    public function execute(): int
    {
        $this->eventManager->dispatch($this->getEventName(), [
            'attrData' => $this->attrData,
            'code'     => $this->code,
        ]);

        $eavSetup = $this->eavSetupFactory->create();

        return $eavSetup->getAttributeId(Product::ENTITY, $this->code);
    }

    /**
     * @return string
     */
    abstract protected function getEventName(): string;
}
