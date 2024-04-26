<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Model\Commission\Pdf;

class Invoice extends \Magedelight\Commissions\Model\Commission\Pdf\Invoice
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $information;
    
    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * Invoice constructor.
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magedelight\Commissions\Model\Commission\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Store\Model\Information $information
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magedelight\Commissions\Model\Commission\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Store\Model\Information $information,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->information = $information;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct(
            $string,
            $scopeConfig,
            $filesystem,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $storeManager,
            $localeResolver,
            $vendorRepository,
            $data
        );
    }

     /**
      * Insert store address to pdf page
      *
      * @param \Zend_Pdf_Page &$page
      * @return void
      */
    protected function insertStoreAddress(&$page)
    {
        $storeData = $this->information->getStoreInformationObject($this->_storeManager->getStore());
        $storeAddressArray = [];
        $storeAddressArray[] = $storeData->getName();
        $storeAddressArray[] = $storeData->getStreetLine1();
        if (!empty($storeData->getStreetLine2())) {
            $storeAddressArray[] = $storeData->getStreetLine2();
        }
        $storeAddressArray[] = $storeData->getCity().', '.$storeData->getRegion().', '.$storeData->getPostcode();
        $storeAddressArray[] = $storeData->getCountry();
        $storeAddressArray[] = $storeData->getPhone();
        if (!empty($storeData->getVatNumber())) {
            $storeAddressArray[] = __('VAT Number').': '.$storeData->getVatNumber();
        }
       
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0);
        $page->drawRectangle(25, 760, 275, 735);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));

        $this->_setFontRegular($page, 10);
        $this->_setFontBold($page, 12);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Marketplace'), 35, 743, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 720;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->drawRectangle(25, 735, 275, 640);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        foreach ($storeAddressArray as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(ltrim($_value)), 35, $top, 'UTF-8');
                    $top -= 15;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }
    
    /**
     * Return PDF document
     *
     * @param \Magedelight\Commissions\Model\Commission\Payment[] $commissions
     * @return \Zend_Pdf
     */
    public function getPdf($commissions = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('commission');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($commissions as $commission) {

            $page = $this->newPage();
            $order = $commission;
            /* Add image */
            $this->insertLogo($page, $commission->getStore());
            /* Add address */
            $this->insertStoreAddress($page);
            
            $this->insertAddress($page, $commission->getStore());
            /* Add head */
            $this->insertCommission(
                $page,
                $commission,
                true
            );
            /* Add document text and number */
           /* $this->insertDocumentNumber($page, __('Packing Slip # ') . $commission->getCommissionInvoiceId());*/
            
            /* Add table */
            $this->_drawCustomerOrderHeader($page, $order);
            
            /* create increment_id to fetch vendor order */
            $orderArr = explode('-', $order->getCommissionInvoiceId());
            $vendorOrderId = end($orderArr);
            /* create increment_id to fetch vendor order */
            
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            if ($vendorOrder && $vendorOrder->getId()) {
                $vendorOrder->setShippingHandlingCharge(30);
                /* Add body */
                foreach ($this->getCustomerOrderData() as $code => $child) {
                    $this->_drawItem($vendorOrder, $page, $child);
                    $page = end($pdf->pages);
                }
            }
            
            /* Add table */
            $this->_drawHeader($page);
             
            /* Add body */
            foreach ($this->getFeesType() as $code => $child) {
                $this->_drawItem($commission, $page, $child);
                $page = end($pdf->pages);
            }
            
            $this->insertFooterText($page, __('This is a computer generated invoice. No signature required'));
            if ($commission->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    public function getCustomerOrderData()
    {
        $data = [
            'subtotal' => [
                'id' => 1,
                'text' => __('Total Sales (net)'),
                'code' => 'subtotal'
                ],
            'shipping_amount' => [
                'id' => 2,
                'text' => __('Shipping & Handling (net)'),
                'code' => 'shipping_amount'
                ],
            'tax_amount' => [
                'id' => 3,
                'text' => __('VAT'),
                'code' => 'tax_amount'
                ],
            'grand_total' => [
                'id' => 4,
                'text' => __('Total Gross'),
                'code' => 'grand_total'
                ]
        ];
        return $data;
    }
    
    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -= 15);
        $this->y += 5;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        /*columns headers*/
        $lines[0][] = ['text' => __('Fee No'), 'feed' => 35];
        $lines[0][] = ['text' => __('Fee Description'), 'feed' => 100];

        $lines[0][] = ['text' => __('Amount'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 10;
    }
    
    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawCustomerOrderHeader(\Zend_Pdf_Page $page, $source)
    {
        $source->getOrder();
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -= 15);
        $this->y += 5;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        /* create increment_id to fetch vendor order */
        $orderArr = explode('-', $source->getCommissionInvoiceId());
        unset($orderArr[0]);
        $orderArr = array_values($orderArr);
        /* create increment_id to fetch vendor order */
            
        /*columns headers*/
        $lines[0][] = ['text' => __('Customer Order No').' - '.$orderArr[0], 'feed' => 35];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 10;
    }
    
    /**
     * Insert order to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertCommission(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magedelight\Commissions\Model\Commission) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magedelight\Commissions\Model\Commission\Payment) {
            $shipment = $obj;
            $order = $shipment;
        }
        
        $this->_vendor = $this->_vendorRepository->getById($order->getVendorId());
        
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top-20, 570, $top-80);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            /* create increment_id to fetch vendor order */
            $orderArr = explode('-', $order->getCommissionInvoiceId());
            $vendorOrderId = end($orderArr);
            /* create increment_id to fetch vendor order */
            
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            if ($vendorOrder && $vendorOrder->getId()) {
                $page->drawText(__('Order # ') .$orderArr[0], 35, $top -= 30, 'UTF-8');
                $page->drawText(
                    __('Order Date: ') .
                    $this->_localeDate->formatDate(
                        $vendorOrder->getCreatedAt(),
                        \IntlDateFormatter::MEDIUM,
                        false
                    ),
                    35,
                    $top -= 15,
                    'UTF-8'
                );
            }
            $page->drawText(__('Invoice # ') . $order->getCommissionInvoiceId(), 35, $top -= 15, 'UTF-8');
        }
        $page->drawText(
            __('Invoice Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $this->_storeManager->getStore(),
                    $order->getPaidAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );
        
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0);
        $page->drawRectangle(275, 760, 570, 735);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));

        $this->_setFontRegular($page, 10);
        $this->_setFontBold($page, 12);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText('Vendor', 530, 743, 'UTF-8');
        
        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));

        /* Billing Address */
        $billingAddress = $this->getVendorBillingAddress().'|'.$this->getVendorShippingAddress();
        $billingAddress = $this->_formatAddress($billingAddress);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(275, 735, 570, 640);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $billingTop = 720;
        $addressesStartY = $this->y;
        $font = $this->_setFontRegular($page, 10);
        
        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        $this->getAlignRight($part, 130, 440, $font, 10),
                        $billingTop,
                        'UTF-8'
                    );
                    $billingTop -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            
            $this->y = $top;

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }
    }
    
    public function getVendorBillingAddress()
    {
        $vendor = $this->_vendor;
        $address = [];
        $address[] =  $vendor->getBusinessName();
        $addresses = $vendor->getAddress1();
        $addresses .= (!empty($vendor->getAddress2()))?', ' . $vendor->getAddress2():'';
        $address[] = $addresses;
        
        $address[] =  implode(', ', [
            $vendor->getCity(),
            $vendor->getRegion(),
            $vendor->getCountry(),
            $vendor->getPincode()
        ]);
        return implode('|', $address);
    }
    
    public function getVendorShippingAddress()
    {
        $vendor = $this->_vendor;
        $address = [];
        $address[] =  __('Telephone: ') . $vendor->getMobile();
        $address[] =  __('Email: ') . $vendor->getEmail();
        if ($vendor->getVat()) {
            $address[] =  __('VAT Number: '). $vendor->getVat();
        }
        return implode('|', $address);
    }
}
