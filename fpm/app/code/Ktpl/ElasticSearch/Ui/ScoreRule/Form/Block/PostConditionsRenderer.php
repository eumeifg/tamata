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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Form\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class PostConditionsRenderer
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Form\Block
 */
class PostConditionsRenderer implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Render post conditions
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        /** @var \Ktpl\ElasticSearch\Model\ScoreRule\Rule $rule */
        $rule = $element->getRule();
        if ($rule && $rule->getPostConditions()) {
            return $rule->getPostConditions()->asHtmlRecursive();
        }
        return '';
    }
}
