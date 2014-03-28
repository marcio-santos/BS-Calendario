<?php

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


function certificacao($evoid){
        //Recupera a Carreira do Cliente
try{        
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
        $prog_items = '<div>' ;
        foreach ($wsResult as $obj) {
            if($obj->ID_NIVEL_PROGRAMA >=1 && strlen($obj->DS_PROGRAMA_ABREV)>0){ 
               $prog= strtolower($obj->DS_PROGRAMA) ;
               $id = $obj->ID_SERVICO;
               $prog_items .= <<<EOT
                   <div style="float:left; display:inline-block">
                    <img src="_ferramentas/calendario/images/programas/ban.$prog.jpg" />
                    <input  type="checkbox" id="$id" name = "$id" checked="checked" />
                    </div>
EOT;
            }
        } 
        $prog_items = '</div>' ;
        $cert = true;
    } else {
        $prog_items = "<div class='alerta'>NENHUM PROGRAMA LOCALIZADO.<br/>Por favor entre em contato com nossa central.</div>" ;
        $cert = false;
    }
    
    return json_encode(array($cert,$prog_items)) ;
    
} catch (Exception $e){
            $log_msg = "[".date('Y-m-d H:i:s')."] ".$e->getErrMsg()."\n" ;
            file_put_contents('erros_recuperar_certificacao.log',$log_msg,FILE_APPEND) ;
            $to ='debug@bodysystems.net' ;
            $subject = 'Erro cadastrando boleto no EVO';
            $msg = 'Consulte o arquivo erros_recuperar_certificacao.log na pasta _ferramentas/calendario para detalhes.' ;
            sendEmail($to,$subject,$msg) ;
}
}


?>
