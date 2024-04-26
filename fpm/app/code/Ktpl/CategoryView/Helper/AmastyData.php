<?php

namespace Ktpl\CategoryView\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class AmastyData extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) 
    {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_categoryRepository = $categoryRepository;
    }

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    public function getCategory($categoryId)
    {
      return $this->_categoryRepository->get($categoryId);
    }
}
