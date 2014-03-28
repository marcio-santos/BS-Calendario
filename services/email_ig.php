<?php

$evento= isset($_POST['evento'])? $_POST['evento']:'' ;
$nome = isset($_POST['nome'])? $_POST['nome']:'' ;
$email = isset($_POST['email'])? $_POST['email']:'' ;
$fone = isset($_POST['fone'])? $_POST['fone']:'' ;
$cidade = isset($_POST['cidade'])? $_POST['cidade']:'' ;
$estado = isset($_POST['estado'])? $_POST['estado']:'' ;
$estado = strtoupper($estado);
$obs = isset($_POST['obs'])? $_POST['obs']:'' ;
  
  if($nome==''|| $email==''|| $fone==''|| $cidade==''|| $estado=='') {
      $ret = array(1,'Campos vazios');
      
  } else {
      $to = "iniciacao.ginastica@bodysystems.net" ;
      $from = 'noreply@bodysystems.net';
      $subject = "INSCRICAO DE ULTIMA HORA IG" ;
      $body = <<<EOT
      <html>
      <p>Caro Educador,</p>
      <p>O cliente <strong>$nome</strong>, solicitou informações para sua inscrição no evento abaixo:<br/>
      <h3>$evento</h3>
      <p>E encaminhou seus dados:<hr/>
      Nome:$nome<br/>
      Email:$email<br/>
      Fone: $fone<br/>
      Cidade:$cidade<br/>
      Estado:$estado<br/>
      Observações:<br/>
      $obs<br/>
      <hr/></p>
      <small>Este email é uma comunicação automática. Não responda diretamente o mesmo</small>
      </html>
EOT;
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";


//Additional headers
$headers .= 'To: Equipe IG<iniciacao.ginastica@bodysystems.net>' . "\r\n";
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
