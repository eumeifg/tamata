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
namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Model\Source\CalculationType;

class Vendorcommission extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var CalculationType
     */
    private $calculationType;

    /**
     *
     * @param CalculationType $calculationType
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        CalculationType $calculationType,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->calculationType = $calculationType;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Magedelight\Commissions\Model\ResourceModel\Vendorcommission::class);
    }
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get Particular Vendor Commission Charges
     *
     * @param string $vendorid Vendor Id
     * @return array
     */
    public function getAllFeesValueAndTypes($vendorid)
    {
        $vendorCommissionCharges = [];
        $vendorCommission = $this->load($vendorid, 'vendor_id');
        if ($vendorCommission->getVendorCommissionValue() && $vendorCommission->getVendorStatus()) {
            $vendorCommissionCharges['commissionRate'] =
                $vendorCommission->getVendorCommissionValue();
            $vendorCommissionCharges['commissionType'] =
                $this->calculationType->toArray()[$vendorCommission->getVendorCalculationType()];
            $vendorCommissionCharges['marketplaceRate'] =
                $vendorCommission->getVendorMarketplaceFee();
            $vendorCommissionCharges['marketplaceType'] =
                $this->calculationType->toArray()[$vendorCommission->getVendorMarketplaceFeeType()];
            $vendorCommissionCharges['cancellationRate'] =
                $vendorCommission->getVendorCancellationFee();
            $vendorCommissionCharges['cancellationType'] =
                $this->calculationType->toArray()[$vendorCommission->getVendorCancellationFeeType()];
        } else {
            $vendorCommissionCharges['error'] = __("Not Found");
        }
        return $vendorCommissionCharges;
    }

    /**
     * @param string $vendorid Vendor Id
     * @return array
     */
    public function getUrlForVendorCommissionRates($vendorid)
    {
        $url = [];
        $vendorCommissionId = $this->load($vendorid, 'vendor_id')->getId();
        if ($vendorCommissionId != null) {
            $params = ['vendor_commission_id' => $vendorCommissionId];
            $url[0] = 'Edit';
            $url[1] = $this->_urlBuilder->getUrl('commissionsadmin/vendorcommission/edit', $params);
            return $url;
        }
        $url[0] = 'Add';
        $url[1] = $this->_urlBuilder->getUrl('commissionsadmin/vendorcommission/index');
        return $url;
    }
}
