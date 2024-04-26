<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CategoryRequestRepository implements \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
{

    /**
     * @var Request[]
     */
    protected $instancesById = [];

    /**
     * @var CategoryRequestFactory
     */
    protected $categoryRequestFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\CategoryRequest\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\CategoryRequest
     */
    protected $resourceModel;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * CategoryRequestRepository constructor.
     * @param CategoryRequestFactory $categoryRequestFactory
     * @param ResourceModel\CategoryRequest\CollectionFactory $collectionFactory
     * @param ResourceModel\CategoryRequest $resourceModel
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        CategoryRequestFactory $categoryRequestFactory,
        \Magedelight\Vendor\Model\ResourceModel\CategoryRequest\CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Model\ResourceModel\CategoryRequest $resourceModel,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->categoryRequestFactory = $categoryRequestFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($requestId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        $request = $this->categoryRequestFactory->create();
        if (!$requestId) {
            return $request;
        }
        if (!isset($this->instancesById[$requestId][$cacheKey]) || $forceReload) {
            $request->load($requestId);
            if (!$request->getId()) {
                throw new NoSuchEntityException(__('Requested category request doesn\'t exist'));
            }
            $this->instancesById[$requestId][$cacheKey] = $request;
        }
        return $this->instancesById[$requestId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return sha1($this->serializer->serialize($serializeData));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magedelight\Vendor\Api\Data\CategoryRequestInterface $request)
    {
        try {
            $this->resourceModel->save($request);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __($e->getMessage())
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save category request' . $e->getMessage())
            );
        }

        unset($this->instancesById[$request->getId()]);

        return $this->getById($request->getId());
    }

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\CategoryRequestInterface $request
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(\Magedelight\Vendor\Api\Data\CategoryRequestInterface $request)
    {
        return $this->deleteById($request->getId());
    }

    /**
     *
     * @param $requestId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function deleteById($requestId)
    {
        $request = $this->getById($requestId);
        $request->delete();
        return true;
    }
}
