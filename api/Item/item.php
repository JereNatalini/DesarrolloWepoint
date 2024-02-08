<?php
    class Item{
        //Atributos necesarios para crear un item en Zoho
        private $name;
        private $sku;
        private $description;
        private $unit; //Unidad de medida del item
        private $id_item_zoho;

        public function __construct($item_id_zoho,$name, $sku, $description, $unit){
            $this->item_id_zoho = $item_id_zoho;
            $this->name = $name;
            $this->sku = $sku;
            $this->description = $description;
            $this->unit = $unit;
        }
        
    }
?>