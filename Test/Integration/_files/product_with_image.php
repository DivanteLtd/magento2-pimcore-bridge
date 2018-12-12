<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/product_image.php';
require __DIR__ . '/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

/** @var $product \Magento\Catalog\Model\Product */
$product->setStoreId(0)
    ->setImage('/1/1/117_pim.jpg')
    ->setSmallImage('/1/1/117_pim.jpg')
    ->setThumbnail('/1/1/117_pim.jpg')
    ->setData('media_gallery', ['images' => [
        [
            'file' => '/1/1/117_pim.jpg',
            'position' => 1,
            'label' => 'Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ],
    ]])
    ->setCanSaveCustomOptions(true)
    ->save();
