<?php

namespace Ktpl\Warehousemanagement\Plugin\ResourceModel\Warehouse;

class Grid
{
    public static $table = 'ktpl_warehousemanagement_warehousemanagement';
    public static $leftJoinTable = 'sales_order';
    public static $JoinTable = 'md_vendor_order';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table))
        {
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
            $JoinTableName = $collection->getConnection()->getTableName(self::$JoinTable);
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.increment_id = main_table.main_order_id",
                    ['sales_order_status'=>'co.status']
                )->joinLeft(
                    ['vt'=>$JoinTableName],
                    "vt.increment_id = main_table.sub_order_id",
                    ['md_vendor_order_status'=>'vt.status']
                );
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $alisArrayFiled = ['co`.`status' => 'sales_order_status' , 'vt`.`status'=> 'md_vendor_order_status','main_table`.`created_at'=>'md_created_at'];
            $whereArray = $where;
            foreach($where as $key=>$value) {
            	foreach($alisArrayFiled as $k=>$v) {
            		if(strpos($value, $v) !== false) {
            			$whereArray[$key] = str_replace($v, $k, $value);
            		}
            	}
            }

            if(!empty($whereArray))
            $where = $whereArray;

            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
            //echo $collection->getSelect()->__toString();die;
        }
        return $collection;

    }
}