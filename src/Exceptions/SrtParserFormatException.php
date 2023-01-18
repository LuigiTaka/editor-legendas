<?php

namespace EditorLegenda\Exceptions;

class SrtParserFormatException extends \Exception
{
    const ORDEM_NAO_NUMERICA = "Número de sequência da legenda não é um número.";
    const DELMITADOR_TIMESTAMP_NOT_FOUND = "Delimitador do intervalo da legenda não encontrado.";
    const TIMESTAMP_BAD_FORMAT = "Intervalo da legenda não apresenta o formato correto.";
    const LEGENDA_VAZIA = "Sem legenda para o bloco atual..";
    const MIN_SIZE_BLOCO_LEGENDA_ERROR = "Número mínimo de linhas não atingido.";

}