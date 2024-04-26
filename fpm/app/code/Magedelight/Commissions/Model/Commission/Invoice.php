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

use Magedelight\Commissions\Api\Data\CommissionInterface;
use Magento\Framework\Model\AbstractModel;

class Invoice extends AbstractModel implements CommissionInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Commissions\Model\ResourceModel\Commission\Invoice::class);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getCalculationType()
     */
    public function getCalculationType()
    {
        return $this->getData(self::CALCULATION_TYPE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setCalculationType()
     */
    public function setCalculationType($calculation_type)
    {
        return $this->setData(self::CALCULATION_TYPE, $calculation_type);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getCommissionValue()
     */
    public function getCommissionValue()
    {
        return $this->getData(self::COMMISSION_VALUE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setCommissionValue()
     */
    public function setCommissionValue($value)
    {
        return $this->setData(self::COMMISSION_VALUE, $value);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getMarketplaceFee()
     */
    public function getMarketplaceFee()
    {
        return $this->getData(self::MARKETPLACE_FEE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getMarketplaceFeeType()
     */
    public function getMarketplaceFeeType()
    {
        return $this->getData(self::MARKETPLACE_FEE_TYPE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setMarketplaceFee()
     */
    public function setMarketplaceFee($fee)
    {
        return $this->setData(self::MARKETPLACE_FEE, $fee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setMarketplaceFeeType()
     */
    public function setMarketplaceFeeType($marketplace_fee_type)
    {
        return $this->setData(self::MARKETPLACE_FEE_TYPE, $fee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getProductCategory()
     */
    public function getProductCategory()
    {
        return $this->getData(self::PRODUCT_CATEGORY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setProductCategory()
     */
    public function setProductCategory($category_id)
    {
        return $this->setData(self::PRODUCT_CATEGORY, $category_id);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getStatus()
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setStatus()
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
