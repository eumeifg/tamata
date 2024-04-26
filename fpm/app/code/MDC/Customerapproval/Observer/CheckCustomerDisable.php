<?php

namespace MDC\Customerapproval\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckCustomerDisable implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;
    
    protected $redirect;
    
    /** @var ManagerInterface **/
    protected $messageManager;
    
    protected $request;
     protected $_responseFactory;
   protected $_url;

    public function __construct(
       Session $customerSession,
       CustomerRepositoryInterface $customerRepository,
       \Magento\Framework\App\Response\RedirectInterface $redirect,
       ManagerInterface $messageManager,
       \Magento\Framework\App\Request\Http $request,
       \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
    ){
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $customer = $this->customerRepository->getById($customerId);
            $isApprovedAccount = $customer->getCustomAttribute('approve_account')->getValue();
            if(!$isApprovedAccount)
            {
                
                // $message = "Your account is disabled. Kindly contact website admin for assitance.";
                // $this->messageManager->addError($message);
                if (in_array($this->request->getFullActionName(), ['customer_account_logout']))
                {
                    return $this;
                } 
                $this->customerSession->logout();
                $controller = $observer->getControllerAction();
                // $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
                // return $this;
          
               $this->messageManager->addErrorMessage(__('Your account is not approved. Kindly contact website admin for assitance.'));
               $cartUrl = $this->_url->getUrl('customer/account/login');
               $this->_responseFactory->create()->setRedirect($cartUrl)->sendResponse();  exit;
            }
           
        }
    }
}