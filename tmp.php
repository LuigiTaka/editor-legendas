<?php



require_once __DIR__."/autoload.php";

$parser = new \EditorLegenda\SrtParser();

$dto = new \EditorLegenda\LegendaDTO( "Legenda aqui",1,["00:00:30,000","00:01:00,000"] );
$dto = $dto->addOffset(300);
var_dump($dto->intervalo);
die;
$conteudo = trim(file_get_contents(__DIR__."/data/But.Im.a.Cheerleader.(1999).eng.srt"));
$legendas = $parser->parse( $conteudo );


file_put_contents(__DIR__."/data/tete.srt", $parser->build( $legendas ) );