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
class MicrositeRepository implements \Magedelight\Vendor\Api\MicrositeRepositoryInterface
{
    /**
     * @var MicrositeFactory
     */
    protected $micrositeFactory;

    /**
     * @var MicrositeRegistry
     */
    protected $micrositeRegistry;

    /**
     * @var Microsite[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Microsite
     */
    protected $resourceModel;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @param MicrositeFactory $micrositeFactory
     * @param MicrositeRegistry $micrositeRegistry
     * @param \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magedelight\Vendor\Model\ResourceModel\Microsite $resourceModel
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Vendor\Helper\Microsite\Data $helper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        MicrositeFactory $micrositeFactory,
        MicrositeRegistry $micrositeRegistry,
        \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Vendor\Model\ResourceModel\Microsite $resourceModel,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Vendor\Helper\Microsite\Data $helper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->micrositeFactory = $micrositeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->micrositeRegistry = $micrositeRegistry;
        $this->filterBuilder = $filterBuilder;
        $this->vendorRepository = $vendorRepository;
        $this->_eventManager = $eventManager;
        $this->vendorHelper = $vendorHelper;
        $this->helper = $helper;
        $this->uploaderFactory = $uploaderFactory;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->_file = $file;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($micrositeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        $microsite = $this->micrositeFactory->create();
        if (!$micrositeId) {
            return $microsite;
        }
        if (!isset($this->instancesById[$micrositeId][$cacheKey]) || $forceReload) {
            $microsite->load($micrositeId);
            if (!$microsite->getId()) {
                throw new NoSuchEntityException(__('Requested microsite doesn\'t exist'));
            }
            $this->instancesById[$micrositeId][$cacheKey] = $microsite;
        }
        return $this->instancesById[$micrositeId][$cacheKey];
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
    public function save(\Magedelight\Vendor\Api\Data\MicrositeInterface $microsite)
    {
        $vendorId = $microsite->getVendorId();
        try {
            $vendorData = $this->vendorRepository->getById($vendorId);
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }

        try {
            $storeId = ($microsite->getStoreId() != null) ? $microsite->getStoreId() : 0;
            $existingData = $this->getExistingMicrosite($vendorId, $microsite->getStoreId());
            $bannerData = $this->processBanner('microsite[banner]', $microsite, $existingData);
            $microsite->setDeleteBanner(null);
            if ($existingData->getMicrositeId()) {
                $microsite->setData('banner', $bannerData);
                if ($existingData->getPageTitle() != $microsite->getPageTitle()) {
                    $microsite->setData('url_key', $this->helper->generateUrlKey(trim($microsite->getPageTitle())));
                }

                $micrositeData = $this->micrositeFactory->create();
                $micrositeData->load($existingData->getMicrositeId());
                foreach ($microsite->getData() as $key => $value) {
                    $micrositeData->setData($key, $value);
                }

                $micrositeData->save();
                $eventParams = ['microsite' => $micrositeData, 'post_data' => $micrositeData->getData()];
                $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                return $micrositeData;
            } else {
                $storeIds = $this->vendorHelper->getAllStoreIds();
                $microsite->setData('url_key', $this->helper->generateUrlKey(trim($microsite->getPageTitle())));
                $microsite->setData('banner', $bannerData);
                foreach ($storeIds as $store) {
                    $micrositeData = $this->micrositeFactory->create();
                    $microsite->setData('store_id', $store);
                    $micrositeData->setData($microsite->getData());
                    $micrositeData->save();
                    $eventParams = ['microsite' => $micrositeData, 'post_data' => $micrositeData->getData()];
                    $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                }
                return $this->getExistingMicrosite($vendorId, $storeId);
            }
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __($e->getMessage())
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save microsite'));
        }

        unset($this->instancesById[$microsite->getId()]);

        return $this->getById($microsite->getId());
    }

    protected function getExistingMicrosite($vendorId, $storeId)
    {
        $microsite = $this->collectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem();

        return $microsite;
    }

    /**
     * @param string $fileId
     * @param $microsite
     * @param $existingData
     * @return string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function processBanner($fileId, $microsite, $existingData)
    {
        $bannerFile = '';
        $path = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                             ->getAbsolutePath('microsite');
        /* Check banner delete or new */
        $files = $this->request->getFiles()->toArray();
        if (empty($files) && count($files) <= 0) {
            if ($existingData->getBanner() != null && !$microsite->getDeleteBanner()) {
                $bannerFile = $existingData->getBanner();
            } else {
                if ($this->_file->isExists($path . $existingData->getBanner()) && $existingData->getBanner() != null) {
                    $this->_file->deleteFile($path . $existingData->getBanner());
                }
                return $bannerFile;
            }
        } else {
            if ($existingData->getBanner() != null && $microsite->getDeleteBanner()) {
                if ($this->_file->isExists($path . $existingData->getBanner()) && $existingData->getBanner() != null) {
                    $this->_file->deleteFile($path . $existingData->getBanner());
                }
                return $bannerFile;
            }
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $fileData = $uploader->validateFile();
            $hasPostFiles = $fileData && !empty($fileData['name']);

            if ($hasPostFiles) {
                try {
                    $imageAdapter = $this->adapterFactory->create();
                    $uploader->addValidateCallback($fileId, $imageAdapter, 'validateUploadFile');
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    $result = $uploader->save($path);
                    $bannerFile = $result['file'];
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
                }
            }
        }

        return $bannerFile;
    }

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\MicrositeInterface $microsite
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(\Magedelight\Vendor\Api\Data\MicrositeInterface $microsite)
    {
        return $this->deleteById($microsite->getId());
    }

    /**
     *
     * @param type $micrositeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function deleteById($micrositeId)
    {
        $micrositeModel = $this->micrositeRegistry->retrieve($micrositeId);
        $micrositeModel->delete();
        $this->micrositeRegistry->remove($micrositeId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getByVendorId($vendorId, $storeId, $columns = ['*'], $forceReload = false)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        $microsite = $this->micrositeFactory->create();
        if (!$vendorId || !$storeId) {
            return $microsite;
        }
        if (!isset($this->instancesById['microsite'][$vendorId][$cacheKey]) || $forceReload) {
            $microsite = $this->collectionFactory->create()
                ->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToSelect($columns)
                ->getFirstItem();
            if (!$microsite->getId()) {
                throw new NoSuchEntityException(__('Requested microsite doesn\'t exist'));
            }
            $this->instancesById['microsite'][$vendorId][$cacheKey] = $microsite;
        }
        return $this->instancesById['microsite'][$vendorId][$cacheKey];
    }
}
