<?php
/**
 * Gos_Tradein extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Gos
 *                     @package   Gos_Tradein
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Gos\Tradein\Model;

/**
 * @method Tradein setState($state)
 * @method Tradein setLicense($license)
 * @method Tradein setNumberKms($numberKms)
 * @method Tradein setYear($year)
 * @method Tradein setVehicleMake($vehicleMake)
 * @method Tradein setVehicleModel($vehicleModel)
 * @method Tradein setCondition($condition)
 * @method Tradein setOneOwner($oneOwner)
 * @method Tradein setNeverWrittenOff($neverWrittenOff)
 * @method Tradein setCommercially($commercially)
 * @method Tradein setVin($vin)
 * @method Tradein setNvic($nvic)
 * @method Tradein setValuatation($valuatation)
 * @method mixed getState()
 * @method mixed getLicense()
 * @method mixed getNumberKms()
 * @method mixed getYear()
 * @method mixed getVehicleMake()
 * @method mixed getVehicleModel()
 * @method mixed getCondition()
 * @method mixed getOneOwner()
 * @method mixed getNeverWrittenOff()
 * @method mixed getCommercially()
 * @method mixed getVin()
 * @method mixed getNvic()
 * @method mixed getValuatation()
 * @method Tradein setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Tradein setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Tradein extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'gos_tradein_tradein';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'gos_tradein_tradein';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'gos_tradein_tradein';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gos\Tradein\Model\ResourceModel\Tradein');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
