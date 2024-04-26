<?php

namespace Ktpl\CityDropdown\Controller\Adminhtml\Index;

use Ktpl\CityDropdown\Helper\Data;
use Ktpl\CityDropdown\Model\CityRepository;
use Ktpl\CityDropdown\Model\CityFactory;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\Collection;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\CollectionFactory;
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
    const ADMIN_RESOURCE = 'Ktpl_CityDropdown::ktpl';

    protected $resultPageFactory;

    private $csvProccesor;

    private $moduleReader;

    private $directoryList;

    private $dataHelper;

    private $collectionFactory;

    private $cityFactory;

    private $resultRedirect;

    private $cityRepository;

    public function __construct(
        Context $context,
        Csv $csvProccesor,
        Reader $moduleReader,
        PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        CollectionFactory $collectionFactory,
        ResultFactory $resultRedirect,
        CityFactory $cityFactory,
        CityRepository $cityRepository,
        Data $dataHelper
    ) {
        $this->cityRepository = $cityRepository;
        $this->resultRedirect = $resultRedirect;
        $this->cityFactory = $cityFactory;
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

            foreach ($csvDataProcessed as $dataRow) {
                $countryId = $dataRow['country_id'];
                $cityName = $dataRow['city'];
                $entityId = $dataRow['entity_id'];

                $cityRepository = $this->cityFactory->create();
                if (isset($entityId) && is_numeric($entityId)) {
                    $cityRepository1 = $this->cityRepository->getById($entityId);
                    $cityRepository->setCityName($cityName);
                    $cityRepository->setCountryId($countryId);
                    $this->cityRepository->save($cityRepository);
                    continue;
                }
                $cityRepository->setCountryId($countryId);
                $cityRepository->setCityName($cityName);

                $collection->addItem($cityRepository);
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
                    $csvValueProcessed['country_id'] = $value;
                }
                if ($key == 2) {
                    $csvValueProcessed['city'] = $value;
                }
            }
            $csvDataProcessed[] = $csvValueProcessed;
        }
        return [$collection, $csvDataProcessed];
    }
}


