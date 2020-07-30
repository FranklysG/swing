<?php
/**
 * Reserva Active Record
 * @author  <your-name-here>
 */
class Reserva extends TRecord
{
    const TABLENAME = 'reserva';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $usuario;
    private $quarto;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('usuario_id');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_usuario
     * Sample of usage: $reserva->usuario = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario(SystemUser $object)
    {
        $this->usuario = $object;
        $this->usuario_id = $object->id;
    }
    
    /**
     * Method get_usuario
     * Sample of usage: $reserva->usuario->attribute;
     * @returns SystemUser instance
     */
    public function get_usuario()
    {
        // loads the associated object
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->usuario_id);
    
        // returns the associated object
        return $this->usuario;
    }
    
    
    /**
     * Method set_quarto
     * Sample of usage: $reserva->quarto = $object;
     * @param $object Instance of Quarto
     */
    public function set_quarto(Quarto $object)
    {
        $this->quarto = $object;
        $this->quarto_id = $object->id;
    }
    
    /**
     * Method get_quarto
     * Sample of usage: $reserva->quarto->attribute;
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
