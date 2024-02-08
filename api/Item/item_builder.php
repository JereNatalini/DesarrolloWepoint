<?php
    class ItemBuilder {
        private $attributes = [];

        public function set($name, $value){
            $this->attributes[$name] = $value;
            return $this;
        }
        
        public function buildItem(){
            return new item($this->attributes['name'],
                            $this->attributes['sku'],
                            $this->attributes['description'],
                            $this->attributes['unit']);
        }

    }    

    /*Ejemplo de uso
    $itemBuilder = new itemBuilder();
    $item = $itemBuilder->set('name','producto A') 
                        ->set('sku','0001')
                        ->set('description', 'hola')
                        ->set('unit', 1)
                        ->buildItem();
    */
    
?>


