<?php
/**
 * Copyright © Magedelight, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\CustomMobileApi\Model;

use http\Exception\UnexpectedValueException;
use Magento\Quote\Api;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use MDC\CustomMobileApi\Api\StoreCreditManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Cart\TotalsConverter;


class StoreCreditManagement implements \MDC\CustomMobileApi\Api\StoreCreditManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ConfigurationPool
     */
    private $itemConverter;

    /**
     * @var TotalsConverter
     */
    protected $totalsConverter;

    /**
     * @var CouponManagementInterface
     */
    protected $couponService;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Api\Data\TotalsInterfaceFactory $totalsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param TotalsConverter $totalsConverter
     * @param ItemConverter $converter
     * @param CouponManagementInterface $couponService
     * @param CartRepositoryInterface $cartRepository
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param \Magento\Framework\Webapi\Rest\Request $webApiRequest
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Api\Data\TotalsInterfaceFactory $totalsFactory,
        DataObjectHelper $dataObjectHelper,
        TotalsConverter $totalsConverter,
        ItemConverter $converter,
        CouponManagementInterface $couponService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\App\RequestInterface $request,
        CartTotalRepositoryInterface $cartTotalRepository,
        \Magento\Framework\Webapi\Rest\Request $webApiRequest,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->totalsFactory = $totalsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->totalsConverter = $totalsConverter;
        $this->itemConverter = $converter;
        $this->couponService = $couponService;
        $this->cartRepository = $cartRepository;
        $this->balanceFactory = $balanceFactory;
        $this->json = $json;
        $this->queryFactory = $queryFactory;
        $this->request = $request;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->webApiRequest = $webApiRequest;
        $this->userContext = $userContext;
    }

    /**
     * @inheritdoc
     *
     * @param int $cartId The cart ID.
     * @return Totals Quote totals data.
     */
    public function addStoreCreditAmount($cartId)
    {

        $quote = $this->quoteRepository->getActive($cartId);

        // if ($this->webApiRequest->getBodyParams() ) {
        if ($this->webApiRequest->getParams() ) {

            $customerId = $this->userContext->getUserId();

            $balanceModel = $this->balanceFactory->create()->setCustomerId($customerId)->loadByCustomer();

            // $bodyParams = $this->webApiRequest->getBodyParams();
            $bodyParams = $this->webApiRequest->getParams();

            if (!empty($bodyParams['credit_amount']) && (float)$bodyParams['credit_amount'] > 0 ) {

                if(  (float) $bodyParams['credit_amount'] > (float) $balanceModel->getAmount()){

                    $result['status'] = false;
                    $result['message'] = __("Entered Store Credit must be less than or equal to your available limit.");

                    $response =  $this->json->serialize($result);
                    echo $response;
                    die();
                }


                $quote->setCustomerCustomBalanceAmountUsed($bodyParams['credit_amount']);
            }
        }

        $quote->setUseCustomerBalance(true);
        $quote->collectTotals();
        $quote->save();
        $quoteTotals = $this->getCartTotal($cartId);
        return $quoteTotals;
    }

    /**
     * @inheritdoc
     *
     * @param int $cartId The cart ID.
     * @return Totals Quote totals data.
     */
    public function removeStoreCreditAmount($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setCustomerCustomBalanceAmountUsed(0);
        $quote->setUseCustomerBalance(false)->collectTotals()->save();
        $quoteTotals = $this->getCartTotal($cartId);
        return $quoteTotals;
    }

    public function getCartTotal($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }
        unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        /** @var \Magento\Quote\Api\Data\TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            \Magento\Quote\Api\Data\TotalsInterface::class
        );
        $items = [];
        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->itemConverter->modelToDataObject($item);
        }
        $calculatedTotals = $this->totalsConverter->process($addressTotals);
        $quoteTotals->setTotalSegments($calculatedTotals);

        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItems($items);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());

        return $quoteTotals;
    }

    /**
     * Returns storecredit balance.
     *
     * @param int $customerId The Customer ID.
     * @param int $cartId The Cart ID.
     * @return string.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */

    public function displayStoreCreditAmount($customerId,$cartId)
    {
        if (!$customerId) {
            return 0;
        }
        $result = $totalSagement = [];
        $result['store_ credit'] = 0;
        $result['status'] = false;
        $result['message'] = "You don't have enough store credit";
        $result['credit_available'] = false;

        $balance = $this->checkCustomerBalanceUsed($customerId,$cartId);
        if($balance == 1) {
            $result['credit_available'] = true;
        }
        $model = $this->balanceFactory->create()
            ->setCustomerId($customerId)
            ->loadByCustomer();

        if($model->getAmount() > 0) {
            $result['store_ credit'] = $model->getAmount();
            $result['status'] = true;
            $result['message'] = "";
        }

        //$quote = $this->quoteRepository->getActive($cartId);
        $totals = $this->cartTotalRepository->get($cartId);

        foreach ($totals->getTotalSegments() as $key => $totalSegment) {
            $totalSegmentArray = $totalSegment->toArray();
            $totalSagement['total_segments'][] = $totalSegmentArray;
        }

        $a = array_merge($totalSagement, $result);
        $response =  $this->json->serialize($a);
        echo $response;
        die();
    }

    /**
     * Returns search Term.
     *
     * @param string $searchTerm .
     * @return data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function searchTermData(){

        $searchTerm = $this->request->getParam('search_term');
        $searchCollection = $this->queryFactory->create()
            ->getCollection()
            ->addFieldToFilter('query_text', $searchTerm);
        $result = [];
        $counter = 0;
        foreach($searchCollection->getData() as $key=>$item){
            if((is_null($item['redirect_id']) || is_null($item['redirection_type'])) ||
                ($item['redirect_id']== '0')
            )
            {
            }else{
                $result['terms'][$counter]['terms_id'] = $item['query_id'];
                $result['terms'][$counter]['term_name'] = $item['query_text'];
                $result['terms'][$counter]['num_results'] = $item['num_results'];
                $result['terms'][$counter]['type_id'] = $item['redirect_id'];
                $result['terms'][$counter]['type'] = $item['redirection_type'];
                $counter++;
            }

        }
        if(count($result) > 0)
        {
            $status['status'] = true;
        }else
        {
            $status['status'] = false;
        }
        $b = array_merge($status, $result);
        $response =  $this->json->serialize($b);
        echo $response;
        die();
    }

    public function checkCustomerBalanceUsed($customerId, $cartId)
    {
        $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        if ($cartId == $quote->getId()) {
            return $quote->getUseCustomerBalance();
        }
        /*$quote = $this->quoteRepository->getActive($cartId);
        $cusId = $quote->getCustomerId();
        if($cusId == $customerId) {
            $balance = (bool)$quote->getUseCustomerBalance();
            return $balance;
        }
        return false;*/
    }
}
