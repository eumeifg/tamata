<?php 
namespace CAT\AIRecommend\Model;
 
use Ktpl\Productslider\Api\Data\ProductsliderInterfaceFactory;
use CAT\AIRecommend\Helper\Data;
use Ktpl\Productslider\Api\ProductRepositoryInterface;
use CAT\AIRecommend\Api\Data\RecommenderBuilderResultsInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
class RecommenderBuilder {

	/**
     * @var ProductsliderInterfaceFactory
     */
	
	protected $productSliderFactory;

	/**
     * @var StoreManagerInterface
     */
	
	protected $storeManagerInterface;

	/**
     * @var Data
     */
	protected $helperData;
	/**
     * @var ProductRepositoryInterface
     */
   protected $productRepository;
	/**
  * @var RecommenderBuilderResultsInterface
  */
   protected $recommenderBuilderResultsInterface;
   /**
  * @var CartManagementInterface
  */
   private $cartManagement;

	public function __construct(
        ProductsliderInterfaceFactory    $productSliderFactory,
        ProductRepositoryInterface       $productRepository,
        RecommenderBuilderResultsInterface       $recommenderBuilderResultsInterface,
        Data $helperData,
        StoreManagerInterface $storeManager,
        CartManagementInterface $cartManagement
    )
     {
        $this->productSliderFactory = $productSliderFactory;
        $this->productRepository = $productRepository;
        $this->recommenderBuilderResultsInterface = $recommenderBuilderResultsInterface;
        $this->helperData = $helperData;
        $this->storeManagerInterface = $storeManager;
        $this->cartManagement = $cartManagement;
    }


	/**
	 * {@inheritdoc}
	 */
	public function itembase($item_id)
	{
		$storeId = (int)$this->storeManagerInterface->getStore()->getId();
		$AIProducts = [];
		if($this->helperData->IsAppItembasedEnable()){
			$AIProducts = $this->helperData->getItembasedProduct($item_id);
    		//$AIProducts = $this->helperData->getAIProducts();
		}
     	$limit = $this->helperData->getLimit();
     	$title = $this->helperData->getItembasedTitle($storeId);
     	$productSlider = $this->productSliderFactory->create();
     	$productSlider->setProductType('custom');
     	$productSlider->setProductIds($AIProducts);
     	$products = $this->productRepository->getList($productSlider, $limit)->getItems();
     	$response = $this->recommenderBuilderResultsInterface->setProducts($products);
     	$response = $response->setTitle($title);
     	return $response;
	}


	/**
     * @inheritDoc
     * @throws \Exception
     */
	public function fashionbase($item_id)
	{
		$storeId = (int)$this->storeManagerInterface->getStore()->getId();
		$AIProducts = [];
		if($this->helperData->IsAppFashionbasedEnable()){
			$AIProducts = $this->helperData->getFashionbasedProduct($item_id);
    		//$AIProducts = $this->helperData->getAIProducts();
		}
     	$limit = $this->helperData->getLimit();
     	$title = $this->helperData->getfashionbasedTitle($storeId);
     	$productSlider = $this->productSliderFactory->create();
     	$productSlider->setProductType('custom');
     	$productSlider->setProductIds($AIProducts);
     	$products = $this->productRepository->getList($productSlider, $limit)->getItems();
     	$response = $this->recommenderBuilderResultsInterface->setProducts($products);
     	$response = $response->setTitle($title);
     	return $response;
	}


	/**
     * @inheritDoc
     * @throws \Exception
     */
	public function userbase($customerId)
	{
		$storeId = (int)$this->storeManagerInterface->getStore()->getId();
		$AIProducts = [];
		if($this->helperData->IsAppUserbasedEnable()){
			$AIProducts = $this->helperData->getUserbasedProduct($customerId);
    		//$AIProducts = $this->helperData->getAIProducts();
		}
     	$limit = $this->helperData->getLimit();
     	$title = $this->helperData->getUserbasedTitle($storeId);
     	$productSlider = $this->productSliderFactory->create();
     	$productSlider->setProductType('custom');
     	$productSlider->setProductIds($AIProducts);
     	$products = $this->productRepository->getList($productSlider, $limit)->getItems();
     	$response = $this->recommenderBuilderResultsInterface->setProducts($products);
     	$response = $response->setTitle($title);
     	return $response;
	}


	/**
     * @inheritDoc
     * @throws \Exception
     */
	public function marketbase($customerId)
	{

		$storeId = (int)$this->storeManagerInterface->getStore()->getId();
		$AIProducts = [];
		if($this->helperData->IsAppMarketbasedEnable()){
			$quote = $this->cartManagement->getCartForCustomer($customerId);
			 $cartitems =  $quote->getAllVisibleItems();
            $productID = '';
            foreach($cartitems as $key => $cartitem){
                if($key > 0){
                    $productID.= '&';
                }
                $productID.= 'items_list='.$cartitem->getProductId();
                //break;
            }
			$AIProducts = $this->helperData->getMarketbasedProduct($productID);
    		//$AIProducts = $this->helperData->getAIProducts();
		}
     	$limit = $this->helperData->getLimit();
     	$title = $this->helperData->getMarketbasedTitle($storeId);
     	$productSlider = $this->productSliderFactory->create();
     	$productSlider->setProductType('custom');
     	$productSlider->setProductIds($AIProducts);
     	$products = $this->productRepository->getList($productSlider, $limit)->getItems();
     	$response = $this->recommenderBuilderResultsInterface->setProducts($products);
     	$response = $response->setTitle($title);
     	return $response;
	}

		/**
     * @inheritDoc
     * @throws \Exception
     */
	public function eventbase($customerId = null)
	{

		$gendertxt = "unspecified";
		if($customerId){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
			//Get gender value like 1,2,3
			$genderValue = $customer->getGender();

		  if($genderValue == '1'){
	         $gendertxt = "male";
	        }
	        if($genderValue == '2'){
	            $gendertxt = "female";
	        }
		}
		$title = "";
		$language = "English";
		$storeId = (int)$this->storeManagerInterface->getStore()->getId();
		if($storeId == 2){
            $language = "Arabic";
       }
		$AIProducts = [];
		if($this->helperData->IsAppEventbasedEnable()){
			$EAIProducts = $this->helperData->getEventbasedProduct($language,$gendertxt);
			if(!empty($EAIProducts)){
				$AIProducts = $EAIProducts['recommendation'];
				$title = $EAIProducts['event'];
			}
    		//$AIProducts = $this->helperData->getAIProducts();
		}
     	$limit = $this->helperData->getLimit();
     	$productSlider = $this->productSliderFactory->create();
     	$productSlider->setProductType('custom');
     	$productSlider->setProductIds($AIProducts);
     	$products = $this->productRepository->getList($productSlider, $limit)->getItems();
     	$response = $this->recommenderBuilderResultsInterface->setProducts($products);
     	$response = $response->setTitle($title);
     	return $response;
	}
}