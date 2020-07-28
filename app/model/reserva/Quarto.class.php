<?php
/**
 * Quarto Active Record
 * @author  <your-name-here>
 */
class Quarto extends TRecord
{
    const TABLENAME = 'quarto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $reserva;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('reserva_id');
        parent::addAttribute('n_quarto');
        parent::addAttribute('valor');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_reserva
     * Sample of usage: $quarto->reserva = $object;
     * @param $object Instance of Reserva
     */
    public function set_reserva(Reserva $object)
    {
        $this->reserva = $object;
        $this->reserva_id = $object->id;
    }
    
    /**
     * Method get_reserva
     * Sample of usage: $quarto->reserva->attribute;
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

}
