<?php
/**
 * Consumo Active Record
 * @author  <your-name-here>
 */
class Consumo extends TRecord
{
    const TABLENAME = 'consumo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $produto;
    private $quarto;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto_id');
        parent::addAttribute('quarto_id');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_produto
     * Sample of usage: $consumo->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_produto
     * Sample of usage: $consumo->produto->attribute;
     * @returns Produto instance
     */
    public function get_produto()
    {
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Produto($this->produto_id);
    
        // returns the associated object
        return $this->produto;
    }
    
    
    /**
     * Method set_quarto
     * Sample of usage: $consumo->quarto = $object;
     * @param $object Instance of Quarto
     */
    public function set_quarto(Quarto $object)
    {
        $this->quarto = $object;
        $this->quarto_id = $object->id;
    }
    
    /**
     * Method get_quarto
     * Sample of usage: $consumo->quarto->attribute;
     * @returns Quarto instance
     */
    public function get_quarto()
    {
        // loads the associated object
        if (empty($this->quarto))
            $this->quarto = new Quarto($this->quarto_id);
    
        // returns the associated object
        return $this->quarto;
    }
    


}
