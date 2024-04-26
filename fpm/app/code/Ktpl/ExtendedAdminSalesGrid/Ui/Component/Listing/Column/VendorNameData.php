<?php

namespace Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Magedelight\Sales\Model\Order as VendorOrder;

class VendorNameData extends Column
{
    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    protected $_orderRepository;
    protected $_searchCriteria;
    protected $vendorOrderCollectionFactory;
    private $_vendorHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        array $components = [],
        array $data = []
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->_config = $config;
        $this->_vendorHelper = $_vendorHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key =>$item) {
                $vendorOrders = $this->vendorOrderCollectionFactory->create()
                                ->addFieldToSelect(['order_id','increment_id','status','vendor_id'])
                                ->addFieldToFilter("order_id", $item['entity_id']);
                $allData = $vendorOrders->getData();
                $vendorData = [];
                foreach ($allData as $data) {
                    $vendorName = $this->_vendorHelper->getVendorNameById($data['vendor_id']);
                    $vendorData[] = $vendorName;
                }
                $dataSource['data']['items'][$key]['md_vendor_name'] = implode("<br/><br/>", $vendorData);
            }
        }
        return $dataSource;
    }
}
