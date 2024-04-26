<?php

declare(strict_types=1);

namespace CAT\SearchPage\Block\Adminhtml\Items\Edit;

use Magento\Backend\Block\Widget\Context;
use CAT\SearchPage\Model\SearchPageRepository;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

  /**
   * @var SearchPageRepository
   */
    protected $searchRepository;

  /**
   * GenericButton constructor.
   * @param Context $context
   * @param SearchPageRepository $searchRepository
   */
    public function __construct(
        Context $context,
        SearchPageRepository $searchRepository
    ) {
        $this->context = $context;
        $this->searchRepository = $searchRepository;
    }


    public function getSearchPageId()
    {
        try {
            return $this->searchRepository->getById(
                $this->context->getRequest()->getParam('search_page_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
