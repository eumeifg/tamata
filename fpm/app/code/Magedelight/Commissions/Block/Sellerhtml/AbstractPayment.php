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
namespace Magedelight\Commissions\Block\Sellerhtml;

use Magento\Framework\Pricing\PriceCurrencyInterface;

abstract class AbstractPayment extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory
     */
    protected $_paymentReportCollection;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timeZone;

    /**
     * @var \Magedelight\Commissions\Model\Source\PaymentStatus
     */
    protected $_paymentStatus;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $_vendorOrderFactory;

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus,
        PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->authSession = $authSession;
        $this->_vendorOrderFactory = $vendorOrderFactory->create();
        $this->_paymentStatus = $paymentStatus;
        $this->timeZone = $context->getLocaleDate();
        $this->_paymentReportCollection = $paymentReportCollection;
        $this->priceFormatter = $priceFormatter;
        $this->_date = $date;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatAmount($amount, $currencyCode = null)
    {
        return $this->priceFormatter->format(
            $amount,
            false,
            null,
            null,
            $currencyCode
        );
    }
}
