<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Order\SplitOrder;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Process shipping for sub order data.
 * @author Rocket Bazaar Core Team
 * Created at 31 Dec , 2019 11:30:00 AM
 */
class ShippingProcessor extends \Magento\Framework\DataObject
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ShippingProcessor constructor.
     * @param Json $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $order
     * @param array $subOrderData
     * @param int $totalItems
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($order, $subOrderData, $totalItems)
    {
        /**
         * @todo set shipping information for vendor if dropshipping
         */
        $subOrderData->setShippingDiscountAmount(0);
        $subOrderData->setBaseShippingDiscountAmount(0);
        $subOrderData->setShippingMethod($order->getShippingMethod());
        $subOrderData->setShippingDescription($order->getShippingDescription());
        $vendorShippingData = ($order->getVendorShippingData()) ?
            $this->serializer->unserialize($order->getVendorShippingData()) : [];
        $subOrderData->setShippingAmount(0);
        $subOrderData->setBaseShippingAmount(0);
        $subOrderData->setShippingDiscountTaxCompensationAmount(0);
        $subOrderData->setBaseShippingDiscountTaxCompensationAmnt(0);
        $subOrderData->setShippingTaxAmount(0);
        $subOrderData->setBaseShippingTaxAmount(0);
        if ($order->getShippingAmount() > 0) {
            $orderShippingMethod = $order->getShippingMethod();
            if ((strpos($orderShippingMethod, 'rbmatrixrate_rbmatrixrate') !== false)
                || (strpos($orderShippingMethod, 'mdzonalshipping_mdzonalshipping') !== false)) {
                $subOrderData->setShippingAmount(0);
                $subOrderData->setBaseShippingAmount(0);

                if ((strpos($orderShippingMethod, 'rbmultipleshipping_rbmatrixrate_rbmatrixrate') !== false)){
                    $shippingMethods = explode(\Magedelight\Multipleshipping\Model\Shipping::METHOD_SEPARATOR, $order->getShippingMethod());
                    $vendorId = 0;

                    foreach ($shippingMethods as $method) {
                        $methodInfo = explode(\Magedelight\Multipleshipping\Model\Shipping::SEPARATOR, $method);
                        $vendorId = isset($methodInfo [1])?$methodInfo[1]:0;
                        $shippingMethodId = intval(preg_replace('/[^0-9]+/', '', $methodInfo[0]));
                        //if ($vendorId == $subOrderData->getVendorId()) {
                            foreach ($vendorShippingData as $shippingData) {
                                if (isset($shippingData[$subOrderData->getVendorId()])) {
                                    $vendorShippingRates = $shippingData[$subOrderData->getVendorId()];
                                    foreach ($vendorShippingRates as $vendorShippingRate) {
                                        $vendorShippingMethodId = intval($vendorShippingRate['shipping_method']);
                                        if ($shippingMethodId === $vendorShippingMethodId) {
                                            $shippingAmount = $vendorShippingRate['price'];
                                            $subOrderData->setShippingAmount($shippingAmount);
                                            $subOrderData->setBaseShippingAmount($shippingAmount);
                                        }
                                    }
                                }
                            }
                        //}
                    }
                } else {
                    $shippingMethodId = intval(preg_replace('/[^0-9]+/', '', $order->getShippingMethod()));
                    foreach ($vendorShippingData as $shippingData) {
                        if (isset($shippingData[$subOrderData->getVendorId()])) {
                            $vendorShippingRates = $shippingData[$subOrderData->getVendorId()];
                            foreach ($vendorShippingRates as $vendorShippingRate) {
                                $vendorShippingMethodId = intval($vendorShippingRate['shipping_method']);
                                if ($shippingMethodId === $vendorShippingMethodId) {
                                    $shippingAmount = $vendorShippingRate['price'];
                                    $subOrderData->setShippingAmount($shippingAmount);
                                    $subOrderData->setBaseShippingAmount($shippingAmount);
                                }
                            }
                        }
                    }
                }
            } elseif (strpos($orderShippingMethod, 'mdproductrate_mdproductrate') !== false) {
                $subOrderData->setShippingAmount(0);
                $subOrderData->setBaseShippingAmount(0);

                if (array_key_exists($subOrderData->getVendorId(), $vendorShippingData)) {
                    $vendorShippingAmount = $vendorShippingData[$subOrderData->getVendorId()];
                    $subOrderData->setShippingAmount($vendorShippingAmount);
                    $subOrderData->setBaseShippingAmount($vendorShippingAmount);
                }
            } else {
                if (!$this->isVendorLiableForShipping() &&
                    strpos($orderShippingMethod, 'flatrate_flatrate') !== false) {
                    /*shipping charges will be zero for vendor sub order if fulfillment done by admin*/
                    $subOrderData->setShippingAmount(0);
                    $subOrderData->setBaseShippingAmount(0);
                } else {
                    $subOrderData->setShippingAmount(
                        ($order->getShippingAmount() / $totalItems) * $subOrderData->getItems()
                    );
                    $subOrderData->setBaseShippingAmount(
                        ($order->getBaseShippingAmount() / $totalItems) * $subOrderData->getItems()
                    );

                    $subOrderData->setShippingDiscountTaxCompensationAmount(
                        ($order->getShippingDiscountTaxCompensationAmount() / $totalItems) * $subOrderData->getItems()
                    );
                    $subOrderData->setBaseShippingDiscountTaxCompensationAmnt(
                        ($order->getBaseShippingDiscountTaxCompensationAmnt()) ?
                        ($order->getBaseShippingDiscountTaxCompensationAmnt() / $totalItems) * $subOrderData->getItems()
                            : ($order->getShippingDiscountTaxCompensationAmount() / $totalItems) *
                            $subOrderData->getItems()
                    );

                    $subOrderData->setShippingTaxAmount(
                        ($subOrderData->getShippingAmount() * $order->getShippingTaxAmount()) / $order->getShippingAmount()
                    );
                    $subOrderData->setBaseShippingTaxAmount(
                        ($subOrderData->getBaseShippingAmount() * $order->getBaseShippingTaxAmount()) / $order->getBaseShippingAmount()
                    );
                }
            }
        } else {
            $subOrderData->setShippingTaxAmount(0);
            $subOrderData->setBaseShippingTaxAmount(0);
        }

        $subOrderData->setShippingInclTax(
            $subOrderData->getShippingAmount() + $subOrderData->getShippingTaxAmount() +
            $subOrderData->getShippingDiscountTaxCompensationAmount()
        );
        $subOrderData->setBaseShippingInclTax(
            $subOrderData->getBaseShippingAmount() + $subOrderData->getBaseShippingTaxAmount() +
            $subOrderData->getBaseShippingDiscountTaxCompensationAmount()
        );

        if (array_key_exists('shipping_amount', $subOrderData)) {
            /* Shipping Tax Compensation */
            $subOrderData->setShippingDiscountTaxCompensationAmount(
                $subOrderData->getShippingInclTax() - $subOrderData->getShippingAmount() -
                $subOrderData->getShippingTaxAmount()
            );
            $subOrderData->setBaseShippingDiscountTaxCompensationAmnt(
                $subOrderData->getBaseShippingInclTax() - $subOrderData->getBaseShippingAmount() -
                $subOrderData->getBaseShippingTaxAmount()
            );

            $subOrderData->setTaxAmount(
                $subOrderData->getTaxAmount() + $subOrderData->getShippingDiscountTaxCompensationAmount() +
                $subOrderData->getShippingTaxAmount()
            );
            $subOrderData->setBaseTaxAmount(
                $subOrderData->getBaseTaxAmount() + $subOrderData->getBaseShippingDiscountTaxCompensationAmount() +
                $subOrderData->getBaseShippingTaxAmount()
            );
        }

        if (empty($vendorShippingData)) {
            $subOrderData->setShippingDiscountAmount(
                ($order->getShippingDiscountAmount() / $totalItems) * $subOrderData->getItems()
            );
            $subOrderData->setBaseShippingDiscountAmount(
                ($order->getBaseShippingDiscountAmount() / $totalItems) * $subOrderData->getItems()
            );
        }

        $subOrderData->setGrandTotal($subOrderData->getGrandTotal() + $subOrderData->getShippingAmount());
        $subOrderData->setBaseGrandTotal($subOrderData->getBaseGrandTotal() + $subOrderData->getBaseShippingAmount());
        return $subOrderData;
    }

    /**
     * @param integer $websiteId
     * @return boolean
     */
    public function isVendorLiableForShipping($websiteId = 1)
    {
        $shippingLiableActor = (int)$this->scopeConfig->getValue(
            \Magedelight\Commissions\Model\Commission\Quote::CONFIG_PATH_PO_SHIPPING_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return ($shippingLiableActor === \Magedelight\Commissions\Model\Commission\Quote::ACTOR_VENDOR);
    }
}
