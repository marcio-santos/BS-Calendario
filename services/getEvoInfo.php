<?php

	//VERIICA SE EXISTE CADASTRO NO SITE
	require_once('../../../exec_in_joomla.inc') ;


	$nome = '';
	$email ='';

	//FUNCTION GET EVO INFO
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

	// Função que valida o CPF
	function validaCPF($cpf){    // Verifiva se o número digitado contém todos os digitos
		//$cpf = str_pad(preg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);
		$cpf = str_replace('-','',str_replace('.','',$cpf));

		// Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
		if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999')
		{
			return false;
		}
		else
		{   // Calcula os números para verificar se o CPF é verdadeiro
			for ($t = 9; $t < 11; $t++) {
				for ($d = 0, $c = 0; $c < $t; $c++) {
					$d += $cpf{$c} * (($t + 1) - $c);
				}

				$d = ((10 * $d) % 11) % 10;

				if ($cpf{$c} != $d) {
					return false;
				}
			}

			return true;
		}
	}


		
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
		$ret = array(2,'Erro lendo Certificações do Professor') ;
	}
	return $ret ;
}

	
	
	$programa  = $_POST['programa'] ;
	$treino = $_POST['treino'];
	$cpf = $_POST['cpf'] ;
	$cpf = str_replace('-','',str_replace('.','',$cpf));

  
	/*
	if(validaCPF($cpf)) {

	$client = new 
	SoapClient( 
	"http://177.154.134.90:8084/WCF/Clientes/wcfClientes.svc?wsdl" 
	);

	if(strlen($cpf)>0) {  
	$params = array('IdClienteW12'=>229, 'IdFilial'=>1, 'CpfCnpj'=> $cpf, 'TipoCliente'=>2); 
	$webService = $client->ListarClienteCPFCNPJ($params); 
	$wsResult = $webService->ListarClienteCPFCNPJResult; 
	$obj = print_r($wsResult,true) ;

	if($wsResult->ID_CLIENTE!=0) {
	$template = "<div style='display:block;width:650px;height:300px;overflow:auto;'><pre>$obj</pre></div>" ;  
	}  else {
	$template = "<div style='display:block;width:650px;height:300px;overflow:auto;'><pre>INFORMAÇÕES NÃO LOCALIZADAS PARA ".$cpf."</pre></div>" ;
	}
	}

	} else {

	$template = "<center><div class='alerta'>NUMERO DE CPF INVÁLIDO<br/>Por gentileza, verifique e digite novamente.</div></center>" ;

	}

	*/

	if(strlen($cpf)==11) {
		if(validaCPF($cpf)) {

			//PROCURA CLIENTE NO EVO
			$usuario = getEvoInfo($cpf) ;
		  

			//VERIFICA MTA E SE PODE SER COMPRADO
			if($treino=='MTA') { 
			
				$prog = check_mta($usuario['EVOID'],$programa) ;
                file_put_contents('mta.log',print_r($prog,true));
				if($prog[0]== 1){
					$template = "" ;
					$avatar = 'http://bodysystems.net/_ferramentas/calendario/images/mta_importante.png';
					$ret = array(4,$template,$avatar);
					echo json_encode($ret) ;
					exit();
					
				}
				
			} 
				//USUARIO EXISTE NO EVO
			if($usuario['EXIST']==1) {
					$str  = $usuario['NOME_EVO'] ;
					$nome = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
					$email = strtolower($usuario['EMAIL']) ;
				}

				$db = &JFactory::getDBO();

				//PROCURA SITEID
			if(strlen($nome)==0) {
					$query = "SELECT wow_users.id,name,email FROM wow_users WHERE REPLACE(REPLACE(username,'.',''),'-','') LIKE ".$db->Quote($cpf) ;
					$db->setQuery($query) ;
					$objx = $db->loadObjectList();
					$obj = $objx[0];
					$siteid = $obj->id;
					$nome = $obj->name;
					$email = $obj->email;
				} else {
					$query = "SELECT wow_users.id FROM wow_users WHERE REPLACE(REPLACE(username,'.',''),'-','') LIKE ".$db->Quote($cpf) ;
					$db->setQuery($query) ;
					$siteid = $db->loadResult();
			
				}


				//USUÁRIO EXISTE NO SITE
				if(strlen($siteid)>1) {

					//RECUPERA O AVATAR
					$query = "SELECT avatar FROM  wow_users_details WHERE userid = ".$siteid ;
					$db->setQuery($query) ;
					$path = $db->loadResult();
					$fileOK = file_exists(JPATH_ROOT."/".$path);
					$avatar = ($fileOK) ? $path : 'http://bodysystems.net/_ferramentas/calendario/images/user.png' ;
					
					
					//RECUPERA OS DADOS COMPLEMENTARES
					$query = "SELECT sexo,nascimento,fone_cel,fone_fixo FROM wow_users_details WHERE userid =".$siteid ;
					file_put_contents('getEvoInfo.log',$query) ;
					$db->setQuery($query);
					$compl = $db->loadObjectList();
					$obj = $compl[0];
					$complementos['2']= $obj->sexo;
					$complementos['3']= ($obj->nascimento !='')? date('Ymd',strtotime($obj->nascimento)):'' ;
					$complementos['6']= $obj->fone_cel ;
					$complementos['7']= $obj->fone_fixo ;
						
						

					$ret = array(1,$siteid,$nome,$email,$avatar,$complementos) ; 

				} else {

					//USUARIO EXISTE NO EVO
					if(strlen($nome) > 0) {
						//TEM CADASTRO NO EVO MAS SEM CADASTRO NO SITE
						$avatar = 'http://bodysystems.net/_ferramentas/calendario/images/user.png';
						$ret=array(3,$siteid,$nome,$email,$avatar);
					} else {
						$avatar = 'http://bodysystems.net/_ferramentas/calendario/images/user.png';
						$ret=array(0,$cpf,$avatar);
					}

				}
			   

		} else {
			//CPF INVALIDO
			$template = "<center><div class='alerta'>NÚMERO DE CPF INVÁLIDO<br/>Por gentileza, verifique e digite novamente.</div></center>" ;
			$avatar = 'http://bodysystems.net/_ferramentas/calendario/images/user.png';
			$ret = array(2,$template,$avatar);

		}
	} else {
		//CPF INVALIDO
		$template = "<center><div class='alerta'>NÚMERO DE CPF INVÁLIDO<br/>Por gentileza, verifique e digite novamente.</div></center>" ;
		$avatar = 'http://bodysystems.net/_ferramentas/calendario/images/user.png';
		$ret = array(2,$template,$avatar);
	}
	echo json_encode($ret) ;

?>
