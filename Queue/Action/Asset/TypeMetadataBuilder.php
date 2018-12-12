<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

use Divante\PimcoreIntegration\Exception\InvalidAssetMetadataException;

/**
 * Class TypeMetadataBuilder
 */
class TypeMetadataBuilder implements TypeMetadataBuilderInterface
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $assetTypes;

    /**
     * @var string
     */
    private $metadataPattern = '%s/%s';

    /**
     * TypeMetadataBuilder constructor.
     *
     * @param string $entityType
     * @param array $assetTypes
     *
     * @throws InvalidAssetMetadataException
     */
    public function __construct(string $entityType, array $assetTypes)
    {
        $this->entityType = $entityType;
        $this->assetTypes = $assetTypes;

        if (!$entityType || empty($assetTypes)) {
            throw new InvalidAssetMetadataException(__('Neither entity type or asset type can be empty.'));
        }
    }

    /**
     * @return string
     */
    public function getTypeMetadataString(): string
    {
        $trimmedTypes = array_map('trim', $this->assetTypes);

        return sprintf($this->metadataPattern, $this->entityType, implode(',', $trimmedTypes));
    }
}
