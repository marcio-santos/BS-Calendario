<?php
//include('../../exec_in_joomla.inc') ;
//------------------------------
	function template_eval(&$template, &$vars) { return strtr($template, $vars); }
//------------------------------

$user=&JFactory::getUser();
$document=&JFactory::getDocument();

//$document->addStyleSheet('http://code.jquery.com/ui/1.7.0/themes/base/jquery-ui.css');
$document->addScript('http://code.jquery.com/jquery-1.8.2.js');
$document->addScript('http://code.jquery.com/ui/1.9.0/jquery-ui.js') ;

$document->addStyleSheet('http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css');

//$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js');
//$document->addScript('http://code.jquery.com/ui/1.7.0/jquery-ui.js') ;
$document->addScript('_ferramentas/calendario/js/grid_calendar.js');


$document->addScript('_ferramentas/calendario/js/jquery.multiselect.min.js');
$document->addStyleSheet('_ferramentas/calendario/js/jquery.multiselect.css');
//$document->addStyleSheet('_calendarios/treino/lib/dp/css/redmond/jquery-ui-1.7.1.custom.css');

//TIPO DE VISUALIZAÇÃO
if(isset($_GET['v'])){
	$view = $_GET['v'] ;
} else {
	$view = 2;
}

//TIPO DE TREINO
if(isset($_GET['t'])){
	$treino = $_GET['t'] ;
} else {
	$treino = 1;
}

//PROGRAMA
if(isset($_GET['p'])){
	$programa = $_GET['p'] ;
} else {
	$programa = "";
}

  
$template_main = <<<EOT
<form id="frm_calendar" style="display:none;" name="frm_calendar" action="http://177.154.135.250/~body2013/_ferramentas/calendario/services/evo_calendar.php" method="post">
<table width="980">
<tr>
	<td width="250" valign="top">
	<div><img src="http://177.154.135.250/~body2013/_ferramentas/calendario/images/melhore_sua_busca.png" /></div>
		<div id="treinamento">
			<small>Escolha o calendário</small>
				<select id="tipo_treino" name="tipo_treino">
					<option value="1">Selecione a opção</option>
					<option value="1">Treinamento Inicial</option>
					<option value="2">Treinamento Módulo 2</option>
					<option value="3">Treinamento Avançado MTA</option>
					<option value="13">Iniciação à Ginástica</option>
					<!--<option value="20">Treinamento Integrado</option>-->
				</select>
		
		</div>
		<div id="programas">
			<small>Escolha o programa</small>
				<select id="programa" name="programa" multiple="multiple">
					<option value="">Todos os programas</option>
					<option value="BA">BODYATTACK&trade;</option>
					<option value="BB">BODYBALANCE&trade;</option>
					<option value="BC">BODYCOMBAT&trade;</option>
					<option value="BJ">BODYJAM&trade;</option>
					<option value="BP">BODYPUMP&trade;</option>
					<option value="BV">BODYVIVE&trade;</option>
					<option value="BS">BODYSTEP&trade;</option>
					<option value="PJ">POWER JUMP</option>
					<option value="RPM">RPM&trade;</option>
					<option value="SB">SH&apos;BAM&trade;</option>
					<option value="CX">CXWORX&trade;</option>
				</select>
		
		</div>
		<div id="estado">
			<small>Escolha o estado</small>
			<select name="estado">
				<option value="">Todos os estados</option>
				<option value="AC">Acre</option>
				<option value="AL">Alagoas</option>
				<option value="AM">Amazonas</option>
				<option value="AP">Amapá</option>
				<option value="BA">Bahia</option>
				<option value="CE">Ceará</option>
				<option value="DF">Distrito Federal</option>
				<option value="ES">Espirito Santo</option>
				<option value="GO">Goiás</option>
				<option value="MA">Maranhão</option>
				<option value="MG">Minas Gerais</option>
				<option value="MS">Mato Grosso do Sul</option>
				<option value="MT">Mato Grosso</option>
				<option value="PA">Pará</option>
				<option value="PB">Paraíba</option>
				<option value="PE">Pernambuco</option>
				<option value="PI">Piauí</option>
				<option value="PR">Paraná</option>
				<option value="RJ">Rio de Janeiro</option>
				<option value="RN">Rio Grande do Norte</option>
				<option value="RO">Rondônia</option>
				<option value="RR">Roraima</option>
				<option value="RS">Rio Grande do Sul</option>
				<option value="SC">Santa Catarina</option>
				<option value="SE">Sergipe</option>
				<option value="SP">São Paulo</option>
				<option value="TO">Tocantins</option>
			</select>

		</div>
		<div id="datas">
			<small>Escolha o intervalo de datas</small><br/>
			<input class="datepicker" id="inicio" name="inicio" placeholder="&nbsp;inicio" value="" size="14" />
			<input class="datepicker" id="fim" name="fim"  placeholder="&nbsp;fim" value="" size="14" /> 
			<!--<input id="datas" name="datas" value="" size="25" /> -->
		 </div>
		 <div>
			<input type="submit" id="view_calendar" name="view_calendar" value=" Mostrar Calendário " />
		 </div>
		 
		<div style="padding-top:15px"><img src="http://177.154.135.250/~body2013/_ferramentas/calendario/images/bt_encerrados.png" id="bt_encerrados" style="cursor:pointer" /></div>
	</td>
	<td width="670" valign="top">
		<div id="cog" style='margin-top:30px;margin-left:35%;display:none;'><img src='http://177.154.135.250/~body2013/_ferramentas/calendario/images/cloud.gif' /></div>
		<div id="grid_response"></div>
	</td>
</tr>
</table>
<input type="hidden" id="v" name="v" value="$view"  />
<input type="hidden" id="t" name="t" value="$treino"  />
<input type="hidden" id="h_programa" name="h_programa" value="$programa"  />
</form>
EOT;

echo $template_main ;

?>
