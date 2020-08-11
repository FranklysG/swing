<?php
/**
 * ReservaList Listing
 * @author  <your name here>
 */
class ReservaList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Reserva');
        $this->form->setFormTitle('LISTAGEM DE RESERVAS');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $mapa_reserva_id = new TEntry('mapa_reserva_id');
        $hora = new TEntry('hora');
        $status = new TEntry('status');
        $dtcadastro = new TEntry('dtcadastro');


        // add the fields
        $row = $this->form->addFields( 
                                [ new TLabel('STATUS'), $status],
                                [ new TLabel('DATA'), $dtcadastro ]
                             );
        $row->layout = ['col-sm-3','col-sm-3'];

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        // $this->form->addActionLink(_t('New'), new TAction(['ReservaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'ID', 'left');
        $column_faturamento = new TDataGridColumn('faturamento_dia', 'FATURAMENTO', 'left');
        $column_status = new TDataGridColumn('status', 'STATUS', 'left');
        $column_dtcadastro = new TDataGridColumn('dtcadastro', 'DATA', 'left');
        
        $column_faturamento->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });
        
        $column_status->setTransformer(function ($value) {
           
            if ($value == 0) {
                $class = 'success';
                $label = 'ABERTO';
            } 
            else if ($value == 1) {
                $class = 'danger';
                $label = 'FINALIZADO';
            }

            $div = new TElement('span');
            $div->class = "btn btn-{$class}";
            $div->style = "text-shadow:none; font-size:12px; font-weight:lighter;width:100px;";
            $div->add($label);
            return $div;
        });

        $column_dtcadastro->setTransformer(function ($value) {
            return Convert::toDateBR($value);
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_faturamento);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_dtcadastro);

        $action1 = new TDataGridAction(['ReservaForm', 'onEdit'], ['id'=>'{id}']);
        $action1->setDisplayCondition( array($this, 'displayColumn') );
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        if(TSession::getValue('userid') == 1){
            $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        }
        
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

    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */

    public function displayColumn( $object )
    {
        if (Convert::toDateBR($object->dtcadastro) == date('d/m/Y'))
        {
            return TRUE;
        }
        return FALSE;
    }


    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('app'); // open a transaction with database
            $object = new Reserva($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_mapa_reserva_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_hora',   NULL);
        TSession::setValue(__CLASS__.'_filter_status',   NULL);
        TSession::setValue(__CLASS__.'_filter_dtcadastro',   NULL);


        if (isset($data->hora) AND ($data->hora)) {
            $filter = new TFilter('hora', 'like', "%{$data->hora}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_hora',   $filter); // stores the filter in the session
        }


        if (isset($data->status) AND ($data->status)) {
            $filter = new TFilter('status', 'like', "%{$data->status}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_status',   $filter); // stores the filter in the session
        }


        if (isset($data->dtcadastro) AND ($data->dtcadastro)) {
            $filter = new TFilter('dtcadastro', 'like', "%{$data->dtcadastro}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_dtcadastro',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
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
            
            // criar nova reserva automarica
            // abre e fecha sempre as meia noite
            // status 0 -> aberto 
            $reserva = Reserva::where('date(dtcadastro)','=',date('Y-m-d'))->first();
            if(!isset($reserva)){
                $reserva = new Reserva;
                $reserva->usuario_id = TSession::getValue('userid');
                $reserva->status = 0;
                $reserva->store();
            }
            $reserva = Reserva::where('date(dtcadastro)','=',date('Y-m-d', strtotime('-1 day')))->first();
            if(isset($reserva)){
                $reserva->status = 1;
                $reserva->store();
            }


            // creates a repository for Reserva
            $repository = new TRepository('Reserva');
            $limit = 10;

            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'desc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue(__CLASS__.'_filter_mapa_reserva_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_mapa_reserva_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_hora')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_hora')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_status')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_status')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_dtcadastro')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_dtcadastro')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $object->faturamento_dia = 0;
                    $mapa_reserva_valor = 0;
                    $mapa_reservas = MapaReserva::where('reserva_id','=',$object->id)->load();
                    if($mapa_reservas){
                        foreach($mapa_reservas as $value){
                            $mapa_reserva_valor += $value->valor;
                            $object->faturamento_dia = $mapa_reserva_valor;
                        }
                    }

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
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
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
            $key=$param['key']; // get the parameter $key
            TTransaction::open('app'); // open a transaction with database
            $object = new Reserva($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
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
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
