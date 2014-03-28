<?php

    $evento= isset($_POST['evento'])? $_POST['evento']:'' ;
    $nome = isset($_POST['nome'])? $_POST['nome']:'' ;
    $email = isset($_POST['email'])? $_POST['email']:'' ;
    $fone = isset($_POST['fone'])? $_POST['fone']:'' ;


    if($nome==''|| $email=='') {
        $ret = array(1,'Campos vazios');

    } else {
        $to = $email;
        $from = 'noreply@bodysystems.net';
        $subject = "INSCRICAO TREINAMENTO BODY SYSTEMS" ;
        $body = <<<EOT
      <html>
<style type="text/css">
body,td,th {
    font-family: Arial, Helvetica, sans-serif;
}
</style>

      <p style="font-size:18px">Caro $nome,</p>
      <p>Este email é a confirmação de sua inscrição no $evento.<br>
        <br/>
    <br style="clear:bottom" />
      
      <p>A inscrição foi efetuado utilizando os seguintes dados:
<hr/>
      Nome:$nome<br/>
      Email:$email<br/>
      <hr/></p>
      <p>Em caso de dúvidas, por favor, entre em contato conosco em nossa Central pelo fone (11) 3529-2880.</p>
      <p style="font-size:12px">Este email é uma comunicação automática. Não responda diretamente o mesmo</p>
      </html>
EOT;
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";


        //Additional headers
        $headers .= 'To:'.$email . "\r\n";
        $headers .= 'From: Site Body Systems <noreplay@bodysystems.net>' . "\r\n";
        //$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
        //$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";      



        // Mail it
        $response = mail($to,$subject,$body,$headers);
        if($response) {
            $ret = array(0,'Sucesso no envio') ;
        } else {
            $ret = array(2,'Falha no envio') ;
        }


    }


    echo json_encode($ret);


?>
