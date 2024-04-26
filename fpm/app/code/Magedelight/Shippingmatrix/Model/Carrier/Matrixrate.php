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

namespace Magedelight\Shippingmatrix\Model\Carrier;

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

class Matrixrate extends AbstractCarrier implements CarrierInterface
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
        array                       $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->_matrixrateFactory = $matrixrateFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        foreach ($this->getCode('condition_name') as $k => $v) {
            $this->_conditionNames[] = $k;
        }
        $this->_shippingMethodFactory = $shippingMethodFactory;
        $this->_cart = $cart;
        $this->jsonEncoder = $jsonEncoder;
        $this->userContext = $userContext;
        $this->cartManagement = $cartManagement;
        $this->state = $state;
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->adminQuote = $adminQuote;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $conditionName = $this->getConfigData('condition_name');
        if (!$request->getConditionMRName()) {
            $request->setConditionMRName($conditionName ? $conditionName : $this->_defaultConditionName);
        }
        if ($conditionName === 'package_weight') {
            $result = $this->getRatesByConditionWeight($request, $result);
            return $result;
        } elseif ($conditionName === 'package_value') {
            $result = $this->getRatesByConditionSubtotal($request, $result);
            return $result;
        } elseif ($conditionName === 'package_qty') {
            $result = $this->getRatesByConditionQty($request, $result);
            return $result;
        } else {
            return $result;
        }
        /*$method = $this->_resultMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle('Testing Carrier Title');
        $method->setMethodTitle('Testing Method');
        $method->setPrice(10000);
        $method->setCost(0);
        $method->setVendorId(48);
        $result->append($method);*/
    }

    protected function getRatesByConditionWeight($request, $result)
    {
        return $result;
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
        /* if more than one vendor are there get and set Highest shipping values */

        foreach ($shippingArray as $id => $value) {
            $method = $this->_resultMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $mcode = strtolower(str_replace(" ", "_", $id));
            $method->setMethod($this->_code . $mcode);
            if ((int)$value === 0) {
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

    protected function getRatesByConditionQty($request, $result)
    {
        /* Vendor wise shipping rate calculation*/
        $vendorWiseItems = [];
        foreach ($request->getAllItems() as $item) {
            if (!$item->getVendorId()) {
                continue;
            }
            $vendorWiseItems[$item->getVendorId()][$item->getId()] = $item;
        }
        if (empty($vendorWiseItems)) {
            return false;
        }

        /*Package weight and qty free shipping*/
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();
        $zipRange = $this->getConfigData('zip_range');
        $foundRates = false;
        $vRate = ['price' => 0, 'cost' => 0];

        $rateArrayGroupByShippingMethod = [];
        $groupShippingArray = [];

        $mainShippingRateArray = [];
        foreach ($vendorWiseItems as $vendorId => $vItems) {
            $packageValue = 0;
            $packageQty = 0;
            $packageWeight = 0;

            foreach ($vItems as $vItem) {
                /*exclude Virtual products price from Package value if pre-configured*/
                if ((!$this->getConfigFlag('include_virtual_price') && $vItem->getProduct()->isVirtual()) || $vItem->getParentItem()) {
                    continue;
                }
                if ($vItem->getHasChildren() && $vItem->isShipSeparately()) {
                    foreach ($vItem->getChildren() as $child) {
                        /*Free shipping by qty*/
                        $freeQty = 0;
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $vItem->getQty() * ($child->getQty() - $freeShipping);
                        }
                        $qty = ($vItem->getQty() * $child->getQty()) - $freeQty;
                        $packageQty += $qty;
                        $packageValue = $child->getBaseRowTotal();
                        /*$packageWeight += $vItem->getProduct()->getWeight() * $qty;*/
                        $packageWeight = $vItem->getProduct()->getWeight();
                    }
                } else {
                    /*Free shipping by qty*/
                    $freeQty = 0;
                    if ($vItem->getFreeShipping()) {
                        $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                        $freeQty += $vItem->getQty() - $freeShipping;
                    } else {
                        $packageValue += $vItem->getBaseRowTotal();
                    }
                    $qty = $vItem->getQty() - $freeQty;
                    $packageQty += $qty;
                    /*$packageWeight += $vItem->getProduct()->getWeight() * $qty;*/
                    $packageWeight = $vItem->getProduct()->getWeight();
                }
                $request->setData('vendor_id', $vendorId);
                $request->setPackageWeight($packageWeight);
                $request->setPackageQty($packageQty);
                $request->setPackageValue($packageValue);

                $rateArray = $this->getRate($request, $zipRange);
                /*$logger->info('rateArray'.json_encode($rateArray));*/
                $rateShippingArray = [];
                foreach ($rateArray as $key => $value) {
                    $innerQuoteArray['shipping_method'] = $value['shipping_method'];
                    $innerQuoteArray['price'] = $value['price'];
                    $rateShippingArray[] = $innerQuoteArray;

                    if (!array_key_exists($value['shipping_method'], $groupShippingArray)) {
                        $groupShippingArray[$value['shipping_method']] = $value['price'];
                        $rateArrayGroupByShippingMethod[] = $value;
                    } else {
                        $oldShippingValue = $groupShippingArray[$value['shipping_method']];
                        $newShippingValue = $value['price'] + $oldShippingValue;
                        $groupShippingArray[$value['shipping_method']] = $newShippingValue;
                    }
                }
                $mainShippingRateArray[] = [$vendorId => $rateShippingArray];
            }
            /*$quoteShippingArray[] = $rateShippingArray;*/
        }
        $quote = $this->_cart->getQuote();
        $quote->setData('vendor_shipping_data', $this->jsonEncoder->serialize($mainShippingRateArray));
        $quote->save();

        foreach ($rateArrayGroupByShippingMethod as $rate) {
            $shippingMethodId = $rate['shipping_method'];
            $rate['price'] = $groupShippingArray[$shippingMethodId];
            $vRate['price'] = 0;
            if (!empty($rate) && $rate['price'] >= 0) {
                $vRate['price'] += $rate['price'];
                $vRate['cost'] += $rate['cost'];

                /** Shipping method Grouping*/
                if (is_numeric($this->getConfigData('free_shipping_threshold')) &&
                    $this->getConfigData('free_shipping_threshold') > 0 &&
                    $request->getPackageValue() > $this->getConfigData('free_shipping_threshold')) {
                    $vRate['price'] = 0;
                }
                if ($this->getConfigData('allow_free_shipping_promotions') &&
                    ($request->getFreeShipping() === true ||
                        $request->getPackageQty() == $this->getFreeBoxes())) {
                    $vRate['price'] = 0;
                }

                $method = $this->_resultMethodFactory->create();

                $method->setCarrier($this->_code);
                $mcode = strtolower(str_replace(" ", "_", $rate['shipping_method']));
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($this->_code . $mcode);
                if ($vRate['price'] == 0) {
                    $method->setMethodTitle(__("Free"));
                    $shippingPrice = 0;
                } else {
                    $shippingMethod = $rate['shipping_method'];
                    if ($shippingMethod) {
                        $shippingMethodColln = $this->getShippingMethodById($shippingMethod);
                        if ($shippingMethodColln->getId()) {
                            $method->setMethodTitle($shippingMethodColln->getShippingMethod());
                        }
                    }
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($vRate['price']);
                }
                $method->setPrice($shippingPrice);
                $method->setCost($vRate['cost']);
                $method->setVendorId($vendorId);
                $result->append($method);
                $foundRates = true;
            } elseif (!$this->getConfigFlag('allow_anyhow')) {
                $foundRates = false;
            }
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
        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param bool $zipRange
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRange)
    {
        return $this->_matrixrateFactory->create()->getRate($request, $zipRange);
    }

    /**
     * @param string $type
     * @param string $code
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'condition_name' => [
                'package_weight' => __('Weight vs. Destination'),
                'package_value' => __('Order Subtotal vs. Destination'),
                'package_qty' => __('# of Items vs. Destination'),
            ],
            'condition_name_short' => [
                'package_weight' => __('Weight'),
                'package_value' => __('Order Subtotal'),
                'package_qty' => __('# of Items'),
            ],
        ];

        if (!isset($codes[$type])) {
            throw new LocalizedException(__('Please correct Matrix Rate code type: %1.', $type));
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(__('Please correct Matrix Rate code for type %1: %2.', $type, $code));
        }

        return $codes[$type][$code];
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    protected function getShippingMethodById($id)
    {
        $collection = $this->_shippingMethodFactory->create()->getCollection()->addFieldToFilter('shipping_method_id', $id)->getFirstItem();
        return $collection;
    }
}
