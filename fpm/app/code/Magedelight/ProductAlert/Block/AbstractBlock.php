<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\ResourceConnection;

class AbstractBlock extends \Magento\Framework\View\Element\Template
{

    public $_title;
    public $_type;
    protected $_resource;
    public $_objectManager;
    protected $_customerSession;
    public $_scopeConfig;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurableProduct;
    
     /**
      * @var \Magento\Catalog\Api\ProductRepositoryInterface
      */
    protected $productRepository;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ResourceConnection $resource
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->configurableProduct = $configurableProduct;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->setTemplate('subscr.phtml');
        $this->_loadCollection();
    }


    protected function _loadCollection()
    {
        $tableName = 'product_alert_' . $this->_type;
        $alertTable = $this->_resource->getTableName($tableName);
        //$this->_resource->getTableName($tableName);
        $collection = $this->productFactory->create()->getCollection();
        $collection->addAttributeToSelect('name');

        $select = $collection->getSelect();
        $entityIdName = 'alert_' . $this->_type . '_id';
        $select->joinInner(
            ['s' => $alertTable],
            's.product_id = e.entity_id',
            ['add_date', $entityIdName, 'parent_id']
        )
            ->where('s.status=0')
            ->where(
                'customer_id=? OR email=?',
                $this->_customerSession->getCustomerId(),
                $this->_customerSession->getCustomer()->getEmail()
            )
            ->group(['s.product_id']);
        $stockFlag = 'has_stock_status_filter';
        $collection->setFlag($stockFlag, true);

        $this->setSubscriptions($collection);
    }

    public function getRemoveUrl($order)
    {
        $entityIdName = 'alert_' . $this->_type . '_id';
        $id = $order->getData($entityIdName);
        return $this->getUrl(
            'productalert/' . $this->_type . '/remove',
            ['item' => $id]
        );
    }

    public function getProductUrl($_order)
    {
        $product = $this->getProduct($_order->getEntityId());
        $url = $product->getUrlModel()->getUrl($product);
        return $url;
    }

    public function getSupperAttributesByChildId($id)
    {
        $parentIds = $this->configurableProduct->getParentIdsByChild($id);
        $attributes = [];
        if (!empty($parentIds)) {
            $product = $this->productRepository->getById($parentIds[0]);
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        }
        return $attributes;
    }

    public function getUrlHash($id)
    {
        $attributes = $this->getSupperAttributesByChildId($id);
        $url = '';
        if (!empty($attributes)) {
            $hash = '';
            foreach ($attributes as $_attribute) {
                $attributeCode = $_attribute->getData('product_attribute')->getData('attribute_code');
                $value = $this->productRepository->getById($id)->getData($attributeCode);
                $hash .= '&' . $_attribute->getData("attribute_id") . "=" . $value;
            }
            $url .= '#' . substr($hash, 1);//remove first &
        }
        return $url;
    }

    public function getUrlProduct($product)
    {
        $parentIds = $this->configurableProduct->getParentIdsByChild($product->getId());
        if (!empty($parentIds) && isset($parentIds[0])) {
            $parent = $this->productRepository->getById($parentIds[0]);
            $baseUrl = $parent->getUrlModel()->getUrl($parent);
        } else {
            $baseUrl = $product->getUrlModel()->getUrl($product);
        }
        $hash = $this->getUrlHash($product->getId());
        $url = $baseUrl . $hash;
        return $url;
    }

    public function getProduct($id)
    {
        $product = $this->productRepository->getById($id);
        return $product;
    }
}
