<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Commission;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Quote extends \Magento\Framework\DataObject
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    private $catalogHelper;

    const VENDOR_COMMISSION_TABLE = "md_vendor_commissions";
    const CATEGORY_COMMISSION_TABLE = "md_commissions";
    const SEPARATOR = '~';
    const METHOD_SEPARATOR = ':';
    const ACTOR_ADMIN = 1;
    const ACTOR_VENDOR = 2;
    const COMMISSION_LEVEL_PRODUCT = 1;
    const COMMISSION_LEVEL_CATEGORY = 2;
    const COMMISSION_LEVEL_VENDOR = 3;
    const COMMISSION_LEVEL_GLOBAL = 0;
    const CALCULATION_TYPE_FLAT = 1;
    const CALCULATION_TYPE_PERCENTAGE = 2;
    const CONFIG_PATH_COMM_CALC_TYPE = "commission/general/commission_calc_type";
    const CONFIG_PATH_COMM_RATE = "commission/general/commission_value";
    const CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE = "commission/general/marketplace_fee_calc_type";
    const CONFIG_PATH_COMM_MARKETPLACE_FEE = "commission/general/marketplace_fee";
    const CONFIG_PATH_SERVICE_TAX_RATE = "commission/general/service_tax";
    const CONFIG_PATH_PO_SHIPPING_LIABILITY = "commission/payout/shipping_liability";
    const CONFIG_PATH_PO_ADJUSTMENT_LIABILITY = "commission/payout/adjustment_liability";
    const CONFIG_PATH_PO_TAX_LIABILITY = "commission/payout/tax_liability";
    const CONFIG_PATH_PO_COMM_LEVEL_PRECEDENCE = "commission/payout_commission";
    const CONFIG_PATH_PO_CANCELLATION_FEE_CALC_TYPE = "commission/payout/cancellation_fee_calc_type";
    const CONFIG_PATH_PO_CANCELLATION_FEE = "commission/payout/cancellation_fee";
    const CONFIG_PATH_ADJUST_DISCOUNT = "commission/payout/adjust_discount";

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     *
     * @var array
     */
    protected $categoryCommissionRates = [];

    /**
     *
     * @var array
     */
    protected $vendorCommissionRates = [];

    /**
     * @var array
     */
    protected $precedences = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     * @var array
     */
    private $products = [];

    /**
     *
     * @var int
     */
    protected $websiteId;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->vendorRepository = $vendorRepository;
        $this->_connection = $resource->getConnection();
        $this->quoteRepository = $quoteRepository;
        $this->directoryHelper = $directoryHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->catalogHelper = $catalogHelper;
        parent::__construct($data);
    }

    /*
     * Payout calculations
     */

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        return $this->_connection;
    }

    /**
     *
     * @param int $categoryId
     * @return array
     */
    protected function _getCategoryCommissionRateByCatId($categoryId)
    {
        if (!$categoryId) {
            return [];
        }
        if (!array_key_exists($categoryId, $this->categoryCommissionRates)) {
            $connection = $this->_getConnection();
            $select = $connection->select()
                ->from(self::CATEGORY_COMMISSION_TABLE, ['calculation_type','commission_value', 'marketplace_fee',
                    'marketplace_fee_type', 'cancellation_fee_commission_value', 'cancellation_fee_calculation_type'])
                ->where('status = ?', 1)
                ->where('product_category = ?', $categoryId)
                ->where('website_id = ?', $this->websiteId);
            $this->categoryCommissionRates[$categoryId] = $connection->fetchRow($select);
        }
        return $this->categoryCommissionRates[$categoryId];
    }

    /**
     *
     * @param int $vendorId
     * @return array
     */
    protected function _getVendorCommissionRateByVendorId($vendorId)
    {
        if (!$vendorId) {
            return [];
        }
        if (!array_key_exists($vendorId, $this->vendorCommissionRates)) {
            $connection = $this->_getConnection();
            $select = $connection->select()
                ->from(self::VENDOR_COMMISSION_TABLE, ['vendor_calculation_type', 'vendor_commission_value',
                    'vendor_marketplace_fee_type', 'vendor_marketplace_fee', 'vendor_cancellation_fee_type',
                    'vendor_cancellation_fee'])
                ->where('vendor_status = ?', 1)
                ->where('vendor_id = ?', $vendorId)
                ->where('website_id = ?', $this->websiteId);
            $this->vendorCommissionRates[$vendorId] = $connection->fetchRow($select);
        }
        return $this->vendorCommissionRates[$vendorId];
    }

    public function getGlobalCommRate()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_RATE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getGlobalCommCalcType()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getGlobalMarketRate()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getGlobalMarketCalcType()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getGlobalCancelRate()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CANCELLATION_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getGlobalCancelCalcType()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CANCELLATION_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    /**
     *
     * @param float $total
     * @param int $type
     * @param float $rate
     * @param int $qty (optional)
     * @return float
     */
    public function calculateRate($total, $type, $rate, $qty = 1)
    {
        /* Take quantity as 1 if null passed.*/
        if ($qty === null) {
            $qty = 1;
        }
        /* $this->logger->debug("before calculate ", [$total, $type, $rate, $qty]); */
        switch ($type) {
            case self::CALCULATION_TYPE_FLAT:
                /* $this->logger->debug("after calculate Flat". $rate * $qty); */
                return $rate * $qty;
            case self::CALCULATION_TYPE_PERCENTAGE:
                /* $this->logger->debug("after calculate ". ($total * $rate) / 100); */
                return ($total * $rate) / 100;
            default:
                return null;
        }
    }

    public function canAdjustDiscount()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ADJUST_DISCOUNT,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    public function getItemRowTotalWithAdjustedDiscount(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        if ($this->canAdjustDiscount()) {
            return $item->getRowTotal();
        } else {
            return ($item->getRowTotal() - $item->getDiscountAmount());
        }
    }

    /**
     * Commission Calculations based on product rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnProductRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $prod = $this->_getItemProduct($product->getId());
        $commissionRate = $prod->getData('md_commission');
        if ($commissionRate) {
            $commissionType = $prod->getData('md_calculation_type');
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            return $this->calculateRate($total, $commissionType, $commissionRate, $item->getQty());
        }
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    protected function _getMarketPlaceBasedOnProductRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $marketplaceRate = $product->getData('md_marketplace_fee');
        if ($marketplaceRate) {
            $marketplaceType = $product->getData('md_marketplace_fee_calc_type');
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()-$item->getQtyRefunded()) :
                $item->getQtyOrdered();

            return $this->calculateRate($total, $marketplaceType, $marketplaceRate, $qty);
        }
    }

    /**
     *
     * @param type $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getItemProduct($productId)
    {
        if (!array_key_exists($productId, $this->products)) {
            $this->products[$productId] = $this->productRepository->getById($productId);
        }
        return $this->products[$productId];
    }

    /**
     * Commission Calculations based on category rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnCategoryRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $prod = $this->_getItemProduct($product->getId());
        unset($product);
        $categoryIds = $prod->getCategoryIds();
        if (!empty($categoryIds)) {
            $commRates = $this->_getCategoryCommissionRateByCatId($categoryIds[0]);
            if (!empty($commRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                return $this->calculateRate(
                    $total,
                    $commRates['calculation_type'],
                    $commRates['commission_value'],
                    $item->getQty()
                );
            }
        }
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    protected function _getMarketPlaceBasedOnCategoryRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $catmarketRates = $this->_getCategoryCommissionRateByCatId($categoryIds[0]);
            if (!empty($catmarketRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                        : $item->getQtyOrdered();

                return $this->calculateRate(
                    $total,
                    $catmarketRates['marketplace_fee_type'],
                    $catmarketRates['marketplace_fee'],
                    $qty
                );
            }
        }
    }

    protected function _getCancellationBasedOnCategoryRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $catcancellationRates = $this->_getCategoryCommissionRateByCatId($categoryIds[0]);
            if (!empty($catcancellationRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                        : $item->getQtyOrdered();

                return $this->calculateRate(
                    $total,
                    $catcancellationRates['cancellation_fee_calculation_type'],
                    $catcancellationRates['cancellation_fee_commission_value'],
                    $qty
                );
            }
        }
    }

    /**
     * Commission Calculations based on vendor rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnVendorRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $vendorId = $item->getData('vendor_id');
        $commRates = $this->_getVendorCommissionRateByVendorId($vendorId);
        if (!empty($commRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            return $this->calculateRate(
                $total,
                $commRates['vendor_calculation_type'],
                $commRates['vendor_commission_value'],
                $item->getQty()
            );
        }
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return type
     */
    protected function _getMarketPlaceBasedOnVendorRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $vendorId          = $item->getData('vendor_id');
        $marketVendorRates = $this->_getVendorCommissionRateByVendorId($vendorId);
        if (!empty($marketVendorRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

            return $this->calculateRate(
                $total,
                $marketVendorRates['vendor_marketplace_fee_type'],
                $marketVendorRates['vendor_marketplace_fee'],
                $qty
            );
        }
    }

    protected function _getCancellationBasedOnVendorRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $vendorId                = $item->getData('vendor_id');
        $cancellationVendorRates = $this->_getVendorCommissionRateByVendorId($vendorId);
        if (!empty($cancellationVendorRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

            return $this->calculateRate(
                $total,
                $cancellationVendorRates['vendor_cancellation_fee_type'],
                $cancellationVendorRates['vendor_cancellation_fee'],
                $qty
            );
        }
    }

    /**
     * Commission Calculations based on global rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnGlobalRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $globalCommCalcType = $this->getGlobalCommCalcType();
        $globalCommRate = $this->getGlobalCommRate();
        $total = $this->getItemRowTotalWithAdjustedDiscount($item);
        return $this->calculateRate($total, $globalCommCalcType, $globalCommRate, $item->getQty());
    }

    protected function _getMarketPlaceBasedOnGlobalRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $globalMarketCalcType = $this->getGlobalMarketCalcType();
        $globalMarketRate     = $this->getGlobalMarketRate();
        $total                = $this->getItemRowTotalWithAdjustedDiscount($item);
        $qty                  = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()
            - $item->getQtyRefunded()) : $item->getQtyOrdered();
        return $this->calculateRate(
            $total,
            $globalMarketCalcType,
            $globalMarketRate,
            $qty
        );
    }

    protected function _getCancellationBasedOnGlobalRate(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $globalCancelCalcType = $this->getGlobalCancelCalcType();
        $globalCancelRate = $this->getGlobalCancelRate();
        $total = $this->getItemRowTotalWithAdjustedDiscount($item);
        $qty = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()-$item->getQtyRefunded()) : $item->getQtyOrdered();

        return $this->calculateRate($total, $globalCancelCalcType, $globalCancelRate, $qty);
    }

    /**
     * get commission rate based on precedence level
     * @param int $precedence
     * @return float|null
     */
    public function getCommissionRateByPrecedence($precedence, $vendorId = null, $catId = null)
    {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_CATEGORY:
                $commRates = $this->_getCategoryCommissionRateByCatId($catId);
                if (!empty($commRates)) {
                    return ['rate' => $commRates['commission_value'], 'calc_type' => $commRates['calculation_type']];
                }
                return [];
            case self::COMMISSION_LEVEL_VENDOR:
                $commRates = $this->_getVendorCommissionRateByVendorId($vendorId);
                if (!empty($commRates)) {
                    return [
                        'rate' => $commRates['vendor_commission_value'],
                        'calc_type' => $commRates['vendor_calculation_type']
                    ];
                }
                return [];
            default:
                return ['rate' => $this->getGlobalCommRate(), 'calc_type' => $this->getGlobalCommCalcType()];
        }
    }

    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    public function getCommissionAmountByPrecedence(
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $websiteId
    ) {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getCommissionBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getCommissionBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getCommissionBasedOnVendorRate($item);
            default:
                return $this->_getCommissionBasedOnGlobalRate($item);
        }
    }

    /**
     * calculate markeetplace fee based on precedence level
     * @param int $precedence
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    public function getMarketPlaceAmountByPrecedence(
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $websiteId
    ) {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getMarketPlaceBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getMarketPlaceBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getMarketPlaceBasedOnVendorRate($item);
            default:
                return $this->_getMarketPlaceBasedOnGlobalRate($item);
        }
    }

    public function getCancellationAmountByPrecedence(
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item
    ) {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getCancellationBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getCancellationBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getCancellationBasedOnVendorRate($item);
            default:
                return $this->_getCancellationBasedOnGlobalRate($item);
        }
    }

    /**
     * get commission level precedence from configurations.
     * @return array
     */
    public function getCommissionLevelPrecedences()
    {
        if (empty($this->precedences)) {
            $precedenceConfig = $this->scopeConfig->getValue(
                self::CONFIG_PATH_PO_COMM_LEVEL_PRECEDENCE,
                ScopeInterface::SCOPE_WEBSITE,
                $this->websiteId
            );
            foreach ($precedenceConfig as $precedenceValue) {
                $this->precedences[] = $precedenceValue;
            }
            $this->precedences[] = self::COMMISSION_LEVEL_GLOBAL;
        }
        return $this->precedences;
    }

    /**
     *
     * @param array $vendorItems
     * @return array
     */
    public function calculateCommissionAmount($vendorItems, $websiteId)
    {
        $precedences = $this->getCommissionLevelPrecedences();
        $commissionData = [];
        $commissionData['commission'] = 0;
        $commissionData['marketplace_fee'] = 0;
        $vendorOrderCommission = 0;
        $vendorOrderMarketPlaceFee = 0;
        foreach ($vendorItems as $item) {
            foreach ($precedences as $precedence) {
                $itemCommission = $this->getCommissionAmountByPrecedence($precedence, $item, $websiteId);

                if ($itemCommission !== '' && $itemCommission !== null) {
                    $vendorOrderCommission += $itemCommission;
                    $itemMarketPlaceFee = $this->getMarketPlaceAmountByPrecedence($precedence, $item, $websiteId);
                    $vendorOrderMarketPlaceFee += $itemMarketPlaceFee;
                    /**
                     * @todo create seperate table to define commission level
                     * fields will be as following
                     * item_id
                     * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                     * commission_amount
                     */
                    unset($itemCommission);
                    unset($itemMarketPlaceFee);
                    break;
                }
            }
        }
        $commissionData['commission'] = $this->catalogHelper->currency(
            $vendorOrderCommission,
            false,
            false
        );
        $commissionData['marketplace_fee'] = $this->catalogHelper->currency(
            $vendorOrderMarketPlaceFee,
            false,
            false
        );
        return $commissionData;
    }

    public function getMarketplaceFeeRate()
    {
        $marketplaceFeeRate['calc_type'] = $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
        $marketplaceFeeRate['rate'] = $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
        return $marketplaceFeeRate;
    }

    public function calculateMarketPlaceFeeAmount($vendorItems, $websiteId)
    {
        $precedences = $this->getCommissionLevelPrecedences();
        $vendorOrderMarketPlace = 0;
        foreach ($vendorItems as $item) {
            foreach ($precedences as $precedence) {
                $itemMarketPlace = $this->getMarketPlaceAmountByPrecedence($precedence, $item, $websiteId);
                $vendorOrderMarketPlace += $itemMarketPlace;
                /**
                 * @todo create seperate table to define commission level
                 * fields will be as following
                 * item_id
                 * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                 * commission_amount
                 */
                unset($itemMarketPlace);
                break;
            }
        }
        return $vendorOrderMarketPlace;
    }

    public function calculateCancellationAmount($vendorItems)
    {
        $precedences = $this->getCommissionLevelPrecedences($vendorOrder->getStore()->getWebsiteId());
        $vendorOrderCancellation = 0;
        foreach ($vendorItems as $item) {
            foreach ($precedences as $precedence) {
                $itemCancellation = $this->getCancellationAmountByPrecedence($precedence, $item);
                if ($itemCancellation != '' && $itemCancellation != null) {
                    $vendorOrderCancellation += $itemCancellation;
                    /**
                     * @todo create seperate table to define commission level
                     * fields will be as following
                     * item_id
                     * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                     * commission_amount
                     */
                    unset($itemCancellation);
                    break;
                }
            }
        }
        return $vendorOrderCancellation;
    }

    public function getServiceTaxRate()
    {
        return $this->catalogHelper->currency(
            $this->scopeConfig->getValue(
                self::CONFIG_PATH_SERVICE_TAX_RATE,
                ScopeInterface::SCOPE_WEBSITE,
                $this->websiteId
            ),
            false,
            false
        );
    }

    public function calculateServiceTax($total)
    {
        $serviceTaxRate = $this->getServiceTaxRate();
        return $this->calculateRate($total, self::CALCULATION_TYPE_PERCENTAGE, $serviceTaxRate);
    }

    /**
     *
     * @return boolean
     */
    public function isVendorLiableForShipping()
    {
        $shippingLiableActor = (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PO_SHIPPING_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
        return ($shippingLiableActor === self::ACTOR_VENDOR);
    }

    /**
     *
     * @return boolean
     */
    protected function _isVendorLiableForTax()
    {
        $taxLiableActor = (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PO_TAX_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
        return ($taxLiableActor === self::ACTOR_VENDOR);
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function _getVendorQuoteItemswithTotals(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $vendorQuotes = [];

        $shippingMethod = $quote->getShippingAddress()->getData('shipping_method');
        $totalShipping = $quote->getShippingAddress()->getData('shipping_incl_tax');
        $totalBaseShipping = $quote->getShippingAddress()->getData('base_shipping_incl_tax');
        $perItemShipping = $totalShipping / $quote->getItemsQty();
        $perItemBaesShipping = $totalBaseShipping / $quote->getItemsQty();

        foreach ($quote->getItemsCollection() as $item) {
            if (!$item->getVendorId() || $item->getParentItemId()) {
                continue;
            }
            if (!array_key_exists($item->getVendorId(), $vendorQuotes)) {
                $vendorQuotes[$item->getVendorId()]['subtotal'] = 0;
                $vendorQuotes[$item->getVendorId()]['base_subtotal'] = 0;
                $vendorQuotes[$item->getVendorId()]['grand_total'] = 0;
                $vendorQuotes[$item->getVendorId()]['base_grand_total'] = 0;
                $vendorQuotes[$item->getVendorId()]['discount_invoiced'] = 0;
                $vendorQuotes[$item->getVendorId()]['base_discount_invoiced'] = 0;
                $vendorQuotes[$item->getVendorId()]['tax_amount'] = 0;
                $vendorQuotes[$item->getVendorId()]['shipping_amount'] = 0;
                $vendorQuotes[$item->getVendorId()]['base_shipping_amount'] = 0;
                $vendorQuotes[$item->getVendorId()]['base_tax_amount'] = 0;
            }
            $vendorQuotes[$item->getVendorId()]['items'][] = $item;
            $vendorQuotes[$item->getVendorId()]['subtotal'] +=
                ($item->getRowTotalInclTax() - $item->getTaxAmount());
            $vendorQuotes[$item->getVendorId()]['base_subtotal'] +=
                ($item->getBaseRowTotalInclTax() - $item->getBaseTaxAmount());
            $vendorQuotes[$item->getVendorId()]['grand_total'] +=
                ($item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount());
            $vendorQuotes[$item->getVendorId()]['base_grand_total'] +=
                ($item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount());
            $vendorQuotes[$item->getVendorId()]['discount_invoiced'] +=
                $item->getDiscountAmount();
            $vendorQuotes[$item->getVendorId()]['base_discount_invoiced'] += $item->getBaseDiscountAmount();
            if ($shippingMethod == 'rbmatrixrate_rbmatrixrate') {
                $vendorQuotes[$item->getVendorId()]['shipping_amount'] +=
                    $this->directoryHelper->currencyConvert(
                        $item->getData('shipping_amount'),
                        $quote->getBaseCurrencyCode(),
                        $quote->getGlobalCurrencyCode()
                    );
                $vendorQuotes[$item->getVendorId()]['grand_total'] +=
                    $this->directoryHelper->currencyConvert(
                        $item->getData('shipping_amount'),
                        $quote->getBaseCurrencyCode(),
                        $quote->getGlobalCurrencyCode()
                    );
                $vendorQuotes[$item->getVendorId()]['base_grand_total'] += $item->getData('shipping_amount');
            } elseif (substr($shippingMethod, 0, 18) != 'rbmultipleshipping') {
                $vendorQuotes[$item->getVendorId()]['shipping_amount'] += $perItemShipping * $item->getQty();
                $vendorQuotes[$item->getVendorId()]['base_shipping_amount'] += $perItemBaesShipping * $item->getQty();
                $vendorQuotes[$item->getVendorId()]['grand_total'] += $perItemShipping * $item->getQty();
                $vendorQuotes[$item->getVendorId()]['base_grand_total'] += $perItemBaesShipping * $item->getQty();
            }
            $vendorQuotes[$item->getVendorId()]['tax_amount'] += $item->getTaxAmount();
            $vendorQuotes[$item->getVendorId()]['base_tax_amount'] += $item->getBaseTaxAmount();
        }
        if (substr($shippingMethod, 0, 18) == 'rbmultipleshipping' && $item->getSku() !== 'WALLET_TOPUP') {
            $shippingMethod = str_replace('rbmultipleshipping_', '', $shippingMethod);
            $shippingMethods = explode(self::METHOD_SEPARATOR, $shippingMethod);
            foreach ($shippingMethods as $method) {
                $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
                $methodInfo = explode(self::SEPARATOR, $method);
                if (count($methodInfo) != 2) {
                    continue;
                }
                $vendorId = isset($methodInfo [1]) ? $methodInfo[1] : 0;
                $vendorQuotes[$vendorId]['shipping_amount'] = $this->directoryHelper->currencyConvert(
                    $rate->getPrice(),
                    $quote->getBaseCurrencyCode(),
                    $quote->getGlobalCurrencyCode()
                );
                $vendorQuotes[$vendorId]['grand_total'] += $vendorQuotes[$vendorId]['shipping_amount'];
                $vendorQuotes[$vendorId]['base_grand_total'] += $rate->getPrice();
            }
        }
        return $vendorQuotes;
    }

    /**
     *
     * @param int $cartId
     * @return array
     */
    public function generatePO($cartId)
    {
        $quote = $this->_getQuote($cartId);
        $this->websiteId = $quote->getStore()->getWebsiteId();
        $vendorPayments = [];
        if ($quote->getId()) {
            $vendorQuotes = $this->_getVendorQuoteItemswithTotals($quote);
            foreach ($vendorQuotes as $vendorId => $vendorQuote) {
                $vendorPayment = [];
                $vendorPayment['vendor_id'] = $vendorId;

                $commissionData = $this->calculateCommissionAmount($vendorQuote['items'], $this->websiteId);
                $vendorPayment['total_commission'] = round($commissionData['commission'], 2);
                $vendorPayment['marketplace_fee']  = round($commissionData['marketplace_fee'], 2);

                //$vendorPayment['cancellation_fee'] = $this->getCancellationFee($vendorOrder);
                $stAmount = $vendorPayment['total_commission'] + $vendorPayment['marketplace_fee'];
                $vendorPayment['service_tax'] = round($this->calculateServiceTax($stAmount), 2);
                $vendorPayment['grand_total'] = $vendorQuote['grand_total'];
                $totalAmount = $vendorQuote['grand_total'];
                if ($this->canAdjustDiscount()) {
                    /* Discount added to vendor net payable */
                    $totalAmount += abs($vendorQuote['discount_invoiced']);
                }
                $vendorPayment['shipping_amount'] = $vendorQuote['shipping_amount'];
                if (!$this->isVendorLiableForShipping()) {
                    $totalAmount -= $vendorPayment['shipping_amount'];
                }
                if (!$this->_isVendorLiableForTax()) {
                    $vendorPayment['tax_amount'] = $vendorQuote['tax_amount'];
                    $totalAmount -= $vendorPayment['tax_amount'];
                }

                $totalAmount -= $stAmount;
                $totalAmount -= $vendorPayment['service_tax'];
                $vendorPayment['total_amount'] = round($totalAmount, 2);
                $vendorPayments[$vendorId] = $vendorPayment;
            }
        }
        return $vendorPayments;
    }

    /**
     * Retrieve vendor.
     *
     * @param int $vendorId
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendor($vendorId)
    {
        return $this->vendorRepository->getById($vendorId);
    }

    /**
     *
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getQuote($cartId)
    {
        return $this->quoteRepository->get($cartId);
    }
}
