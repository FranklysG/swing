<?php
/**
 * CadastroTipo Active Record
 * @author  <your-name-here>
 */
class CadastroTipo extends TRecord
{
    const TABLENAME = 'cadastro_tipo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $cadastro;
    private $tipo;
    private $reserva;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_id');
        parent::addAttribute('cadastro_id');
    }

    
    /**
     * Method set_cadastro
     * Sample of usage: $cadastro_tipo->cadastro = $object;
     * @param $object Instance of Cadastro
     */
    public function set_cadastro(Cadastro $object)
    {
        $this->cadastro = $object;
        $this->cadastro_id = $object->id;
    }
    
    /**
     * Method get_cadastro
     * Sample of usage: $cadastro_tipo->cadastro->attribute;
     * @returns Cadastro instance
     */
    public function get_cadastro()
    {
        // loads the associated object
        if (empty($this->cadastro))
            $this->cadastro = new Cadastro($this->cadastro_id);
    
        // returns the associated object
        return $this->cadastro;
    }
    
    
    /**
     * Method set_tipo
     * Sample of usage: $cadastro_tipo->tipo = $object;
     * @param $object Instance of Tipo
     */
    public function set_tipo(Tipo $object)
    {
        $this->tipo = $object;
        $this->tipo_id = $object->id;
    }
    
    /**
     * Method get_tipo
     * Sample of usage: $cadastro_tipo->tipo->attribute;
     * @returns Tipo instance
     */
    public function get_tipo()
    {
        // loads the associated object
        if (empty($this->tipo))
            $this->tipo = new Tipo($this->tipo_id);
    
        // returns the associated object
        return $this->tipo;
    }

    /**
     * Method set_reserva
     * Sample of usage: $cadastro_reserva->reserva = $object;
     * @param $object Instance of reserva
     */
    public function set_reserva(Reserva $object)
    {
        $this->reserva = $object;
        $this->reserva_id = $object->id;
    }
    
    /**
     * Method get_reserva
     * Sample of usage: $cadastro_reserva->reserva->attribute;
     * @returns reserva instance
     */
    public function get_reserva()
    {
        // loads the associated object
        if (empty($this->reserva))
            $this->reserva = new reserva($this->reserva_id);
    
        // returns the associated object
        return $this->reserva;
    }
    


}
