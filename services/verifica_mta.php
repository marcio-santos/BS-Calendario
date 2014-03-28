<?php

	//defined( '_JEXEC' ) or die( 'Acesso restrito' );  
	
	
function check_mta($evoid,$programa) {
	//Recupera a Carreira do Cliente
	$client = new 
	SoapClient( 
		"http://177.154.134.90:8084/WCF/_BS/wcfBS.svc?wsdl" 
	); 

	$params = array('IdClienteW12'=>229, 'IdCliente'=>$evoid); 
	$webService = $client->RetornarCertificacoesProfessor($params); 
	$wsResult = $webService->RetornarCertificacoesProfessorResult; 

	/*
	echo "<pre>" ;
	print_r($wsResult) ;
	echo "</pre>" ;
	die() ;
	*/

	$count= count($wsResult);
	if(is_array($wsResult->VOBS)) {
		$wsResult = $wsResult->VOBS ;
	} 

	if ($count>0) {
		$ret = array(1,'Sem Treinamento Inicial') ;
		foreach ($wsResult as $obj) {
            //LINHA COMENTADA PARA PERMITIR ACESSO A QUALQUER PESSOA QUE TENHA MODULO 1
			//if($obj->ID_NIVEL_PROGRAMA >=1 && strlen($obj->DS_PROGRAMA_ABREV)>0){ 
            if($obj->FL_MODULO1==1 && strlen($obj->DS_PROGRAMA_ABREV)>0){ 
            
				if($obj->DS_PROGRAMA_ABREV == $programa) {
					unset($ret) ;
					$ret = array(0,$programa);
					break;
				}
			}
		} 
	} else {
		$ret = array(3,'Erro lendo Certificações do Professor') ;
	}
}



?>
