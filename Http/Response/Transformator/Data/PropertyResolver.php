<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

/**
 * Class PropertyResolver
 */
class PropertyResolver implements PropertyResolverInterface
{
    /**
     * @var PropertyFactory
     */
    private $propertyFactory;

    /**
     * PropertyResolver constructor.
     *
     * @param PropertyFactory $propertyFactory
     */
    public function __construct(PropertyFactory $propertyFactory)
    {
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * @param string $code
     * @param array $properties
     *
     * @return PropertyInterface|null
     */
    public function getProperty(string $code, array $properties)
    {
        foreach ($properties as $data) {
            if (isset($data['name']) && $data['name'] === $code) {
                /** @var PropertyInterface $property */
                $property = $this->propertyFactory->create();
                $property->setName($data['name'] ?? '');
                $property->setType($data['type'] ?? '');
                $property->setPropData($data['data'] ?? '');
                $property->setInherited($data['inherited'] ?? false);
                $property->setInheritable($data['inheritable'] ?? false);

                return $property;
            }
        }

        return null;
    }
}
