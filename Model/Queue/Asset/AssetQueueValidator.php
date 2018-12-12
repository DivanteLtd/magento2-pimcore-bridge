<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Asset;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\AssetEntity;
use Divante\PimcoreIntegration\Queue\Action\Asset\AssetType;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorFactory;
use Magento\Framework\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Class AssetValidator
 */
class AssetQueueValidator extends AbstractValidator
{
    /**
     * @var TypeMetadataExtractorFactory
     */
    private $metadataExtractorFactory;

    /**
     * AssetQueueValidator constructor.
     *
     * @param TypeMetadataExtractorFactory $metadataExtractorFactory
     */
    public function __construct(TypeMetadataExtractorFactory $metadataExtractorFactory)
    {
        $this->metadataExtractorFactory = $metadataExtractorFactory;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     *
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (!($value instanceof AssetQueueInterface)) {
            $this->_addMessages([
                sprintf("Invalid object type, expected '%s'", AssetQueueInterface::class),
            ]);

            return false;
        }

        if (null === $value->getAssetId()) {
            $this->_addMessages(["Field 'asset_id' is required."]);
        }

        if (null === $value->getStoreViewId()) {
            $this->_addMessages(["Field 'store_view_id' is required."]);
        }

        if ($value->getType()) {
            $this->validateMetadata($value);
        }

        return !$this->hasMessages();
    }

    /**
     * @param $value
     *
     * @return void
     */
    private function validateMetadata($value)
    {
        $metadataExtractor = $this->metadataExtractorFactory->create(['typeString' => $value->getType()]);

        if (empty($metadataExtractor->getEntityType())
            || empty(\in_array($metadataExtractor->getEntityType(), AssetEntity::getEntityTypes(), true))) {
            $this->_addMessages([
                sprintf(
                    'Only the following entity types are available: %s',
                    implode(',', AssetEntity::getEntityTypes())
                ),
            ]);
        }

        if (empty($metadataExtractor->getAssetTypes())
            || empty(array_intersect(AssetType::getAssetTypes(), $metadataExtractor->getAssetTypes()))) {
            $this->_addMessages([
                sprintf(
                    'Only the following asset types are available: %s',
                    implode(',', AssetType::getAssetTypes())
                ),
            ]);
        }
    }
}
