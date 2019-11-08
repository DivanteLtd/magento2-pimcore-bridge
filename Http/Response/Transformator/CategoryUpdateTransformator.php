<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Http\Response;

/**
 * Class CategoryUpdateTransformator
 */
class CategoryUpdateTransformator implements ResponseTransformatorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CategoryUpdateTransformator constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Response $response
     *
     * @return DataObject
     */
    public function transform(Response $response): DataObject
    {
        $container = $this->dataObjectFactory->create();
        $rawDataArr = json_decode($response->getBody(), true);

        $container->setData('is_success', $rawDataArr['success']);

        foreach ($rawDataArr['data'] as $id => $data) {
            $flatData = $this->dataObjectFactory->create();
            $elements = $data['elements'];
            $isRoot = $data['isRoot'] ?? false;

            $flatData->setData('name', $elements['name']['value'])
                ->setData('description', $elements['description']['value'] ?? '')
                ->setData('is_active', (int) $elements['is_active']['value'])
                ->setData('pimcore_id', $id)
                ->setData('image', $elements['image']['value']['id'] ?? false);

            if ($elements['url_key']['value']) {
                $flatData->setData('url_key', $elements['url_key']['value']);
            }

            if($elements['position']["value"]) {
                $flatData->setData('position', $elements['position']['value']);
            }

            if ($this->hasParent($isRoot, $data)) {
                try {
                    $flatData->setData('pimcore_parent_id', $data['parentId']);
                    $parent = $this->categoryRepository->getByPimId($data['parentId']);
                    $flatData->setData('parent_id', $parent->getId());
                } catch (NoSuchEntityException $ex) {
                    $flatData->setData('parent_id', null);
                }
            } else {
                $flatData->setData('parent_id', $this->storeManager->getStore()->getRootCategoryId());
            }

            $container->setData($id, $flatData);
        }

        return $container;
    }

    /**
     * @param bool $isRoot
     * @param array $data
     *
     * @return bool
     */
    private function hasParent(bool $isRoot, array $data): bool
    {
        return !$isRoot && isset($data['parentId']);
    }
}
