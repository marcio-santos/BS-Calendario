<?php
	//validar cupom

	include('../../../exec_in_joomla.inc') ;

	$cupom = $_POST['numero_cupom'] ;

	try {


		if(strlen($cupom)==0) {
				$ret = array(0, "<div class='alerta'>Insira um código de CUPOM antes de verificar</div>");
		} else {
			$db= &JFactory::getDBO();
			$query = "SELECT cupon,utilizado,bloqueado,data_utilizado FROM cupons WHERE cupon LIKE ".$db->Quote($cupom) ;
			$db->setQuery($query);
			$result = $db->loadRow();

			if(count($result)>0) {
				if($result[1]==0 &&$result[1]==0 && $result[2]==0) {
					$ret = array(1,"<div class='ok'>CUPOM VÁLIDO - Clique em 'Prosseguir.'</div>",$result[0]) ;
				} elseif($result[2]== 1) {
					$ret = array(0,"<div class='alerta'>CUPOM BLOQUEADO - Por favor, entre em contato com nossa central pelo telefone<br/> (11) 3529-2880</div>") ;
				} else {
					$dta = date('d-m-Y H:i:s',strtotime($result[3]));
					$ret = array(0,"<div class='alerta'><strong>CUPOM UTILIZADO EM<br/>".$dta."</strong><br/>Este cupom não pode ser utilizado 2 vezes.<br/>Em caso de dúvida, entre em contato com nossa central pelo telefone (11) 3529-2880</div>") ;
				}
			} else {
				$ret = array(0, "<div class='alerta'>CUPOM INVÁLIDO OU INEXISTENTE<br/>Por favor, use outro código ou tente digitar este mesmo código novamente.</div>") ;
			}
		}

		echo json_encode($ret) ;

	} catch(Exception $e) {
		$ret = array(0,$e->getMessage()) ;
		echo json_encode($ret) ;
	}


?>
