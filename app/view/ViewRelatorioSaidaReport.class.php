<?php
/**
 * ViewRelatorioSaidaReport Report
 * @author  <your name here>
 */
class ViewRelatorioSaidaReport extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Saida_report');
        $this->form->setFormTitle('Relatorio de saidas');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $tipo_entrada_saida_id = new TDBUniqueSearch('tipo_entrada_saida_id', 'app', 'TipoEntradaSaida', 'id', 'nome');
        $tipo_entrada_saida_id->setMinLength(0);
        // $tipo_entrada_saida_id->addValidation('Produto', new TRequiredValidator);
        $descricao = new TEntry('descricao');
        
        // create the form fields
        $date_ini = new TDate('date_ini');
        $date_ini->setMask('dd/mm/yyyy');
        $date_ini->setDatabaseMask('yyyy-mm-dd');
        $date_end = new TDate('date_end');
        $date_end->setMask('dd/mm/yyyy');
        $date_end->setDatabaseMask('yyyy-mm-dd');
        
        // add the fields
        $row = $this->form->addFields( [ new TLabel('Tipo'), $tipo_entrada_saida_id ], 
                                        [ new TLabel('Descricao'), $descricao ],
                                        [ new TLabel('')],
                                        [ new TLabel('Data Inicial'), $date_ini ],
                                        [ new TLabel('Data Final'), $date_end ]
                                    );
        
        $row->layout = ['col-sm-4', 'col-sm-8' , 'col-sm-12', 'col-sm-3', 'col-sm-3'];
       
        // add the action button
        $btn = $this->form->addAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            // open a transaction with database 'app'
            TTransaction::open('app');
            
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('Saida');
            $criteria   = new TCriteria;

            if (isset($data->tipo_entrada_saida_id) and $data->tipo_entrada_saida_id)
            {
                $criteria->add(new TFilter('dtcadastro', 'between', "{$data->tipo_entrada_saida_id}"));
            }
            if ((isset($data->date_ini) and $data->date_ini) and (isset($data->date_end) and $data->date_end))
            {
                $criteria->add(new TFilter('dtcadastro', 'between', "{$data->date_ini}", "{$data->date_end}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = 'pdf';
            
            if ($objects)
            {
                $widths = array(160,100,180,160);
                $tr = new TTableWriterPDF($widths);
                
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '11', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '11', 'B',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '11', 'B',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '14', 'B',   '#ffffff', '#6B6B6B');
                // $tr->addStyle('footer', 'Arial', '11', 'B',  '#000000', '#A3A3A3');
                $tr->addStyle('footer', 'Arial', '11', 'B',  '#232323', '#D0D0D0');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Saida', 'center', 'header', 4);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('DATA', 'left', 'title');
                $tr->addCell('TIPO', 'left', 'title');
                $tr->addCell('DESCRICAO', 'left', 'title');
                $tr->addCell('VALOR SAIDA', 'right', 'title');

                
                // controls the background filling
                $colour = FALSE;
                $date = null;
                $id = null;
                
                $valor_saida_total = 0;
                $sum_valores_total = 0;
                // data rows
                foreach ($objects as $object)
                {
                    
                    // $style = $colour ? 'datap' : 'datai';
                    // $tr->addRow();
                    // $tr->addCell($object->tipo_entrada_saida->nome, 'left', $style);
                    // $tr->addCell($object->descricao, 'left', $style);
                    // $tr->addCell(Convert::toMonetario($object->valor_saida), 'right', $style);
                    // $tr->addCell(Convert::toDateBr($object->dtcadastro), 'right', $style);

                    if(is_null($date)){
                        $date = $object->dtcadastro;
                        $tr->addRow();
                        $tr->addCell("Saida do dia ".Convert::toDateBR($object->dtcadastro) , 'left', 'footer', 4);
                    }

                    $style = $colour ? 'datap' : 'datai';
                    
                    // confiro se a data atual é diferente da anterior pra saber
                    // quando pular a linha e quando não pular
                    if($object->dtcadastro != $date){
                        // somatorio das reserva daquele dia
                        $tr->addRow();
                        $tr->addCell('Valor total', 'left', 'footer', 2);
                        $tr->addCell(Convert::toMonetario($valor_saida_total), 'right', 'footer', 2);
                        $tr->addRow();
                        
                        $date = $object->dtcadastro;
                        $valor_saida = 0;
                    
                        $tr->addRow();
                        $tr->addCell("Ocupações do dia ".Convert::toDateBR($object->dtcadastro) , 'left', 'footer', 4);
                    }

                    // conferindo se o id do mapa_reserva é igual ao anterior 
                    // pra não duplicar os valores na hora de gerar o relatorio
                    // tambem já estou somando os valores de cada dia, e o valor completo do quartos
                    
                    if($object->id != $id){
                        $valor_saida = $object->valor_saida;
                        $valor_saida_total += $valor_saida;
                        $sum_valores_total += $valor_saida_total;
                        $id = $object->id;
                    }
                    
                    $tr->addRow();
                    $tr->addCell(Convert::toDateBR($object->dtcadastro), 'left', $style);
                    $tr->addCell($object->tipo_entrada_saida->nome, 'left', $style);
                    $tr->addCell($object->descricao, 'left', $style);
                    $tr->addCell(Convert::toMonetario($object->valor_saida), 'right', $style);
                    
                    $colour = !$colour;
                }
                
                // adicionando a ultima linha do relatorio
                $tr->addRow();
                $tr->addCell('Valor total', 'left', 'footer', 2);
                $tr->addCell(Convert::toMonetario($valor_saida_total), 'right', 'footer', 2);
                
                // valor total das ocupações
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('valor total do relatorio', 'left', 'footer', 2);
                $tr->addCell(Convert::toMonetario($sum_valores_total), 'right', 'footer', 2);

                // footer row
                $tr->addRow();
                $tr->addCell('Relatorio de '.date('d/m/Y h:i:s'), 'center', 'footer', 4);
                
                // stores the file
                if (!file_exists("app/output/Saida.{$format}") OR is_writable("app/output/Saida.{$format}"))
                {
                    $tr->save("app/output/Saida.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Saida.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Saida.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatorio Gerado com sucesso');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encotrado');
            }
    
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
