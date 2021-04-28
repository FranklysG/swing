<?php
/**
 * MapaReserva Active Record
 * @author  <your-name-here>
 */
class MapaReserva extends TRecord
{
    const TABLENAME = 'mapa_reserva';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $reserva;
    private $consumo;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('reserva_id');
        parent::addAttribute('n_quarto');
        parent::addAttribute('valor_quarto');
        parent::addAttribute('valor_consumo');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_reserva
     * Sample of usage: $mapa_reserva->reserva = $object;
     * @param $object Instance of Reserva
     */
    public function set_reserva(Reserva $object)
    {
        $this->reserva = $object;
        $this->reserva_id = $object->id;
    }
    
    /**
     * Method get_reserva
     * Sample of usage: $mapa_reserva->reserva->attribute;
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
     * Method set_consumo
     * Sample of usage: $mapa_reserva->consumo = $object;
     * @param $object Instance of Consumo
     */
    public function set_consumo(Consumo $object)
    {
        $this->consumo = $object;
        $this->consumo_id = $object->id;
    }
    
    /**
     * Method get_consumo
     * Sample of usage: $mapa_reserva->consumo->attribute;
     * @returns Consumo instance
     */
    public function get_consumo()
    {
        // loads the associated object
        if (empty($this->consumo))
            $this->consumo = new Consumo($this->consumo_id);
    
        // returns the associated object
        return $this->consumo;
    }
    


}
