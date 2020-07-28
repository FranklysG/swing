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
        $this->form->setFormTitle('Reserva');
        $this->form->setFieldSizes('100%');
        
        // master fields
        $id = new TEntry('id');
        $cadastro_tipo_id = new THidden('cadastro_tipo_id');
        $quarto_id = new THidden('quarto_id');
        $hora_a = new TEntry('hora_a');
        $hora_f = new TEntry('hora_f');
        $status = new TEntry('status');
        $dtcadastro = new TEntry('dtcadastro');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_n_quarto = new TDBUniqueSearch('detail_n_quarto','app','Quarto','id','n_quarto');
        $detail_n_quarto->setMinLength(0);
        $detail_valor = new THidden('detail_valor');
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
        
        $row = $this->form->addFields([$cadastro_tipo_id,$quarto_id]);
        // master fields
        $row = $this->form->addFields( [new TLabel('Id'), $id],
                                [new TLabel('Hora Abertura'), $hora_a],
                                [new TLabel('Hora Fechamento'), $hora_f]);
        $row->layout = ['col-sm-1','col-sm-3','col-sm-3'];
     
        
        // detail fields
        $this->form->addContent( ['<br><br><strong>QUARTOS OCUPADOS HOJE</strong><hr>']);
        $this->form->addContent( [$detail_uniqid,$detail_id] );

        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Ocupar', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');

        $this->form->addFields( [new TLabel('N° Quarto'), $detail_n_quarto],
                                [new TLabel(''), $add],
                                [$detail_valor],
                                [$detail_status],
                                [$detail_dtcadastro]
                                );
    
        $this->form->addContent( ['<br>']);
        $this->form->addFields([$detail_produto_id]);

        $this->form->addContent( ['<br><br>']);
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('Quarto_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('produto_id', 'Produto Id', 'left', 100) )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('n_quarto', 'N Quarto', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('valor', 'Valor', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('status', 'Status', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('dtcadastro', 'Dtcadastro', 'left', 100) );

        // detail actions
        $action1 = new TDataGridAction([$this, 'onDetailEdit'] );
        $action1->setFields( ['uniqid', '*'] );
        
        $action2 = new TDataGridAction([$this, 'onDetailDelete']);
        $action2->setField('uniqid');
        
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
            
            /** validation sample
            if (empty($data->fieldX))
            {
                throw new Exception('The field fieldX is required');
            }
            **/
            
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['produto_id'] = $data->detail_produto_id;
            $grid_data['n_quarto'] = $data->detail_n_quarto;
            $grid_data['valor'] = ($data->detail_valor)?: '30';
            $grid_data['status'] = ($data->detail_status)?: 1;
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('Quarto_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            // $data->detail_id = '';
            // $data->detail_produto_id = '';
            $data->detail_n_quarto = '';
            $data->detail_valor = '';
            $data->detail_status = '';
            $data->detail_dtcadastro = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Reserva', $data, false, false );
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
        $data->detail_produto_id = $param['produto_id'];
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
        $data->detail_produto_id = '';
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
                $items  = Quarto::where('id', '=', $key)->load();
                
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
            
            $data = $this->form->getData();
            $this->form->validate();
            
            $master = new Reserva;
            $master->cadastro_tipo_id = 1 ;
            $master->fromArray( (array) $data);
            $master->store();
            
            Quarto::where('id', '=', $master->id)->delete();
            
            if( $param['Quarto_list_produto_id'] )
            {
                foreach( $param['Quarto_list_produto_id'] as $key => $item_id )
                {
                    $detail = new Quarto;
                    $detail->produto_id  = $param['Quarto_list_produto_id'][$key];
                    $detail->n_quarto  = $param['Quarto_list_n_quarto'][$key];
                    $detail->valor  = $param['Quarto_list_valor'][$key];
                    $detail->status  = $param['Quarto_list_status'][$key];
                    $detail->dtcadastro  = $param['Quarto_list_dtcadastro'][$key];
                    $detail->id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Reserva', (object) ['id' => $master->id]);
            
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
