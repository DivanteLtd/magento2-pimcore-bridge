<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Pimcore;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreAttributeMapperInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SimpleAttributeMapper
 */
class SimpleAttributeMapper implements PimcoreAttributeMapperInterface
{
    /**
     * @param array $attributeData
     *
     * @return mixed
     *
     * @throws LocalizedException
     */
    public function mapUsingType(array $attributeData)
    {
        switch ($attributeData['type']) {
            case self::TEXT:
            case self::TEXTAREA:
            case self::YESNO:
            case self::DATETIME:
            case self::WYSIWYG:
            case self::QVALUE:
                return $this->mapText($attributeData);
            case self::SELECT:
                return $this->mapSelect($attributeData);
            case self::MULTISELECT:
                return $this->mapMultiSelect($attributeData);
            case self::VISUALSWATCH:
                return $this->mapVisualSwatch($attributeData);
            case self::OBJECT:
            case self::ASSET:
                return $this->mapObject($attributeData);
            case self::MULTIOBJECT:
                return $this->mapMultiobject($attributeData);
            default:
                return null;
        }
    }

    /**
     * @param array $attributeData
     *
     * @return mixed
     */
    private function mapText(array $attributeData)
    {
        return $attributeData['value'];
    }

    /**
     * We need to map a key instead of value because this is how magento store option value
     *
     * @param array $attributeData
     *
     * @return array
     */
    private function mapSelect(array $attributeData): array
    {
        return [
            'type' => 'select',
            'value' => $attributeData['value']['key'] ?? '',
        ];
    }

    /**
     * @param array $attributeData
     *
     * @return array
     */
    private function mapMultiSelect(array $attributeData): array
    {
        $values = [];

        if ($attributeData['value'] && \is_array($attributeData['value'])) {
            foreach ($attributeData['value'] as $data) {
                $values[] = $data['key'] ?? '';
            }
        }

        return [
            'type' => 'multiselect',
            'value' => $values,
        ];
    }

    /**
     * @param array $attributeData
     *
     * @return array
     */
    private function mapVisualSwatch(array $attributeData): array
    {
        return [
            'type' => 'visualswatch',
            'value' => $attributeData['value']['key'] ?? '',
        ];
    }

    /**
     * @param array $object
     *
     * @return mixed
     *
     * @throws LocalizedException
     */
    private function mapObject(array $object)
    {
        if (isset($object['id'])) {
            return $object['id'];
        }

        if (empty($object['value'])) {
            return '';
        }

        if (isset($object['value']['id'])) {
            return $object['value']['id'];
        }

        if (!isset($object['value']) || empty($object['value'])) {
            return '';
        }

        return $this->mapUsingType($object['value']);
    }

    /**
     * @param array $attributeData
     *
     * @return array
     *
     * @throws LocalizedException
     */
    private function mapMultiobject(array $attributeData): array
    {
        $mappedResult = [];
        /** @var array $objects */
        $objects = $attributeData['value'];

        foreach ($objects as $data) {
            $mappedResult[] = $this->mapUsingType($data);
        }

        return $mappedResult;
    }
}
