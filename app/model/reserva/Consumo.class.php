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
    
    
    private $mapa_reserva;
    private $produto;
    private $entrada;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('mapa_reserva_id');
        parent::addAttribute('produto_id');
        parent::addAttribute('entrada_id');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_mapa_reserva
     * Sample of usage: $consumo->mapa_reserva = $object;
     * @param $object Instance of MapaReserva
     */
    public function set_mapa_reserva(MapaReserva $object)
    {
        $this->mapa_reserva = $object;
        $this->mapa_reserva_id = $object->id;
    }
    
    /**
     * Method get_mapa_reserva
     * Sample of usage: $consumo->mapa_reserva->attribute;
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
     * Method set_entrada
     * Sample of usage: $consumo->entrada = $object;
     * @param $object Instance of entrada
     */
    public function set_entrada(Entrada $object)
    {
        $this->entrada = $object;
        $this->entrada_id = $object->id;
    }
    
    /**
     * Method get_entrada
     * Sample of usage: $consumo->entrada->attribute;
     * @returns entrada instance
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
