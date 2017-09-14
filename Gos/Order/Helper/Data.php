<?php
namespace Gos\Order\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function infoFunc()
    {
        echo "This is Helper of Module Gos_Order - Gos\Order\Helper";
    }

    public function checkAllKeysInArray($array, $keys)
    {
        $array_keys = array_keys($array);

        foreach($keys as $key) {
            if (in_array($key, $array_keys)) continue;  // already set
            $array[$key] = '';
        }

        return $array;
    }
}
