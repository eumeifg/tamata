<?php 

declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory as ProductLabelCollectionFactory;

class SaleOffer implements ResolverInterface
{		
	protected $productRepository;
	protected $productAttributeRepository;
	protected $productLabelCollectionFactory;
	protected $imageHelper;

	public function __construct(    	
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
		ProductLabelCollectionFactory $productLabelCollectionFactory,
		\Ktpl\ProductLabel\Model\ImageLabel\Image $imageHelper
         
    ) {        
        $this->productRepository = $productRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productLabelCollectionFactory = $productLabelCollectionFactory;
        $this->imageHelper = $imageHelper;
    }

	/**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
      
        $imgUrl = $this->imageHelper->getBaseUrl() . '/';

        $product = $value['model'];
        $productId = $product->getId();
        $saleLabel = "";
	    	$currProduct = $this->productRepository->getById($productId);
	    	$isOffered = $currProduct->getData('ktpl_offer');

	    	$attribute = $this->productAttributeRepository->get('ktpl_offer');
    		$ktplOfferAttrId = $attribute->getAttributeId();

    		if($isOffered === "1"){
    			
    			$productLabelsCollection = $this->productLabelCollectionFactory->create();
            	$productLabel = $productLabelsCollection	                
	                ->addFieldToFilter('attribute_id', $ktplOfferAttrId)
	                ->addFieldToFilter('is_active', true)
	                ->getData();

	            if($productLabel[0]['labeltype'] === "image"){

	            	$saleLabel = $imgUrl.$productLabel[0]['image'];
	            } 
    		}

        return $saleLabel;

    }


}
