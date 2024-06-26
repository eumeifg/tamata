<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchLanding
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchLanding\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Ktpl\SearchLanding\Api\Data\PageInterface;

/**
 * Class View
 *
 * @package Ktpl\SearchLanding\Block
 */
class View extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * View constructor.
     *
     * @param Registry $registry
     * @param Template\Context $context
     */
    public function __construct(
        Registry $registry,
        Template\Context $context
    )
    {
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * Prepare layout
     *
     * @return Template
     */
    protected function _prepareLayout()
    {
        /** @var PageInterface $page */
        $page = $this->registry->registry('search_landing_page');

        $this->pageConfig->getTitle()->set($page->getTitle());
        $this->pageConfig->setKeywords($page->getMetaKeywords());
        $this->pageConfig->setDescription($page->getMetaDescription());

        return parent::_prepareLayout();
    }
}
