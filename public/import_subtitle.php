<?php

use EditorLegenda\Session;
use EditorLegenda\Legendas\SrtParser;

require_once __DIR__ . "/../autoload.php";

session_start();

$legendasJSON = null;
$filename = 'legenda';
if (isset($_FILES['arquivo']) && isset($_POST['enviar'])) {
    $filename = $_FILES['arquivo']['name'] . ".srt";
    $filepath = __DIR__ . "/../data/$filename";
    move_uploaded_file($_FILES['arquivo']['tmp_name'], $filepath);
    $pathinfo = pathinfo($filepath);
    if (!empty($pathinfo['filename'])){
        $filename = preg_replace("/\.".$pathinfo['extension']."$/","",$pathinfo['filename']);
    }
    $legenda = trim(file_get_contents($filepath));
    $parser = new SrtParser();
    try {
        $legendasDTO = $parser->parse($legenda);
        $legendasJSON = json_encode($legendasDTO, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (json_last_error()){
            throw new Exception(json_last_error_msg());
        }
    } catch (\EditorLegenda\Exceptions\SrtParserFormatException $e) {
        //@todo tratar erros...
        throw $e;
    }
}else{
    header("Location: /public/index.html");
}

?>


<!doctype html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Editor de Legendas - Importação de Arquivo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">


    <style>

        .form-wrapper {
            max-width: 80%;
        }


        .header {
            font-size: .9rem;
        }

        .order {
            padding-right: 1rem;
        }

        .order::after {
            content: "ª";
        }

        .legenda {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .flex-wrapper {
            flex-wrap: wrap-reverse;
        }

        .more-vertical.icon {
            color: #000;
            position: absolute;
            margin-left: 9px;
            margin-top: 9px;
            width: 2px;
            height: 2px;
            border-radius: 50%;
            border: solid 1px currentColor;
        }

        .more-vertical.icon:before {
            content: '';
            position: absolute;
            left: -1px;
            top: -8px;
            width: 2px;
            height: 2px;
            border-radius: 50%;
            border: solid 1px currentColor;
        }

        .more-vertical.icon:after {
            content: '';
            position: absolute;
            left: -1px;
            top: 6px;
            width: 2px;
            height: 2px;
            border-radius: 50%;
            border: solid 1px currentColor;
        }



    </style>

</head>
<body>

<div class="container-fluid">
    <div class="mt-2">
        <div>
            <h1> <?= $filename ?> </h1>
        </div>
        <p>Ao finalizar de editiar o seu arquivo, é possível clicar em <i>baixar</i> para obter o arquivo com as modificações realizadas.</p>
        <p>Caso queira trabalhar com outro arquivo, basta clicar em finalizar e será redirecionado para a página inicial.</p>
        <hr/>
    </div>

    <div class="flex-center">
        <div class="flex-wrapper hud row">

            <div class="legendas col col-sm-12 col-md-4">
                <div class="d-flex flex-row-reverse align-items-end mb-2">
                    <form action="/api/download_file.php" method="post" id="download_form">
                        <div class="btn-group mb-2" role="group" aria-label="Ações das legendas">
                            <button class="btn btn-light"
                                    id="download"
                                    name="action"
                                    value="download"
                                    title="Faz o download do arquivo com as modificações feitas nas legendas">Baixar
                            </button>

                            <button name="action"
                                    value="mod-time"
                                    disabled
                                    class="btn btn-light btn-sm">Ajustar Tempo
                            </button>

                            <button name="nova-legenda"
                            class="btn btn-light btn-sm" id="finalizar"> Finalizar </button>
                            <input type="text" id="legenda-content" name="legenda" hidden>
                        </div>
                    </form>
                    <span class="mr-auto text-muted">Lista de Legendas</span>
                </div>

                <div class="overflow-auto" style="max-height: 400px; overflow-y: scroll">
                    <ul class="list-group" id="legendas-list">


                    </ul>
                </div>
            </div>


            <div class="editor col col-sm-12 col-md mb-sm-4" id="editor">

                <div class="form-group">
                    <span class="order"> 0 </span>
                    <span class="timestamp"> 00:00:00,000 --> 00:00:00,000 </span>
                </div>

                <div class="form-group">
                    <label for="campo-legenda" hidden>Legenda</label>
                    <textarea name="legenda" id="campo-legenda" cols="30" rows="10" class="form-control">A faixa de legenda selecionada para editar irá aparecer aqui</textarea>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <button id="save-current" class="btn btn-outline-success">Salvar</button>
                </div>


            </div>

        </div>
    </div>

</div>

</body>


<script type="module">

    import {Storage} from "../js/Storage.js";
    import {create, removeAllChildren} from "../js/utils.js";



    function removeLegenda(legenda)
    {
        const order = legenda.order;

        legendas = legendas.filter(l => l.order !== order);
        legendas = legendas.map(l => {
            if (l.order > order) {
                l.order -= 1;
            }
            return l;
        });
        renderListLegendas($ul, legendas);
    }

    function callbackMenu(event,legenda){
    }

    //cria o elemento que irá conter os dados das legendas na listagem lateral
    function createLegendaListItem(legenda,contextMenuEventCallback) {

        const $li = create('a',["list-group-item", "list-group-item-action",'list-group-item-light']),
            $wrapper = create("div"),
            $header = create("div",['header','d-flex']),
            $body = create("div"),
            $spanLegenda = create("span",['legenda']),
            $spanOrder = create("span",['order']),
            $spanTimestamp = create("span",['timestamp']),
            $contextMenuTrigger = create("button",['btn', 'btn-small','btn-light','ml-auto']);
        //@todo alterar texto por um ícone de verdade
        $contextMenuTrigger.innerText = 'M';
        $contextMenuTrigger.addEventListener("click",(event) => {
            contextMenuEventCallback.call( this,event,legenda );
            //caso queria imepdir a seleção da legenda atual ao clicar no ícone de menu, descomente a linha abaixo
            //event.stopPropagation();
        })

        $spanOrder.innerText = legenda.order;
        $spanTimestamp.innerText = legenda.intervalo.join(" --> ");
        $spanLegenda.innerText = legenda.legenda;

        $header.appendChild($spanOrder);
        $header.appendChild($spanTimestamp);
        $header.appendChild($contextMenuTrigger);
        $body.appendChild($spanLegenda);

        $wrapper.appendChild($header);
        $wrapper.appendChild($body);

        $li.appendChild($wrapper);
        return $li;
    }

    //atualiza os dados no editor de legenda
    function updateEditorUI(legenda) {

        const $editor = document.getElementById("editor");

        $editor.getElementsByClassName("order")[0].innerText = legenda.order
        $editor.getElementsByClassName("timestamp")[0].innerText = legenda.intervalo.join(" --> ");
        const $textarea = $editor.getElementsByTagName("textarea")[0];

        $textarea.value = legenda.legenda;
        $textarea.focus(); //foca no campo de edição
        $textarea.setSelectionRange($textarea.value.length, $textarea.value.length) //coloca cursor no final do texto
    }

    //evento disparado ao clicar no elemento da lista de legendas
    function onClickLegendaListItem(event,legenda)
    {
        let lastActive = document.querySelector("a.list-group-item.active");
        if (lastActive) {
            //remove destaque de cor
            lastActive.classList.remove('active');
        }

        let path = event.composedPath();
        //acha o elemento pai do evento e adiciona a classe de destaque de cor
        let rootIndex = path.findIndex((element) => element.classList.contains("list-group-item-action"));
        let $root = path[rootIndex];
        $root.classList.add("active");
        //define a legenda selecionada como a legenda sendo editada
        currentLegenda = Object.assign({}, legenda);
        //Atualiza os dados de edição
        updateEditorUI(legenda);
    }

    function renderListLegendas($ul, legendas,legendaEventCallback) {

        removeAllChildren($ul);

        legendas.forEach((value, key) => {
            const $li = createLegendaListItem(value,callbackMenu);

            $li.addEventListener("click", (e) => {
                //pegea o último elemento ativo
                legendaEventCallback.call(this,e,value)
            });

            $ul.appendChild($li);
        });
    }

    const MEMORY = new Storage();
    const MEMORY_WORK_KEY = "legenda";
    const $ul = document.getElementById("legendas-list"); //elemento que contém todas as faixas de legenda
    const $saveCurrentEdit = document.getElementById("save-current"); // botão para salvar a edição atual
    const $downloadBtn = document.getElementById("download") //botão para baixar o arquivo
    const $textarea = document.getElementsByTagName("textarea")[0]; //elemento que contém a faixa sendo editada
    const $finalizarBtn = document.getElementById("finalizar"); //elemento que redireciona para a página inicial e zera a memória do app.



    let currentLegenda = null; //legenda sendo editada.
    let legendas = <?=  $legendasJSON ?>;

    $finalizarBtn.addEventListener("click",(e) =>{
        console.count("finalizar btn")
        e.preventDefault();

        MEMORY.delete(MEMORY_WORK_KEY);
        window.location = "/public/index.html";
        //redirect
    })

    //adiciona evento de clique para salvar a edição atual.
    $saveCurrentEdit.addEventListener("click", (e) => {
        //pega os valores atuais dos campos.
        let legendaModificada = Object.assign({}, currentLegenda, {legenda: $textarea.value});

        //edita a legenda dentro da lista de legendas
        let targetIndex = legendas.findIndex(legenda => legenda.order === legendaModificada.order);
        legendas[targetIndex].legenda = legendaModificada.legenda;
        renderListLegendas($ul, legendas,onClickLegendaListItem);
        //MEMORY.set("legenda", JSON.stringify(legendas));
    })


    $downloadBtn.addEventListener("click", (e) => {
        let legendaContent = document.getElementById("legenda-content")
        legendaContent.setAttribute("value", JSON.stringify(legendas));
    });


    if (Array.isArray(legendas)) {
        //renderiza as faixas no DOM
        renderListLegendas($ul, legendas,onClickLegendaListItem);
    }


</script>


</html>
