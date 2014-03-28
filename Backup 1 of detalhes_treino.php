<?php
//include('../../exec_in_joomla.inc') ;
//ESTE ARQUIVO TEM QUE SER EXECUTADO ATRAVÉS DO JUMI.
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$user=&JFactory::getUser();
$document=&JFactory::getDocument();

//$document->addScript('plugins/system/cdscriptegrator/libraries/jquery/js/jquery-noconflict.js') ;
$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js');
//$document->addScript('http://code.jquery.com/jquery-1.9.0.js');
$document->addScript('http://177.154.135.250/~body2013/_ferramentas/calendario/js/jquery.smartWizard-2.0.js');
$document->addScript('http://177.154.135.250/~body2013/_ferramentas/calendario/js/jquery.meio.mask.js');
$document->addScript('http://177.154.135.250/~body2013/_ferramentas/calendario/js/detalhes.js');
$document->addScript('http://malsup.github.io/jquery.blockUI.js') ;
$document->addStyleSheet('http://177.154.135.250/~body2013/_ferramentas/calendario/styles/smart_wizard_vertical.css');
$document->addStyleSheet('http://177.154.135.250/~body2013/_ferramentas/calendario/styles/main.css');


   
function uuid(){
	// version 4 UUID
	$get =  sprintf(
		'%08x-%04x-%04x-%02x%02x-%012x',
		mt_rand(),
		mt_rand(0, 65535),
		bindec(substr_replace(
			sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
		),
		bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
		mt_rand(0, 255),
		mt_rand()
	);
	return strtoupper($get) ;
}

function template_eval(&$template, &$vars) {
		return strtr($template, $vars);
	}


//CARREGA AS INFORMAÇÕES INICIAIS DO TIPO TREINO
//VALORES PRAZOS E ETC.
$treinamento = $_POST['treino'] ;
$ini_file = parse_ini_file('calendario.cfg',true) ;
$treino = $ini_file[$treinamento] ;
$valor_1 = $treino['valor1'] ;
$valor_2 = $treino['valor2'] ;
$valor_certificacao = $treino['certificacao'] ;
$desconto = $treino['desconto'] ;

if(!isset($_POST['id-evento']) || !isset($_POST['nome-evento'])) {
	Header("Location: index.php?option=com_jumi&fileid=75") ;
	exit();
}

/*
$d_ecode = $_GET['data'] ;
$data = base64_decode($d_ecode);
$arInfo = json_decode($data,true);

$evento_nome = $arInfo['nome-evento'] ;
$evento_id =$arInfo['id-evento'] ;
$cidade = $arInfo['cidade'] ;
$datas = $arInfo['datas'] ;
$logo = $arInfo['logo'] ;
$ende = $arInfo['ende-evento'] ;
//$horario = $arInfo['horario'] ;
$horario = "9:00 às 18:00<br>Carga horária:<br>16 horas (Sábado: 8:00 | Domingo: 8:00)" ;
$inicio = $arInfo['inicio'] ;
$intervalo = $arInfo['intervalo'] ;
$aviso = $arInfo['aviso'] ;
*/
//CARREGA AS INFORMAÇÕES DO TREINO ESCOLHIDO
$banner= $_POST['banner'];
$cidade = $_POST['cidade'];
$local = $_POST['local'];
$horario = $_POST['horario'] ;
$eventoid= $_POST['id-evento'];
$nome_evento = $_POST['nome-evento'];
$endereco= $_POST['ende-evento'];
$
$dtm1 = $_POST['dt-m1'];
$dtm2 = $_POST['dt-m2'];
$obs = $_POST['obs'];
$intervalo = $_POST['intervalo'] ;
$sigla = $_POST['sigla'] ;
$treino = $_POST['treino'];
$img_valor = $_POST['img_valor'];
$datas = $dtm1.' e '.$dtm2 ;
$cnab = 'E'.$eventoid.'|TR|'.$sigla.'|EV' ;

$encerrado = ($intervalo<2)? 'block':'none' ;
$choose = ($intervalo<2)? 'none':'block' ;
$detalhes_valor = ($treino =='M1')?'block':'none' ;

//GERA O ID DA TRANSACAO
$transacaoid = uuid();
switch($treino) {
	Case 'M1':
		$template_main = file_get_contents('http://177.154.135.250/~body2013/_ferramentas/calendario/treino_ui.html') ;    
		break;
	Case 'IG':
		$template_main = file_get_contents('http://177.154.135.250/~body2013/_ferramentas/calendario/treino_ui_ig.html') ;    
		break;
	Case 'MTA':
		$template_main = file_get_contents('http://177.154.135.250/~body2013/_ferramentas/calendario/treino_ui_ig.html') ;    
		break;
}

/*
$template_main = <<<EOT
<div id="wizard" class="swMain">
  <ul>
	<li><a href="#step-1">
		  <label class="stepNumber">1</label>
		  <span class="stepDesc">
			 Passo 1<br />
			 <small>Informações Gerais</small>
		  </span>
	  </a></li>
	<li><a href="#step-2">
		  <label class="stepNumber">2</label>
		  <span class="stepDesc">
			 Passo 2<br />
			 <small>Informações Pessoais</small>
		  </span>
	  </a></li>
	<li><a href="#step-3">
		  <label class="stepNumber">3</label>
		  <span class="stepDesc">
			 Passo 3<br />
			 <small>Endereço para Remessa</small>
		  </span>                  
	   </a></li>
	<li><a href="#step-4">
		  <label class="stepNumber">4</label>
		  <span class="stepDesc">
			 Passo 4<br />
			 <small>Inscrição e Pagamento</small>
		  </span>                  
	  </a></li>
	  <li><a href="#step-5">
		  <label class="stepNumber">5</label>
		  <span class="stepDesc">
			 Passo 5<br />
			 <small>Finalização</small>
		  </span>                  
	  </a></li>
  </ul>
  <div id="step-1">  
	  <h2 class="StepTitle">Detalhes</h2>
	  <div id="regras" style="overflow:auto;padding:5px;width:100%;max-width:680px;">
	<h5>
		<strong>PROFESSOR CONFIRME SUA INSCRIÇÃO!</strong>
	</h5>
	<h5>
		<strong>É IMPORTANTE</strong> entrar em contato com a central Body Systems para a confirmação do evento e de sua inscrição, na SEXTA-FEIRA anterior ao dia do treino.
	</h5>
	<p>
		Telefone: 11 3529.2880 | e-mail: treinamento@bodysystems.net
	</p>
	<p style="margin-left:20px;">
		É necessário ficar atento ao site, pois todos os treinamentos previstos neste Calendário estão sujeitos a alterações e são atualizados diariamente. Somente serão aceitas as inscrições feitas até a terça-feira que antecede o treinamento;<br>
		Após esta data, o treinamento será automaticamente retirado do ar.
	</p>
	<h2 class="StepTitle">Regras para sua participação</h2>
	<ul>
		<h5>
		<strong>Para confirmar a sua inscrição e participação, é imprescindível:</strong>
		</h5>
		<li>Ligar na Central Body Systems na sexta-feira anterior ao treinamento para confirmação do mesmo.</li>
		<li>Levar consigo seu Notebook ou disc man no dia do treino para estudo das músicas e vídeos
		</li><li>Entregar o seu comprovante de pagamento do treinamento no dia do mesmo (sujeito a não liberação da entrada)</li>
		<li>Entregar também no dia do treino uma cópia do seu comprovante de escolaridade CREF ou cópia do diploma ou histórico escolar" (sujeito a não liberação da entrada); Lembre-se:" somente profissionais e estudantes de educação física podem se inscrever</li>
		<li>Chegar com antecedência. (Atrasos não serão bem vindos)</li>
		<li>E não esqueça de levar roupas para troca durante o dia, pois você vai suar muito!</li>
	</ul>
	  </div>
	  <br style="clear:bottom" />
	  <div style="float:right;position:relative; padding-right:30px;padding-top:20px;"><input type="checkbox" id="agree" name="agree" /><label for="agree">&nbsp;&nbsp;Li e Concordo com os termos acima</label></div>
  </div>
  <div id="step-2">
	  <h2 class="StepTitle">Informações Pessoais</h2>
	  <p style="padding-left:30px;">Inicie o preenchimento de seu dados digitando abaixo seu CPF e clicando em 'Verificar'.</p>
	  <form id="frm_checar_cpf" method="post" action="_ferramentas/calendario/services/getEvoInfo.php" style="padding-left:30px;padding-top:10px;">
	  <label for="cpf">Informe seu CPF&nbsp;&nbsp;</label><input alt="cpf" type="text" id="cpf" name="cpf" size="30" placeholder="somente numeros" /><input type="button" id="bt_checar_cpf" name="bt_checar_cpf" value="Verificar"  />
	  </form>
  </div>                     
  <div id="step-3">
	  <h2 class="StepTitle">Remessa</h2>  
		<form id="frm_checar_cep" method="post" action="_ferramentas/calendario/services/correios.php" style="padding-left:30px;padding-top:30px;">
	  <label for="cep">Informe seu CEP&nbsp;&nbsp;</label><input type="text" id="cep" name="cep" size="30" placeholder="somente numeros" /><input type="button" id="bt_checar_cep" name="bt_checar_cep" value="Verificar"  />
	  </form>
  </div>
  <div id="step-4">
	  <h2 class="StepTitle">Pagamento</h2>  
	  <table id="tb_pagto" style="width:100%">
		  <tr>
			<td>
				<form id="frm_boleto_bs" method="post" action="_ferramentas/calendario/services/boleto_bs.php">
					<div id="bt_boletos" class="bt_boletos">Boleto Bonificado</div>
				</form>
			</td>
			<td>
			
			</td>
			<td>
			
			</td>
		</tr>
	  </table>                   
  </div>
  <div id="step-5">
	  <h2 class="StepTitle">Finalização</h2>  
	  &nbsp;                 
  </div>
</div>

EOT;
*/

$params = array(
	'{EVENTO_NOME}' => $nome_evento,
	'{EVENTO_ID}' => $eventoid,
	'{LOCAL}' => $endereco,
	'{CIDADE}' => $cidade,
	'{DATAS}' => $datas,
	'{LOGO}' => $banner,
	'{CNAB}' => $cnab,
	'{ENDERECO}' => $endereco,
	'{HORARIO}' => $horario,
	'{AVISO}' => $aviso,
	'{TREINO}' => $treinamento,
	'{VALOR_1}' => $valor_1,
	'{VALOR_2}' => $valor_2,
	'{VALOR_CERTIFICACAO}' => $valor_certificacao,
	'{VALOR_DESCONTO}' => $desconto,
	'{TIPO_TREINO}' => $treino,
	'{SIGLA}' => $sigla,
	'{IMG_VALOR}' =>$img_valor,
	'{DETALHES_VALOR}' => $detalhes_valor,
	'{UUID}' => $transacaoid

);

echo template_eval($template_main,$params) ;



?>