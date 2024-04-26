<?php

namespace MDC\Catalog\Plugin\Catalog\Block\Sellerhtml\Listing\Nonlive;

/**
 * Class Disapproved
 * @package MDC\Catalog\Plugin\Catalog\Block\Sellerhtml\Listing\Nonlive
 */
class Disapproved
{

    /**
     * @param \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Disapproved $subject
     * @param $result
     * @return mixed
     */
    public function afterGetDisapprovedProductRequests(
        \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Disapproved $subject, $result
    ) {
        $result->getSelect()->columns('mdvprs.attributes');
        return $result;
    }
}