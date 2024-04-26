<?php

namespace CAT\ReferralProgram\Plugin\Checkout\Controller\Cart;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CouponPostPlugin
 * @package CAT\ReferralProgram\Plugin\Checkout\Controller\Cart
 */
class CouponPostPlugin
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Context $context
     */
    public function __construct(
        Session $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->_messageManager = $messageManager;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    public function aroundExecute(\Magento\Checkout\Controller\Cart\CouponPost $couponPost, \Closure $proceed) {
        $remove = $couponPost->getRequest()->getParam('remove');
        if ($remove != 1) {
            $couponCode = $couponPost->getRequest()->getParam('coupon_code');
            if (!empty($this->checkoutSession->getQuote()->getCustomer()->getId())) {
                $customer = $this->customerRepository->getById($this->checkoutSession->getQuote()->getCustomer()->getId());
                if (!empty($customer->getCustomAttribute('customer_referral_code'))) {
                    $referralCode = $customer->getCustomAttribute('customer_referral_code')->getValue();
                    if ($couponCode == $referralCode) {
                        $this->_messageManager->addErrorMessage('Referral code ' . $couponCode . ' belongs to you. You can\'t apply this coupon.');
                        return $this->resultRedirectFactory->create()
                            ->setPath('checkout/cart/index');
                    }
                }
            }
            return $proceed();
        }
        return $proceed();
    }
}
