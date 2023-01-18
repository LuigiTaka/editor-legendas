<?php
require_once __DIR__."/../autoload.php";

$legendas = json_decode( $_POST['legenda'],true );
$legendas = array_map(function ($l){
    return new \EditorLegenda\Legendas\LegendaDTO($l['legenda'],$l['order'],$l['intervalo']);
},$legendas);

$parser = new EditorLegenda\Legendas\SrtParser();
$content = $parser->build( $legendas );
$filename = empty($_POST['legenda'])?trim($_POST['titulo']):"legenda";
$filename .= ".txt";

ob_start();
header("Pragma: public");
header("Content-Type: application/force-download");
header("Content-Type: text");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=$filename");
echo $content;