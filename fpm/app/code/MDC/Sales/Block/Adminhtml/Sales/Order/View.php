<?php 

namespace MDC\Sales\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View as Views;
use Magento\Sales\Model\Order as MainOrder;
use Magedelight\Sales\Model\Order as VendorOrder;

class View extends Views
{
	
	public function beforeSetLayout(Views $view)
    {	
    	if ($this->canShowAdminInvoiceShipButton()) {
            $view->addButton(
                'invoice_and_ship',
                [
                    'label'     =>  __('Invoice and Ship'),
                    'class'     =>  'go',
                     // 'onclick'   =>  "confirmSetLocation('Are you sure you want to do this?', '{$this->getInvoiceShipUrl()}');"
                ]
            );
    	}

        if($this->canShowAdminMassCancelButton()){
            $view->addButton(
                'mass_cancel',
                [
                    'label'     =>  __('Cancel Orders'),
                    'class'     =>  'go',                    
                ]
            );
        }
    }

    public function getInvoiceShipUrl()
    {
        return $this->getUrl('rbsales/order_invoiceandship/index', ['_current'=>true]);
    }

    public function canShowAdminInvoiceShipButton()
    {
         
        $resource = \Magento\Framework\App\ObjectManager::getInstance()
                		->get(\Magento\Framework\App\ResourceConnection::class);

        $connection = $resource->getConnection();
                            
     	$select_qry = "SELECT COUNT(md_vendor_order.status) as no_of_processing_orders  FROM `" . $resource->getTableName('md_vendor_order') . "` WHERE order_id = '".$this->getOrder()->getId()."' AND status IN ('".VendorOrder::STATUS_PROCESSING  . "', '".VendorOrder::STATUS_PACKED  . "') ";     	 
        
        $rows = $connection->fetchRow($select_qry);

        if($rows['no_of_processing_orders'] > 0 ){
        	return true;
        }else{
        	return false;
        }
    }

    public function canShowAdminMassCancelButton()
    {
         
        $resource = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magento\Framework\App\ResourceConnection::class);

        $connection = $resource->getConnection();
                            
        $select_qry = "SELECT COUNT(md_vendor_order.status) as no_of_active_orders  FROM `" . $resource->getTableName('md_vendor_order') . "` WHERE order_id = '".$this->getOrder()->getId()."' AND status NOT IN ('".VendorOrder::STATUS_CANCELED  . "', '".VendorOrder::STATUS_CLOSED  . "', '".VendorOrder::STATUS_COMPLETE  . "') ";          
        
        $rows = $connection->fetchRow($select_qry);

        if($rows['no_of_active_orders'] > 0 ){
            return true;
        }else{
            return false;
        }
    }
}