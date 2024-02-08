<?php
    class PoBuilder {
        private $attributes = [];

        public function set($name, $value){
            $this->attributes[$name] = $value;
            return $this;
        }
        
        public function buildPO(){
            return new Purchase_order($this->attributes['purchaseorder_number'],
                                        $this->attributes['date'],
                                        $this->attributes['vendor_id'],
                                        $this->attributes['is_drop_shipment'],
                                        $this->attributes['contact_persons'],
                                        $this->attributes['notes'],
                                        $this->attributes['reference_number'],
                                        $this->attributes['line_items']);
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


