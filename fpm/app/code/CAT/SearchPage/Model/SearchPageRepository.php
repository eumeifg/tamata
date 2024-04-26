<?php
namespace CAT\SearchPage\Model;

use CAT\SearchPage\Model\ResourceModel\SearchPage;
use CAT\SearchPage\Model\SearchPageFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class SearchPageRepository{

  private $searchPageFactory;

  private $searchPage;

  public function __construct(
    SearchPageFactory $searchPageFactory,
    SearchPage $searchPage
  )
  {
    $this->searchPageFactory = $searchPageFactory;
    $this->searchPage = $searchPage;
  }

  public function getById($searchPageId){
    $searchPageResouce = $this->searchPageFactory->create();
    $this->searchPage->load($searchPageResouce, $searchPageId);
    if ($searchPageResouce->getSearchPageId()){
      throw new NoSuchEntityException(__('the search page with the "%1" ID does not exist', $searchPageId));
    }
    return $searchPageResouce;
  }
}
