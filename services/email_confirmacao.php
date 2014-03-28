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
      <div>
<ol>
<h4>
          <strong>Leia com atenção:</strong>
    </h4>
<li><strong>Sua inscrição somente será efetuada à partir da confirmação de seu pagamnto.</strong></li>
<li>Após confirmada a inscrição para essa data, não será  possível realizar nenhuma alteração.</li>
<li>O inscrito declara estar ciente que o treino tem a  duração de dois dias, e que o não comparecimento em um dos dias o obrigará a  refazê-lo.</li>
<li>Após efetivada a inscrição, o inscrito declara estar  ciente que não poderá desistir do treino.</li>
<li>Caso o inscrito deseje refazer o treino por não ter  comparecido, terá que pagar valor equivalente a 50% do valor integral.</li>
<li>Na hipótese de desistência, o inscrito declara estar  ciente que, para cobrir custos operacionais, somente terá direito ao  ressarcimento de 50% do valor do treino.&nbsp;</li>
</ol>
</div>
    <h2 class="StepTitle">Regras para sua participação</h2>
<ul>
        <h4>
        <strong>Para efetivar a sua inscrição e participação, é imprescindível:</strong>
        </h4>
        <li>Ligar na Central Body Systems na sexta-feira anterior ao treinamento para confirmação do mesmo.</li>
        <li>Levar consigo seu Notebook ou disc man no dia do treino para estudo das músicas e vídeos
        </li><li>Entregar o seu comprovante de pagamento do treinamento no dia do mesmo (sujeito a não liberação da entrada)</li>
        <li>Entregar também no dia do treino uma cópia do seu comprovante de escolaridade CREF ou cópia do diploma ou histórico escolar" (sujeito a não liberação da entrada); Lembre-se:" somente profissionais e estudantes de educação física podem se inscrever</li>
        <li>Chegar com antecedência. (Atrasos não serão bem vindos)</li>
        <li>E não esqueça de levar roupas para troca durante o dia, pois você vai suar muito!</li>
    </ul>
      </div>
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
