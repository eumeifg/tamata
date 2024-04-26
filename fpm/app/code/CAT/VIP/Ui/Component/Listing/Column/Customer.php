<?php

namespace CAT\VIP\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Customer extends Column
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, CustomerRepositoryInterface $customerRepository, UrlInterface $urlInterface, array $components = [], array $data = [])
    {
        $this->_customerRepository = $customerRepository;
        $this->urlInterface = $urlInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $customer = $this->_customerRepository->getById($item['customer_id']);
                } catch (\Exception $e) {
                    continue;
                }
                $html = '';
                $html.='<a target="_blank" href="'.$this->urlInterface->getUrl('customer/index/edit/', ['id' => $customer->getId()]).'">'.$customer->getFirstname().' '.$customer->getLastname().'</a>
                         <strong>(ID: '.$customer->getId().')</strong>';

                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}
