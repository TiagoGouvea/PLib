<?php

/**
 * @author Tiago
 */
namespace TiagoGouvea;

class PLib
{

    static function only_numbers($str)
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    static function format_cash($aGrana,$currency='R$')
    {
        if ($aGrana == '')
            $aGrana = 0;
        return $currency . number_format($aGrana, 2, ",", ".");
    }

//    static function format_date($aData, $aTextoNulo = null)
//    {
//        if ($aData == '' || $aData == 0 || $aData == '0001-01-01T00:00:00') {
//            if ($aTextoNulo != null)
//                return $aTextoNulo;
//            else
//                return '';
//        }
//        //var_Dump($aData);
//        $aData = substr($aData, 0, 10);
//        $aData = strtotime($aData);
//        return date('d/m/Y', $aData);
//    }



    public static function coalesce($value, $defaulValue)
    {
        return ($value != null ? $value : $defaulValue);
    }

    public static function validade_cpf($cpf = null)
    {
        function calc_digitos_posicoes($digitos, $posicoes = 10, $soma_digitos = 0)
        {
            for ($i = 0; $i < strlen($digitos); $i++) {
                $soma_digitos = $soma_digitos + ($digitos[$i] * $posicoes);
                $posicoes--;
            }
            $soma_digitos = $soma_digitos % 11;
            if ($soma_digitos < 2) {
                $soma_digitos = 0;
            } else {
                $soma_digitos = 11 - $soma_digitos;
            }
            $cpf = $digitos . $soma_digitos;
            return $cpf;
        }
        if (!$cpf)
            return false;
        $cpf = self::only_numbers('/[^0-9]/is', '', $cpf);
        if (strlen($cpf) != 11) {
            return false;
        }
        $digitos = substr($cpf, 0, 9);
        $novo_cpf = calc_digitos_posicoes($digitos);
        $novo_cpf = calc_digitos_posicoes($novo_cpf, 11);
        return $novo_cpf === $cpf;
    }

    /**
     * Aplly mask to string
     * Example:
     * $value=3288735683;
     * $mask=(##)####-####;
     * echo str_mask($value,$mask); // (32)8873-5683
     * @param $val
     * @param $mask
     * @return string
     */
    static function str_mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    static function var_dump($data,$title=null){
        echo "<br>";
        if ($title)
            echo "<B>$title</B>";
        echo "<pre>";
        var_Dump($data);
        echo "</pre>";
    }


    /**** Date relative methods *****/


    const TIMESTAMP_UM_DIA = 86400;
    const TIMESTAMP_UM_MINUTO = 60;
    const TIMESTAMP_UMA_HORA = 3600;
    const TIMESTAMP_UMA_SEMANA = 604800;

    /**
     * Converte data no formato [aaaa-mm-dd hh:mm:ss]/[aaaa-mm-dd] para [dd abreviacaoMes]
     * Ex: 2010-08-12 => 12 ago
     * @param string $aDataHora aaaa-mm-dd hh:mm:ss ou aaaa-mm-dd
     * @return string dd abreviacaoMes
     */
    public static function dataDiaMesExtenso($aDataHora, $aExibirHora = false, $abreviarMes = true)
    {
        $meses = array('janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'oututbro', 'novembro', 'dezembro');
        $mes = $meses[date('n', strtotime($aDataHora)) - 1];
        if ($abreviarMes) $mes = substr($mes, 0, 3);
        return date('d', strtotime($aDataHora)) . ' ' . $mes . ' ' . ($aExibirHora ? 'às ' . date('H:i', strtotime($aDataHora)) : '');
    }

    /**
     * Converte data no formato [aaaa-mm-dd hh:mm:ss] para [HhMmin]
     * @param string $arg aaaa-mm-dd hh:mm:ss
     * @return string HhMmin
     */
    public static function dataToHora($arg)
    {
        return date('H:i', strtotime($arg));
    }

    /**
     * Converte data em relação ao tempo nos moldes do orkut
     * Se a data é hoje, exibe no formato [Hh Mmin]
     * Se a data é no ano corrente, exibe no formato [dd abreviacaoMes]
     * Senão, exibe no formato [dd/mm/aaaa]
     * @param string $aData aaaa-mm-dd hh:mm:ss
     * @return string (Hh Mmin) ou (dd abreviacaoMes) ou (dd/mm/aaaa)
     */
    public static function dataRelativa($aData, $aExibirHora = false, $abreviarMes = true)
    {
        if ($aData == null) {
            return null;
        }
        $aData = strtotime($aData);
        $hora = date('H:i', $aData);
        if (date('d/m/Y', $aData) == date('d/m/Y') && date('Hi', $aData) != '0000') {
            // Hoje!
            return 'Hoje às ' . self::dataToHora(date('Y-m-d H:i:s', $aData));
        } elseif ($aData > time() && $aData < strtotime(date('Y-m-d 23:59:59 ', time() + self::TIMESTAMP_UM_DIA))) {
            // Amanhã
            if ($hora != '00:00') {
                return 'Amanhã às ' . $hora;
            } else {
                return 'Amanhã';
            }
        } elseif ($aData < time() && $aData > strtotime(date('Y-m-d 00:00:00', time() - self::TIMESTAMP_UM_DIA))) {
            // Ontem
            if ($hora != '00:00') {
                return 'Ontem às ' . $hora;
            } else {
                return 'Ontem';
            }
        } elseif (date('Y', $aData) == date('Y')) {
            // Neste ano
            if ($hora != '00:00') {
                return self::dataDiaMesExtenso(date('Y-m-d H:i:s', $aData), true, $abreviarMes);
            } else {
                return self::dataDiaMesExtenso(date('Y-m-d', $aData), false, $abreviarMes);
            }
        } else {
            // Em outro ano
            if ($hora != '00:00') {
                return self::sqlToDataHora(date('Y-m-d H:i:s', $aData), true);
            } else {
                return self::sqlToData(date('Y-m-d', $aData), false);
            }
        }

        return $aData;
    }


    public static function days_between_dates($aData1, $aData2 = null)
    {
        if ($aData2 == null)
            $aData2 = time();
        else
            $aData2 = strtotime($aData2);

        $aData1 = strtotime($aData1);
        $datediff = $aData2 - $aData1;
        if ($datediff < 0) $datediff = -$datediff;
        return floor($datediff / (60 * 60 * 24));
    }


    /*** Date and mysql methods ***/


    /**
     * Converte data no formato [aaaa-mm-dd hh:mm:ss]/[aaaa-mm-dd] para [dd/mm/aaaa]
     * @param string $aDataHora aaaa-mm-dd hh:mm:ss ou aaaa-mm-dd
     * @return string dd/mm/aaaa
     */
    public static function sqlToData($aDataHora, $aPermitirVazio = false, $aExibirHora = false)
    {
        if (empty($aDataHora)) {
            if (!$aPermitirVazio) {
                $aDataHora = date('Y-m-d H:i:s');
                //var_dump($aDataHora);die();
            } else {
                return '';
            }
        }

        return date('d/m/Y', strtotime($aDataHora)) . ' ' . ($aExibirHora ? date('H:i', strtotime($aDataHora)) : '');
    }

    /**
     * Converte data no formato [aaaa-mm-dd hh:mm:ss]/[aaaa-mm-dd] para [dd/mm/aaaa HhMmin]
     * @param string $arg aaaa-mm-dd hh:mm:ss ou aaaa-mm-dd
     * @return string dd/mm/aaaa HhMmin
     */
    public static function sqlToDataHora($arg, $permitirvazio = false)
    {
        if ($arg == '0000-00-00 00:00:00' or empty($arg)) {
            if ($permitirvazio) {
                return '';
            } else {
                return '00/00/0000 00h00min';
            }
        }

        return date('d/m/Y H\:i', strtotime($arg));
    }


}
