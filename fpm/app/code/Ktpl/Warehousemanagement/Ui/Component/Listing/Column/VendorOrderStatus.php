<?php
namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class VendorOrderStatus extends Column
{

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * VendorOrderStatus constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $data = explode('-', $item["sub_order_id"]);
                if(!empty($data[1])){
                    try{
                        $order  = $this->vendorOrderRepository->getById($data[1]);
                        $order_status = $order->getStatusLabel();
                        if(!empty($order_status)){
                            $item[$this->getData('name')] = $order_status;
                        }
                    }catch(\Exception $exception){

                    }
                }

            }
        }
        return $dataSource;
    }
}
