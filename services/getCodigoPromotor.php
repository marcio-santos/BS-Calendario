<?php
  // VERIFICA O CÓDIGO DO PROMOTOR
  // RETORNA O VALOR DO DESCONTO PARA O TIPO DE CURSO
  
	$codigo_promotor = $_POST['in_promocode'];
	$treino = (isset($_POST['treino']))? $_POST['treino'] : 'M1' ;
	
	//VERIFICA A PROCEDENCIA DO CODIGO DO PROMOTOR
	$client = new SoapClient("http://177.154.134.90:8084/WCF/_BS/wcfBS.svc?wsdl", array('cache_wsdl' => WSDL_CACHE_NONE));
	$params=array('CodigoPromotor' => $codigo_promotor);
	$webService=$client->VerificaCodigoPromotor($params);
	$wsResult  =$webService->VerificaCodigoPromotorResult;

	if (strlen($wsResult->CODIGO_PROMOTOR) > 0)
		{
				$PromoCode=$wsResult->CODIGO_PROMOTOR;
		} else {
				$PromoCode = 'np';
		}

if($PromoCode !='np') {
	$ini_file = parse_ini_file('../calendario.cfg',true) ;
	$treino = $ini_file[$treino] ;
	$desconto= $treino['desconto'] ;
	/*
	switch ($treino) {
		Case 1:
			$desconto = 60 ;
			break;
		Case 2:
			$desconto = 0;
			break;
		Case 3:
			$desconto = 0;
			break;
		Case 4:
			$desconto = 0;
			break;
		Case 5:
			$desconto = 0;
			break;
		Case 6:
			$desconto = 0;
			break;
		Case 7:
			$desconto = 0;
			break;
		Case 8:
			$desconto = 60 ;
			break;
		Case 9:
			$desconto = 0;
			break;
		Case 10:
			$desconto = 0;
			break;
		Case 11:
			$desconto = 0;
			break;
		Case 12:
			$desconto = 0;
			break;
		Case 13:
			$desconto = 60;
			break;
		Default:
			$desconto = 60;
			break;
		
	}
	*/
} else {
	$desconto = 0 ;
}  
   $ret = array($PromoCode,$desconto) ; 
	
  echo json_encode($ret) ;
  
  
  
?>
