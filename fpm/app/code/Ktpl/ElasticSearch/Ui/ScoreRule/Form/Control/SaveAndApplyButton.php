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

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;

/**
 * Class SaveAndApplyButton
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Form\Control
 */
class SaveAndApplyButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Apply Now'),
                'class' => 'apply',
                'on_click' => 'window.location.href="' . $this->getApplyUrl() . '"',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get apply url
     *
     * @return string
     */
    public function getApplyUrl()
    {
        return $this->getUrl('*/*/apply', [ScoreRuleInterface::ID => $this->getId(), 'back' => true]);
    }
}
