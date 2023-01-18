<?php

namespace EditorLegenda\Legendas;
use function PHPUnit\TestFixture\func;

/**
 * Carrega as informações da legenda
 */
class LegendaDTO
{

    /**
     * @param string $legenda Texto presente na faixa de legenda
     * @param int $order posição da legenda
     * @param array $intervalo Intervalo de tempo onde a legenda aparece. Index 0: Tempo de Incial, Index 1: Tempo Final
     */
    public function __construct(
        public readonly string $legenda,
        public readonly int $order,
        public readonly array $intervalo
    )
    {

    }


    /**
     * A duração da legenda permanece a mesma, o que é alterado são os tempos de apresentação da legenda. Ou seja, dividimos o valor nas pontas do intervalo
     * @param int $ms
     * @return LegendaDTO
     */
    public function addOffset( int $ms ) : LegendaDTO
    {

        $currentIntervalo = $this->intervalo;
        $halfTime = $ms/2;
        $intervalo = array_map(function ($intervalo) use ($halfTime){
            return $this->addMiliseconds( $intervalo,$halfTime);
        },$currentIntervalo);
        return new LegendaDTO( $this->legenda,$this->order,$intervalo );
    }

    /**
     * @param string $intervalo
     * @param $ms
     * @return string
     */
    protected function addMiliseconds( string $intervalo, $ms ): string
    {
        $parts = explode(":",$intervalo);
        $parts = array_combine(["hora",'minuto','segundo'],$parts);

        $secondMilisecond = explode(',',$parts['segundo']);
        $parts['segundo'] = $secondMilisecond[0];
        $parts['ms'] = $secondMilisecond[1];


        $fatoresMilisegundos = [
            'hora' => 3600000,
            "minuto" => 60000,
            "segundo" => 1000,
            "ms" => 1,
        ];

        $partsInMillieseconds = [ ];
        foreach ($parts as $key => $value){
            $factor = $fatoresMilisegundos[$key];
            $partsInMillieseconds[$key] = $value * $factor;
        }

        $intervaloOnMiliseconds = array_sum($partsInMillieseconds);
        if ($intervaloOnMiliseconds === 0){
            return $intervalo;
        }

        $intervaloOnMiliseconds += $ms;

        $intervaloNovo = [ ];
        foreach ($fatoresMilisegundos as $unidade => $valor){
             $intervaloNovo[$unidade] = floor(round($intervaloOnMiliseconds/$valor,2));
             $intervaloOnMiliseconds -= ($intervaloNovo[$unidade] * $valor);

             $novoIntervaloTmp = $intervaloNovo[$unidade];
             $novoIntervaloTmp = ($novoIntervaloTmp <= 0)?0:$novoIntervaloTmp;
             $intervaloNovo[$unidade] = $novoIntervaloTmp;
        }
        $msNovo = str_pad((string)$intervaloNovo['ms'],3,"0",STR_PAD_LEFT);
        unset($intervaloNovo['ms']);

        $intervaloNovo = array_map(function($val){
            return str_pad($val,2,'0',STR_PAD_LEFT);
        },$intervaloNovo);
        return implode(":",$intervaloNovo).",$msNovo";
    }

}