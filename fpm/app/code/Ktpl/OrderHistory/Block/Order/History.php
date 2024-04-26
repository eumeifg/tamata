<?php
namespace Ktpl\OrderHistory\Block\Order;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
/**
 * Sales order history block
 */
class History extends \Magedelight\Sales\Block\Vendor\Order\History
{
    protected $productRepository;
    protected $_storeManager;
    protected $_blockFactory;
    protected $orderCollectionFactory;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {

 	$this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig,$stockItem, $_productloader, $vendorFactory, $salesHelper, $imageHelper);
    }
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderlist()
    {

        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->getOrderActive($customerId);            
        }else{
            $this->getOrderFilterList($customerId);
        }      
    }

    private function getOrderActive($customerId){

        $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
            '*'
        );
        $this->orders->addFieldToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        );

        return $this->orders;
    }

    private function getOrderFilterList($customerId){

    $post = $this->getRequest()->getParams();
    if(isset($post)){
        $this->orders = $this->getOrderCollectionFactory()->create($customerId);
            if(!empty($post['orderid'])){                      
                    $this->orders->addFieldToFilter(
                        'increment_id',
                        $post['orderid']
                    );
            }
            $this->orders->addFieldToSelect(
                '*'
            );

            if(!empty($post['from_date']) && !empty($post['to_date'])){
                $date=['from' =>date("Y-m-d H:i:s",strtotime( $post['from_date'].' 00:00:00')),'to'=>date("Y-m-d H:i:s",strtotime( $post['to_date'].' 24:00:00'))];
                $this->orders->addFieldToFilter(
                    'main_table.created_at',
                    $date
                );         
            }else if(!empty($post['from_date'])){
                $this->orders->addFieldToFilter(
                    'main_table.created_at',
                    ['like' =>date("Y-m-d ",strtotime($post['from_date'])).'%']
                );
            }else if(!empty($post['to_date'])){

                $this->orders->addFieldToFilter(
                    'main_table.created_at',
                    ['like' =>date("Y-m-d",strtotime($post['to_date'])).'%']
                );

            }

            $this->orders->addFieldToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }   
 
        return $this->orders;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrderlist()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pagersearch'
            )->setCollection(
                $this->getOrderlist()
            );
            $this->setChild('pager', $pager);
            $this->getOrderlist()->load();
        }
        return $this;
    }
}
