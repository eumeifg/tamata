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
namespace Magedelight\Sales\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation\AbstractCalculator;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Calculation $calculation
     * @param CalculatorFactory $calculatorFactory
     * @param Config $config
     * @param TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param TaxClassManagementInterface $taxClassManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->calculationTool = $calculation;
        $this->calculatorFactory = $calculatorFactory;
        $this->config = $config;
        $this->taxDetailsDataObjectFactory = $taxDetailsDataObjectFactory;
        $this->taxDetailsItemDataObjectFactory = $taxDetailsItemDataObjectFactory;
        $this->storeManager = $storeManager;
        $this->taxClassManagement = $taxClassManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_customerSession = $customerSession;
        parent::__construct(
            $calculation,
            $calculatorFactory,
            $config,
            $taxDetailsDataObjectFactory,
            $taxDetailsItemDataObjectFactory,
            $storeManager,
            $taxClassManagement,
            $dataObjectHelper
        );
    }

    /**
     * Calculate item tax with customized rounding level
     *
     * Added Vendor Id from quote item to session object. It will be used to get vendor pickup address.
     *
     * @param QuoteDetailsItemInterface $item
     * @param AbstractCalculator $calculator
     * @param bool $round
     * @return TaxDetailsItemInterface
     */
    protected function processItem(
        QuoteDetailsItemInterface $item,
        AbstractCalculator $calculator,
        $round = true
    ) {
        $quantity = $this->getTotalQuantity($item);
        $this->_customerSession->setCurrentVendor($item->getVendorId());
        return $calculator->calculate($item, $quantity, $round);
    }
}
