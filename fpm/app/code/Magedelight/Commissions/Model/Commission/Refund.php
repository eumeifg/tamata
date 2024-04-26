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

use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class Refund extends Payment
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Psr\Log\LoggerInterface $logger,
        DiscountProcessor $discountProcessor,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Json $serializer = null,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $context,
            $registry,
            $scopeConfig,
            $date,
            $vendorRepository,
            $vendorOrderCollectionFactory,
            $catalogHelper,
            $logger,
            $discountProcessor,
            $resource,
            $resourceCollection,
            $serializer,
            $data
        );
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param float $qty
     * @return float
     */
    public function calculateTotalCommissionAmountByItem($orderId, $itemsArray)
    {
        $totalCommFees = 0;
        $totalCommission = 0;
        $order = $this->orderRepository->get($orderId);
        $precedences = $this->getCommissionLevelPrecedences();
        foreach ($order->getAllItems() as $item) {
            if (array_key_exists($item->getId(), $itemsArray)) {
                $totalMarketPlaceCharges = 0;
                $qty = $itemsArray[$item->getId()]['qty'];
                foreach ($precedences as $precedence) {
                    $itemCommission = $this->getCommissionAmountByPrecedence($precedence, $item);
                    if ($itemCommission != '' && $itemCommission != null) {
                        $totalCommFees += $itemCommission;
                        /**
                         * @todo create seperate table to define commission level
                         * fields will be as following
                         * item_id
                         * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                         * commission_amount
                         */
                        unset($itemCommission);
                        break;
                    }
                }
                $totalMarketPlaceCharges = ($totalCommFees / ($item->getQtyOrdered() - $item->getQtyRefunded())) * $qty;
                unset($totalCommFees);
                $marketplaceFeeRate = $this->getMarketplaceFeeRate();
                $itemTotal = ($this->getItemRowTotalWithAdjustedDiscount(
                    $item
                ) / ($item->getQtyOrdered() - $item->getQtyRefunded())) * $qty;
                $totalMarketPlaceCharges += $this->calculateRate(
                    $itemTotal,
                    $marketplaceFeeRate['calc_type'],
                    $marketplaceFeeRate['rate'],
                    $qty
                );
                unset($qty);
                $totalMarketPlaceCharges += round($this->calculateServiceTax($totalMarketPlaceCharges), 2);
                unset($item);
                $totalCommission += $totalMarketPlaceCharges;
            }
        }
        return $totalCommission;
    }
}
