<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = Bootstrap::getObjectManager();
$pathResolver = $objectManager->create(PathResolver::class);
$mediaConfig = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);
$mediaDirectory->create('catalog/category');

copy(__DIR__ . '/117_pim.jpg', $pathResolver->getCategoryAssetPath('117_pim.jpg'));
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
