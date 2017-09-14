<?php

/**
 * Created by Sublime 3.
 * User: nguyenngo
 * Date: 17/1/2016
 * Time: 2:19 PM
 */
namespace Gos\Quickar\Helper;

class Filter {

    public static function filterFeature($productFactory, $eavConfig, $params) {
        
        $result = [];

        $propertyCounts = [
            'auto' => ['s_gear_type','Automatic'],
            'manual' => ['s_gear_type' , 'Manual'],
            'petrol' => ['s_fuel_type' , 'Petrol'],
            'diesel' => ['s_fuel_type' , 'Diesel'],
            'small_medium' => ['weight', ''],
            'large' => ['weight', '1000'],
            'suv'       => ['f_body_type' , 'SUV'],
            'spark'       => ['s_car_model' , 'Spark'],
            'barina'       => ['s_car_model' , 'Barina'],
            'astra'       => ['s_car_model' , 'Astra'],
            'trax'       => ['s_car_model' , 'Trax'],
            'commodore'       => ['s_car_model' , 'Commodore'],            
        ];


        foreach($propertyCounts as $key => $propertyFilter){
            $attribute = $eavConfig->getAttribute('catalog_product', $propertyFilter[0]);
            $optionId = $attribute->getSource()->getOptionId($propertyFilter[1]);
            $optionValue = $attribute->getSource()->getOptionText($optionId);
            //var_dump($optionValue);
           $collection = $productFactory->create()->getCollection();
           $collection->addAttributeToFilter($propertyFilter[0], $optionId);
           //var_dump($collection);
//            $collection->addAttributeToFilter('type_id', 'bundle');

//            foreach($params as $k => $value) {
//                if($k == 'min_price'){
//                    $collection->addAttributeToFilter($k, array('lt' => $value));
//                }else{
//                    $collection->addAttributeToFilter($k, $value);
//                }
//            }

            $result[$key] = [0, $propertyFilter[0], $optionId];
        }

        return $result;
        // var_dump($result);

    }


    public static function getPropertyCounts($items, $eavConfig) {

        $propertyCounts = [
            'auto' => ['s_gear_type','Automatic'],
            'manual' => ['s_gear_type' , 'Manual'],
            'petrol' => ['s_fuel_type' , 'Petrol'],
            'diesel' => ['s_fuel_type' , 'Diesel'],
            'small_medium' => ['weight', ''],
            'large' => ['weight', '1000'],
            'suv'       => ['f_body_type' , 'SUV'],
            'spark'       => ['s_car_model' , 'Spark'],
            'barina'       => ['s_car_model' , 'Barina'],
            'astra'       => ['s_car_model' , 'Astra'],
            'trax'       => ['s_car_model' , 'Trax'],
            'commodore'       => ['s_car_model' , 'Commodore'],
        ];



        $result = [];
        foreach($propertyCounts as $key => $propertyFilter){
            $result[$key] = 0;
            foreach($items as $item){
                if(empty($propertyFilter[1])){
                    continue;
                }
                $attribute = $eavConfig->getAttribute('catalog_product', $propertyFilter[0]);
                $optionId = $attribute->getSource()->getOptionId($propertyFilter[1]);

                if($item->getData($propertyFilter[0]) == $optionId){
                    $result[$key] ++;
                }
            }
        }

        return $result;

    }
    
}