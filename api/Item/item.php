<?php
    class Item{
        //Atributos necesarios para crear un item en Zoho
        private $name;
        private $sku;
        private $description;
        private $unit; //Unidad de medida del item

        public function __construct($name, $sku, $description, $unit){
            $this->name = $name;
            $this->sku = $sku;
            $this->description = $description;
            $this->unit = $unit;
        }
        
    }
?>