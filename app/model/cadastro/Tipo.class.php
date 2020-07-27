<?php
/**
 * Tipo Active Record
 * @author  <your-name-here>
 */
class Tipo extends TRecord
{
    const TABLENAME = 'tipo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $cadastro_tipos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }

    
    /**
     * Method addCadastroTipo
     * Add a CadastroTipo to the Tipo
     * @param $object Instance of CadastroTipo
     */
    public function addCadastroTipo(CadastroTipo $object)
    {
        $this->cadastro_tipos[] = $object;
    }
    
    /**
     * Method getCadastroTipos
     * Return the Tipo' CadastroTipo's
     * @return Collection of CadastroTipo
     */
    public function getCadastroTipos()
    {
        return $this->cadastro_tipos;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->cadastro_tipos = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related CadastroTipo objects
        $repository = new TRepository('CadastroTipo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_id', '=', $id));
        $this->cadastro_tipos = $repository->load($criteria);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related CadastroTipo objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_id', '=', $this->id));
        $repository = new TRepository('CadastroTipo');
        $repository->delete($criteria);
        // store the related CadastroTipo objects
        if ($this->cadastro_tipos)
        {
            foreach ($this->cadastro_tipos as $cadastro_tipo)
            {
                unset($cadastro_tipo->id);
                $cadastro_tipo->tipo_id = $this->id;
                $cadastro_tipo->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related CadastroTipo objects
        $repository = new TRepository('CadastroTipo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
