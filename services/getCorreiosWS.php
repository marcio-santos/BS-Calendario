<?php
  
require_once('Correios-Dez12.php');
require_once ("Correios-Cep.php");

/* 
echo $correios->informacoesCorreios->getLogradouro();
echo "<br />";
echo $correios->informacoesCorreios->getBairro();
echo "<br />";
echo $correios->informacoesCorreios->getLocalidade();
echo "<br />";
echo $correios->informacoesCorreios->getUf();
echo "<br />";
echo $correios->informacoesCorreios->getCep();
echo "<br />";

*/





try {

$cep_destino = $_POST['cep'] ;
$peso = $_POST['peso'] ;
$cep_origem = '06541015';// NETUNO

if(strlen($cep_destino)==0) {
	$ex = array(false,'Informe um CEP antes de verificar!') ;
	
} else {
	
$frete = new RsCorreios();


//$peso = 1;

$resposta = $frete
	->setCepOrigem($cep_origem)
	->setCepDestino($cep_destino)
	->setLargura('26')
	->setComprimento('36')
	->setAltura('5')
	->setPeso($peso)
	->setFormatoDaEncomenda(RsCorreios::FORMATO_ENVELOPE)
	->setServico(empty($tipo) ? RsCorreios::TIPO_SEDEX : $data['tipo'])
	->dados();


$correios = new Correios;
$correios->retornaInformacoesCep($cep_destino);
$logradouro = $correios->informacoesCorreios->getLogradouro();
$bairro = $correios->informacoesCorreios->getBairro();
$cidade = $correios->informacoesCorreios->getLocalidade();
$uf = $correios->informacoesCorreios->getUf();
$frete = $resposta['valor'];

$ex = array(
	utf8_encode($logradouro),
	utf8_encode($bairro),
	utf8_encode($cidade),
	utf8_encode($uf),
	$frete
	) ;
}
echo json_encode($ex) ;

} catch (Exception $e) {
	
	echo $e->getErrorNum()+ "<br/>" +$e->getMessage() ;
	
}
/*
$ende = utf8_encode(print_r($correios,true));
$ret = utf8_encode(print_r($resposta,true)) ;
$dx  = utf8_encode(print_r($ex,true)) ;

echo "<h3>DADOS DO ENDEREÇO</H3><pre>".$ende."</pre><hr/><h3>DADOS DO FRETE</h3><pre>".$ret."</pre><hr/><h3>Dados Individuais</h3><pre>".$dx."</pre>" ;

*/
				


			

		//=========================================================================

?>
