<?php

namespace CAT\ReferralProgram\Cron;

use CAT\ReferralProgram\Helper\Data as ReferralHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class UpdateReferralCode {

    const PAGE_SIZE_PATH = 'cat_customer_referral/general/page_size';
    const CURRENT_PAGE_PATH = 'cat_customer_referral/general/current_page';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ReferralHelper
     */
    protected $referralHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ReferralHelper $referralHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        ReferralHelper $referralHelper,
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->referralHelper = $referralHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute() {
        $pageSize = $this->scopeConfig->getValue(
            self::PAGE_SIZE_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
        $currentPage = $this->scopeConfig->getValue(
            self::CURRENT_PAGE_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
        $pageSize = !empty($pageSize) ? $pageSize : 10;
        $currentPage = !empty($currentPage) ? $currentPage : 1;
        $customerCollection = $this->customerFactory->create()->getCollection();
        $customerCollection->addAttributeToSelect(['customer_referral_code']);
        $customerCollection->addAttributeToFilter('customer_referral_code', ['null' => true]);
        $customerCollection->addAttributeToFilter('firstname', ['neq' => 'NULL']);
        $customerCollection->addAttributeToFilter('lastname', ['neq' => 'NULL']);
        /*$customerCollection->addAttributeToFilter('default_billing', ['neq' => 'NULL']);*/
        $customerCollection->setOrder('entity_id', 'DESC')->setPageSize($pageSize)->setCurPage($currentPage)->load();

        if ($customerCollection->getSize()) {
            foreach ($customerCollection as $customer) {
                try {
                    $_customer = $this->customerRepository->getById($customer->getEntityId());
                    $couponCodeArray = $this->referralHelper->generateCouponCode();
                    if (!empty($couponCodeArray)) {
                        $_customer->setCustomAttribute('customer_referral_code', $couponCodeArray[0]);
                        $this->customerRepository->save($_customer);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($customer->getId()."=====>".$e->getMessage());
                }
            }
        }
    }
}
