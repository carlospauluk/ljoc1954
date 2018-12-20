<?php
class ModelExtensionTotalPaymentDiscount extends Model {
    public function getTotal($total) {
        if ($this->config->get('total_payment_discount_status')) {
            if (isset($this->session->data['payment_method']['code'])) {
                $this->language->load('extension/total/payment_discount');
                $code = $this->session->data['payment_method']['code'];
                $subtotal = $this->cart->getSubTotal();
                $payments = $this->config->get('total_payment_discount_list');
                if (is_array($payments)) {
                    foreach ($payments as $payment) {
                        if (($payment['payment'] == $code) && ($subtotal >= $payment['total']) && ($payment['discount'] > 0)) {
                            $percentual = $payment['discount'];
                            $desconto = ($subtotal*$percentual)/100;

                            $total['totals'][] = array( 
                                'code' => 'payment_discount',
                                'title' => sprintf($this->language->get('text_desconto'), $percentual),
                                'value' => -$desconto,
                                'sort_order' => $this->config->get('total_payment_discount_sort_order')
                            );

                            $total['total'] -= $desconto;
                        }
                    }
                }
            }
        }
    }
}