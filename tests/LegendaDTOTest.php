<?php


use EditorLegenda\Legendas\LegendaDTO;
use PHPUnit\Framework\TestCase;

class LegendaDTOTest extends TestCase
{


    /**
     * @test
     * @dataProvider  validLegendaDataProvider
     * @param $ordem
     * @param $timestamp
     * @param $legenda
     * @param $timestampEsperado
     * @return void
     */
    public function addOffset( $ordem, $timestamp,$legenda, $timestampEsperado,$addMs ): void
    {
        $dto = new LegendaDTO( $legenda,$ordem,$timestamp );
        $dto = $dto->addOffset($addMs);

        $this->assertEquals( $timestampEsperado[0], $dto->intervalo[0], "O tempo inicial modificado: ".$dto->intervalo[0]." não bate com o valor esperado:".$timestampEsperado[0] );
        $this->assertEquals( $timestampEsperado[1], $dto->intervalo[1], "O tempo inicial modificado: ".$dto->intervalo[1]." não bate com o valor esperado:".$timestampEsperado[1] );
    }

    public function validLegendaDataProvider(): array
    {
        return [

            [  1,
                ["00:00:30,000","00:01:0,000"],
                "Legenda aqui",
                ["00:00:30,150","00:01:00,150"],
                300
            ],
            [
                1,
                ["00:01:33,177","00:01:35,470"],
                "legenda aqui",
                ["00:01:33,677","00:01:35,970"],
                1000
            ],
            [
                1,
                ["00:05:03,011","00:05:04,929"],
                "legenda aqui",
                ["00:05:04,011","00:05:05,929"],
                2000
            ],
        ];
    }
}
