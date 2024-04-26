<?php

namespace Ktpl\ReorderItem\Controller\Order;

class Reorder extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->_pageFactory = $pageFactory;
        $this->orderItemRepository = $orderItemRepository;
        return parent::__construct($context);
    }

    public function execute()
    {
        $data       = $this->_request->getParams();
        $itemId     = $data['item_id'];

        $resultRedirect = $this->resultRedirectFactory->create();

        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $item = $this->orderItemRepository->get($itemId);
        $cart->getCheckoutSession()->setProductVendorId($item->getVendorId());

        try {
                $cart->addOrderItem($item); /* Adding Item to the cart */
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/history');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            return $resultRedirect->setPath('checkout/cart');
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
