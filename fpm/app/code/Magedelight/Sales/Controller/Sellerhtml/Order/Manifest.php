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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;

class Manifest extends \Magedelight\Backend\App\Action implements OrderLoaderInterface
{

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->fileFactory = $fileFactory;
        $this->shipmentCollectionFactotory = $shipmentCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->load($this->_request)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Specified order does not found.'));
            $resultRedirect->setPath('*/*/index', ['tab'=> $this->getTab()]);
            return $resultRedirect;
        }
        // Vendor Order
        $vendorOrder = $this->registry->registry('vendor_order');
        $incrementOrderId = $vendorOrder['increment_id'];
        $orderIds[] = $vendorOrder->getOrderId();
        $pageNumber = 0;
        $shipmentsCollection = $this->shipmentCollectionFactotory
            ->create()
            ->addFieldToFilter('vendor_order_id', ['eq' => $this->getRequest()->getParam('vendor_order_id') ]);

        $vendorData = $this->_auth->getUser()->getData();
        $detailsAddress = $vendorData['address1'] . ", " . $vendorData['address2'] . "\n" . $vendorData['city'] . ", "
            . $vendorData['pincode'] . ", " . $vendorData['region'] . ", " . $vendorData['country_id'];

        $pdf = new \Zend_Pdf();

        foreach ($shipmentsCollection as $shipment) {
            $trackingNumber = $note = '';
            $note = $shipment->getCustomerNote();
            $tracks = $shipment->getAllTracks();
            foreach ($tracks as $track) {
                $trackingNumber = $track->getNumber();
            }
            $this->createManifestPages(
                $pdf,
                $pageNumber,
                $trackingNumber,
                $vendorData['business_name'],
                $detailsAddress,
                $incrementOrderId,
                $note
            );
            $pageNumber++;
        }

        $fileName = 'Manifest-' . $incrementOrderId . '.pdf';

        $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            /* this pdf will be saved in var directory with the name*/
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\ResultInterface
     */
    public function load(RequestInterface $request)
    {
        $id = (int) $request->getParam('vendor_order_id');
        if (!$id) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        if ($this->getVendorOrder()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool|\Magedelight\Sales\Model\Order
     */
    public function getVendorOrder()
    {
        if (!($vendorId = $this->_auth->getUser()->getVendorId())) {
            return false;
        }
        $id = $this->getRequest()->getParam('vendor_order_id', false);
        $vendorOrder = $this->vendorOrder->load($id);
        if ($vendorOrder->getId() && $vendorOrder->getVendorId() == $vendorId) {
            $this->registry->register('vendor_order', $vendorOrder);
            return $vendorOrder;
        }
        return false;
    }

    /**
     * @param $pdf
     * @param int $pageNumber
     * @param string $trackingNumber
     * @param string $vendorBusinessName
     * @param string $detailsAddress
     * @param $incrementOrderId
     * @param string $note
     * @throws \Zend_Pdf_Exception
     */
    public function createManifestPages(
        $pdf,
        $pageNumber = 0,
        $trackingNumber = '',
        $vendorBusinessName = '',
        $detailsAddress = '',
        $incrementOrderId,
        $note = ''
    ) {
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[$pageNumber]; // this will get reference to the first page.
        $trackingNumber = ($trackingNumber) ?: 'N/A';
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font, 16);
        $bottomLine = 600;
        $page->setStyle($style);

        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("Supplier Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__("Name: %1", $vendorBusinessName), $x + 5, $this->y+33, 'UTF-8');
        $page->drawText(__("Address: %1", $detailsAddress), $x + 5, $this->y+16, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Sr No"), $x + 10, $this->y-10, 'UTF-8');
        $page->drawText(__("Tracking Number"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Order Id"), $x + 200, $this->y-10, 'UTF-8');
        $page->drawText(__("RTS Done on"), $x + 310, $this->y-10, 'UTF-8');
        /* Add note only if note data is available */
        if (trim($note) != '') {
            $page->drawText(__("Notes"), $x + 10, $this->y-200, 'UTF-8');
        }

        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText("1", $x + 10, $this->y-30, 'UTF-8');
        $page->drawText(__($trackingNumber), $x + 60, $this->y-30, 'UTF-8');
        $page->drawText($incrementOrderId, $x + 200, $this->y-30, 'UTF-8');
        $page->drawText("", $x + 330, $this->y-30, 'UTF-8');
        /* Add note data with line break */
        if (trim($note) != '') {
            $note = wordwrap($note, 115);//preg_replace("/(.{100})/", "$1<br />", $note);
            $i = 215;
            foreach (explode("\n", $note) as $i => $line) {
                $bottomLine = $this->y - 215 - ($i * 12);
                $page->drawText($line, 65, $bottomLine, 'UTF-8');
            }
        }
        $pro = "";
        $page->drawText($pro, $x + 60, $this->y-30, 'UTF-8');

        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $page->drawText(__("Pickup in time:"), $x + 10, $this->y-80, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-80, 'UTF-8');

        $page->drawText(__("Total Items picked:"), $x + 280, $this->y-80, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 360, $this->y-80, 'UTF-8');

        $page->drawText(__("Pickup out time:"), $x + 10, $this->y-100, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-100, 'UTF-8');

        $page->drawText(__("FE Name:"), $x + 10, $this->y-120, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-120, 'UTF-8');

        $page->drawText(__("Seller Name:"), $x + 280, $this->y-120, 'UTF-8');
        $page->drawText(__($vendorBusinessName), $x + 360, $this->y-120, 'UTF-8');

        $page->drawText(__("FE Signature:"), $x + 10, $this->y-140, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-140, 'UTF-8');

        $page->drawText(__("Seller Signature:"), $x + 280, $this->y-140, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 360, $this->y-140, 'UTF-8');

        $style->setFont($font, 9);
        $page->setStyle($style);
        $page->drawText(__("This is a system generated document"), $x + 180, $bottomLine - 60, 'UTF-8');
        $page->drawRectangle(
            30,
            $this->y -62,
            $page->getWidth()-30,
            $bottomLine - 70,
            \Zend_Pdf_Page::SHAPE_DRAW_STROKE
        );
        $style->setFont($font, 15);
        $page->setStyle($style);
    }
}
