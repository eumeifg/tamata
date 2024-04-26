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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Totals;

/**
 * Description of Tax
 *
 * @author Rocket Bazaar team
 */
class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * Tax helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * Tax calculation
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalculation;

    /**
     * Tax factory
     *
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $_taxOrderFactory;

    /**
     * Sales admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_salesAdminHelper;

    /**
     * @param Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory
     * @param \Magento\Sales\Helper\Admin $salesAdminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory,
        \Magento\Sales\Helper\Admin $salesAdminHelper,
        array $data = []
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrderFactory = $taxOrderFactory;
        $this->_salesAdminHelper = $salesAdminHelper;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Get full information about taxes applied to order
     *
     * @return array
     */
    public function getFullTaxInfo()
    {
        $source = $this->getSource();
        if (!$source instanceof \Magento\Sales\Model\Order\Invoice
            && !$source instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            $source = $this->getOrder();
        }

        $taxClassAmount = [];
        if (empty($source)) {
            return $taxClassAmount;
        }

        $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($source);
        if (empty($taxClassAmount)) {
            $rates = $this->_taxOrderFactory->create()->getCollection()->loadByOrder($source)->toArray();
            $taxClassAmount = $this->_taxCalculation->reproduceProcess($rates['items']);
        }

        return $taxClassAmount;
    }

    /**
     * Display tax amount
     *
     * @param string $amount
     * @param string $baseAmount
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->_salesAdminHelper->displayPrices($this->getSource(), $baseAmount, $amount, false, '<br />');
    }
    
    public function getVendorOrder()
    {
        return $this->getParentBlock()->getVendorOrder();
    }

    /**
     * Get store object for process configuration settings
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
}
