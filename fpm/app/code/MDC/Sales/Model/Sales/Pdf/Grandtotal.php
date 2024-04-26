<?php
namespace MDC\Sales\Model\Sales\Pdf;

class Grandtotal extends \Magento\Tax\Model\Sales\Pdf\Grandtotal
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Grandtotal constructor.
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->appState = $appState;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $taxConfig, $data);
    }

    /**
     * Check if tax amount should be included to grandtotals block
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        if (!$this->_taxConfig->displaySalesTaxWithGrandTotal($store)) {
            return $this->getDefaultTotalsForDisplay();
        }

        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount() + (-$this->getSource()->getDiscountAmount()) - $this->getSource()->getShippingAmount());
        }else{
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        }
        $amountExclTax = $this->getAmount() - $this->getSource()->getTaxAmount();
        $amountExclTax = $amountExclTax > 0 ? $amountExclTax : 0;
        $amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount() + (-$this->getSource()->getDiscountAmount()) - $this->getSource()->getShippingAmount());
        }else{
            $tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount());
        }
        $tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $totals = [
            [
                'amount' => $this->getAmountPrefix() . $amountExclTax,
                'label' => __('Grand Total (Excl. Tax)') . ':',
                'font_size' => $fontSize,
            ],
        ];

        if ($this->_taxConfig->displaySalesFullSummary($store)) {
            $totals = array_merge($totals, $this->getFullTaxInfo());
        }

        $totals[] = [
            'amount' => $this->getAmountPrefix() . $tax,
            'label' => __('Tax') . ':',
            'font_size' => $fontSize,
        ];
        $totals[] = [
            'amount' => $this->getAmountPrefix() . $amount,
            'label' => __('Grand Total (Incl. Tax)') . ':',
            'font_size' => $fontSize,
        ];
        return $totals;
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getDefaultTotalsForDisplay()
    {

        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount() + (-$this->getSource()->getDiscountAmount()) - $this->getSource()->getShippingAmount());
        } else {
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        }

        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }
}
