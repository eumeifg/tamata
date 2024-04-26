<?php 

namespace MDC\CustomerBalance\Model;

class BalanceManagement extends \Magento\CustomerBalance\Model\BalanceManagement
{
	
	 /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Framework\Registry $registry 
    ) {
    	
    	parent::__construct($cartRepository);

        $this->cartRepository = $cartRepository;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($cartId)
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */

        $quote = $this->cartRepository->get($cartId);
        
        if ($this->request->getBodyParams() ) {
        	$params = $this->request->getBodyParams();
        	
        	if (!empty($params['value']) && (float)$params['value'] > 0 ) {
        		$quote->setCustomerCustomBalanceAmountUsed($params['value']);
        	}   	
        }

        
        $quote->setUseCustomerBalance(true);
        $quote->collectTotals();
        $quote->save();
        return true;
    }

}