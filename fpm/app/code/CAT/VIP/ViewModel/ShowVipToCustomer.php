<?php

namespace CAT\VIP\ViewModel;

use CAT\VIP\Model\VipCustomer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ShowVipToCustomer implements ArgumentInterface
{
    /**
     * @var VipCustomer
     */
    protected $vipCustomer;

    /**
     * @param VipCustomer $vipCustomer
     */
    public function __construct(
        VipCustomer $vipCustomer
    )
    {
        $this->vipCustomer = $vipCustomer;
    }

    public function showSomething()
    {

       $vipinfo = $this->vipCustomer->getVipForCustomer();

       if($vipinfo->getIsVip()){
        return '
                <div style="width: 20%;margin: 15px auto;">
                    <div class="set-size charts-container more_180">
                      <div class="pie-wrapper progress-90">
                        <span class="label">VIP</span>
                            <div class="pie">
                              <div class="left-side half-circle" style="transform: rotate(360deg)"></div>
                              <div class="right-side half-circle"></div>
                            </div>
                          <div class="shadow"></div>
                      </div>
                    </div>
                </div>';
       }
       else{
            $count = ($vipinfo->getThresholdVipOrderCount() - $vipinfo->getCustomerVipOrderCount());
            $percentage = (($vipinfo->getCustomerVipOrderCount() / $vipinfo->getThresholdVipOrderCount()) * 100);
            $percentage = 0;
            $classname = ($percentage > 50) ? 'more_180': '';
            return '<div style="width: 20%;margin: 15px auto;">
                        <div class="set-size charts-container not_vip '.$classname.'">
                          <div class="pie-wrapper progress-90">
                            <span class="label">NOT VIP</span>
                            <div class="pie">
                              <div class="left-side half-circle" style="transform: rotate('.($percentage*3.6).'deg)"></div>
                              <div class="right-side half-circle"></div>
                            </div>
                              <div class="shadow"></div>
                          </div>
                        </div>
                    </div>';
       }
    }
}
