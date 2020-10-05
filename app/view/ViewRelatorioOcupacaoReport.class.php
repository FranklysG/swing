<?php
/**
 * ViewRelatorioOcupacaoReport Report
 * @author  <your name here>
 */
class ViewRelatorioOcupacaoReport extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ViewRelatorioOcupacao_report');
        $this->form->setFormTitle('Relatorio de ocupações');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $date_ini = new TDate('date_ini');
        $date_ini->setMask('dd/mm/yyyy');
        $date_ini->setDatabaseMask('yyyy-mm-dd');
        $date_end = new TDate('date_end');
        $date_end->setMask('dd/mm/yyyy');
        $date_end->setDatabaseMask('yyyy-mm-dd');
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $row = $this->form->addFields( 
                                [ new TLabel('Data Inicial'), $date_ini ],
                                [ new TLabel('Data Final'), $date_end ],
                                [ new TLabel('Tipo de arquivo'), $output_type ] 
                            );
        $row->layout = ['col-sm-4','col-sm-4','col-sm-4'];

        $output_type->addValidation('Tipo de arquivo', new TRequiredValidator);

        $output_type->addItems(['pdf'=>'PDF', 'xls' => 'XLS']);
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
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
            
            $repository = new TRepository('ViewRelatorioOcupacao');
            $criteria   = new TCriteria;

            if ((isset($data->date_ini) and $data->date_ini) and (isset($data->date_end) and $data->date_end))
            {
                $criteria->add(new TFilter('dtcadastro', 'between', "{$data->date_ini}", "{$data->date_end}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(80,80,80,80,160,100);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '11', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '11', 'B',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '11', 'B',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '14', 'B',   '#ffffff', '#6B6B6B');
                // $tr->addStyle('footer', 'Arial', '11', 'B',  '#000000', '#A3A3A3');
                $tr->addStyle('footer', 'Arial', '11', 'B',  '#232323', '#D0D0D0');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Relatorio de ocupações', 'center', 'header', 6);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('DATA', 'left', 'title');
                $tr->addCell('N° QTO', 'left', 'title');
                $tr->addCell('VALOR', 'left', 'title');
                $tr->addCell('CONSUMO', 'left', 'title');
                $tr->addCell('NOME', 'left', 'title');
                $tr->addCell('VL UNI', 'left', 'title');

                // controls the background filling
                $colour = FALSE;
                $date = null;
                $id = null;

                // data rows
                foreach ($objects as $object)
                {
                    if(is_null($date)){
                        $date = $object->dtcadastro;
                        $tr->addRow();
                        $tr->addCell("Ocupações do dia ".Convert::toDateBR($object->dtcadastro) , 'left', 'footer', 6);
                    }

                    $style = $colour ? 'datap' : 'datai';
                    
                    // confiro se a data atual é diferente da anterior pra saber
                    // quando pular a linha e quando não pular
                    if($object->dtcadastro != $date){
                        // somatorio das reserva daquele dia
                        $tr->addRow();
                        $tr->addCell('Valor total', 'left', 'footer', 3);
                        $tr->addCell(Convert::toMonetario($sum_valores_dia), 'right', 'footer', 3);
                        $tr->addRow();
                        
                        $sum_valores_dia = 0;
                        $date = $object->dtcadastro;
                        
                        $tr->addRow();
                        $tr->addCell("Ocupações do dia ".Convert::toDateBR($object->dtcadastro) , 'left', 'footer', 6);
                    }

                    // conferindo se o id do mapa_reserva é igual ao anterior 
                    // pra não duplicar os valores na hora de gerar o relatorio
                    // tambem já estou somando os valores de cada dia, e o valor completo do quartos
                    $valor_quarto = '';
                    $valor_consumo = '';
                    if($object->id != $id){
                        $valor_quarto = $object->valor_quarto;
                        $valor_consumo = $object->valor_consumo;
                        $valor_quarto_consumo = ($object->valor_quarto + $object->valor_consumo);
                        $sum_valores_reserva_total += $valor_quarto_consumo;
                        $sum_valores_dia += $valor_quarto_consumo;
                        $id = $object->id;
                    }
                    
                    $tr->addRow();
                    $tr->addCell(Convert::toDateBR($object->dtcadastro), 'left', $style);
                    $tr->addCell($object->n_quarto, 'right', $style);
                    $tr->addCell((!empty($valor_quarto))?Convert::toMonetario($valor_quarto) : '', 'right', $style);
                    $tr->addCell((!empty($valor_consumo))?Convert::toMonetario($valor_consumo) : '', 'right', $style);
                    $tr->addCell(mb_strimwidth($object->produto_nome, 0, 21, "..."), 'left', $style);
                    $tr->addCell(Convert::toMonetario($object->valor_venda_uni), 'right', $style);

                    
                    $colour = !$colour;
                }
                
                // adicionando a ultima linha do relatorio
                $tr->addRow();
                $tr->addCell('Valor total', 'left', 'footer', 3);
                $tr->addCell(Convert::toMonetario($sum_valores_dia), 'right', 'footer', 3);
                
                // valor total das ocupações
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('valor total do relatorio', 'left', 'footer', 3);
                $tr->addCell(Convert::toMonetario($sum_valores_reserva_total), 'right', 'footer', 3);

                // footer row
                $tr->addRow();
                $tr->addCell('Relatorio de '.date('d/m/Y h:i:s'), 'center', 'footer', 6);
                
                // stores the file
                if (!file_exists("app/output/relatorio_ocupacao.{$format}") OR is_writable("app/output/relatorio_ocupacao.{$format}"))
                {
                    $tr->save("app/output/relatorio_ocupacao.{$format}");
                }
                else
                {
                    throw new Exception('Verifique a permissão da pasta '. ': ' . "app/output/relatorio_ocupacao.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/relatorio_ocupacao.{$format}");
                $criteria->resetProperties();
                // shows the success message
                TScript::create("__adianti_load_page('index.php?class=ViewRelatorioOcupacaoReport');");
                new TMessage('info', 'Relatorio gerado com sucesso');
            
                
            }
            else
            {
                new TMessage('error', 'Nenhum Registro entre essas datas');
                new TMessage('error', 'data inicial não pode ser maior que data final');
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
