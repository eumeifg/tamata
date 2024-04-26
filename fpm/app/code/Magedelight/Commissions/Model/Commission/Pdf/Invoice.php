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
namespace Magedelight\Commissions\Model\Commission\Pdf;

/**
 * Description of Invoice
 *
 * @author Rocket Bazaar Core Team
 */
class Invoice extends AbstractPdf
{
     /**
      * @var \Magento\Store\Model\StoreManagerInterface
      */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        array $data = []
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $string,
            $scopeConfig,
            $filesystem,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $storeManager,
            $vendorRepository,
            $data
        );
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
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        /*columns headers*/
        $lines[0][] = ['text' => __('Sr. No'), 'feed' => 35];
        $lines[0][] = ['text' => __('Description'), 'feed' => 100];

        $lines[0][] = ['text' => __('Particulars'), 'feed' => 450];

        $lines[0][] = ['text' => __('Amount'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
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

            /* Add heading label */
            $this->insertTopHeading($page);
            /* Add image */
            $this->insertLogo($page, $commission->getStore());
            /* Add address */
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

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
