<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Pimcore;

/**
 * Interface PimcoreAttributeHandlerInterface
 */
interface PimcoreAttributeMapperInterface
{
    /**
     * Pimcore datetime object
     */
    const DATETIME = 'datetime';

    /**
     * Pimcore text object
     */
    const TEXT = 'text';

    /**
     * Pimcore textarea object
     */
    const TEXTAREA= 'textarea';

    /**
     * Pimcore wysiwyg object
     */
    const WYSIWYG= 'wysiwyg';

    /**
     * Pimcore quantityValue type
     */
    const QVALUE= 'quantityValue';

    /**
     * Pimcore object type object
     */
    const OBJECT = 'object';

    /**
     * Pimcore object type yesno
     */
    const YESNO = 'yesno';

    /**
     * Pimcore multiobject type object
     */
    const MULTIOBJECT = 'multiobject';

    /**
     * Pimcore asset type object
     */
    const ASSET = 'asset';

    /**
     * Pimcore select type object
     */
    const SELECT = 'select';

    /**
     * Pimcore multiselect type object
     */
    const MULTISELECT = 'multiselect';

    /**
     * Pimcore multiselect type object
     */
    const VISUALSWATCH = 'visualswatch';

    /**
     * @param array $attributeData
     *
     * @return mixed
     */
    public function mapUsingType(array $attributeData);
}
