<?php

namespace CAT\Custom\Model\Entity;

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use CAT\Custom\Helper\Automation;
use CAT\Custom\Model\Source\Option;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Filesystem\DirectoryList;
use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class StoreCredit
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Automation
     */
    protected $automationHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var BalanceFactory
     */
    protected $_balanceFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var array
     */
    protected $reportData = [['email', 'comments']];

    /**
     * @param Csv $csv
     * @param DateTime $date
     * @param Filesystem $filesystem
     * @param Automation $automationHelper
     * @param BalanceFactory $balanceFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Csv $csv,
        DateTime $date,
        Filesystem $filesystem,
        Automation $automationHelper,
        BalanceFactory $balanceFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->csv = $csv;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->automationHelper = $automationHelper;
        $this->_balanceFactory = $balanceFactory;
        $this->_storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $logger
     */
    public function updateStoreCredit($logger) {
        $collection = $this->automationHelper->checkIfSheetAvailable(Option::STORE_CREDIT_KEYWORD);
        if ($collection) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/cat/'.Option::STORE_CREDIT_KEYWORD.'/').$collection->getFileName();
            $csvData = $this->csv->getData($filePath);

            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
            $logger->info(__("Website ID . ".$currentWebsiteId));
            $currentWebsiteId = !empty($currentWebsiteId) ? $currentWebsiteId : 1;

            foreach ($csvData as $row => $data) {
                if ($row >= 1) {
                    try {
                        $customer = $this->customerRepository->get($data[0],$currentWebsiteId);
                        $balance = $this->_balanceFactory->create();
                        $balance->setCustomer($customer)
                            ->setWebsiteId($currentWebsiteId)
                            ->setAmountDelta($data[1])
                            ->setHistoryAction(
                                \Magento\CustomerBalance\Model\Balance\History::ACTION_UPDATED
                            )
                            ->setUpdatedActionAdditionalInfo(__('Added By www.tamata.com'))
                            ->save();
                        $logger->info(__("Successfully added for email %1.", $data[0]));
                        $this->addToReport($data[0], 'Successfully added for email.');
                    } catch (NoSuchEntityException $e) {
                        $logger->info(__("The customer email isn't defined."));
                        $this->addToReport($data[0], 'The customer email isn\'t defined.');
                    }
                }
            }

            $reportUrl = '';
            if(!empty($this->reportData)) {
                $fileName = Option::STORE_CREDIT_KEYWORD.'_'.$collection->getImportId().'_'.time().'.csv';
                $reportUrl = $this->automationHelper->generateCsvFile($this->reportData, $fileName, Option::STORE_CREDIT_KEYWORD);
            }
            $processedTime = $this->_date->gmtDate('Y-m-d H:i:s');
            $this->automationHelper->updateStatus($collection, $reportUrl, $processedTime);
        }
    }

    /**
     * @param string $subOrderId
     * @param string $msg
     * @return void
     */
    public function addToReport($subOrderId, $msg = 'success')
    {
        $this->reportData[] = [
            'email_id' => $subOrderId,
            'comment' => $msg
        ];
    }
}