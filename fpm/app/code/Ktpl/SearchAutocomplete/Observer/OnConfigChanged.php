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

namespace Ktpl\SearchAutocomplete\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Ktpl\SearchAutocomplete\Service\JsonConfigService;

/**
 * Class OnConfigChanged
 *
 * @package Ktpl\SearchAutocomplete\Observer
 */
class OnConfigChanged implements ObserverInterface
{
    /**
     * @var JsonConfigService
     */
    private $jsonConfigService;

    /**
     * OnConfigChanged constructor.
     *
     * @param JsonConfigService $jsonConfigService
     */
    public function __construct(
        JsonConfigService $jsonConfigService
    ) {
        $this->jsonConfigService = $jsonConfigService;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->jsonConfigService->ensure(JsonConfigService::AUTOCOMPLETE);
        $this->jsonConfigService->ensure(JsonConfigService::TYPEAHEAD);
    }
}
