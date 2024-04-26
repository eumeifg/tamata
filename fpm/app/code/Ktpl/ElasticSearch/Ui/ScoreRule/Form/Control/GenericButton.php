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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Form\Control;

use Magento\Backend\Block\Widget\Context;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;

/**
 * Class GenericButton
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Form\Control
 */
class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    /**
     * GenericButton constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        $this->context = $context;
    }

    /**
     * Get request param id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam(ScoreRuleInterface::ID);
    }

    /**
     * Get requested url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
