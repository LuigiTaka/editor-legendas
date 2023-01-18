<?php

namespace EditorLegenda\Legendas;

interface LegendaStringBuilder
{

    /**
     * @param LegendaDTO[] $legendas
     * @return string
     */
    public function build(array $legendas) : string;

}