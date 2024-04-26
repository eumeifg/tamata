<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;

/**
 * Class QuoteRepositoryPlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\Quote
 */
class QuoteRepositoryPlugin
{
    /**
     * RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Throw exception to display a message
     *
     * @param CartRepositoryInterface $subject
     * @param $result
     * @param Quote $quote
     * @return null
     * @throws LocalizedException
     */
    public function afterSave(CartRepositoryInterface $subject, $result, $quote)
    {
        if ($quote->getAwRafThrowException()) {
            throw new LocalizedException(__('Additional discount cannot be applied with referral discount'));
        }

        return $result;
    }
}
