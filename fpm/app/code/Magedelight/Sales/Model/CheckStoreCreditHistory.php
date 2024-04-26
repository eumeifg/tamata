<?php

namespace Magedelight\Sales\Model;

use Magento\Framework\Model\AbstractModel;

class CheckStoreCreditHistory extends AbstractModel
{
     
     public function getOrderRevertRefundHistory($order, $creditAction)
     {     	
        $creditAdditionalInfo = "Order #".$order->getIncrementId()."";       

     	$resource = \Magento\Framework\App\ObjectManager::getInstance()
                		->get(\Magento\Framework\App\ResourceConnection::class);

		$connection = $resource->getConnection();
                            
     	$select_qry = "SELECT SUM(magento_customerbalance_history.balance_delta) as reverted  FROM `" . $resource->getTableName('magento_customerbalance_history') . "` WHERE action IN (" .$creditAction  . ") AND additional_info LIKE '%" .$creditAdditionalInfo  . "%' ";
        
        $rows = $connection->fetchRow($select_qry);

        return $rows;
         
     }

     public function getCustomerRefundableAmount($order, $subOrderTotal, $vendorOrderId){

        $customerRefundAmount = 0;
        
    
        if($order->getBaseCustomerBalanceAmount()){
           
            $orderAvailableCreditBalance = number_format((float)$order->getBaseCustomerBalanceAmount(), 2, '.', '');
        }else{
            $orderAvailableCreditBalance  = 0;
        }

        $creditAction = '4,5'; // 5 = reverted, 4 = refunded
        $creditReverted  = $this->getOrderRevertRefundHistory($order, $creditAction);
        
        
        $customerRefundAmount = $orderAvailableCreditBalance;  /* Total available refund credit limit */
        
        
        
        /* if anything is refunded, Subsctract refunded amount from Total credit refund limit*/
        if($creditReverted['reverted'] && $creditReverted['reverted'] > 0 ){

            $customerRefundAmount = $customerRefundAmount - $creditReverted['reverted']; 
        }
        else{        
            $customerRefundAmount =  $orderAvailableCreditBalance ; /* if anything is not refunded then */  
        }

        if($customerRefundAmount > $subOrderTotal)
        {
            $customerRefundAmount = $subOrderTotal;
        }elseif (is_null($creditReverted['reverted']) && $customerRefundAmount < $subOrderTotal) {
            $customerRefundAmount =  $orderAvailableCreditBalance;
        }
        else{
            $customerRefundAmount = $customerRefundAmount;
        }
        
        return $customerRefundAmount;
     }
}