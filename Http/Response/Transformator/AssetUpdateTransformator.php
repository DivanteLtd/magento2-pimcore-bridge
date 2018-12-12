<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator;

use Divante\PimcoreIntegration\File\Mime;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetDataObjectFactory;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\ChecksumFactory;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\ChecksumInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Zend\Http\Response;

/**
 * Class AssetUpdateTransformator
 */
class AssetUpdateTransformator implements ResponseTransformatorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $assetDataObjectFactory;

    /**
     * @var ChecksumFactory
     */
    private $checksumFactory;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var string
     */
    private $suffix = '_pim';

    /**
     * CategoryUpdateTransformator constructor.
     *
     * @param AssetDataObjectFactory $assetDataObjectFactory
     * @param ChecksumFactory $checksumFactory
     * @param Mime $mime
     */
    public function __construct(
        AssetDataObjectFactory $assetDataObjectFactory,
        ChecksumFactory $checksumFactory,
        Mime $mime
    ) {
        $this->assetDataObjectFactory = $assetDataObjectFactory;
        $this->checksumFactory = $checksumFactory;
        $this->mime = $mime;
    }

    /**
     * @param Response $response
     *
     * @return DataObject
     */
    public function transform(Response $response): DataObject
    {
        /** @var AssetInterface|DataObject $dto */
        $dto = $this->assetDataObjectFactory->create();
        $rawDataArr = json_decode($response->getBody(), true);

        $dto->setData(AssetInterface::IS_SUCCESS, $rawDataArr['success']);
        $assetData = $rawDataArr['data'];
        $dto->setData(AssetInterface::ENCODED_IMAGE, $assetData['data']);
        $dto->setData(AssetInterface::DECODED_IMAGE, base64_decode($assetData['data']));

        /** @var ChecksumInterface $checksum */
        $checksum = $this->checksumFactory->create(['data' => $assetData['checksum']]);
        $dto->setData(AssetInterface::CHECKSUM, $checksum);

        $dto->setData(AssetInterface::MIMETYPE, $assetData['mimetype']);
        $dto->setData(AssetInterface::PIM_ID, $assetData['id']);
        $dto->setData(AssetInterface::NAME, $this->createFilename($assetData['id']));

        $dto->setData(AssetInterface::EXT, $this->mime->getExtension($assetData['mimetype']));

        return $dto;
    }

    /**
     * @param string $base
     *
     * @return string
     */
    private function createFilename(string $base = ''): string
    {
        return sprintf('%s%s', $base, $this->suffix);
    }
}
