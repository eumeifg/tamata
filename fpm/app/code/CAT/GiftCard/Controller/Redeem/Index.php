<?php

namespace CAT\GiftCard\Controller\Redeem;

use CAT\GiftCard\Helper\Data as GiftCardHelper;
use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Throwable;

class Index extends Action
{
    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param Context $context
     * @param GiftCardHelper $giftCardHelper
     * @param Session $customerSession
     * @param UrlInterface $urlInterface
     */
    public function __construct(Context $context, GiftCardHelper $giftCardHelper, Session $customerSession, UrlInterface $urlInterface)
    {
        $this->giftCardHelper = $giftCardHelper;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                $customerId = $this->customerSession->getCustomer()->getId();
                $response = $this->giftCardHelper->validateGiftCardV2($code, $customerId);
                if ($response) {
                    $this->messageManager->addSuccessMessage(__(
                        'Gift Card "%1" was redeemed.',
                        $this->_objectManager->get(Escaper::class)->escapeHtml($code)
                    ));
                } else {
                    $this->messageManager->addErrorMessage(__('We cannot redeem this gift card.'));
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e, __('We cannot redeem this gift card.'));
            } catch (Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('We cannot redeem this gift card.'));
            }
        }
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
