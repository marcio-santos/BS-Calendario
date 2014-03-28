<?php
    include('../../../exec_in_joomla.inc') ;

    //DADOS PROVENIENTES DO FORMULARIO PRINCIPAL
    $action = $_POST['h_action'];
    $transacaoid = $_POST['h_transacao'];
    $valor1 = $_POST['h_valor1'];
    $valor2 = $_POST['h_valor2'];
    $valor_cobrado = $_POST['h_valor_cobrado'];
    $certificacao = $_POST['h_certificacao'];
    $desconto = $_POST['h_desconto'];
    $valor_frete = $_POST['h_valor_frete'];
    $bonus_frete = $_POST['h_bonus_frete'];
    $formaPagto = $_POST['h_formaPagto'];
    $cupom = $_POST['h_cupom'];
    $cpf = $_POST['h_cpf'];
    $siteid = $_POST['h_siteid'];
    $username = $_POST['h_user'];
    $email = $_POST['h_email'];
    $password = $_POST['h_password'];
    $nome = $_POST['h_nome'];
    $sexo = $_POST['h_sexo'];
    $nascimento = $_POST['h_nascimento'];
    $fone = $_POST['h_fone'];
    $celular = $_POST['h_celular'];
    $promocode = $_POST['h_promocode'];
    $logradouro = $_POST['h_logradouro'];
    $numero = $_POST['h_numero'];
    $compl = $_POST['h_compl']; 
    $bairro = $_POST['h_bairro'];
    $cidade = $_POST['h_cidade'];
    $estado = $_POST['h_uf'];
    $cep = $_POST['h_cep'];
    $evento = $_POST['h_evento_descricao'] ;
    $eventoid = $_POST['h_evento_id'] ;
    $cnab = $_POST['h_cnab'] ;
    
    $ret_array = explode('|',$cnab);
    $sigla = strtolower($ret_array[2]);
    $endereco = $logradouro.','.$numero.' '.$compl.' - '.$bairro.' '.$cidade.' / '.$estado.' - CEP '.$cep ;

    
    
    //ELIMINA PONTOS E TRAÇOS
    $cpf = str_replace('-','',str_replace('.','',$cpf));
    $cep = str_replace('-','',str_replace('.','',$cep));
    $nascimento = str_replace('/','-',$nascimento) ;
    $nascimento = date('Y-m-d',strtotime($nascimento)) ;
    $fone = str_replace('-','',$fone);
    $celular = str_replace('-','',$celular);

    //---------UTILITARIOS-------------------------------

    function template_eval(&$template, &$vars){
        return strtr($template, $vars);
    }

    //------IDENTIFICACAO --------------------- 
    // CRIA USUARIO NO SITE 

    function criaUser($name, $username, $password, $email){
        jimport('joomla.user.helper'); 
        $salt = JUserHelper::genRandomPassword(32); 
        $crypted = JUserHelper::getCryptedPassword($password, $salt); 
        $cpassword = $crypted.':'.$salt;
        $data = array( "name"=>$name, "username"=>$username, "password"=>$password, "password2"=>$password, "email"=>$email, "block"=>0, "groups"=>array("1","2") );
        $user = new JUser;
        //Write to database 
        if(!$user->bind($data)) { 
            throw new Exception("Erro: " . $user->getError()); 
        } 

        if (!$user->save()) { 
            throw new Exception("Erro: " . $user->getError()); 
        } 
        return $user->id;  
    }  

    function create_detail($userid,$cpf,$nome,$email,$sexo,$nascimento,$cep,
        $logradouro,$compl,$numero,$bairro,$cidade,$estado,$fone,$celular) {

        $tipo_perfil = 2;
        unset($dbErrors) ;

        $db =& JFactory::getDBO();
        //===================================================================================================

        $query = "INSERT INTO wow_users_details VALUES('','$userid','$cpf','$logradouro', '$numero','$compl','$bairro','$cidade','$estado','$cep','','','','$sexo','$nascimento','$fone','$celular','',$tipo_perfil)" ;




        $db->setQuery($query) ;
        $db->Query();

        if($db->getErrorNum()> 0) {
            $dbErrors[] = $db->getErrorMsg()."\n".$query."\n\n";
        }


        //====================================================================================================

        if(count($dbErrors)> 0) {
            $dump = str_repeat("-=", 100)."\n".'USERID: '.$siteid.' EM '.date('Y-m-d H:i:s')."\n".str_repeat("-=", 100)."\n" ;
            $dump .= print_r($dbErrors,true);
            $dump .= "\n" ;
            file_put_contents('erros_cadastramento.log',$dump);
            return false ;

        } else {
            return true;
        }

        //====================================================================================================

        /*
        $msg = "[".date('Y-m-d H:i:s',time())."] ".$db->getErrorNum(). " -> ". $db->getErrorMsg();
        file_put_contents('err-log.log',$msg);
        */   

    }


    //ENVIA DADOS PARA O EVO - RECUPERA EVOID
    function prospect($nome,$cpf,$sexo,$nascimento,$cep,$logradouro,$compl,
        $numero,$bairro,$cidade,$estado,$email,$fone,$celular) {


        //CRIA USUÁRIO NO EVO
        $nasc = date('Ymd', strtotime($nascimento)) ;
        $params = array (
            IdClienteW12 => 229 ,
            TipoProspect => 2,
            IdFilial => 1 ,
            Nome => $nome ,
            CpfCnpj=>$cpf ,
            RG=>"" ,
            Sexo=> $sexo ,
            dtNascimento=> $nasc ,
            Cep=> $cep, 
            Endereco=> $endereco ,
            Complemento=> $compl ,
            Numero=> $numero  ,
            Bairro=> $bairro ,
            Cidade=> $cidade,
            SiglaEstado=> $estado ,
            Email=>$email ,
            TelefoneFixo=> $fone , 
            TelefoneCelular=> $celular
        );
        

        
        try{
            $client = new SoapClient("http://177.154.134.90:8084/WCF/Clientes/wcfClientes.svc?wsdl",array('cache_wsdl'=>WSDL_CACHE_NONE)); 
            $webService = $client->InsereClienteWebsite($params); 
            $evoid = $webService->InsereClienteWebsiteResult;
            return $evoid ;

        } catch (Exception $e) {
            $msg = "Retornando IDCliente |".date("Y-m-d | H:i:s", time()) .' - '.$e->getMessage()." - Origem -> IdCliente".$evo_id."\r\n";
            $thefile =  'prospects-error.log' ;
            file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            $evoid = 0;
            return $evoid ;

        } 

    }


    //------BOLETOS ---------------------  
    //CRIA O NOSSO NÚMERO PARA BOLETO	
    function nosso_numero() {
        do {
            $ultimo_id = file_get_contents('nosso_numero.count');
        } while ($ultimo_id === false);
        $ultimo_id = $ultimo_id +1 ;
        file_put_contents('nosso_numero.count',$ultimo_id,LOCK_EX) ;

        return $ultimo_id ;
    } 


    //INSERE O BOLETO NA TABELA BOLETOS_BS 
    function inserirBoletoBs($evento,$tipo_evento,$cnab,$evoid,$siteid,$nome,$email,$endereco,$promocode,$data_documento,$data_vencimento,$nnum,$docnum,$linha,$gvalor,$gfrete,$nivel_bonus,$transacaoid) {

        //REGISTRA O IP DO USUÁRIO

        if ( isset($_SERVER["REMOTE_ADDR"]) )    { 

            $ip=$_SERVER["REMOTE_ADDR"] . ' '; 

        } else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    { 

            $ip=$_SERVER["HTTP_X_FORWARDED_FOR"] . ' '; 

        } else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    { 

            $ip=$_SERVER["HTTP_CLIENT_IP"] . ' '; 

        } 


        try {

            $db = &JFactory::getDBO() ;

            if($gvalor!=0 && $evoid!=0 && $cnab!='') {

                $query = "INSERT INTO boletos_bs (id,evento,tipo_evento,cnab,idcliente,userid,nome_evo,user_email,endereco_remessa,promo_code,data_geracao,data_vencimento,nosso_numero,doc_numero,linha_digitavel,valor_cobrado,valor_frete,nivel_bonus,ip,compensado,data_compensado,transacaoid) VALUES ('null', '$evento','$tipo_evento', '$cnab','$evoid','$siteid','$nome','$email','$endereco','$promocode','$data_documento','$data_vencimento','$nnum','$docnum','$linha','$gvalor','$gfrete','$nivel_bonus','$ip','false','0000-00-00','$transacaoid')" ;
                $db->setQuery($query) ;
                $db->Query();

                if ($db->getErrorNum()) {
                    $ret = array(false,$db->getErrorMsg());
                    $thefile =  'erros_boletos_site.log' ;
                    $msg = "[".date("Y-m-d | H:i:s", time()) .'] - '.$db->getErrorMsg()."\n";
                    file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
                }  else { 
                    $ret = array(true,'Boleto inserido na base com sucesso.') ;
                }  

            } else {
                $ret = array(false,'Existe algum problema com o valor do boleto.');
            }

            return $ret ;

        } catch (Exception $e) {
            $msg = "[".date("Y-m-d | H:i:s", time()) .'] - '.$e->getMessage()." - Origem -> IdCliente:".$evoid." - SiteID:".$siteid." - NNum:".$nnum." - CNAB:".$cnab."\r\n";
            $thefile =  'erros_boletos_site.log' ;
            file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            $ret = array(false,$e->getMessage()) ;
            return $ret ;

        } 

    } 

    //INSERE O BOLETO NA TABELA DO EVO
    function inserirBoletosEvo($evento,$cnab,$evoid,$promocode,$data_documento,$data_vencimento,$nnum,$docnum,$gvalor,$gfrete,$endereco){

        $client = new SoapClient( 
            "http://177.154.134.90:8084/WCF/_BS/wcfBS.svc?wsdl" , array('cache_wsdl' => 0)
        ); 

        $params = array(

            'Evento'=>$evento,
            'CNAB'=>$cnab,
            'IdCliente'=>$evoid,
            'CodigoPromotor'=>$promocode,
            'DataGeracao'=> date('Ymd His', strtotime($data_documento)),
            'DataVencimento' => date('Ymd His', strtotime($data_vencimento)),
            'NossoNumero' =>$nnum,
            'DocNumero' =>$docnum,
            'ValorCobrado' =>$gvalor,
            'ValorFrete' =>$gfrete,
            'Endereco' =>$endereco

        ); 

        $parametros = print_r($params,true) ;
        $thefile =  'parametros.log' ;
        file_put_contents ($thefile, "[".date("Y-m-d | H:i:s", time()) .'] - '."\n".$parametros."\n", FILE_APPEND | LOCK_EX);

        try {
            $webService = $client->InserirBoleto($params); 
            $result = $webService->InserirBoletoResult ;

            if($result==0){
                $ret = array(true,'Boleto Inserido com Sucesso');
            } else {
                $ret = array(false,$result) ;
                $thefile =  'erros_boletos_evo.log' ;
                file_put_contents ($thefile, "[".date("Y-m-d | H:i:s", time()) .'] - '.$ret[1]."\n", FILE_APPEND | LOCK_EX);
            }

            return $ret ;

        } catch (Exception $e) {
            $msg = "[".date("Y-m-d | H:i:s", time()) .'] - '.$e->getMessage()." - Origem -> IdCliente:".$evo_id." - SiteID:".$siteid." - NNum:".$nnum." - CNAB:".$cnab."\r\n";
            $thefile =  'erros_boletos_evo.log' ;
            file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            $ret = array(false,$e->getMessage()) ;
            return $ret ;

        } 

    }


    //------CUPONS --------------------
    //LANCA PROFESSOR NA LISTA DE TREINO
    function lancarCupom($transacaoid,$evoid,$Item_desc,$code,$evento_id) {

        //CHAMA O SERVIÇO DO EVO

        $params = array( 
            'IdClienteW12' => 229,
            'IdTransacao'=> $transacaoid,
            'IdCliente'=> $evoid,
            'NomeProduto'=> $Item_desc.' '.$code ,
            'Descricao'=> $evento_id,
            'Valor'=> 0,
            'FreteValor'=> 0,
        );

        try {

            $client = new SoapClient("http://177.154.134.90:8084/WCF/_BS/wcfBS.svc?wsdl" ,array('cache_wsdl'=>WSDL_CACHE_NONE)); 
            $webService = $client->TratarEvento($params); 
            $result = $webService->TratarEventoResult ;

            $thefile = 'parametros_cupom_evo.log' ;
            $msg = date('Y-m-d H:i:s')."\n" ;
            $msg .= print_r($params,true) ;
            file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            $result = substr($result,0,1);
            if($result== '0'){
                $ret = array(true,'Boleto Inserido com Sucesso');

            } else {
                $ret = array(false,$result) ;
                $thefile =  'erros_envio_lista_presenca_treinamento.log' ;
                $msg = date('Y-m-d H:i:s').' ----> TRANSACAO ID:'.$transacaoid.' | ID_CLIENTE:'.$evoid.' | MENSAGEM EVO:'.$result ;
                file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            }


        } catch (Exception $e) {
            $msg =  time().' - '.$e->getMessage();
            $thefile =  'erros_envio_lista_presenca_treinamento.log' ;
            //$msg = date('Y-m-d H:i:s').' ----> TRANSACAO ID:'.$transacaoid.' | ID_CLIENTE:'.$evoid.' | MENSAGEM EVO:'.$webService ;
            file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
            $ret = array(false,$msg) ;
        }    
        return $ret ;
    }

    //BAIXA CUPOM DA TABELA CUPONS
    function baixarCupom($cupom,$destino,$siteid,$decricao) {
        $db = &JFactory::getDBO();
        //EXECUTA O PAGAMENTO COM O CUPOM
        $vDestino = $destino;
        $Item_desc = $descricao;
        $query="UPDATE cupons SET beneficiario=".$siteid.", 
        destino=".$db->Quote($vDestino).", utilizado=True,data_utilizado=now() WHERE cupon=".$db->Quote($cupom) ;

        try {
            $db->setQuery($query);
            $db->Query();

            $affected_row = $db->getAffectedRows();

            if($affected_row > 0 && $db->getErrorNum()==0){
                $ret = array(true,'Cupom baixado com Sucesso');
            } else {
                $ret = array(false, 'Erro baixando cupom') ;
                $dbError = $db->getErrorMsg()."\n".$query."\n\n";
                $thefile = 'erros_baixa_cupom.log' ;
                file_put_contents($thefile,$dbError);

            }
            return $ret ;

        } catch(Exception $e) {
            $ret = array(false,$e->getMessage()) ;
            return $ret ;
        }


    }



    //------PAGTO PAGSEGURO-------------
    //CRIA PAGAMENTO PARA PAGSEGURO
    function setPagSeg($evoid,$siteid,$eventoid,$descricao,$promocode,$valor,$nome,$email) {
        $template = <<<EOT
		<form id="pag_1" name="pag_1" target="_blank" method="post" action="https://pagseguro.uol.com.br/checkout/checkout.jhtml">
			  <input type="image" name="submit" src="http://bodysystems.net/images/pagseguro_btn.png" alt="Pagar com Cartão" />  
			  <input type="hidden" name="email_cobranca" value="modulos@bodysystems.net" />
			  <input type="hidden" name="evoid" value="{EVO_ID}" />
			  <input type="hidden" name="siteid" value="{SITE_ID}" />
			  <input type="hidden" name="tipo" value="CP" />
			  <input type="hidden" name="moeda" value="BRL" />
			  <input type="hidden" name="item_id_1" value="{ID_TREINO}" />
			  <input type="hidden" name="item_descr_1" value="{EVENTO}" />
			  <input type="hidden" name="ref_transacao" value="{PROMOCODE};{EVO_ID};{SITE_ID}" />
			  <input type="hidden" name="item_quant_1" value="1" />
			  <input type="hidden" name="anotacao" value="" />
			  <input type="hidden" name="item_valor_1" value="{VALOR}" />
			  <input type="hidden" name="tipo_frete" value="SD"  />
			  <input type="hidden" name="nome" value="{NOME}"  />
			  <input type="hidden" name="email" value="{EMAIL}"  />
			  <!-- <input type="hidden" name="item_peso_1" value="300" /> -->
			  <input type="hidden" name="item_peso_1" value="0" />
			  <input type="hidden" name="encoding" value="UTF-8" />
	   </form>

EOT;

        $params = array(
            '{EVO_ID}' => $evoid,
            '{SITE_ID}' => $siteid,
            '{ID_TREINO}'=> $eventoid,
            '{EVENTO}' => $descricao,
            '{PROMOCODE}' => $promocode,
            '{VALOR}'=> $valor,
            '{NOME}'=> $nome,
            '{EMAIL}' => $email

        );

        $botao = template_eval($template,$params) ;
        return $botao;
    }



    //------PAGTO BOLETO ---------------------
    //CRIA PAGAMENTO VIA BOLETOS
    function ProcBoleto($nnum,$data_documento,$data_vencimento,$valor_cobrado,$nome,$endereco,$cpf,$i1,$i2,$i3,$i4,$i5) {

        $valor_cobrado = str_replace(',','.', $valor_cobrado) ;

        $codigobanco = '341'; // O Itau sempre será este número
        $agencia = '0350'; // 4 posições
        $conta = '19192';  // 5 posições sem dígito
        $carteira = '175'; // A sem registro é 175 para o Itaú
        $moeda = '9'; // Sempre será 9 pois deve ser em Real
        $nossonumero = $nnum; // Número de controle do Emissor (pode usar qq número de até 8 digitos);
        $data = $data_documento;  //'05/03/2005'; // Data de emissão do boleto
        $vencimento = $data_vencimento;   //'05/03/2006'; // Data no formato dd/mm/yyyy
        $valor = $valor_cobrado; // Colocar PONTO no formato REAIS.CENTAVOS (ex: 666.01)

        // NOS CAMPOS ABAIXO, PREENCHER EM MAIÚSCULAS E DESPREZAR ACENTUAÇÃO, CEDILHAS E
        // CARACTERES ESPECIAIS (REGRAS DO BANCO)

        $cedente = 'BODY SYSTEMS LTDA.';

        $sacado = $nome;
        $endereco_sacado = $endereco;
        //$cidade = 'UBERLANDIA';
        //$estado = 'MG';
        //$cep = '38400-000';
        $cpf_cnpj = $cpf;
        $instrucoes1 = $i1;
        $instrucoes2 = $i2;
        $instrucoes3 = $i3;
        $instrucoes4 = $i4;
        $instrucoes5 = $i5;

        // FIM DA ÁREA DE CONFIGURAÇÃO

        function Modulo11($valor) {
            $multiplicador = '4329876543298765432987654329876543298765432';
            for ($i = 0; $i<=42; $i++ ) {
                $parcial = $valor[$i] * $multiplicador[$i];
                $total += $parcial;
            }
            $resultado = 11-($total%11);
            if (($resultado >= 10)||($resultado == 0)) {
                $resultado = 1;
            }

            return $resultado;
        }


        function calculaDAC ($CalculaDAC) {
            $tamanho = strlen($CalculaDAC);
            for ($i = $tamanho-1; $i>=0; $i--) {
                if ($multiplicador !== 2) {
                    $multiplicador = 2;
                }
                else {
                    $multiplicador = 1;
                }
                $parcial = strval($CalculaDAC[$i] * $multiplicador);

                if ($parcial >= 10) {
                    $parcial = $parcial[0] + $parcial[1];
                }
                $total += $parcial;
            }
            $total = 10-($total%10);
            if ($total >= 10) {
                $total = 0;
            }
            return $total;
        }

        function calculaValor ($valor) {
            $valor = str_replace('.','',$valor);
            return str_repeat('0',(10-strlen($valor))).$valor;
        }

        function calculaNossoNumero ($valor) {
            return str_repeat('0',(8-strlen($valor))).$valor;
        }

        function calculaFatorVencimento ($dia,$mes,$ano) {
            $vencimento = mktime(0,0,0,$mes,$dia,$ano)-mktime(0,0,0,7,3,2000);
            return ceil(($vencimento/86400)+1000);
        }

        // CALCULO DO CODIGO DE BARRAS (SEM O DAC VERIFICADOR)
        $codigo_barras = $codigobanco.$moeda.calculaFatorVencimento(substr($vencimento,0,2),substr($vencimento,3,2),substr($vencimento,6,4));
        $codigo_barras .= calculaValor($valor).$carteira.calculaNossoNumero($nossonumero).calculaDAC($agencia.$conta.$carteira.calculaNossoNumero($nossonumero)).$agencia.$conta.calculaDAC($agencia.$conta).'000';



        // CALCULO DA LINHA DIGITÁVEL
        $parte1 = $codigobanco.$moeda.substr($carteira,0,1).substr($carteira,1,2).substr(calculaNossoNumero($nossonumero),0,2);
        $parte1 = substr($parte1,0,5).'.'.substr($parte1,5,4).calculaDAC($parte1);

        $parte2 = substr(calculaNossoNumero($nossonumero),2,5).substr(calculaNossoNumero($nossonumero),7,1).calculaDAC($agencia.$conta.$carteira.calculaNossoNumero($nossonumero)).substr($agencia,0,3);
        $parte2 = substr($parte2,0,5).'.'.substr($parte2,5,5).calculaDAC($parte2);

        $parte3 = substr($agencia,3,1).$conta.calculaDAC($agencia.$conta).'000';
        $parte3 = substr($parte3,0,5).'.'.substr($parte3,5,8).calculaDAC($parte3);

        $parte5 = calculaFatorVencimento(substr($vencimento,0,2),substr($vencimento,3,2),substr($vencimento,6,4)).calculaValor($valor);

        $numero_boleto = $parte1.' '.$parte2.' '.$parte3.' '.Modulo11($codigo_barras).' '.$parte5;

        // INSERÇÃO DO DAC NO CODIGO DE BARRAS

        $codigo_barras = substr($codigo_barras,0,4).Modulo11($codigo_barras).substr($codigo_barras,4,43);
        $m_codigo_barras = Modulo11($codigo_barras);
        //   print Modulo11($codigo_barras);
        //   exit;

        $ret = array($numero_boleto,$m_codigo_barras) ;
        return $ret ;

    }

    function setBoleto($cnab,$nnum,$data_documento,$data_vencimento,$valor_cobrado,$sac,$endereco,$cpf,$evento,$transacaoid) {
        $template = <<<EOT
		 <form id="form_boleto" target="_blank" name="form_boleto" method="post" action="http://bodysystems.net/_ferramentas/calendario/services/boletos.php">
			  <input type="image" name="submit" src="http://bodysystems.net/images/bt_boletos.png" alt="Pagar com Boleto" />
			  <input type="hidden" name="nnum" value="{NNUM}" />
			  <input type="hidden" name="data_documento" value="{DATA_DOCUMENTO}" />
			  <input type="hidden" name="data_vencimento" value="{DATA_VENCIMENTO}" />
			  <input type="hidden" name="cnab" value="Uso interno BS: {CNAB}" />
			  <input type="hidden" name="valor_cobrado" value="{VALOR_COBRADO}" />
			  <input type="hidden" name="sac" value="{SAC}" />
			  <input type="hidden" name="endereco" value="{ENDERECO}" />
			  <input type="hidden" name="cpf" value="{CPF}" />
			  <input type="hidden" name="evento" value="{EVENTO}" />
			  <input type="hidden" name="transacaoid" value="{TRANSACAOID}" />
		 </form>

EOT;
        $endereco = utf8_decode($endereco) ;
        $params = array(
            '{CNAB}' => $cnab,
            '{NNUM}' => $nnum,
            '{DATA_DOCUMENTO}' => $data_documento,
            '{DATA_VENCIMENTO}'=> $data_vencimento,
            '{EVENTO}' => $evento,
            '{ENDERECO}' => $endereco,
            '{CPF}' => $cpf,
            '{VALOR_COBRADO}'=> $valor_cobrado,
            '{SAC}'=> $sac,
            '{TRANSACAOID}' => $transacaoid,
        );

        $botao = template_eval($template,$params) ;
        return $botao;
    }

    //-----PAGTO MERCADO PAGO -----------------
    function setMP($nome,$sobrenome,$email,$transacaoid,$userid,$evoid,$cpf_cnpj,$remessa_cep,$remessa_logradouro,$remessa_numero,$remessa_complemento,$remessa_bairro,$remessa_cidade,$remesssa_uf,$remessa_tipo,$cnab,$produto_descricao,$valor_transacao,$valor_frete,$codigo_promotor,$nsa,$sigla){

        $logo = 'http://bodysystems.net/_ferramentas/mp/ilhas/ilha_mp_'.$sigla.'.jpg';
        require_once("../../mp/lib/mercadopago.php");
        
        //CONTA treinamento
        $mp = new MP("568147034909341", "9GqSviruB5RWnJ7muqOOxVL9AHHarP1G");
        $mp->sandbox_mode(FALSE);

        //CRIA A SAIDA DO MERCADO PAGO
        try {

            $preference = array(
                'items'=> 
                array(array(
                    'id'=> '232',//$cnab,
                    'title'=> $produto_descricao,
                    'description'=> $cnab,
                    'quantity'=> 1,
                    'unit_price'=> (float)$valor_transacao,
                    'currency_id'=> 'BRL',
                    'picture_url'=> $logo
                )),
                'external_reference'=> $transacaoid,
                'payer'=> 
                array(
                    'name'=> $nome,
                    'surname'=> $sobrenome,
                    'email'=> $email,
                    'date_created' => date('c')
                ),
                'shipments' => array( 
                    'receiver_address' => array(
                        'zip_code'=> $remessa_cep,
                        'street_number'=> $remessa_numero
                )),
                'back_urls'=> 
                array(
                    'success'=> 'http://bodysystems.net/final-treino-mp?success',
                    'failure'=> 'http://bodysystems.net/final-treino-mp?fail',
                    'pending'=> 'http://bodysystems.net/final-treino-mp?pending'
                ),
                'payment_methods'=> 
                array(
                    'excluded_payment_methods'=>array(array( 

                        'id'=> 'bolbradesco'

                        )
                    ),'excluded_payment_types'=>array(array( 

                        'id'=> 'ticket'
                        )
                    ),'installments'=> 12
                )
            );

            file_put_contents('prefencias.log',print_r($preference,true)."\n",FILE_APPEND);
            $preferenceResult = $mp->create_preference($preference);
            $preference['response']['init_point'];
            $pref = $preferenceResult['response']['init_point'];
            $data_criado = date('Y-m-d H:i:s');
            
            //INSERE AS INFORMAÇÕES NO DATABASE
            $query = "INSERT INTO MercadoPagoTransacoes (transacaoid,userid,evoid,cpf_cnpj,remessa_cep,remessa_logradouro,
            remessa_numero,remessa_complemento,remessa_bairro,remessa_cidade,remessa_uf,remessa_tipo,cnab,produto_descricao,
            valor_transacao,valor_frete,codigo_promotor,nsa,prefs,data_criado) VALUES('$transacaoid','$userid','$evoid','$cpf_cnpj','$remessa_cep','$remessa_logradouro',
            '$remessa_numero','$remessa_complemento','$remessa_bairro','$remessa_cidade','$remesssa_uf','$remessa_tipo','$cnab','$produto_descricao',
            '$valor_transacao','$valor_frete','$codigo_promotor','$nsa','$pref','$data_criado')";

            $db = &JFactory::getDBO();
            $db->setQuery($query) ;
            $db->Query();

            if($db->getErrorNum()>0) {
                return '<p class="alert">Ocorreu um erro registrando sua operação</p><pre>'.$db->getErrorMsg().'</pre>' ;
                file_put_contents('_ferramentas/calendario/services/ErrosMP.log','['.date('Y-m-d').'] '.$db->getErrorMsg()."\n",FILE_APPEND);

            } else {

                $h_ref= str_replace('https:','',$preferenceResult['response']['init_point']);
                //echo $bt_template;
                $btn = "<a href='". $h_ref."' ><img src='http://bodysystems.net/_ferramentas/workshop/images/bt_mercadopago.png' /></a>";
                return $btn;

            }

        } catch(Exception $e) {

            return $e->getMessage()."<br/><pre>".print_r($preference,true)."</pre>";
        }
    }

    //------COMUNICACAO ---------------------
    //ENVIA EMAILS TRANSACIONAIS PARA TREINO
    function sendEmail($to,$subject,$msg) {

        $from = 'noreply@bodysystems.net';
        $body = <<<EOT
	  <html>
	  <p>Caro Webmaster,</p>
	  <p>Existem <strong>assuntos que requerem sua atenção</strong>.<br/>
	  <h3>$subject</h3>
	  <pre>
		$msg
	  </pre>
	  <hr/>
	  <small>Este email é uma comunicação automática. Não responda diretamente o mesmo</small>
	  </html>
EOT;
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: Site Body Systems <noreplay@bodysystems.net>' . "\r\n";
        $response = mail($to,$subject,$body,$headers);


    } 

    //DECOMPOE NOME E SOBRENOME
    function sur($full_nome) {
        $espaco = strrpos($full_nome,' ');
        $nome = substr($full_nome,0,$espaco);

        //tenta encontrar a posição de preposiçào do sobrenome
        $espaco2 = strrpos($nome,' ');
        $sur_prep = trim(substr($nome,$espaco2));
        $sur_prep = strtolower($sur_prep);
        if( $sur_prep=='da' || $sur_prep=='de' || $sur_prep=='do' || $sur_prep=='das' || $sur_prep=='dos' || $sur_prep=='e') {
            $sur_size = strlen($full_nome)- $espaco2;
            $nome = substr($full_nome,0,$espaco2);
            $sobrenome = trim(substr($full_nome,$espaco2,$sur_size));
        } else {
            $sur_size = strlen($full_nome)- $espaco;
            $sobrenome = trim(substr($full_nome,$espaco,$sur_size));
        }
        return array($nome,$sobrenome);

    }

    /* ======================================		
    * 	CADASTRAMENTO 
    * ========================================
    */		

    //VERIFICA SE PRECISA CADASTRAR O CLIENTE NO SITE
    if($action == 1) {

        $siteid = criaUser($nome,$username,$password,$email) ;
        $creaOK = create_detail($siteid,$cpf,$nome,$email,$sexo,$nascimento,$cep,$logradouro,$compl,$numero,$bairro,$cidade,$estado,$fone,$celular);
        //SE O CADASTRAMENTO DE DETALHES FALHAR, ENVIA AVISO 
        if(!creaOK) {
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro cadastrando cliente no site Body Systems';
            $msg = 'Consulte o arquivo erros_cadastramento.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
        }

    }

    //RECUPERA O ID DO EVO

     file_put_contents('dump-prospect-evo.log', $nome.$cpf.$sexo.$nascimento.$cep.$logradouro.$compl.$numero.$bairro.$cidade.$estado.$email.$fone.$celular);
         echo $nome,$cpf,$sexo,$nascimento,$cep,$logradouro,$compl,$numero,$bairro,$cidade,$estado,$email,$fone,$celular;
         die();
         
    $evoid = prospect($nome,$cpf,$sexo,$nascimento,$cep,$logradouro,$compl,$numero,$bairro,$cidade,$estado,$email,$fone,$celular);
    $mArr = "EVOID: ".$evoid."\n" ;
    file_put_contents('incricao_treino.log',$mArr,FILE_APPEND);

    if($evoid ==0) {
        $to ='debug@bodysystems.net' ;
        $subject = 'Erro cadastrando cliente no EVO';
        $msg = 'Consulte o arquivo prospects-error.log na pasta _ferramentas/calendario para detalhes.' ;
        sendEmail($to,$subject,$msg) ;
        echo "<span class='alert'>Problemas informando seus dados ao CRM</span>";
        exit();
    }


    // ARMAZENA VALOR DO FRETE PARA FRETE GRATIS.
    if($bonus_frete > 0){
        $query = "INSERT INTO bonus_frete VALUES('','$siteid','$cnab','$bonus_frete','$transacaoid')" ;
        $db = &JFactory::getDBO();
        $db->setQuery($query);
        $db->Query();

        if($db->getErrorNum()>0) {
            file_put_contents('bonus_frete.log',$db->getErrorMsg()."\n".$query."\n"."\n",FILE_APPEND);
        }

    }

    /* ======================================		
    * 	FORMAS DE PAGAMENTO 
    * ========================================
    */	

    //DETERMINA SE O PAGAMENTO FOI FEITO POR CUPOM
    if($formaPagto=='cupom') {

        //LANCA NA LISTA DE PRESENCA
        $lancadoOK = lancarCupom($transacaoid,$evoid,$evento,$cupom,$eventoid) ;
        $mArr = "LISTA DE PRESENCA EVO (LancadoOK):\n".print_r($lancadoOK,true)."\n" ;
        file_put_contents('cupom_treino.log',$mArr,FILE_APPEND);

        if(!$lancadoOK[0]) {
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro cadastrando cliente na lista de Treinamento';
            $msg = 'Consulte o arquivo erros_envio_lista_presenca_treinamento.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
        }

        //DA BAIXA NO CUPOM
        $baixaOK = baixarCupom($cupom,$evento,$siteid,$cnab);
        $mArr = "LISTA CUPONS SITE (BaixaOK):\n".print_r($baixaOK,true)."\n" ;
        file_put_contents('cupom_treino.log',$mArr,FILE_APPEND);

        if(!$baixaOK[0]) {
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro baixando Cupom de Cliente';
            $msg = 'Consulte o arquivo erros_baixa_cupom.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
        }

        if($lancadoOK[0]==false || $baixaOK[0]==false) {
            $ret = "PROBLEMAS REALIZANDO SUA INSCRIÇÃO COM CUPOM<br/>POR FAVOR ENTRE EM CONTATO COM NOSSA CENTRAL
            <br/>E INFORME SEUS DADOS E O NÚMERO DO CUPOM:<br/><h2>".$cupom."</h2>" ;
        } else {
            $ret = "<div class='cadastro' style='margin-left:20%'><h1>".$cupom."</h1><br/><p>Sua inscrição foi ativada com sucesso 
            com o cupom acima. Em caso de dúvidas entre em contato com nossa central.</p>" ;
        }

        echo $ret ;

    } 

    else if($formaPagto == 'boleto') {

        $nnum = nosso_numero() ;

        $i1 = 'NÃO RECEBER ESTE DOCUMENTO APÓS VENCIMENTO';
        $i2 = 'PAGAMENTO REFERENTE A INSCRIÇÃO' ;
        $i3 = $evento ;
        $i4 = '';
        $i5 = 'USO INTERNO: '.$cnab ;

        //PROCESSA OS DADOS DO BOLETO
        // $boleto[0] ==> linha digitavel
        // $boleto[1] ==> codigo de barras
        $data_documento = date('d/m/Y');
        $data_vencimento = date('d/m/Y',strtotime('+1 day'));
        $docnum = $evoid;

        $data_doc_db = date('Y-m-d');
        $data_venc_db = date('Y-m-d',strtotime('+1 day'));


        $boleto = ProcBoleto($nnum,$data_documento,$data_vencimento,$valor_cobrado,$nome,$endereco,$cpf,$i1,$i2,$i3,$i4,$i5) ;
        $linha = $boleto[0] ;

        //envia dados do boleto para o site (tabela boleto_bs)
        $boleto_bs = inserirBoletoBs($evento,'TR',$cnab,$evoid,$siteid,$nome,$email,$endereco,$promocode,$data_doc_db,$data_venc_db,$nnum,$docnum,$linha,$valor_cobrado,$valor_frete,'1',$transacaoid) ;

        if(!$boleto_bs[0]) {
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro cadastrando boleto no Site';
            $msg = 'Consulte o arquivo erros_boletos_site.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
        }

        //envia dados do boleto para o evo
        $boleto_evo = inserirBoletosEvo($evento,$cnab,$evoid,$promocode,$data_doc_db,$data_venc_db,$nnum,$docnum,$valor_cobrado,$valor_frete,$endereco) ;

        if(!$boleto_evo[0]) {
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro cadastrando boleto no EVO';
            $msg = 'Consulte o arquivo erros_boletos_evo.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
        }

        //gera o botào do boleto
        $botao = setBoleto($cnab,$nnum,$data_documento,$data_vencimento,$valor_cobrado,$nome,$endereco,$cpf,$evento,$transacaoid) ;
        $mArr = "BOTAO BOLETO: "."\n".$botao."\n" ;
        file_put_contents('incricao_treino.log',$mArr,FILE_APPEND) ;

        echo $botao ;

        //FIM DO BOLETO //////

    } 

    else if($formaPagto == 'mp') {
        $tipo_remessa = 'SD';
        $nsa = '';
        $ret = sur($nome);
        $nome = $ret[0] ;
        $sobrenome = $ret[1];
        $botao = setMP($nome,$sobrenome,$email,$transacaoid,$siteid,$evoid,$cpf,$cep,$logradouro,$numero,$compl,$bairro,$cidade,$estado,$tipo_remessa,$cnab,$evento,$valor_cobrado,$valor_frete,$promocode,$nsa,$sigla) ;
        $mArr = "MERCADOPAGO: "."\n".$botao."\n" ;
        file_put_contents('inscricao_treino.log',$mArr,FILE_APPEND) ;
        echo $botao;

    } 

    else if($formaPagto == 'pagseg') {
        $botao = setPagSeg($evoid,$siteid,$cnab,$evento,$promocode,$valor_cobrado,$nome,$email) ;
        $mArr = "PAGSEGURO: "."\n".$botao."\n" ;
        file_put_contents('incricao_treino.log',$mArr,FILE_APPEND) ;
        echo $botao ;



    }



?>
