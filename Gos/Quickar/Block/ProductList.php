<?php

/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/6/2016
 * Time: 2:56 PM
 */
namespace Gos\Quickar\Block;

class ProductList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    public function getPriceHtml($product)
    {
        if($product->getTypeId() == 'bundle'){
            $price = $product->getData('min_price');
        }else{
            $price = $product->getData('price');
        }
        $html = '<div class="price-box"><div class="price">$'.number_format($price, 0, '.', ',') .'</div></div>';
        return $html;
    }

}