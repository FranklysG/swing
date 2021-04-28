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
    private $mapa_reserva;

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
     * Method set_mapa_reserva
     * Sample of usage: $reserva->mapa_reserva = $object;
     * @param $object Instance of MapaReserva
     */
    public function set_mapa_reserva(MapaReserva $object)
    {
        $this->mapa_reserva = $object;
        $this->mapa_reserva_id = $object->id;
    }
    
    /**
     * Method get_mapa_reserva
     * Sample of usage: $reserva->mapa_reserva->attribute;
     * @returns MapaReserva instance
     */
    public function get_mapa_reserva()
    {
        // loads the associated object
        if (empty($this->mapa_reserva))
            $this->mapa_reserva = new MapaReserva($this->mapa_reserva_id);
    
        // returns the associated object
        return $this->mapa_reserva;
    }
    


}
