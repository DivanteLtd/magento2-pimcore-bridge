<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

use Magento\Framework\DataObject;

/**
 * Class Property
 */
class Property extends DataObject implements PropertyInterface
{
    /**
     * @param string $name
     *
     * @return PropertyInterface
     */
    public function setName(string $name): PropertyInterface
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * @param string $type
     *
     * @return PropertyInterface
     */
    public function setType(string $type): PropertyInterface
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * @param $data
     *
     * @return PropertyInterface
     */
    public function setPropData($data): PropertyInterface
    {
        return $this->setData(self::KEY_DATA, $data);
    }

    /**
     * @param bool $inheritable
     *
     * @return PropertyInterface
     */
    public function setInheritable(bool $inheritable): PropertyInterface
    {
        return $this->setData(self::KEY_INHERITABLE, $inheritable);
    }

    /**
     * @param bool $inherited
     *
     * @return PropertyInterface
     */
    public function setInherited(bool $inherited): PropertyInterface
    {
        return $this->setData(self::KEY_INHERITED, $inherited);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * @return bool
     */
    public function getInheritable(): bool
    {
        return $this->getData(self::KEY_INHERITABLE);
    }

    /**
     * @return bool
     */
    public function getInherited(): bool
    {
        return $this->getData(self::KEY_INHERITED);
    }

    /**
     * @return mixed
     */
    public function getPropData()
    {
        return $this->getData(self::KEY_DATA);
    }
}
