<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/category_image.php';
require __DIR__ . '/category.php';

/** @var \Divante\PimcoreIntegration\Api\CategoryRepositoryInterface $repo */
$repo = $objectManager->create(\Divante\PimcoreIntegration\Api\CategoryRepositoryInterface::class);
$category = $repo->get(333);
$category->setImage('117_pim.jpg')->save();
