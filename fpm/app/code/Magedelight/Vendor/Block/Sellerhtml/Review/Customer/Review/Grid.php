<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Sellerhtml\Review\Customer\Review;

class Grid extends \Magedelight\Vendor\Block\Sellerhtml\Review\Customer\Review\AbstractRatings
{
    const PAGING_LIMIT = 10;

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCustomerReviews()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.customer.ratings.pager'
            );

            $limit = $this->getRequest()->getParam('limit', false);

            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');

            if (!$limit) {
                $limit = self::PAGING_LIMIT;
                $pager->setPage(1);
            }

            $pager->setLimit($limit)
                ->setCollection(
                    $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param $review
     * @return string
     */
    public function getViewUrl($review)
    {
        return $this->getUrl(
            'rbvendor/review_customer/viewfeedback',
            ['vendor_rating_id' => $review->getId(), 'tab' => '4,1']
        );
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbvendor/review_customer/reviews');
    }

    /**
     *
     * @param $date
     * @return date|string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
    }
}
