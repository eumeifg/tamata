<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Ui\Component\Form\Advocate;

use Magento\Ui\Component\Form\Field;

/**
 * Class CustomerInfo
 * @package Aheadworks\Raf\Ui\Component\Form\Advocate
 */
class CustomerInfo extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);

        if ($customerId = $dataSource['data']['customer_id']) {
            $dataSource['data']['customer_name_url'] = $this->getUrl(
                'customer/index/edit',
                ['id' => $customerId]
            );
        }
        return $dataSource;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route = '', $params = [])
    {
        return $this->getContext()->getUrl($route, $params);
    }
}
