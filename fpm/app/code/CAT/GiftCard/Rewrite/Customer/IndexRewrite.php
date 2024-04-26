<?php

namespace CAT\GiftCard\Rewrite\Customer;

use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;
use Magento\Framework\App\Action\Context;
use Magento\GiftCardAccount\Controller\Customer\Index;
use Magento\GiftCardAccount\Model\Spi\GiftCardAccountManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Model\Giftcardaccount as GiftCardAccount;
use Magento\GiftCardAccount\Model\GiftcardaccountFactory as GiftCardAccountFactory;
use CAT\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Customer\Model\Session;

class IndexRewrite extends Index implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var CustomerBalanceHelper
     */
    private $customerBalanceHelper;

    /**
     * @var GiftCardAccountManagerInterface
     */
    private $manager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GiftCardAccountFactory
     */
    private $giftCardFactory;

    /**
     * @var GiftCardHelper
     */
    protected $giftCardHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * IndexRewrite constructor.
     * @param Context $context
     * @param CustomerBalanceHelper|null $customerBalanceHelper
     * @param GiftCardAccountManagerInterface|null $manager
     * @param StoreManagerInterface|null $storeManager
     * @param GiftCardAccountFactory|null $giftCardFactory
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        Context                          $context,
        ?CustomerBalanceHelper           $customerBalanceHelper = null,
        ?GiftCardAccountManagerInterface $manager = null,
        ?StoreManagerInterface           $storeManager = null,
        ?GiftCardAccountFactory          $giftCardFactory = null,
        GiftCardHelper                   $giftCardHelper,
        Session                          $customerSession
    )
    {
        parent::__construct($context, $customerBalanceHelper, $manager, $storeManager, $giftCardFactory);

        $this->customerBalanceHelper = $customerBalanceHelper
            ?? ObjectManager::getInstance()->get(CustomerBalanceHelper::class);
        $this->manager = $manager ?? ObjectManager::getInstance()->get(GiftCardAccountManagerInterface::class);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->giftCardFactory = $giftCardFactory ?? ObjectManager::getInstance()->get(GiftCardAccountFactory::class);
        $this->giftCardHelper = $giftCardHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            $customerId = $this->customerSession->getCustomer()->getId();
            if ($this->giftCardHelper->validateGiftCard($code, $customerId)) {
                //if($this->giftCardHelper->redeemGiftCard($code, $customerId) ) {
                $this->messageManager->addSuccess(
                    __(
                        'Gift Card "%1" was redeemed.',
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($code)
                    )
                );
                $this->_redirect('*/*/*');
                return;
                //}
            }
        }
        if (isset($data['giftcard_code']) && !empty($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                /** @var GiftCardAccount $card */
                $card = $this->giftCardFactory->create();
                $card->setCode($code);
                $card->loadByCode($code);
                $card->redeem();
                $this->messageManager->addSuccess(
                    __(
                        'Gift Card "%1" was redeemed.',
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($code)
                    )
                );
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('We cannot redeem this gift card.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Throwable $e) {
                $this->messageManager->addException($e, __('We cannot redeem this gift card.'));
            }
            $this->_redirect('*/*/*');
            return;
        }
        $this->_view->loadLayout();
        $this->_view->loadLayoutUpdates();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Gift Card'));
        $this->_view->renderLayout();
    }
}
