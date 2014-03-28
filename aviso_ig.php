<?php
  //$document->addScript('plugins/system/cdscriptegrator/libraries/jquery/js/jquery-noconflict.js') ;
$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js');
$document->addScript('_ferramentas/calendario/js/ig.js');
$document->addScript('http://malsup.github.io/jquery.blockUI.js') ;
$document->addStyleSheet('_ferramentas/calendario/styles/ig.css');


function template_eval(&$template, &$vars) {
        return strtr($template, $vars);
    }
    

$evento = base64_decode($_POST['data']) ;
$template_main = file_get_contents('_ferramentas/calendario/aviso_ig.html') ;
$params = array('{EVENTO}' => $evento) ;

echo template_eval($template_main,$params) ;



?>
