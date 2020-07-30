<?php
/**
 * ReservaForm Master/Detail
 * @author  <your name here>
 */
class ReservaForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Reserva');
        $this->form->setFormTitle('QUARTOS OCUPADOS HOJE - '.date('d/m/Y'));
        $this->form->setFieldSizes('100%');
        
        // master fields
        $id = new THidden('id');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_n_quarto = new TEntry('detail_n_quarto');
        $detail_n_quarto->addValidation('Numero do quarto', new TRequiredValidator);
        $detail_valor = new TEntry('detail_valor');
        $detail_status = new THidden('detail_status');
        $detail_dtcadastro = new THidden('detail_dtcadastro');
        $detail_produto_id   = new TDBCheckGroup('detail_produto_id', 'app', 'Produto', 'id', 'nome');
        $detail_produto_id->setLayout('horizontal');
        if ($detail_produto_id->getLabels()) {
            foreach ($detail_produto_id->getLabels() as $key => $label) {
                $label->setSize(200);
            }
        }
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // // detail fields
        $this->form->addFields([$id]);
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
        
        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Register', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');

        $row = $this->form->addFields( [new TLabel('N° Quarto'), $detail_n_quarto],
                                        [new TLabel('Valor'), $detail_valor],
                                        [new TLabel(''), $add],
                                        [new TLabel(''), $detail_status],
                                        [new TLabel(''), $detail_dtcadastro] );
        
        $row->layout = ['col-sm-3','col-sm-3','col-sm-3','col-sm-2','col-sm-2'];
        
        $this->form->addContent( ['<strong>PRODUTOS</strong><hr>']);
        $this->form->addFields( [$detail_produto_id] );
        $this->form->addContent( ['<br>']);
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('Quarto_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        $column_valor = new TDataGridColumn('valor', 'VALOR', 'left', 100);
        $column_dtcadastro = new TDataGridColumn('dtcadastro', 'DATA', 'left', 100);
        $column_status = new TDataGridColumn('status', 'STATUS', 'left', 100);
        
        $column_valor->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_status->setTransformer(function ($value) {
           
            if ($value == 0) {
                $class = 'success';
                $label = 'LIMPO';
            } 
            else if ($value == 1) {
                $class = 'danger';
                $label = 'OCUPADO';
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

        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'left') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'left') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('n_quarto', 'N QUARTO', 'left', 100) );
        $this->detail_list->addColumn( $column_valor );
        $this->detail_list->addColumn( $column_status );
        $this->detail_list->addColumn( $column_dtcadastro );

        // detail actions
        $action1 = new TDataGridAction([$this, 'onDetailEdit'] );
        $action1->setFields( ['uniqid', '*'] );
        
        $action2 = new TDataGridAction([$this, 'onDetailDelete']);
        $action2->setFields(['uniqid', '*']);
        
        // add the actions to the datagrid
        $this->detail_list->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->detail_list->addAction($action2, _t('Delete'), 'far:trash-alt red');
        
        $this->detail_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );
        
        $this->form->addAction( 'Save',  new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save green');
        $this->form->addAction( 'Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    
    /**
     * Clear form
     * @param $param URL parameters
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Add detail item
     * @param $param URL parameters
     */
    public function onDetailAdd( $param )
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
            TTransaction::open('app');
            
            /** validation sample
            if (empty($data->fieldX))
            {
                throw new Exception('The field fieldX is required');
            }
            **/
            
            $produto_valor = 0;
            if(isset($param['detail_produto_id'])){
                foreach ($param['detail_produto_id'] as $key => $value) {
                    $produto_valor += Produto::find($value)->valor;
                }
            }
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['n_quarto'] = $data->detail_n_quarto;
            $grid_data['valor'] = ((int)$data->detail_valor + (int)$produto_valor)?: 30;
            $grid_data['status'] = ($data->detail_status)?: 1;
            // $grid_data['dtcadastro'] = date('d/m/Y');
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('Quarto_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_n_quarto = '';
            $data->detail_valor = '';
            $data->detail_status = '';
            $data->detail_dtcadastro = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Reserva', $data, false, false );

            TTransaction::close();
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Edit detail item
     * @param $param URL parameters
     */
    public static function onDetailEdit( $param )
    {
        $data = new stdClass;
        $data->detail_uniqid = $param['uniqid'];
        $data->detail_id = $param['id'];
        $data->detail_n_quarto = $param['n_quarto'];
        $data->detail_valor = $param['valor'];
        $data->detail_status = $param['status'];
        $data->detail_dtcadastro = $param['dtcadastro'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Reserva', $data, false, false );
    }
    
    /**
     * Delete detail item
     * @param $param URL parameters
     */
    public static function onDetailDelete( $param )
    {
        // clear detail form fields
        $data = new stdClass;
        $data->detail_uniqid = '';
        $data->detail_id = '';
        $data->detail_n_quarto = '';
        $data->detail_valor = '';
        $data->detail_status = '';
        $data->detail_dtcadastro = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Reserva', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('Quarto_list', $param['uniqid']);
    }
    
    /**
     * Load Master/Detail data from database to form
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('app');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Reserva($key);
                $items  = Quarto::where('reserva_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Save the Master/Detail data from form to database
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('app');
            $master_id = $param['id'];
            
            $data = $this->form->getData();
            $this->form->validate();
            
            Quarto::where('reserva_id', '=', $master_id)->delete();
            
            if( isset($param['Quarto_list_n_quarto']) )
            {
                foreach( $param['Quarto_list_n_quarto'] as $key => $item_id )
                {
                    $detail = new Quarto;
                    $detail->n_quarto  = $param['Quarto_list_n_quarto'][$key];
                    $detail->valor  = $param['Quarto_list_valor'][$key];
                    $detail->status  = $param['Quarto_list_status'][$key];
                    $detail->reserva_id = $master_id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Reserva', (object) ['id' => $master_id]);
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
}
