<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ktpl\SearchAutocomplete\Model\Result;

/**
 * Class Suggest
 *
 * @package Ktpl\SearchAutocomplete\Controller\Ajax
 */
class Suggest extends Action
{
    /**
     * @var Result
     */
    private $result;

    /**
     * Suggest constructor.
     *
     * @param Result $result
     * @param Context $context
     */
    public function __construct(
        Result $result,
        Context $context
    ) {
        $this->result = $result;

        parent::__construct($context);
    }

    /**
     * Initialize ajax autocomplete result page
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->result->init();

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->setHeader('cache-control', 'max-age=86400, public, s-maxage=86400', true);
        $response->representJson(\Zend_Json::encode(
            $this->result->toArray()
        ));
    }
}