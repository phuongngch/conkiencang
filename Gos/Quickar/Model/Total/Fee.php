<?php
namespace Gos\Quickar\Model\Total;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address;
use Magento\Shipping\Model\Rate\Result;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    protected $catalogSession = null;

    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator,
                                \Magento\Catalog\Model\Session $catalogSession)
    {
        $this->quoteValidator = $quoteValidator;
        $this->catalogSession = $catalogSession;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $qc_checkout_data = $this->catalogSession->getData('qc_checkout_data');


        if($shippingAssignment->getData('shipping')->getData('address')->getData('address_type') == 'billing'){
            return $this;
        }
        $exist_amount = 0; //$quote->getFee();
        $amount_credit = isset($qc_checkout_data['amount_credit'])?$qc_checkout_data['amount_credit']:0;
        $fee = -($amount_credit); //Excellence_Fee_Model_Fee::getFee();
        $balance = $fee - $exist_amount;

        $total->setTotalAmount('fee', $balance);
        $total->setBaseTotalAmount('fee', $balance);

        $total->setFee($balance);
        $total->setBaseFee($balance);

        $total->setGrandTotal($total->getGrandTotal() + $balance);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $balance);

//        $total->setDiscountAmount($discountAmount);
//        $total->setBaseDiscountAmount($discountAmount);
//        $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
//        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

        return $this;
    }
//
//    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
//    {
//        $total->setTotalAmount('subtotal', 0);
//        $total->setBaseTotalAmount('subtotal', 0);
//        $total->setTotalAmount('tax', 0);
//        $total->setBaseTotalAmount('tax', 0);
//        $total->setTotalAmount('discount_tax_compensation', 0);
//        $total->setBaseTotalAmount('discount_tax_compensation', 0);
//        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
//        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
//        $total->setSubtotalInclTax(0);
//        $total->setBaseSubtotalInclTax(0);
//    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $qc_checkout_data = $this->catalogSession->getData('qc_checkout_data');
        $duration = isset($qc_checkout_data['duration']) ? $qc_checkout_data['duration'] : 0;
        $init_payment = isset($qc_checkout_data['init_payment']) ? $qc_checkout_data['init_payment'] : 0;
        $monthly_payment = isset($qc_checkout_data['monthly_payment']) ? $qc_checkout_data['monthly_payment'] : 0;
        $trade_in = isset($qc_checkout_data['trade_in']) ? $qc_checkout_data['trade_in'] : 0;
        $amount_credit = isset($qc_checkout_data['amount_credit']) ? $qc_checkout_data['amount_credit'] : 0;
        $rate = isset($qc_checkout_data['rate']) ? $qc_checkout_data['rate'] : 0;
        $amount_owning = isset($qc_checkout_data['amount_owning']) ? $qc_checkout_data['amount_owning'] : 0;
        $total_amount = isset($qc_checkout_data['total_amount']) ? $qc_checkout_data['total_amount'] : 0;
        $product_id = isset($qc_checkout_data['product']) ? $qc_checkout_data['product'] : 0;
        $year_transmission = '';
        $product_name = '';

        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
            $year_transmission = $product->getData('year').' - '.$product->getData('transmission_type');
            $product_name = $product->getName();
        }

        $option_price = isset($qc_checkout_data['option_price']) ? $qc_checkout_data['option_price'] : 0;
        $option_color = isset($qc_checkout_data['option_color']) ? $qc_checkout_data['option_color'] : '';
        $accessory_price = isset($qc_checkout_data['accessory_price']) ? $qc_checkout_data['accessory_price'] : 0;

        return [
            'code' => 'fee',
            'label' => __('Amount of Credit'),
            'value' => [
                $duration,
                $init_payment,
                $monthly_payment,
                $trade_in,
                $amount_credit,
                $rate,
                $amount_owning,
                $total_amount,
                $product_name,
                $option_price,
                $option_color,
                $accessory_price,
                $year_transmission,
            ]
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Amount of Credit');
    }
}
