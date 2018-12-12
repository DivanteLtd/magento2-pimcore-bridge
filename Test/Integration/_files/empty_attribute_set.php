<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$entityTypeId = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode('catalog_product')->getId();
$attributeSet->setData([
    'attribute_set_name' => 'pimcore-set-1',
    'entity_type_id'     => $entityTypeId,
    'sort_order'         => 200,
    'checksum'           => '#123',
]);
$attributeSet->validate();
$attributeSet->save();
