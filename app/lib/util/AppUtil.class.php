<?php

use Adianti\Widget\Form\TDate;

class AppUtil
{
    public static function objectInStdClass($obj)
    {
        $array = $obj->toArray();
        $std = new StdClass;
        foreach ($array as $key => $value) {
            $std->$key = $value;
        }

        return $std;
    }

    /**
     * Sort array with Isertion Sort
     * @param $my_array array to sort
     * @param $field field array to sort
     * @return Sorted array
     */
    public static function insertionSort($my_array, $field)
    {
        if (is_array($my_array)) {
            for ($i = 0; $i < count($my_array); $i++) {
                $val = $my_array[$i][$field];
                $info = $my_array[$i];
                $j = $i - 1;
                while ($j >= 0 && $my_array[$j][$field] > $val) {
                    $my_array[$j + 1] = $my_array[$j];
                    $j--;
                }
                $my_array[$j + 1] = $info;
            }
            return $my_array;
        } else {
            new TMessage('error', 'Não é um Array');
        }
    }

    public static function toCleanForm($form)
    {
        $array = array();
        foreach ($form->getFields() as $field) {
            if (!$field instanceof TButton) {
                if ($field instanceof TCombo) {
                    $array += [$field->getName() => null];
                } elseif ($field instanceof TDBUniqueSearch) {
                    $field->setValue('');
                } elseif ($field instanceof TDBMultiSearch) {
                    $array += [$field->getName() => null];
                } else {
                    $array += [$field->getName() => ''];
                }
            }
        }

        TForm::sendData($form->getName(), Convert::inStdClass($array));
    }

    public static function Hash($digitos, $tipo = '')
    {
        
        $Caracteres = 'ABCDEFGHIJKLMPQRSTUVXWYZ0123456789';
        if ($tipo == 'a') {
            $Caracteres = 'ABCDEFGHIJKLMPQRSTUVXWYZ';
        }else if ($tipo == '0') {
            $Caracteres = '0123456789';
        }

        $QuantidadeCaracteres = strlen($Caracteres);
        $QuantidadeCaracteres--;

        $Hash = null;
        for ($x = 1; $x <= $digitos; $x++) {
            $Posicao = rand(0, $QuantidadeCaracteres);
            $Hash .= substr($Caracteres, $Posicao, 1);
        }

        return $Hash;
    }

    public static function Chave($model, $campo, $digitos = 6, $prefixo = '', $sufixo = '', $tipo = '')
    {
        $chave = AppUtil::Hash($digitos, $tipo);

        while ($model::where($campo, '=', $chave)->count() == 1) {
            $chave = AppUtil::Hash($digitos);
        }

        return $prefixo . $chave . $sufixo;
    }

    public static function rUf($sigla = '')
    {
        $itens_uf = array();
        $itens_uf['AC'] = (!$sigla) ? 'Acre' : 'AC';
        $itens_uf['AL'] = (!$sigla) ? 'Alagoas' : 'AL';
        $itens_uf['AP'] = (!$sigla) ? 'Amapá' : 'AP';
        $itens_uf['AM'] = (!$sigla) ? 'Amazonas' : 'AM';
        $itens_uf['BA'] = (!$sigla) ? 'Bahia' : 'BA';
        $itens_uf['CE'] = (!$sigla) ? 'Ceará' : 'CE';
        $itens_uf['DF'] = (!$sigla) ? 'Distrito Federal' : 'DF';
        $itens_uf['ES'] = (!$sigla) ? 'Espirito Santo' : 'ES';
        $itens_uf['GO'] = (!$sigla) ? 'Goiás' : 'GO';
        $itens_uf['MA'] = (!$sigla) ? 'Maranhão' : 'MA';
        $itens_uf['MT'] = (!$sigla) ? 'Mato Grosso' : 'MT';
        $itens_uf['MS'] = (!$sigla) ? 'Mato Grosso do Sul' : 'MS';
        $itens_uf['MG'] = (!$sigla) ? 'Minas Gerais' : 'MG';
        $itens_uf['PA'] = (!$sigla) ? 'Pará' : 'PA';
        $itens_uf['PB'] = (!$sigla) ? 'Paraiba' : 'PB';
        $itens_uf['PR'] = (!$sigla) ? 'Paraná' : 'PR';
        $itens_uf['PE'] = (!$sigla) ? 'Pernambuco' : 'PE';
        $itens_uf['PI'] = (!$sigla) ? 'Piauí' : 'PI';
        $itens_uf['RJ'] = (!$sigla) ? 'Rio de Janeiro' : 'RJ';
        $itens_uf['RN'] = (!$sigla) ? 'Rio Grande do Norte' : 'RN';
        $itens_uf['RS'] = (!$sigla) ? 'Rio Grande do Sul' : 'RS';
        $itens_uf['RO'] = (!$sigla) ? 'Rondônia' : 'RO';
        $itens_uf['RR'] = (!$sigla) ? 'Roraima' : 'RR';
        $itens_uf['SC'] = (!$sigla) ? 'Santa Catarina' : 'SC';
        $itens_uf['SP'] = (!$sigla) ? 'São Paulo' : 'SP';
        $itens_uf['SE'] = (!$sigla) ? 'Sergipe' : 'SE';
        $itens_uf['TO'] = (!$sigla) ? 'Tocantins' : 'TO';

        return $itens_uf;
    }

    public static function rMsg($msg)
    {
        $mensagem = '';

        if ($msg) {
            foreach ($msg as $key => $value) {
                # code...

                if ($key == 'img') {
                    $mensagem .= " $value <br><br>";
                } elseif ($key == 'rodape') {
                    $mensagem .= "<br> $value <br>";
                } elseif ($key == 'msg') {
                    $mensagem .= "<br> $value <br>";
                } elseif ($key == 'div') {
                    $mensagem .= "$value";
                } elseif ($key == '/div') {
                    $mensagem .= "$value";
                } else {
                    $mensagem .= "<b>$key:</b> $value <br>";
                }
            }
        }
        return $mensagem;
    }

    public static function vDuplicidadeDados($classModel, $dados, $campo, $id = '')
    {
        if ($id) {
            return ($classModel::where($campo, '=', $dados)
                ->where('id', '<>', $id)->count() == 1);
        } else {
            return ($classModel::where($campo, '=', $dados)->count() == 1);
        }
    }

    public static function fMask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }

    public static function fCpfCnpj($val)
    {
        $mascara = '';

        if (strlen($val) == 11) {
            $mascara = self::fMask($val, '###.###.###-##');
        }
        if (strlen($val) == 14) {
            $mascara = self::fMask($val, '##.###.###/####-##');
        }

        return $mascara;
    }

    public static function fPlaca($val)
    {
        return $mascara = self::fMask($val, '###-####');
    }

    public static function date2brCompleto($data)
    {

        $data = explode(' ', $data);

        return $data = TDate::date2br($data[0]) . ' ' . $data[1];
    }

    public static function calendario($value = 'm'){
        $meses = [
            "01" => 'Jan',
            "02" => 'Fev',
            "03" => 'Mar',
            "04" => 'Abr',
            "05" => 'Mai',
            "06" => 'Jun',
            "07" => 'Jul',
            "08" => 'Ago',
            "09" => 'Set',
            "10" => 'Out',
            "11" => 'Nov',
            "12" => 'Dez'
            
        ];

        return $meses;
    }
    public static function calculoEntreDatas($data1, $data2, $retorno)
    {

        $datatime1 = new DateTime($data1);
        $datatime2 = new DateTime($data2);

        $data1 = $datatime1->format('Y-m-d H:i:s');
        $data2 = $datatime2->format('Y-m-d H:i:s');

        $diff = $datatime1->diff($datatime2);
        $horas = $diff->h + ($diff->d * 24);
        $dias = $diff->d + ($diff->m *30);
        $meses = $diff->m + ($diff->y *12);

        if ($retorno == 'h')
            return $horas;
        if ($retorno == 'd')
            return $dias;
        if ($retorno == 'm')
            return $meses;
    }

    public static function infoCoordenadas($lat, $long)
    {

        /** Exemplo do retorno para captura
         *
        {
        "place_id": 198306368,
        "licence": "Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright",
        "osm_type": "way",
        "osm_id": 608991515,
        "lat": "-4.09903964603161",
        "lon": "-38.5024004742452",
        "display_name": "Rodovia Santos Dumont, Horizonte, Microrregião de Pacajus, Mesorregião Metropolitana de Fortaleza, Ceará, Região Nordeste, 62880-000, Brasil",
        "address": {
        "road": "Rodovia Santos Dumont",
        "town": "Horizonte",
        "county": "Microrregião de Pacajus",
        "state_district": "Mesorregião Metropolitana de Fortaleza",
        "state": "Ceará",
        "postcode": "62880-000",
        "country": "Brasil",
        "country_code": "br"
        },
        "boundingbox": ["-4.1040662", "-4.0604612", "-38.5038596", "-38.4980512"]
        } */
        $localizacao = '';

        if (!empty($lat) and !empty($long)) {
            $consulta = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$long";

            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $consulta);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Coordenadas');
            $resultado = curl_exec($curl_handle);
            curl_close($curl_handle);

            $dados = json_decode($resultado);

            $endereco = ($dados->address->road == '') ? '' : $dados->address->road . ', ';
            $cidade = ($dados->address->town == '') ? '' : $dados->address->town . '/';
            $uf = $dados->address->state;
            $pais = $dados->address->country;

            if ($resultado)
                $localizacao = $endereco . $cidade . $uf . ' - ' . $pais;
        }
        return $localizacao;
    }

    public static function rDiaSemana($dia = '')
    {
        $itens_dia = array();
        $itens_dia['0'] = "Domingo";
        $itens_dia['1'] = "Segunda";
        $itens_dia['2'] = "Terça";
        $itens_dia['3'] = "Quarta";
        $itens_dia['4'] = "Quinta";
        $itens_dia['5'] = "Sexta";
        $itens_dia['6'] = "Sabado";

        if ($dia) {
            return $itens_dia[$dia];
        }

        return $itens_dia;
    }

    public static function envioRest($url_rest, $posts)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url_rest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_POST, true);

        if (is_array($posts)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function rPeriodo($data_inicio, $periodo_dias, $formato = 'd/m/Y')
    {
        $period = new DatePeriod(
            new DateTime($data_inicio),
            new DateInterval('P1D'),
            (new DateTime($data_inicio))->add(new DateInterval("P{$periodo_dias}D"))
        );
        $dias = array();
        foreach ($period as $key => $value) {
            $dias[$value->format('Y-m-d')] = $value->format($formato);
        }

        return $dias;
    }

    public static function limita_caracteres($texto, $limite, $quebra = true){
        $tamanho = strlen($texto);
        if($tamanho <= $limite){
           $novo_texto = $texto;
        }else{
           if($quebra == true){ 
              $novo_texto = trim(substr($texto, 0, $limite))."...";
           }else{
              $ultimo_espaco = strrpos(substr($texto, 0, $limite), " ");
              $novo_texto = trim(substr($texto, 0, $ultimo_espaco))."..."; 
           }
        }
        return $novo_texto; 
     }
}
