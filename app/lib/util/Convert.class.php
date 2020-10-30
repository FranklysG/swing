<?php

class Convert
{

    public static function toCPF_CNPJ($value){
        $cnpj_cpf = preg_replace("/\D/", '', $value);
        
        if (strlen($cnpj_cpf) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
        } 
        
        return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
    }

    public static function toDouble($value)
	{
		if (is_array($value)) {
			$values = array();
			foreach ($value as $val) {
				$values[] = self::toDouble($val);
			}
			return $values;
		} else {
			if (is_int(strpos((string)$value, ','))) {
				$value = str_replace('R$', '', (string) $value);
				$value = str_replace('%', '', (string) $value);
				$value = str_replace('.', '', (string) $value);
				$value = (double) str_replace(',', '.', (string) $value);
			}
	
			return round($value, 2);
		}
	}

    public static function toPesoKG($value)
    {
        // o erro era nos dados update de correção
        return number_format((string) $value, 0,'.',' ');
    }

    public static function toNumeric($value)
    {
        // o erro era nos dados update de correção
        return number_format((string) $value, 2,'.',',');
    }

    public static function toMonetario($value)
    {
        return 'R$ ' . number_format((string) $value, 2, ',', '.');
    }

    public static function toExtenso($value)
    {
        $value = str_replace('.', '', (string) $value);
        $value = str_replace(',', '', (string) $value);
        return GExtenso::moeda($value, 2);
    }

    public static function toClean($value)
    {
        $value = str_replace('.', '', (string) $value);
        $value = str_replace(',', '', (string) $value);
        $value = str_replace('-', '', (string) $value);
        $value = str_replace('/', '', (string) $value);
        $value = str_replace('(', '', (string) $value);
        $value = str_replace(')', '', (string) $value);
        $value = str_replace('[', '', (string) $value);
        $value = str_replace(']', '', (string) $value);
        $value = str_replace('{', '', (string) $value);
        $value = str_replace('}', '', (string) $value);
        $value = str_replace('%', '', (string) $value);
        $value = str_replace('R$', '', (string) $value);
        return $value;
    }


    public static function toOnlyNumber($value)
    {
        $matches = null;
        preg_match_all('!\d+!', $value, $matches);
        return implode('', $matches[0]);
    }


    public static function inStdClass($array)
    {
        $std = new StdClass;
        if (!is_array($array)) {
            $array = $array->toArray();
        }
        foreach ($array as $key => $value) {
            if(is_array($value))
                $std->$key = self::inStdClass($value);
            else
                $std->$key = $value;
        }
        return $std;
    }

    public static function toSizeR($value, $size)
    {
        $value = substr((string) $value, 0, $size);
        $value = str_pad((string) $value, $size, '0', STR_PAD_RIGHT);
        return $value;
    }

    public static function toSizeL($value, $size)
    {
        // $value = substr($value, 0, $size);
        $value = str_pad((string) $value, $size, '0', STR_PAD_LEFT);
        return $value;
    }

    // de datas BR para US
    public static function toDateUS($value)
    {
        $date = DateTime::createFromFormat( 'd-mY', $value );
        return $date->format('Y-m-d');
    }

    // de datas US para BR
    public static function toDateBR($value)
    {
        $date = new DateTime($value);
        return $date->format('d/m/Y');
    }

    public static function toDatTimeUS($value)
    {
        $date = new DateTime($value);
        return $date->format('Y-m-d');
    }

    public static function toDateTimeBR($value)
    {
        $date = new DateTime($value);
        return $date->format('d/m/Y H:i:s');
    }

    // pegar o mes o dia ou ano da data us
    public static function toSeparatorDateUS($value){
        if($value != " "){
            $dia_mes_ano = explode('-', $value);
            return $dia_mes_ano;
        }else{
            return ' ';
        }
    }

    // quando tiver pegando a data de um arquivo json e tiver dando erro tenta essa
    public static function toDateJson($value){
        if($value != " "){
            $dia_mes = explode('/', $value);
            $year = explode(' ', $dia_mes[2]);
            return $dia_mes[0].'/'.$dia_mes[1].'/'.$year[0];
        }else{
            return ' ';
        }
    }
    
    public static function noAccent($str){
        return preg_replace('{W/}', '_', preg_replace('{ +}', '_', strtr(
            utf8_decode(html_entity_decode($str)),
            utf8_decode('?ÀÁÃÂÉÊÍÓÕÔÚÜÇÑàáãâéêíóõôúüçñ'),
            'AAAAAEEIOOOUUCNaaaaeeiooouucn')));
    }

    public static function toWithoutAccent($str)
    {
        $a = array('/(à|á|â|ã|ä|å|æ)/','/(è|é|ê|ë)/','/(ì|í|î|ï)/','/(ð|ò|ó|ô|õ|ö|ø)/','/(ù|ú|û|ü)/','/ç/','/þ/','/ñ/','/ß/','/(ý|ÿ)/','/[^a-z0-9_ -.]/s');
        $b = array('a','e','i','o','u','c','d','n','s','y','-','');
        return trim(trim(trim(preg_replace('/-{2,}/s', '-', preg_replace($a, $b, strtolower($str)))), '_'), '-');
    }

    public static function toUpper($str)
    {
        return strtr(strtoupper($str), "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß");
    }

    public static function toLower($str)
    {
        return strtr(strtolower($str), "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
    }

    public static function toMask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i<=strlen($mask)-1; $i++) {
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

    public static function toWindowData($param, $window_name = 'Dados')
    {
        // show form values inside a window
        $win = TWindow::create($window_name, 0.6, 0.8);
        $win->add('<pre>'.str_replace("\n", '<br>', print_r($param, true)).'</pre>');
        $win->show();
    }

    public static function rMes($string , $value = 'm'){
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

        if(!empty($value)){
            foreach ($meses as $key => $value) {
               if($string == $key){
                    return $value;
               }
            }
        }else{
            foreach ($meses as $key => $value) {
                if($string == $key){
                     return $key;
                }
             }
        }
 
    }
}
