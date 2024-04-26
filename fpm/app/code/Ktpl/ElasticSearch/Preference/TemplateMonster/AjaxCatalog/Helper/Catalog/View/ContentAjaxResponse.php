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

namespace Ktpl\ElasticSearch\Preference\TemplateMonster\AjaxCatalog\Helper\Catalog\View;

if (class_exists('\TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse')) {
    require_once('ContentAjaxResponseMediatorExtended.php');
} else {
    require_once('ContentAjaxResponseMediatorSimple.php');
}

use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;

/**
 * Class ContentAjaxResponse
 *
 * @package Ktpl\ElasticSearch\Preference\TemplateMonster\AjaxCatalog\Helper\Catalog\View
 */
class ContentAjaxResponse extends ContentAjaxResponseMediator
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * ContentAjaxResponse constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Data $helperData
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_helperData = $helperData;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $pageFactory, $helperData);
    }

    /**
     * Render part of page for Ajax Catalog request.
     *
     * @param $subject
     * @param $proceed
     * @return mixed
     */
    public function getAjaxSearchResult($subject, $proceed)
    {
        $response = $subject->getResponse();

        $page = $this->_pageFactory->create();
        $result = [];

        try {
            $result['content'] = $page->getLayout()->renderElement('content');
            $result['layer'] = $page->getLayout()->renderElement('sidebar.main');
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = 'Can not finished request';
        }

        return $response->representJson(
            $this->_helperData->jsonEncode($result)
        );
    }
}
