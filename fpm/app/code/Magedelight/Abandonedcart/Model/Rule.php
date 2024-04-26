<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magedelight\Abandonedcart\Model\ResourceModel\Rule as Ruleresource;

class Rule extends AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CANCEL_CONDITION_NEWCART = 1;
    const CANCEL_CONDITION_LINKCLICK = 2;
    const CANCEL_CONDITION_APRODUCT_OUT_STOCK = 3;
    const CANCEL_CONDITION_ALLPRODUCT_OUT_STOCK = 4;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'abandonedcart_rules';
 
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';
 
    /** @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory */
    protected $condCombineFactory;
 
    /** @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory */
    protected $condProdCombineF;
 
    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $validatedAddresses = [];
 
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }
 
    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Ruleresource::class);
        $this->setIdFieldName('abandoned_cart_rule_id');
    }
 
    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }
 
    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }
 
    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }
 
    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }
 
    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    /**
     * Prepare rule's statuses.
     * customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    //getCancelconditions
    /**
     * Prepare rule's Cancel Condition.
     * customize Cancel Condition.
     *
     * @return array
     */
    public function getCancelconditions()
    {
       /* return [self::CANCEL_CONDITION_NEWCART => __('New cart was created'),
        self::CANCEL_CONDITION_LINKCLICK => __('Link from Email Clicked'),
        self::CANCEL_CONDITION_APRODUCT_OUT_STOCK => __('Any product went out of stock'),
        self::CANCEL_CONDITION_ALLPRODUCT_OUT_STOCK => __('All products went out of stock')];*/
        return [self::CANCEL_CONDITION_NEWCART => __('New cart was created'),
        self::CANCEL_CONDITION_APRODUCT_OUT_STOCK => __('Any product went out of stock'),
        self::CANCEL_CONDITION_ALLPRODUCT_OUT_STOCK => __('All products went out of stock')];
    }
}
