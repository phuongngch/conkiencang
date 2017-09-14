<?php

/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 12/9/2016
 * Time: 11:03 AM
 */
namespace Gos\Quickar\Helper;
class Price
{
    protected static $financeTable = null;

    public static function calPMT($interest, $term, $loan, $fv = 0, $type = 0) {
        if ($term == 0) return $term;

        $interest = $interest / 1200;

        if (!isset($term)) {
            $term = 48;
        }

//        $xp =  pow((1 + $interest), $term);
//        $amount = $interest * -$loan * pow((1 + $interest), $term) / (1 - pow((1 + $interest), $term));
//        return $amount;

        $xp = pow( (1 + $interest), $term);
        $result = ( $loan * $interest * $xp / ( $xp - 1) +  $interest / ($xp - 1) * $fv) * ( $type == 0 ? 1 : 1/( $interest + 1));
        //$result = round($result * 100) / 100;
        return $result;
    }

    public static function getMonthlyPrice($product, $duration, $initPayment, $tradeInValue, $payPerMonth, $numMiles){
        if($product->getTypeId() == 'bundle'){
            $price = $product->getData('min_price');
        }else{
            $price = $product->getData('price');
        }

        $interest_rate = is_null($product->getData('interest_rate'))?6.9:$product->getData('interest_rate');

        //get standing finance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        $standingFinance = $checkoutSession->getTradeinOwing();
        $standingFinance = !empty($standingFinance)?$standingFinance:0;

        $payAmount = intval($price) - intval($initPayment) - intval($tradeInValue) + $standingFinance;

        if($payPerMonth != 500 || $numMiles <=  200000){//balloon payment
            //$fv = static::getFutureValue($payAmount, $duration);
            $balloonValue = static::getBalloonValue($price, $duration);
            $price_month = self::calPMT($interest_rate, $duration, $payAmount, -$balloonValue);
            if($price_month < 0){//swith to car loan payment if < 0
                $price_month = self::calPMT($interest_rate, $duration, $payAmount);
            }
        }else{//car loan
            $price_month = self::calPMT($interest_rate, $duration, $payAmount);
        }

        if($price_month < 0){
            $price_month = 0;
        }

        //var_dump([$interest_rate, $duration, $payAmount, $price_month, $balloonValue]);exit;
        return $price_month;
    }

    private static function getFutureValue($curValue, $term){
        $depreciation = 0.21;
        return $curValue * (1 - $depreciation) * $term / 12 ;
    }

    private static function getBalloonValue($curValue, $term ){
        $financeTable = \Gos\Quickar\Helper\Price::getFinanceTable();
        $balloon = 60;
        if(isset($financeTable[$term])){
            $balloon = $financeTable[$term];
        }
        return $curValue * $balloon / 100;
    }
 
    public static function getFinanceTable()
    {
        if (!isset(static::$financeTable)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $financeTable = [];
            $terms = [24, 36, 42, 48, 60];
            foreach($terms as $term){
                $item = $objectManager->create('Finance\Offers\Model\ResourceModel\Financeoffer\Collection')->addFieldToFilter('term', $term)->addFieldToFilter('promo', 'BALL2016')->getFirstItem();
                $financeTable[$term] = $item->getData('future_value');
            }
            static::$financeTable = $financeTable;
        }
        return static::$financeTable;
    }

}