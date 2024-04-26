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
namespace MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \MDC\VendorCommissions\Model\VendorCategoryCommissionFactory
     */
    protected $vendorCategoryCommissionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $uploaderFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \MDC\VendorCommissions\Model\VendorCategoryCommissionFactory $vendorCategoryCommissionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->vendorCategoryCommissionFactory = $vendorCategoryCommissionFactory;
        $this->registry = $registry;
        $this->_fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context, $resultPageFactory, $resultForwardFactory, $vendorCategoryCommissionFactory, $registry);
    }

    public function execute()
    {
        $name = date('m-d-Y-H-i-s');
        // $mediapath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle

        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV

        $columns = ['vendor_category_commission_id','calculation_type','commission_value', 'marketplace_fee_type', 'marketplace_fee', 'cancellation_fee_commission_value', 'cancellation_fee_calculation_type', 'vendor_id', 'product_category', 'status', 'website_id', 'store_id', 'created_at', 'updated_at'];

        foreach ($columns as $column)
        {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);

        $vc = $this->vendorCategoryCommissionFactory->create();

        $vcc = $vc->getCollection()->addFieldToFilter('vendor_id', $this->getRequest()->getParam('vendorid')); // get Collection of Table data

        foreach($vcc as $item){

            $itemData = [];
            $itemData[] = $item->getData('vendor_category_commission_id');
            $itemData[] = $item->getData('calculation_type');
            $itemData[] = $item->getData('commission_value');
            $itemData[] = $item->getData('marketplace_fee_type');
            $itemData[] = $item->getData('marketplace_fee');
            $itemData[] = $item->getData('cancellation_fee_commission_value');
            $itemData[] = $item->getData('cancellation_fee_calculation_type');
            $itemData[] = $item->getData('vendor_id');
            $itemData[] = $item->getData('product_category');
            $itemData[] = $item->getData('status');
            $itemData[] = $item->getData('website_id');
            $itemData[] = $item->getData('store_id');
            $itemData[] = $item->getData('created_at');
            $itemData[] = $item->getData('updated_at');

            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'vcc-import-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }
}
