<?php
    class Purchase_order{
        //Atributos necesarios para crear un item en Zoho
        private $purchaseorder_number;
        private $date;
        private $vendor_id;
        private $is_drop_shipment;
        private $contact_persons;
        private $notes;
        private $reference_number;
        private $line_items;

        public function __construct($purchaseorder_number, $date, $vendor_id, $is_drop_shipment, $contact_persons, $notes, $reference_number, $line_items){
            $this->purchaseorder_number = $purchaseorder_number;
            $this->date = $date;
            $this->vendor_id = $vendor_id;
            $this->is_drop_shipment = $is_drop_shipment;
            $this->contact_persons = $contact_persons;
            $this->notes = $notes;
            $this->reference_number = $reference_number;
            $this->line_items = $line_items;
        }
        
    }
?>