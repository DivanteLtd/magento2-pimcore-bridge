<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

/**
 * Class TypeMetadataExtractor
 */
class TypeMetadataExtractor implements TypeMetadataExtractorInterface
{
    /**
     * @var array
     */
    private $types;

    /**
     * @var string
     */
    private $typeString;

    /**
     * @var array|null
     */
    private $chunks;

    /**
     * TypeMetadata constructor.
     *
     * @param string $typeString
     */
    public function __construct(string $typeString)
    {
        $this->typeString = $typeString;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        if (isset($this->getChunks()[0])) {
            return $this->getChunks()[0];
        }

        return '';
    }

    /**
     * @return array
     */
    private function getChunks(): array
    {
        if (!$this->chunks) {
            $this->chunks = explode('/', trim($this->typeString, '/'));
        }

        return $this->chunks;
    }

    /**
     * Type string must contain at least target_entity/asset_type
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return count($this->getChunks()) >= 2;
    }

    /**
     * @return array
     */
    public function getAssetTypes(): array
    {
        if (!$this->types) {
            $types = explode(',', $this->getChunks()[1]) ?? [];
            $types = array_map('trim', $types);
            $this->types = $types;
        }

        return $this->types;
    }
}
