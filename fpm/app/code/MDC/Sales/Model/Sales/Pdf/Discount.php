<?php

namespace MDC\Sales\Model\Sales\Pdf;

class Discount extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Discount constructor.
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->appState = $appState;
        parent::__construct($taxHelper,$taxCalculation,$ordersFactory,$data);
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
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $amount = 0;
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }

    /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            return false;
        }else{
            $amount = $this->getAmount();
            return $this->getDisplayZero() === 'true' || $amount != 0;
        }
    }
}
