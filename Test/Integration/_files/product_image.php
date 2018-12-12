<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
$mediaConfig = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);
$targetDirPath = $mediaConfig->getBaseMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/1/1/');
$targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/1/1/');
$mediaDirectory->create($targetDirPath);
$mediaDirectory->create($targetTmpDirPath);

$targetTmpFilePath = $mediaDirectory->getAbsolutePath() . DIRECTORY_SEPARATOR . $targetTmpDirPath
    . DIRECTORY_SEPARATOR . '117_pim.jpg';
copy(__DIR__ . '/117_pim.jpg', $targetTmpFilePath);
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
