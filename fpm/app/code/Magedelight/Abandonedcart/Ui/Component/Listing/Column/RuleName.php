<?php
namespace Magedelight\Abandonedcart\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RuleName extends Column
{
    protected $systemStore;
    protected $ruleFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magedelight\Abandonedcart\Model\RuleFactory $ruleFactory,
        array $components = [],
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order  = $this->ruleFactory->create()->load($item[$this->getData('name')]);
                $item[$this->getData('name')] =$order->getrule_name();
            }
        }
        return $dataSource;
    }
}
