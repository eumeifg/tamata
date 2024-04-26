<?php

namespace CAT\Address\Controller\Adminhtml\Index;

use CAT\Address\Helper\Data;
use CAT\Address\Model\RomCityRepository;
use CAT\Address\Model\RomCityFactory;
use CAT\Address\Model\ResourceModel\Collection\Collection;
use CAT\Address\Model\ResourceModel\Collection\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'CAT_Address::city';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Csv
     */
    private $csvProccesor;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RomCityFactory
     */
    private $romCityFactory;

    /**
     * @var ResultFactory
     */
    private $resultRedirect;

    /**
     * @var RomCityRepository
     */
    private $romCityRepository;

    /**
     * @param Context $context
     * @param Csv $csvProccesor
     * @param Reader $moduleReader
     * @param PageFactory $resultPageFactory
     * @param DirectoryList $directoryList
     * @param CollectionFactory $collectionFactory
     * @param ResultFactory $resultRedirect
     * @param RomCityFactory $romCityFactory
     * @param RomCityRepository $romCityRepository
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Csv $csvProccesor,
        Reader $moduleReader,
        PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        CollectionFactory $collectionFactory,
        ResultFactory $resultRedirect,
        RomCityFactory $romCityFactory,
        RomCityRepository $romCityRepository,
        Data $dataHelper
    ) {
        $this->romCityRepository = $romCityRepository;
        $this->resultRedirect = $resultRedirect;
        $this->romCityFactory = $romCityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->directoryList = $directoryList;
        $this->moduleReader = $moduleReader;
        $this->csvProccesor = $csvProccesor;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action list city.
     * @return $resultRedirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);

        $this->readCsv();

        return $resultRedirect;
    }

    public function readCsv()
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $fieName = $this->dataHelper->getConfigFileName();
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/test';

        $file = $pubMediaDir . $dirTest . $ds . $fieName;

        if (!empty($file)) {
            $csvData = $this->csvProccesor->getData($file);

            $csvDataProcessed = [];
            unset($csvData[0]);
            list($collection, $csvDataProcessed) = $this->csvProcessValues($csvData, $csvDataProcessed);
            //echo "<pre>"; print_r($csvDataProcessed); echo "</pre>"; die();
            foreach ($csvDataProcessed as $dataRow) {
                $regionId = $dataRow['region_id'];
                $cityName = $dataRow['city'];
                $cityNameAr = $dataRow['city_ar'];
                $entityId = $dataRow['entity_id'];

                $romCityRepository = $this->romCityFactory->create();
                if (isset($entityId) && is_numeric($entityId)) {
                    $romCityRepository = $this->romCityRepository->getById($entityId);
                    $romCityRepository->setCityName($cityName);
                    $romCityRepository->setCityArabicName($cityNameAr);
                    $this->romCityRepository->save($romCityRepository);
                    continue;
                }

                $romCityRepository->setRegionId($regionId);
                $romCityRepository->setCityName($cityName);
                $romCityRepository->setCityArabicName($cityNameAr);

                $collection->addItem($romCityRepository);
            }
        }
        $collection->walk('save');
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @param $csvData
     * @param $csvDataProcessed
     * @return array
     */
    private function csvProcessValues($csvData, $csvDataProcessed)
    {
        /** @var  Collection $collection */
        $collection = $this->collectionFactory->create();

        foreach ($csvData as $csvValue) {
            $csvValueProcessed = [];
            foreach ($csvValue as $key => $value) {
                if ($key == 0) {
                    $csvValueProcessed['entity_id'] = $value;
                }

                if ($key == 1) {
                    $csvValueProcessed['region_id'] = $value;
                }

                if ($key == 2) {
                    $csvValueProcessed['city'] = $value;
                }

                if ($key == 3) {
                    $csvValueProcessed['city_ar'] = $value;
                }
            }
            $csvDataProcessed[] = $csvValueProcessed;
        }
        return [$collection, $csvDataProcessed];
    }
}


