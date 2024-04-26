<?php

namespace CAT\AIRecommend\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{
    protected $scopeConfig;
    protected $clickedAPI = 'https://a1b2casms0.execute-api.eu-central-1.amazonaws.com/staging/recommendationsclicked';
    public function __construct(
               ResourceConnection $resourceConnection,
               \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
    }

    public function getUserID(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $UserID = $objectManager->get('Magento\Customer\Model\Session')->getCustomerId();
        $userData = [];
        if($UserID){
            $userData['id'] =  $UserID;
        }
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        $userData['ip'] =  $ip;
        $userData['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        return $userData;
    }

    public function getClickedAPI(){
        return $this->clickedAPI;
    }

    /**
     * @return Collection
     */
    public function getLimit(){
        return $this->scopeConfig->getValue('ai_recommend/general/limit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isEnabled(){
        return $this->scopeConfig->getValue('ai_recommend/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }   

    public function isAppEnabled(){
        return $this->scopeConfig->getValue('ai_recommend/general/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsItembasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/itembased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function IsAppItembasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/itembased/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsEventbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/eventbased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsAppEventbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/eventbased/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getItembasedTitle($storeID){
        if($storeID == 2){
            return $this->scopeConfig->getValue('ai_recommend/itembased/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('ai_recommend/itembased/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function IsFashionbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/fashionbased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsAppFashionbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/fashionbased/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getfashionbasedTitle($storeID){
        if($storeID == 2){
            return $this->scopeConfig->getValue('ai_recommend/fashionbased/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('ai_recommend/fashionbased/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    public function IsUserbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/userbased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsAppUserbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/userbased/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUserbasedTitle($storeID){
        if($storeID == 2){
            return $this->scopeConfig->getValue('ai_recommend/userbased/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('ai_recommend/userbased/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getUserbasedProduct($userID){
        $APIURL = $this->scopeConfig->getValue('ai_recommend/userbased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data = $this->request($APIURL,'GET',['user_id'=>$userID]);
        if(!empty($data) && count($data)){
            return $data;
        }
        else{
             return [];
        }
    }

    public function IsSeasonbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/seasonbased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function IsAppSeasonbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/seasonbased/appenable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getSeasonbasedTitle($storeID){
        if($storeID == 2){
            return $this->scopeConfig->getValue('ai_recommend/seasonbased/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('ai_recommend/seasonbased/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getEventbasedProduct($language,$gender){

        //get Events

        if(!empty($language)){
            $APIURL = $this->scopeConfig->getValue('ai_recommend/eventbased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $data = $this->request($APIURL.'/'.$language.','.$gender,'GET',[]);
            return $data;
        }
        else{
            return [];
        }

    }

    public function getSeasonbasedProduct(){

        //get season

        $season = $this->scopeConfig->getValue('ai_recommend/seasonbased/Season',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($season)){
            $APIURL = $this->scopeConfig->getValue('ai_recommend/seasonbased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $data = $this->request($APIURL,'GET',['season'=>$season]);
            if(isset($data['product_name'])){
                return array_keys($data['product_name']);
            }
            else{
                return [];
            }
        }
        else{
            return [];
        }

    }

    public function IsMarketbasedEnable(){
        return $this->scopeConfig->getValue('ai_recommend/marketbased/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketbasedTitle($storeID){
        if($storeID == 2){
            return $this->scopeConfig->getValue('ai_recommend/marketbased/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('ai_recommend/marketbased/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getMarketbasedProduct($productID){

        if(!empty($productID)){
            $APIURL = $this->scopeConfig->getValue('ai_recommend/marketbased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $data = $this->request($APIURL.'?'.$productID,'GET',[]);
            if($data && count($data)){
                return $data;
            }
            else{
                 return [];
            }
        }
        else{
            return [];
        }
    }

    public function getFashionbasedProduct($productID){
        $APIURL = $this->scopeConfig->getValue('ai_recommend/fashionbased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data = $this->request($APIURL,'GET',['item_id'=>$productID]);
        if($data && count($data)){
            return $data;
        }
        else{
             return [];
        }
    }

    public function getItembasedProduct($productID){
        $APIURL = $this->scopeConfig->getValue('ai_recommend/itembased/apiurl',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data = $this->request($APIURL,'GET',['item_id'=>$productID]);
        if(!empty($data) && count($data)){
            return $data;
        }
        else{
             return [];
        }
    }

    public function request($api,$method,$data){
        if($method == 'GET' && count($data) > 0){
           $api = $api.'?'.http_build_query($data);
        }
        $ch = curl_init($api);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result = curl_exec($ch);
        return json_decode($result,1);
    }

    public function getAIProducts()
    {
        // Get value for Query;
        $fromdate = $this->scopeConfig->getValue('ai_recommend/general/fromdate',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $interval = (int)$this->scopeConfig->getValue('ai_recommend/general/interval',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $txtarabic = $this->scopeConfig->getValue('ai_recommend/general/txtarabic',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $txteng = $this->scopeConfig->getValue('ai_recommend/general/txteng',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $notinproducts = $this->scopeConfig->getValue('ai_recommend/general/notinproducts',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $productIds = [];

        if(!empty($fromdate) && !empty($interval) && !empty($txtarabic) && !empty($txteng) ){
            $connection = $this->resourceConnection->getConnection();
            $query = 'select product_id,b.name as "product_name",count(order_id) as total_orders,a.created_at as "date"
    from  sales_order as a join sales_order_item as b on a.entity_id = b.order_id
    join customer_entity as c on c.entity_id=a.customer_id where  a.created_at <="'.$fromdate.'" and a.created_at >= DATE_SUB("'.$fromdate.'", interval '.$interval.' day)';
            if(!empty($notinproducts)){
                $query = $query.' and product_id not in ('.$notinproducts.')';
            }
            $query = $query.'group by product_id having  b.name like "%'.$txtarabic.'%" or b.name like "%'.$txteng.'%" or count(order_id)>10 order by count(b.order_id)desc;';
            $result = $connection->fetchAll($query);
            foreach($result as $data){
                $productIds[] = $data['product_id'];
            }
        }
        return $productIds;
    }

}
