<?php

namespace MDC\Catalog\Plugin\Catalog\Block\Sellerhtml\Listing\Nonlive;
/**
 * Class Pending
 * @package MDC\Catalog\Plugin\Catalog\Block\Sellerhtml\Listing\Nonlive
 */
class Pending
{
    /**
     * @param \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Pending $subject
     * @param $result
     * @return mixed
     */
    public function afterGetPendingProductRequests(
        \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Pending $subject,
        $result
    ) {
        $result->getSelect()->columns('mdvprs.attributes');
        return $result;
    }
}