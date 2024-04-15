<?php
    class SoBuilder {
        private $attributes = [];

        public function set($name, $value){
            $this->attributes[$name] = $value;
            return $this;
        }
        
        public function buildPO(){
            return new Purchase_order($this->attributes['customer_id']?? null,
                                        $this->attributes['reference_number']?? null,
                                        $this->attributes['date']?? null,
                                        $this->attributes['shipment_date']?? null,
                                        $this->attributes['custom_fields']?? null,
                                        $this->attributes['is_inclusive_tax']?? null,
                                        $this->attributes['line_items']?? null,
                                        $this->attributes['notes']?? null,
                                        $this->attributes['terms']?? null,
                                        $this->attributes['is_discount_before_tax']?? null,
                                        $this->attributes['discount_type']?? null,
                                        $this->attributes['adjustment_description']?? null,
                                        $this->attributes['pricebook_id']?? null,
                                        $this->attributes['order_status']?? null,
                                        $this->attributes['template_id']?? null,
                                        $this->attributes['documents']?? null,
                                        $this->attributes['shipping_address_id']?? null,
                                        $this->attributes['billing_address_id']?? null,
                                        $this->attributes['zcrm_potential_id']?? null,
                                        $this->attributes['payment_terms']?? null,
                                        $this->attributes['payment_terms_label']?? null,
                                        $this->attributes['is_adv_tracking_in_package']?? null);
        }

        //Funcion que agrega los items al array Line_items de la PO
        public function addItem($item){
            // Verifica si el atributo line_items ya es un array
            if (!isset($this->attributes['line_items'])) {
                $this->attributes['line_items'] = [];
            }
            // Agrega el ítem al array line_items
            $this->attributes['line_items'][] = $item;
            return $this;
        }
        

    }    
?>


