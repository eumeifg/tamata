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
namespace Magedelight\Commissions\Model\Commission\Pdf\Items;

use Magedelight\Commissions\Model\Source\CalculationType;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Description of AbstractItems
 *
 * @author Rocket Bazaar Core Team
 */
abstract class AbstractItems extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Order model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Source model (invoice, shipment, creditmemo)
     *
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_source;

    /**
     * Item object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_item;

    /**
     * Pdf object
     *
     * @var \Magento\Sales\Model\Order\Pdf\AbstractPdf
     */
    protected $_pdf;

    /**
     * Pdf current page
     *
     * @var \Zend_Pdf_Page
     */
    protected $_pdfPage;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $priceHelper;

    protected $currencyCode = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Catalog\Helper\Pricing\Data $priceHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Catalog\Helper\Pricing\Data $priceHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Json $serializer = null,
        \Magedelight\Commissions\Model\Source\CalculationType $calculationType,
        \Magedelight\Commissions\Model\Source\CommissionType $commissionType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->filterManager = $filterManager;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->priceHelper = $priceHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->calculationType = $calculationType;
        $this->commissionType = $commissionType;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set order model
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return $this
     */
    //public function setOrder(\Magedelight\Commissions\Model\Commission\Payment $order)
    public function setOrder($order = [])
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Set Source model
     *
     * @param  \Magento\Framework\Model\AbstractModel $source
     * @return $this
     */
    public function setSource(\Magento\Framework\Model\AbstractModel $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Set item object
     *
     * @param  \Magento\Framework\DataObject $item
     * @return $this
     */
    public function setItem(\Magento\Framework\DataObject $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Set Pdf model
     *
     * @param  \Magento\Sales\Model\Order\Pdf\AbstractPdf $pdf
     * @return $this
     */
    public function setPdf(\Magedelight\Commissions\Model\Commission\Pdf\AbstractPdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Set current page
     *
     * @param  \Zend_Pdf_Page $page
     * @return $this
     */
    public function setPage(\Zend_Pdf_Page $page)
    {
        $this->_pdfPage = $page;
        return $this;
    }

    /**
     * Retrieve order object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (null === $this->_order) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order object is not specified.'));
        }
        return $this->_order;
    }

    /**
     * Retrieve source object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getSource()
    {
        if (null === $this->_source) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The source object is not specified.'));
        }
        return $this->_source;
    }

    /**
     * Retrieve item object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\DataObject
     */
    public function getItem()
    {
        if (null === $this->_item) {
            throw new \Magento\Framework\Exception\LocalizedException(__('An item object is not specified.'));
        }
        return $this->_item;
    }

    /**
     * Retrieve Pdf model
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Model\Order\Pdf\AbstractPdf
     */
    public function getPdf()
    {
        if (null === $this->_pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF object is not specified.'));
        }
        return $this->_pdf;
    }

    /**
     * Retrieve Pdf page object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf_Page
     */
    public function getPage()
    {
        if (null === $this->_pdfPage) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF page object is not specified.'));
        }
        return $this->_pdfPage;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    abstract public function draw();

    /**
     * Format option value process
     *
     * @param array|string $value
     * @return string
     */
    protected function _formatOptionValue($value)
    {
        $order = $this->getOrder();

        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= $this->filterManager->sprintf($value['qty'], ['format' => '%d']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

    /**
     * Set font as regular
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Return Amount
     * @param mixed $item
     * @param mixed $order
     * @return float
     */
    public function getAmount($item, $order)
    {
        if ($this->currencyCode === null && !empty($item->getData($order['code']))) {
            $this->currencyCode = $item->getData('order_currency_code');
        }
        if ($item->getData($order['code'])) {
            return $this->formatAmount($item->getData($order['code']));
        }
        return $this->formatAmount(0);
    }

    public function getServiceTax($item)
    {
        if ($item->getData('service_tax')) {
            return $item->getData('service_tax');
        } else {
            return '0.00';
        }
    }
    /**
     * Retrieve formated amount
     *
     * @param float $amount
     * @return string
     */
    public function formatAmount($amount)
    {
        return $this->priceHelper->format($amount, false, null, null, $this->currencyCode);
    }

    /**
     * @param $feeCode
     * @param $commission
     * @return string
     */
    public function getAppliedFeesDetails($feeCode, $commission)
    {
        $finalFee = "";
        if($commission->getTransactionSummary()){
            $feeCode = $feeCode[0];
            $transactionSummary = $this->serializer->unserialize($commission->getTransactionSummary());
            $transactionSummary = $transactionSummary[$commission->getVendorOrderId()];

            $calculationType = "";

            if (array_key_exists($feeCode, $transactionSummary) && is_array($transactionSummary[$feeCode])) {
                $calculationTypes = $this->calculationType->toArray();
                $commissionTypes = $this->commissionType->toOptionArray(true);

                foreach ($commissionTypes as $key => $commissionType) {
                    $commissionRate = $transactionSummary[$feeCode]['commissionRate'];

                    if ($commissionType['value'] == $transactionSummary[$feeCode]['precedence']) {
                        $commissionPrecedence = __($commissionType['label']->getText() . " Level");

                        if ($calculationTypes[$transactionSummary[$feeCode]['calculationType']] ==
                            CalculationType::PERCENTAGE_LABEL) {
                            $calculationType .= number_format($commissionRate, 2) . '%';
                        } else {
                            $calculationType .= $this->formatAmount(
                                $commissionRate
                            ) . ' (' . CalculationType::FLAT_LABEL . ')';
                        }

                        $finalFee = $calculationType . " " . $commissionPrecedence;
                    }
                }
            }
        }

        if ($feeCode == "service_tax") {
            $finalFee = number_format($transactionSummary[$feeCode], 2) .
                __("% (Levied on commission and marketplace fee.)");
        }

        return $finalFee;
    }
}
