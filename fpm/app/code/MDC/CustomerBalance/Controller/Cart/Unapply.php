<?php 

namespace MDC\CustomerBalance\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * 
 */
class Unapply extends \Magento\CustomerBalance\Controller\Cart\Unapply
{
	
	/**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param Context $context
     * @param CartRepositoryInterface $cartRepository
     * @param UserContextInterface $userContext
     */
    public function __construct(
        Context $context,
        CartRepositoryInterface $cartRepository,
        UserContextInterface $userContext
    ) {
    	parent::__construct($context,$cartRepository,$userContext);
    	
        $this->cartRepository = $cartRepository;
        $this->userContext = $userContext;
    }

    /**
     * Remove Store Credit from current quote.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('customer/account/');
        }

        $cartId = $this->getRequest()->getParam('cartId');

        /** @var CartInterface $quote */
        $quote = $this->cartRepository->get($cartId);
        if ($this->userContext->getUserId() !== $quote->getCustomer()->getId()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $result->forward('noroute');
        }

        $this->unapply($quote);

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(true);
        return $result;
    }

	/**
     * Unapply store credit.
     *
     * @param CartInterface $quote
     * @return void
     */

	private function unapply(CartInterface $quote): void
    {
        $quote->setUseCustomerBalance(false);
        $quote->setCustomerCustomBalanceAmountUsed(0);
        $quote->collectTotals();
        $quote->save();
    }

}