<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Validator
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml
 */
abstract class Validator extends Action
{
    /**
     * Success status
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Error status
     */
    const STATUS_ERROR = 'error';

    /**
     * @var Context
     */
    protected $context;

    /**
     * Validator constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Ktpl_ElasticSearch::search');
        $resultPage->getConfig()->getTitle()->prepend(__('Validate Search'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     *
     * return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_ElasticSearch::search');
    }
}
