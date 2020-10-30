<?php
/**
 * Venda Active Record
 * @author  <your-name-here>
 */
class Venda extends TRecord
{
    const TABLENAME = 'venda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $entrada;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('entrada_id');
        parent::addAttribute('qtd_venda');
        parent::addAttribute('valor_venda');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_entrada
     * Sample of usage: $venda->entrada = $object;
     * @param $object Instance of Entrada
     */
    public function set_entrada(Entrada $object)
    {
        $this->entrada = $object;
        $this->entrada_id = $object->id;
    }
    
    /**
     * Method get_entrada
     * Sample of usage: $venda->entrada->attribute;
     * @returns Entrada instance
     */
    public function get_entrada()
    {
        // loads the associated object
        if (empty($this->entrada))
            $this->entrada = new Entrada($this->entrada_id);
    
        // returns the associated object
        return $this->entrada;
    }
    


}
