<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace CAT\TamataCollect\Model\Carrier;

use Exception;
use Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\MatrixrateFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magedelight\Shippingmatrix\Model\ShippingMethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Backend\Model\Session\Quote as AdminQuote;
use CAT\TamataCollect\Helper\Data as TamataCollectData;

class Matrixrate extends \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate
{
        /**
     * @var ShippingMethodFactory
     */
    protected $_shippingMethodFactory;

    /**
     * @var string
     */
    protected $_code = 'rbmatrixrate';

    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var string
     */
    protected $_defaultConditionName = 'package_weight';

    /**
     * @var array
     */
    protected $_conditionNames = [];

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_resultMethodFactory;

    /**
     * @var MatrixrateFactory
     */
    protected $_matrixrateFactory;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var SerializerInterface
     */
    protected $jsonEncoder;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var AdminQuote
     */
    protected $adminQuote;

    /*
     * @var TamataCollectData
     */
    public $tamataCollectData;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $resultMethodFactory
     * @param MatrixrateFactory $matrixrateFactory
     * @param ShippingMethodFactory $shippingMethodFactory
     * @param Cart $cart
     * @param SerializerInterface $jsonEncoder
     * @param UserContextInterface $userContext
     * @param CartManagementInterface $cartManagement
     * @param State $state
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        ScopeConfigInterface        $scopeConfig,
        ErrorFactory                $rateErrorFactory,
        LoggerInterface             $logger,
        ResultFactory               $rateResultFactory,
        MethodFactory               $resultMethodFactory,
        MatrixrateFactory           $matrixrateFactory,
        ShippingMethodFactory       $shippingMethodFactory,
        Cart                        $cart,
        SerializerInterface         $jsonEncoder,
        UserContextInterface        $userContext,
        CartManagementInterface     $cartManagement,
        State                       $state,
        Session                     $customerSession,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AdminQuote                  $adminQuote,
        array                       $data = [],
        TamataCollectData           $tamatacollectdata
    )
    {
        
        parent::__construct($scopeConfig, $rateErrorFactory, $logger,$rateResultFactory,$resultMethodFactory,$matrixrateFactory,$shippingMethodFactory,$cart,$jsonEncoder,$userContext,$cartManagement,$state,$customerSession,$customerRepositoryInterface,$adminQuote,$data);
        $this->tamataCollectData = $tamatacollectdata;
        
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getRatesByConditionSubtotal($request, $result)
    {
        $foundRates = false;
        $zipRange = $this->getConfigData('zip_range');
        /* Vendor wise shipping rate calculation*/
        $vendorWiseItems = [];
        $grandTotalOfAllVendors = 0;
        foreach ($request->getAllItems() as $item) {
            if (!$item->getVendorId()) {
                continue;
            }
            $vendorWiseItems[$item->getVendorId()][$item->getId()] = $item;
            $grandTotalOfAllVendors += $item->getBaseRowTotal();
        }
        if (empty($vendorWiseItems)) {
            return false;
        }

        if ($this->state->getAreaCode() == 'webapi_rest') {
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                if (empty($customer->getGroupId())) {
                    return false;
                }
                $request->setCustomerGroupId($customer->getGroupId());
            }
        } elseif ($this->state->getAreaCode() == 'adminhtml') {
            $customerId = $this->adminQuote->getCustomerId();
            if ($customerId) {
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                $request->setCustomerGroupId($customer->getGroupId());
            } else {
                $request->setCustomerGroupId(0);
            }
        } else {
            if (!$this->customerSession->isLoggedIn()) {
                return false;
            }
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            if (empty($customerGroupId)) {
                return false;
            }
            $request->setCustomerGroupId($customerGroupId);
        }
        $totalValue = 0;
        $vendorValue = 0;
        $mainShippingRateArray = $groupShippingArray = [];
        foreach ($vendorWiseItems as $vendorId => $vItems) {
            $request->setVendorId($vendorId);
            foreach ($vItems as $vItem) {
                /*exclude Virtual products price from Package value if pre-configured*/
                if ((!$this->getConfigFlag('include_virtual_price') && $vItem->getProduct()->isVirtual()) || $vItem->getParentItem()) {
                    continue;
                }
                $vendorValue += $vItem->getBaseRowTotal();
                $itemVendorId [] = $vItem->getVendorId();
            }
            $totalValue += $vendorValue;
            $request->setData($request->getConditionMRName(), $vendorValue);
            $rateArray = $this->getRate($request, $zipRange);

            /* if vendor id not exist in rates, allow rates of admin (vendor_id =0) for the Subtotal range START*/
            $adminRateArrayForEligibleTotal = [];

            if (!in_array($vendorId, $rateArray) && $request->getConditionMRName() === "package_value") {

                foreach ($rateArray as $key => $value) {

                    if ($grandTotalOfAllVendors >= $value['condition_from_value'] && $grandTotalOfAllVendors <= $value['condition_to_value']) {
                        $adminRateArrayForEligibleTotal[$key] = $value;
                    }
                }
                $rateArray = $adminRateArrayForEligibleTotal;
            }
            /* if vendor id not exist in rates, allow rates of admin (vendor_id =0) for the Subtotal range END*/

            $rateShippingArray = [];
            foreach ($rateArray as $key => $value) {
                $innerQuoteArray['shipping_method'] = $value['shipping_method'];
                $innerQuoteArray['price'] = $value['price'];
                $innerQuoteArray['vendor_id'] = $value['vendor_id'];
                $rateShippingArray[] = $innerQuoteArray;

                if (!array_key_exists($value['shipping_method'], $groupShippingArray)) {
                    $groupShippingArray[$value['shipping_method']] = $value['price'];
                } else {
                    $oldShippingValue = $groupShippingArray[$value['shipping_method']];
                    $newShippingValue = $value['price'] + $oldShippingValue;
                    $groupShippingArray[$value['shipping_method']] = $newShippingValue;
                }
            }
            $mainShippingRateArray[] = [$vendorId => $rateShippingArray];
        }

        $quote = $this->_cart->getQuote();
        $quote->setData('vendor_shipping_data', $this->jsonEncoder->serialize($mainShippingRateArray));
        $quote->save();
        $request->setData($request->getConditionMRName(), $totalValue);
        $shippingArray = [];
        $maxShipPrice = [];
        foreach ($mainShippingRateArray as $shippingValue) {
            foreach ($shippingValue as $shipping) {
                foreach ($shipping as $ship) {

                    if (!array_key_exists($ship['shipping_method'], $shippingArray)) {
                        $shippingArray[$ship['shipping_method']] = $ship['price'];
                    } else {
                        $shippingArray[$ship['shipping_method']] += $ship['price'];
                    }
                }
            }
        }

        /* if more than one vendor are there get and set Highest shipping values */
        $adminMethodShipPrice = [];
        if (count($shippingArray) == 1) {
            foreach ($mainShippingRateArray as $shippingValue) {
                foreach ($shippingValue as $shipping) {
                    foreach ($shipping as $ship) {

                        if (!in_array($ship['vendor_id'], $itemVendorId)) {

                            $adminMethodShipPrice[] = array('id' => $ship['shipping_method'], 'price' => $ship['price'], 'vendor_id' => $ship['vendor_id']);
                        }
                        $maxShipPrice[] = array('id' => $ship['shipping_method'], 'price' => $ship['price'], 'vendor_id' => $ship['vendor_id']);
                    }
                }
            }

            if ($adminMethodShipPrice) {
                $maxShipPrice = $adminMethodShipPrice;
            }
            if ($maxShipPrice) {
                $maxShipPriceMethod = max(array_column($maxShipPrice, 'id'));
                $finalShipPriceArray = max(array_column($maxShipPrice, 'price'));

                $shippingArray[$maxShipPriceMethod] = $finalShipPriceArray;
            }
        }

        /* tamata Collect */
        $isTamataCollect = false;

        if($this->tamataCollectData->isEnabled()){
            $optionID = $this->tamataCollectData->getOptionId();
            $shipping = $quote->getShippingAddress();

            if($request->getCustomAttributes()){
                $isTamataCollect = false;
                $addressObject = $request->getCustomAttributes();
                foreach($addressObject as $attrbute){
                    if($attrbute->getAttributeCode() == 'addresstype' && $attrbute->getValue() == $optionID){
                        $isTamataCollect = true;
                    }
                }
            }
            if($shipping && $shipping->getCustomAttributes()){
                $isTamataCollect = false;
                $addressObject = $shipping->getCustomAttributes();
                foreach($addressObject as $attrbute){
                    if($attrbute->getAttributeCode() == 'addresstype' && $attrbute->getValue() == $optionID){
                        $isTamataCollect = true;
                    }
                }
            }
            if($request->getNcustomAttributes()){
                $isTamataCollect = false;
                $naddressObject = $request->getNcustomAttributes();
                foreach($naddressObject as $nattrbute){
                    if($nattrbute['attribute_code'] == 'addresstype' && $nattrbute['value'] == $optionID){
                        $isTamataCollect = true;
                    }
                }
            }
        }

        /* Tamata Collect */

        /* if more than one vendor are there get and set Highest shipping values */
        foreach ($shippingArray as $id => $value) {
            $method = $this->_resultMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $mcode = strtolower(str_replace(" ", "_", $id));
            $method->setMethod($this->_code . $mcode);
            if ((int)$value === 0 || $isTamataCollect) {
                $method->setMethodTitle(__("Free"));
                $shippingPrice = 0;
            } else {
                $shippingMethodColln = $this->getShippingMethodById($id);
                if ($shippingMethodColln->getId()) {
                    $method->setMethodTitle($shippingMethodColln->getShippingMethod());
                }
                // $shippingPrice = $this->getFinalPriceWithHandlingFee($value);
                $shippingPrice = $value;
            }
            $method->setPrice($shippingPrice);
            $method->setCost(0);
            $result->append($method);
            $foundRates = true;
        }
        if (!$foundRates) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ]
                ]
            );
            $result->append($error);
        }
        return $result;
    }

}
