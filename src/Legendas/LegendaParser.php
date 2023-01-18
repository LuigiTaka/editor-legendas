<?php

namespace EditorLegenda\Legendas;

interface LegendaParser
{
    public function parse(string $legendas) : array;

}