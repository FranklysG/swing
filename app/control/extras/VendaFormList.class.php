<?php
/**
 * VendaFormList Form List
 * @author  <your name here>
 */
class VendaFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        $this->form = new BootstrapFormBuilder('form_venda_externas');
        $this->form->setFormTitle('Vendas externas');
        

        // create the form fields
        $id = new THidden('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('qtd_estoque','>=',1));
        $entrada_id = new TDBUniqueSearch('entrada_id', 'app', 'Entrada', 'id', 'descricao',null,$criteria);
        $entrada_id->setMinLength(0);
        $entrada_id->addValidation('Produto', new TRequiredValidator);
        $qtd_venda = new TEntry('qtd_venda');
        $qtd_venda->addValidation('Quantidade', new TRequiredValidator);

        // add the fields
        $this->form->addFields( [$id ]);
        $row = $this->form->addFields( 
                                        [ new TLabel('Produto'), $entrada_id ],
                                        [ new TLabel('Quantidade'), $qtd_venda ]
                                    );
                                        
        $row->layout = ['col-sm-4', 'col-sm-6'];



        // set sizes
        $id->setSize('100%');
        $entrada_id->setSize('100%');
        $qtd_venda->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        // $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_entrada_id = new TDataGridColumn('entrada->produto->nome', 'PRODUTO', 'left');
        $column_qtd_venda = new TDataGridColumn('qtd_venda', 'QUANTIDADE', 'left');
        $column_valor_venda_uni = new TDataGridColumn('entrada->valor_venda_uni', 'VALOR', 'lrft');
        $column_qtd_venda_total = new TDataGridColumn('={qtd_venda}*{entrada->valor_venda_uni}', 'TOTAL', 'left');
        $column_dtcadastro = new TDataGridColumn('dtcadastro', 'DATA', 'right');

        $column_valor_venda_uni->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });
        
        $column_qtd_venda_total->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_dtcadastro->setTransformer(function ($value) {
            return Convert::toDateBR($value);
        });

        // add the columns to the DataGrid
        // $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_entrada_id);
        $this->datagrid->addColumn($column_qtd_venda);
        $this->datagrid->addColumn($column_valor_venda_uni);
        $this->datagrid->addColumn($column_qtd_venda_total);
        $this->datagrid->addColumn($column_dtcadastro);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction([$this, 'onEdit']);
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('far:edit blue');
        $action1->setField('id');
        $action1->setDisplayCondition( array($this, 'displayColumnToday') );
        
        $action2 = new TDataGridAction([$this, 'onDelete']);
        $action2->setFields(['id', 'entrada_id', 'qtd_venda']);
        //$action2->setUseButton(TRUE);
        //$action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
        $action2->setImage('far:trash-alt red');
        $action2->setDisplayCondition( array($this, 'displayColumnToday') );
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    // dessativa os botão quando a data não for a de hoje
    public function displayColumnToday( $object )
    {
        if(isset($object->dtcadastro)){
            if (Convert::toDateBR($object->dtcadastro) == date('d/m/Y')){
                return TRUE;
            }else{
                return FALSE;
            }
        }
        
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'app'
            TTransaction::open('app');
            
            // creates a repository for Venda
            $repository = new TRepository('Venda');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dtcadastro';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('app'); // open a transaction with database
            $object = new Venda($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            
            $entrada = Entrada::find($param['entrada_id']);
            $entrada->qtd_estoque += $param['qtd_venda'];
            $entrada->store();
            
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('app'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $entrada = Entrada::find($param['entrada_id']);
            $entrada->qtd_estoque -= $param['qtd_venda'];
            $entrada->store();

            $object = new Venda;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->valor_venda = $entrada->valor_venda_uni;
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), new TAction([$this, 'onReload'])); // success message
            // $this->onReload(); // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('app'); // open a transaction
                $object = new Venda($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
