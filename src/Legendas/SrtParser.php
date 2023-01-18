<?php

namespace EditorLegenda\Legendas;

use EditorLegenda\Legendas\LegendaDTO;
use EditorLegenda\Exceptions\SrtParserFormatException;
use EditorLegenda\Legendas\LegendaParser;
use EditorLegenda\Legendas\LegendaStringBuilder;

class SrtParser implements LegendaParser, LegendaStringBuilder
{
    const END_OF_SUBTITLE_REGEX = "\n\s*\n";
    const TIMESTAMP_DELIMITER = "-->";
    const MIN_SIZE_BLOCO_LEGENDA = 3;

    /**
     * @throws SrtParserFormatException
     */
    public function parse(string $legendas): array
    {
        $legendasSeparadas = preg_split("/".self::END_OF_SUBTITLE_REGEX."/",$legendas);

        $legendas = [ ];
        foreach ($legendasSeparadas as $index => $blocoLegenda)
        {
            $this->isValidSrtFormat( $blocoLegenda );

            $linhas = explode(PHP_EOL,$blocoLegenda);
            $ordem = (int)trim(array_shift($linhas));
            $timestamps = explode("-->",array_shift($linhas));
            $timestamps = array_map("trim",$timestamps);
            $legendaTexto =  implode(PHP_EOL,$linhas);
            $dto = new LegendaDTO( $legendaTexto,$ordem,$timestamps );
            $legendas[] = $dto;
        }

        return $legendas;
    }


    public function build(array $legendas): string
    {

        $arquivo = "";
        foreach ($legendas as $index => $block){
            $legenda = "";
            $legenda  .= $block->order.PHP_EOL;
            $legenda .= $block->intervalo[0]." ". self::TIMESTAMP_DELIMITER ." ".$block->intervalo[1].PHP_EOL;
            $legenda .= $block->legenda.PHP_EOL;
            $arquivo .= $legenda.PHP_EOL;
        }

        return $arquivo;
    }

    /**
     * @param string $blocoLegenda
     * @return bool
     * @throws SrtParserFormatException
     */
    protected function isValidSrtFormat( string $blocoLegenda ): bool
    {

        $linhas = explode(PHP_EOL,$blocoLegenda);
        $qtdeLinhas = count($linhas);
        if ($qtdeLinhas < self::MIN_SIZE_BLOCO_LEGENDA){
            throw new SrtParserFormatException(SrtParserFormatException::MIN_SIZE_BLOCO_LEGENDA_ERROR);
        }


        //primeira linha deve ser a sequência númerica da legenda
        $numero = trim(array_shift( $linhas ));
        $numero = preg_replace("/[^\d]/","",$numero);
        if (!ctype_digit( $numero )){
            throw new SrtParserFormatException( SrtParserFormatException::ORDEM_NAO_NUMERICA  );
        }

        //Temos o formato: hh:mm:ss,ms --> hh:mm:ss:,ms
        $intervaloTempo = array_shift($linhas);
        if (!str_contains($intervaloTempo,self::TIMESTAMP_DELIMITER)){
            throw new SrtParserFormatException(SrtParserFormatException::DELMITADOR_TIMESTAMP_NOT_FOUND);
        }

        $timestampRegex = "\d{2}:\d{2}:\d{2},\d{3}";
        $intervaloTempo = explode(self::TIMESTAMP_DELIMITER,$intervaloTempo);
        foreach ($intervaloTempo as $tempo){
            $tempo = trim($tempo);
            if ( !preg_match("/".$timestampRegex."/",$tempo) ){
                throw new SrtParserFormatException(SrtParserFormatException::TIMESTAMP_BAD_FORMAT);
            }
        }

        if (empty($linhas)){
            throw new SrtParserFormatException(SrtParserFormatException::LEGENDA_VAZIA);
        }


        return true;

    }
}