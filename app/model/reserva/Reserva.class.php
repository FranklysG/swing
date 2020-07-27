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
    
    
    private $cadastro_tipo;
    private $quarto;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cadastro_tipo_id');
        parent::addAttribute('quarto_id');
        parent::addAttribute('hora_a');
        parent::addAttribute('hora_f');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_cadastro_tipo
     * Sample of usage: $reserva->cadastro_tipo = $object;
     * @param $object Instance of CadastroTipo
     */
    public function set_cadastro_tipo(CadastroTipo $object)
    {
        $this->cadastro_tipo = $object;
        $this->cadastro_tipo_id = $object->id;
    }
    
    /**
     * Method get_cadastro_tipo
     * Sample of usage: $reserva->cadastro_tipo->attribute;
     * @returns CadastroTipo instance
     */
    public function get_cadastro_tipo()
    {
        // loads the associated object
        if (empty($this->cadastro_tipo))
            $this->cadastro_tipo = new CadastroTipo($this->cadastro_tipo_id);
    
        // returns the associated object
        return $this->cadastro_tipo;
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
