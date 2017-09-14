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
namespace Gos\Tradein\Block\Adminhtml\Tradein\Edit\Tab;

class Tradein extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Country options
     * 
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_booleanOptions;

    /**
     * constructor
     * 
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->_booleanOptions = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Gos\Tradein\Model\Tradein $tradein */
        $tradein = $this->_coreRegistry->registry('gos_tradein_tradein');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('tradein_');
        $form->setFieldNameSuffix('tradein');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Vehicle Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($tradein->getId()) {
            $fieldset->addField(
                'tradein_id',
                'hidden',
                ['name' => 'tradein_id']
            );
        }
        $fieldset->addField(
            'state',
            'text',
            [
                'name'  => 'state',
                'label' => __('State'),
                'title' => __('State'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'license',
            'text',
            [
                'name'  => 'license',
                'label' => __('License'),
                'title' => __('License'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'number_kms',
            'text',
            [
                'name'  => 'number_kms',
                'label' => __('Number Kms'),
                'title' => __('Number Kms'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'year',
            'text',
            [
                'name'  => 'year',
                'label' => __('Year'),
                'title' => __('Year'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'vehicle_make',
            'text',
            [
                'name'  => 'vehicle_make',
                'label' => __('Vehicle Make'),
                'title' => __('Vehicle Make'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'vehicle_model',
            'text',
            [
                'name'  => 'vehicle_model',
                'label' => __('Vehicle Model'),
                'title' => __('Vehicle Model'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'condition',
            'text',
            [
                'name'  => 'condition',
                'label' => __('Condition'),
                'title' => __('Condition'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'one_owner',
            'select',
            [
                'name'  => 'one_owner',
                'label' => __('One Owner'),
                'title' => __('One Owner'),
                'required' => true,
                'values' => $this->_booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'never_written_off',
            'select',
            [
                'name'  => 'never_written_off',
                'label' => __('Never Written Off'),
                'title' => __('Never Written Off'),
                'required' => true,
                'values' => $this->_booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'commercially',
            'select',
            [
                'name'  => 'commercially',
                'label' => __('Commercially'),
                'title' => __('Commercially'),
                'required' => true,
                'values' => $this->_booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'vin',
            'text',
            [
                'name'  => 'vin',
                'label' => __('Vin'),
                'title' => __('Vin'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'nvic',
            'text',
            [
                'name'  => 'nvic',
                'label' => __('Nvic'),
                'title' => __('Nvic'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'valuatation',
            'text',
            [
                'name'  => 'valuatation',
                'label' => __('Valuatation'),
                'title' => __('Valuatation'),
                'required' => true,
            ]
        );

        $tradeinData = $this->_session->getData('gos_tradein_tradein_data', true);
        if ($tradeinData) {
            $tradein->addData($tradeinData);
        } else {
            if (!$tradein->getId()) {
                $tradein->addData($tradein->getDefaultValues());
            }
        }
        $form->addValues($tradein->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Tradein');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
