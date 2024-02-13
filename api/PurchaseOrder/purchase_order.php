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
            // Getter y Setter para $purchaseorder_number
    public function getPurchaseorderNumber() {
        return $this->purchaseorder_number;
    }

    public function setPurchaseorderNumber($purchaseorder_number) {
        $this->purchaseorder_number = $purchaseorder_number;
    }

    // Getter y Setter para $date
    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    // Getter y Setter para $vendor_id
    public function getVendorId() {
        return $this->vendor_id;
    }

    public function setVendorId($vendor_id) {
        $this->vendor_id = $vendor_id;
    }

    // Getter y Setter para $is_drop_shipment
    public function getIsDropShipment() {
        return $this->is_drop_shipment;
    }

    public function setIsDropShipment($is_drop_shipment) {
        $this->is_drop_shipment = $is_drop_shipment;
    }

    // Getter y Setter para $contact_persons
    public function getContactPersons() {
        return $this->contact_persons;
    }

    public function setContactPersons($contact_persons) {
        $this->contact_persons = $contact_persons;
    }

    // Getter y Setter para $notes
    public function getNotes() {
        return $this->notes;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    // Getter y Setter para $reference_number
    public function getReferenceNumber() {
        return $this->reference_number;
    }

    public function setReferenceNumber($reference_number) {
        $this->reference_number = $reference_number;
    }

    // Getter y Setter para $line_items
    public function getLineItems() {
        return $this->line_items;
    }

    public function setLineItems($line_items) {
        $this->line_items = $line_items;
    }

    // Método para convertir cada item en JSON
private function generateLineItemsJson() {
    $lineItemsJsonArray = [];

    foreach ($this->line_items as $item) {
        // Verificar si el item es una instancia de la clase Item
        if ($item instanceof Item) {
            // Crear un array asociativo con 'item_id' y agregarlo al array final
            $lineItemsJsonArray[] = ["item_id" => $item->getIdItemZoho(),
                                    "quantity" => $item->getQuantity()
                                    ]; // Ajusta según tu implementación de la clase Item
        }
    }

    return $lineItemsJsonArray;
}

    //Metodo para convertir el objeto a JSON
        public function toJson() {
            $jsonArray = [
                "purchaseorder_number" => "",
                "date" => $this->date,
                "vendor_id" => $this->vendor_id,
                "is_drop_shipment" => $this->is_drop_shipment,
                "contact_persons" => $this->contact_persons,
                "notes" => $this->notes,
                "reference_number" => $this->reference_number,
                "line_items" => $this->generateLineItemsJson(),
            ];
    
            return json_encode($jsonArray);
        }
        
    }
?>