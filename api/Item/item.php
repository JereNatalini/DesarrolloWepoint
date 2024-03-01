<?php
class Item {
    private $name;
    private $sku;
    private $description;
    private $unit;
    private $id_item_zoho;
    private $quantity;
    private $item_type;
    private $purchase_rate;

    public function __construct($name) {
        $this->name = $name;
        $this->item_type = "Inventory";
    }

    public function getPurchaseRate(){
        return $this->purchase_rate;
    }

    public function getType(){
        return $this->item_type;
    }

    public function setType(){
        $this->item_type = $item_type;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getSku() {
        return $this->sku;
    }

    public function setSku($sku) {
        $this->sku = $sku;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function setUnit($unit) {
        $this->unit = $unit;
        return $this;
    }

    public function getIdItemZoho() {
        return $this->id_item_zoho;
    }

    public function setIdItemZoho($id_item_zoho) {
        $this->id_item_zoho = $id_item_zoho;
        return $this;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

    public function setPurchaseRate($purchase_rate){
        $this->purchase_rate = $purchase_rate;
    }
    
    }
?>