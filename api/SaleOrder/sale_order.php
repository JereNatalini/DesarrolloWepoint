<?php

class SalesOrder {
    private $customer_id;
    private $contact_persons;
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

    // CONSTRUCTOR SIN PARAMETROS
    // Al usar este constructor se setean los valores default y quedan por setear customer_id,contact_persons, reference_number, line_items
    public function __construct() {
        $this->reference_number = "1234"; //posible numero de factura del cliente
        $this->date = date("Y-m-d");
        $this->shipment_date = date("Y-m-d", strtotime("+1 day"));
        $this->is_inclusive_tax = false;
        $this->is_discount_before_tax = "";
        $this->discount_type = "";
        $this->adjustment_description = "Ajuste";
        $this->order_status = "Confirmed";
        $this->payment_terms = "0";
        $this->payment_terms_label = "Pagadero a la recepción";
        $this->is_adv_tracking_in_package = true;
    }
    // Getters
    public function getCustomerId() {
        return $this->customer_id;
    }

    public function getContactPersons() {
        return $this->contact_persons;
    }

    public function getReferenceNumber() {
        return $this->reference_number;
    }

    public function getDate() {
        return $this->date;
    }

    public function getShipmentDate() {
        return $this->shipment_date;
    }

    public function getCustomFields() {
        return $this->custom_fields;
    }

    public function getIsInclusiveTax() {
        return $this->is_inclusive_tax;
    }

    public function getLineItems() {
        return $this->line_items;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getTerms() {
        return $this->terms;
    }

    public function getIsDiscountBeforeTax() {
        return $this->is_discount_before_tax;
    }

    public function getDiscountType() {
        return $this->discount_type;
    }

    public function getAdjustmentDescription() {
        return $this->adjustment_description;
    }

    public function getPricebookId() {
        return $this->pricebook_id;
    }

    public function getOrderStatus() {
        return $this->order_status;
    }

    public function getTemplateId() {
        return $this->template_id;
    }

    public function getDocuments() {
        return $this->documents;
    }

    public function getShippingAddressId() {
        return $this->shipping_address_id;
    }

    public function getBillingAddressId() {
        return $this->billing_address_id;
    }

    public function getZcrmPotentialId() {
        return $this->zcrm_potential_id;
    }

    public function getPaymentTerms() {
        return $this->payment_terms;
    }

    public function getPaymentTermsLabel() {
        return $this->payment_terms_label;
    }

    public function getIsAdvTrackingInPackage() {
        return $this->is_adv_tracking_in_package;
    }

    // Setters
    public function setCustomerId($customer_id) {
        $this->customer_id = $customer_id;
    }

    public function setContactPersons($contact_persons) {
        $this->contact_persons = $contact_persons;
    }

    public function setReferenceNumber($reference_number) {
        $this->reference_number = $reference_number;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setShipmentDate($shipment_date) {
        $this->shipment_date = $shipment_date;
    }

    public function setCustomFields($custom_fields) {
        $this->custom_fields = $custom_fields;
    }

    public function setIsInclusiveTax($is_inclusive_tax) {
        $this->is_inclusive_tax = $is_inclusive_tax;
    }

    public function setLineItems($line_items) {
        $this->line_items = $line_items;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    public function setTerms($terms) {
        $this->terms = $terms;
    }

    public function setIsDiscountBeforeTax($is_discount_before_tax) {
        $this->is_discount_before_tax = $is_discount_before_tax;
    }

    public function setDiscountType($discount_type) {
        $this->discount_type = $discount_type;
    }

    public function setAdjustmentDescription($adjustment_description) {
        $this->adjustment_description = $adjustment_description;
    }

    public function setPricebookId($pricebook_id) {
        $this->pricebook_id = $pricebook_id;
    }

    public function setOrderStatus($order_status) {
        $this->order_status = $order_status;
    }

    public function setTemplateId($template_id) {
        $this->template_id = $template_id;
    }

    public function setDocuments($documents) {
        $this->documents = $documents;
    }

    public function setShippingAddressId($shipping_address_id) {
        $this->shipping_address_id = $shipping_address_id;
    }

    public function setBillingAddressId($billing_address_id) {
        $this->billing_address_id = $billing_address_id;
    }

    public function setZcrmPotentialId($zcrm_potential_id) {
        $this->zcrm_potential_id = $zcrm_potential_id;
    }

    public function setPaymentTerms($payment_terms) {
        $this->payment_terms = $payment_terms;
    }

    public function setPaymentTermsLabel($payment_terms_label) {
        $this->payment_terms_label = $payment_terms_label;
    }

    public function setIsAdvTrackingInPackage($is_adv_tracking_in_package) {
        $this->is_adv_tracking_in_package = $is_adv_tracking_in_package;
    }

    private function generateLineItemsJson() {
        $lineItemsJsonArray = [];
    
        foreach ($this->line_items as $item) {
            // Verificar si el item es una instancia de la clase Item
            if ($item instanceof Item) {
                // Crear un array asociativo con 'item_id' y agregarlo al array final
                $lineItemsJsonArray[] = ["item_id" => $item->getIdItemZoho(),
                                        "quantity" => $item->getQuantity(),
                                        "name" => $item->getName(), 
                                        "unit" => $item->getUnit(),
                                        "description" => $item->getDescription()
                                        ];
            }
        }
    
        return $lineItemsJsonArray;
    }

    public function toJson() {
        $jsonArray = [
            "customer_id" => $this->customer_id,
            "reference_number" => $this->reference_number,
            "date" => $this->date,
            "shipment_date" => $this->shipment_date,
            "is_inclusive_tax" => $this->is_inclusive_tax,
            "is_discount_before_tax" => $this->is_discount_before_tax,
            "discount_type" => $this->discount_type,
            "adjustment_description" => $this->adjustment_description,
            "order_status" => $this->order_status,
            "payment_terms" => $this->payment_terms,
            "payment_terms_label" => $this->payment_terms_label,
            "is_adv_tracking_in_package" => $this->is_adv_tracking_in_package,
            "contact_persons" => $this->contact_persons,
            "line_items" => $this->generateLineItemsJson(),
        ];
    
        return json_encode($jsonArray);
    }
    
}
?>