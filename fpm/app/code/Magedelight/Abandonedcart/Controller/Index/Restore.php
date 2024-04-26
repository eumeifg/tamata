<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Index;

class Restore extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;
    
    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->historyFactory = $historyFactory;
        $this->_customer = $customer;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->_customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $quote = $this->quoteFactory->create()->load($data['quote_id']);

        if (!empty($this->_customerSession->getCustomerId())) {
            $lastCustomerId = $this->_customerSession->getCustomerId();
        }
        
        $this->checkoutSession->setLoadInactive(false);
        if (!empty($lastCustomerId)) {
            $this->checkoutSession->setCustomerData(null)->clearQuote()->clearStorage();
            $this->_customerSession->logout()->setLastCustomerId($lastCustomerId);
        } else {
            $this->quoteRepository->save($quote);
            $this->checkoutSession->setQuoteId($data['quote_id']);
        }

        $websiteId = $this->helper->getWebsiteByStoreId($quote->getStoreId());

        $customer = $this->_customer->setWebsiteId($websiteId)->loadByEmail($data['email']);

        if (is_object($customer) && !empty($customer->getId())) {
            $this->_customerSession->setCustomerAsLoggedIn($customer);
        }
        
        try {
            $history = $this->historyFactory->create()->getCollection()
                ->addFieldToFilter('queue_id', $data['queue_id'])
                ->getFirstItem();
            $historyObj = $this->historyFactory->create()->load($history->getHistoryId());
            $historyObj->setIsRestored(1);
            $historyObj->save();
            $this->dataPersistor->clear('magedelight_abandonedcart_emailqueue');

            return $resultRedirect->setPath('checkout/cart');
        } catch (\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }
        
        return $resultRedirect->setPath('customer/account/login');
    }
}
