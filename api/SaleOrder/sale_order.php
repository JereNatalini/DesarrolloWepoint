<?php

class SalesOrder
{
    private $customer_id;
    private $reference_number;
    private $date;
    private $shipment_date;
    private $custom_fields;
    private $is_inclusive_tax;
    private $line_items;
    private $notes;
    private $terms;
    private $is_discount_before_tax;
    private $discount_type;
    private $adjustment_description;
    private $pricebook_id;
    private $order_status;
    private $template_id;
    private $documents;
    private $shipping_address_id;
    private $billing_address_id;
    private $zcrm_potential_id;
    private $payment_terms;
    private $payment_terms_label;
    private $is_adv_tracking_in_package;

    public function __construct(
        $customer_id,
        $reference_number,
        $date,
        $shipment_date,
        $custom_fields,
        $is_inclusive_tax,
        $line_items,
        $notes,
        $terms,
        $is_discount_before_tax,
        $discount_type,
        $adjustment_description,
        $pricebook_id,
        $order_status,
        $template_id,
        $documents,
        $shipping_address_id,
        $billing_address_id,
        $zcrm_potential_id,
        $payment_terms,
        $payment_terms_label,
        $is_adv_tracking_in_package
    ) {
        $this->customer_id = $customer_id;
        $this->reference_number = $reference_number;
        $this->date = $date;
        $this->shipment_date = $shipment_date;
        $this->custom_fields = $custom_fields;
        $this->is_inclusive_tax = $is_inclusive_tax;
        $this->line_items = $line_items;
        $this->notes = $notes;
        $this->terms = $terms;
        $this->is_discount_before_tax = $is_discount_before_tax;
        $this->discount_type = $discount_type;
        $this->adjustment_description = $adjustment_description;
        $this->pricebook_id = $pricebook_id;
        $this->order_status = $order_status;
        $this->template_id = $template_id;
        $this->documents = $documents;
        $this->shipping_address_id = $shipping_address_id;
        $this->billing_address_id = $billing_address_id;
        $this->zcrm_potential_id = $zcrm_potential_id;
        $this->payment_terms = $payment_terms;
        $this->payment_terms_label = $payment_terms_label;
        $this->is_adv_tracking_in_package = $is_adv_tracking_in_package;
    }
}

?>