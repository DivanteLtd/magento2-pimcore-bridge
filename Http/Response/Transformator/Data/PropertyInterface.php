<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

/**
 * Interface PropertyInterface
 */
interface PropertyInterface
{
    /**
     * Data structure name
     */
    const KEY_NAME = 'name';

    /**
     * Data structure data
     */
    const KEY_DATA = 'prop_data';

    /**
     * Data structure type
     */
    const KEY_TYPE = 'type';

    /**
     * Data structure inheritable
     */
    const KEY_INHERITABLE = 'inheritable';

    /**
     * Data structure inherited
     */
    const KEY_INHERITED = 'inherited';

    /**
     * @param string $name
     *
     * @return PropertyInterface
     */
    public function setName(string $name): PropertyInterface;

    /**
     * @param $data
     *
     * @return PropertyInterface
     */
    public function setPropData($data): PropertyInterface;

    /**
     * @param string $type
     *
     * @return PropertyInterface
     */
    public function setType(string $type): PropertyInterface;

    /**
     * @param bool $inheritable
     *
     * @return PropertyInterface
     */
    public function setInheritable(bool $inheritable): PropertyInterface;

    /**
     * @param bool $inherited
     *
     * @return PropertyInterface
     */
    public function setInherited(bool $inherited): PropertyInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getPropData();

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function getInheritable(): bool;

    /**
     * @return bool
     */
    public function getInherited(): bool;
}
