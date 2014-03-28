<?php

require_once "informacoesCorreios.php";

class Correios
{
	public $informacoesCorreios;

	public function buscarCep($cep){
		$ch = curl_init();

			curl_setopt_array($ch, array
			(
				CURLOPT_URL 			=> "http://www.buscacep.correios.com.br/servicos/dnec/consultaEnderecoAction.do",
				CURLOPT_POST			=> TRUE,
				CURLOPT_POSTFIELDS		=> "relaxation={$cep}&TipoCep=ALL&semelhante=N&Metodo=listaLogradouro&TipoConsulta=relaxation&StartRow=1&EndRow=10&cfm=1",
				CURLOPT_RETURNTRANSFER	=> TRUE
			));

			$response = curl_exec($ch);
			curl_close($ch);

			preg_match_all("/>(.*?)<\/td>/", $response, $matches);

			return $matches[1];
	}

	public function retornaInformacoesCep($cep)
	{
	 	$informacoesCorreios = $this->buscarCep($cep);
        $thefile=  'retorno_correios.log';
        $tdata = print_r($informacoesCorreios,true);
        file_put_contents($thefile,$tdata);

		$this->informacoesCorreios = new informacoesCorreios();

        if(count($informacoesCorreios)==4) {
            $this->informacoesCorreios->setLogradouro('');
            $this->informacoesCorreios->setBairro($informacoesCorreios[0]);
            $this->informacoesCorreios->setLocalidade($informacoesCorreios[1]);
            $this->informacoesCorreios->setUf($informacoesCorreios[2]);
            $this->informacoesCorreios->setCep($informacoesCorreios[3]);
        } else if(count($informacoesCorreios)==3) {
            $this->informacoesCorreios->setLogradouro('');
            $this->informacoesCorreios->setBairro('');
            $this->informacoesCorreios->setLocalidade($informacoesCorreios[0]);
            $this->informacoesCorreios->setUf($informacoesCorreios[1]);
            $this->informacoesCorreios->setCep($informacoesCorreios[2]);
        } else {
            $this->informacoesCorreios->setLogradouro($informacoesCorreios[0]);
            $this->informacoesCorreios->setBairro($informacoesCorreios[1]);
            $this->informacoesCorreios->setLocalidade($informacoesCorreios[2]);
            $this->informacoesCorreios->setUf($informacoesCorreios[3]);
            $this->informacoesCorreios->setCep($informacoesCorreios[4]);    
        }
        
		
	}
}

?>