<?php
namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class ExecutiveEvent extends Column
{

    protected $_searchCriteria;
    protected $_customfactory;
    protected $userFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $criteria,
        \Magento\User\Model\UserFactory $userFactory,
        array $components = [], array $data = [])
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_searchCriteria  = $criteria;
        $this->userFactory = $userFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $item) {
                $user = $this->userFactory->create()->load($item['user_id']);
                $dataSource['data']['items'][$key]['user_id'] = $user->getName();
            }
        }
        return $dataSource;
    }
}
