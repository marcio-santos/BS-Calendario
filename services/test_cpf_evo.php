<?php
/**
 * Created by PhpStorm.
 * User: MárcioAlex
 * Date: 24/02/14
 * Time: 11:50
 */
function getEvoInfo($cpf) {
    $cpf = str_replace('-','',str_replace('.','',$cpf));
    $tipo = 2;
    $client = new
    SoapClient(
        "http://177.154.134.90:8084/WCF/Clientes/wcfClientes.svc?wsdl",
        array('cache_wsdl'=>WSDL_CACHE_NONE)
    );
    $params = array('IdClienteW12'=>229, 'IdFilial'=>1, 'CpfCnpj'=> $cpf, 'TipoCliente'=>$tipo);
    $webService = $client->ListarClienteCPFCNPJ($params);
    $wsResult = $webService->ListarClienteCPFCNPJResult;

    if($wsResult->ID_CLIENTE!=0) {
        $cliente = array(
            'EXIST' => true,
            'EVOID' => $wsResult->ID_CLIENTE,
            'NOME_EVO' => $wsResult->NOME,
            'EMAIL' => $wsResult->EMAIL
        );
    } else {
        $cliente = array(
            'EXIST' => false
        );
    }
    return $cliente;
}

$cpf = $_GET['cpf'] ;

print_r(getEvoInfo($cpf));