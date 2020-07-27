<?php
/**
 * Produto Active Record
 * @author  <your-name-here>
 */
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $reserva;
    private $quarto;


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('quarto_id');
        parent::addAttribute('nome');
        parent::addAttribute('valor');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_reserva
     * Sample of usage: $produto->reserva = $object;
     * @param $object Instance of Reserva
     */
    public function set_reserva(Reserva $object)
    {
        $this->reserva = $object;
        $this->reserva_id = $object->id;
    }
    
    /**
     * Method get_reserva
     * Sample of usage: $produto->reserva->attribute;
     * @returns Reserva instance
     */
    public function get_reserva()
    {
        // loads the associated object
        if (empty($this->reserva))
            $this->reserva = new Reserva($this->reserva_id);
    
        // returns the associated object
        return $this->reserva;
    }
    
     /**
     * Method set_quarto
     * Sample of usage: $reserva->quarto = $object;
     * @param $object Instance of quarto
     */
    public function set_quarto(Quarto $object)
    {
        $this->quarto = $object;
        $this->quarto_id = $object->id;
    }
    
    /**
     * Method get_quarto
     * Sample of usage: $reserva->quarto->attribute;
     * @returns quarto instance
     */
    public function get_quarto()
    {
        // loads the associated object
        if (empty($this->quarto))
            $this->quarto = new quarto($this->quarto_id);
    
        // returns the associated object
        return $this->quarto;
    }
    

}
