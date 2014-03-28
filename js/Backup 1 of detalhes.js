
jQuery(document).ready(function() {

	jQuery.noConflict() ;

	//CARREGA O DATEPICKER
	//DATEPICKER

	//RESET DOS DADOS DE USUARIO
	function resetUser() {
		jQuery('#h_siteid').val('')
		jQuery('#h_user').val('');jQuery('#user').val('');
		jQuery('#h_password').val('');jQuery('#password').val('');
		jQuery('#h_nome').val('');jQuery('#nome').val('');
		jQuery('#h_email').val('');jQuery('#email').val('');
		jQuery('#h_sexo').val('');jQuery('#sexo').val('0');
		jQuery('#h_nascimento').val('');jQuery('#nascimento').val('');
		jQuery('#h_fone').val('');jQuery('#fone1').val('');
		jQuery('#h_celular').val('');jQuery('#fone2').val('');
		return false;
	}

	//ESTRUTURA DO FORMULARIO
	jQuery('#wizard').smartWizard({  
		// Properties
		selected: 0,  // Selected Step, 0 = first step  
		keyNavigation: true, // Enable/Disable key navigation(left and right keys are used if enabled)
		enableAllSteps: false,  // Enable/Disable all steps on first load
		transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
		contentURL:null, // specifying content url enables ajax content loading
		contentCache:true, // cache step contents, if false content is fetched always from ajax url
		cycleSteps: false, // cycle step navigation
		enableFinishButton: false, // makes finish button enabled always
		errorSteps:[],    // array of step numbers to highlighting as error steps
		labelNext:'Proximo', // label for Next button
		labelPrevious:'Anterior', // label for Previous button
		labelFinish:'Concluir',  // label for Finish button       
		// Events
		onLeaveStep: leaveAStepCallback, // triggers when leaving a step
		onShowStep: enterAStepCallback,  // triggers when showing a step
		onFinish: onFinishCallback  // triggers when Finish button is clicked

	});

	//CALCULA VALORES DA TRANSAÇÃO
	function ValorTransacao($frete,$boleto) {

		// -----VALORES DO CURSO ---------  
		var $valor_curso = 0 ;

		if($boleto){
			$valor_curso = jQuery('#h_valor2').val() ;
		} else {
			$valor_curso = jQuery('#h_valor1').val() ;
		}

		var $valor_certificacao = jQuery('#h_certificacao').val()
		var $valor_total = parseFloat($valor_curso) + parseFloat($frete)+parseFloat($valor_certificacao);
		$valor_total = $valor_total.toFixed(2);

		//--------------------------------  
		var $ret = new Array($valor_curso,$valor_certificacao,$valor_total) ;  
		return $ret ;

	}

	//SISTEMA DE MASCARAS PARA AS ENTRADAS
	function leaveAStepCallback(obj){
		var step_num= obj.attr('rel'); // get the current step number
		return validateSteps(step_num); // return false to stay on step and true to continue navigation
	}

	//ENTRA NA ETAPA
	function enterAStepCallback(obj) {
		var step_num= obj.attr('rel');
		if(step_num==1) { jQuery('#cpf').focus()} ;
		if(step_num==2) { jQuery('#cep').focus()} ;
		if(step_num==3) { jQuery('#boleto').focus()} ;
	}

	function onFinishCallback(){

		if(!jQuery('#agree').is(':checked')) {
			isStepValid = false
			var $msg = "Você precisa concordar com os termos para prosseguir!" ;
			alert($msg);
			showWizardMessage($msg) ;
		} else {	

			jQuery.blockUI({ 
				message: '<h2>Processando...</h2>', 
				css: { border: '2px solid #333' } 
			}); 
			jQuery('.buttonFinish').addClass('buttonDisabled') ;
			jQuery('#resume').html('');
			/*
			jQuery('#frm_resume').children().each(
			function(){
			if(jQuery(this).attr('id') == 'h_evento_descricao' || jQuery(this).attr('id') == 'h_transacao' ||
			jQuery(this).attr('id') == 'h_evento_descricao'  || jQuery(this).attr('id') == 'h_nome'  || jQuery(this).attr('id') == 'h_email') {
			jQuery('#resume').append('<strong>'+jQuery(this).val()+'</strong><br/>');
			}
			});
			*/
			var $chl = encodeURIComponent(jQuery('#h_evento_descricao').val()+"\nInscrito(a):"+jQuery('#h_nome').val()+"\nEmail: "+jQuery('#h_email').val()+"\nInscrição\n"+jQuery('#h_transacao').val());
			$url = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl="+$chl ;
			var $qrcode = "<img src="+$url+" />" ;

			jQuery('#qrcode').append($qrcode);
			jQuery('#resume').append('<span style="font-size:20px; background-color:yellow;margin-left:10px;margin-bottom:10px;line-height:22px;">'+jQuery('#h_evento_descricao').val()+'</span><br/>');
			jQuery('#resume').append('<span style="font-size:14px;margin-left:10px;">'+jQuery('#h_nome').val()+'</span><br/>');
			jQuery('#resume').append('<span style="font-size:14px;margin-left:10px;">'+jQuery('#h_email').val()+'</span><hr/>');
			jQuery('#resume').append('<span style="font-size:11px;margin-left:10px;">'+jQuery('#h_transacao').val()+'</span>');

			//EXECUTA A CHAMADA AJAX PARA AS TAREFAS EM LOTE.
			if(jQuery('#h_finalizado').val(0)) {
				jQuery('#h_finalizado').val(1)
				finishHIM();
			}

			//------------------------------------------------

			isStepValid = true
			/*
			if(validateAllSteps()){
			jQuery('form').submit();
			}
			*/
		}

	}

	// Your Step validation logic
	function validateSteps(stepnumber){
		var isStepValid = true;
		// TERMOS E CONDICOES
		if(stepnumber == 4){
			// Your step validation logic
			// set isStepValid = false if has errors
			if(!jQuery('#agree').is(':checked')) {
				isStepValid = false
				var $msg = "Leia os Termos até o final. Você precisa concordar com os termos para prosseguir!" ;
				alert($msg);
				showWizardMessage($msg) ;
			} 
		}
		//DADOS PESSOAIS
		if(stepnumber == 1) {
			if(jQuery('#cpf').val() == '' || jQuery('#h_cpf').val()=='' ) {
				isStepValid = false;
				var $msg = "Informe seu CPF e clique em 'VERIFICAR' antes de prosseguir!" ;
				alert($msg);
				showWizardMessage($msg) ;
			}
			if(jQuery('#h_cpf').val()==2) {
				isStepValid = false;
				resetUser();
				var $msg = "Você precisa informar um CPF válido e clicar em 'Verificar' antes de prosseguir!" ;
				alert($msg);
				showWizardMessage($msg) ;
			}
			if(jQuery('#h_action').val()==1) {
				if(jQuery('#nome').val()=='' || jQuery('#user').val()=='' || jQuery('#password').val()=='' || jQuery('#email').val()=='' || jQuery('#nascimento').val()=='' || jQuery('#cod_area1').val()=='' || jQuery('#fone1').val()=='' || jQuery('#cod_area2').val()=='' || jQuery('#fone2').val()=='') {
					isStepValid = false;
					var $msg = "Existem campos que não foram informados!" ;
					alert($msg);
					showWizardMessage($msg) ;   
				} else {
					var $userOK = false;
					$userOK = checkNewUser() ;
					if($userOK==true) {
						$itstrue = '$userOK->true' ;
					} else {
						$itstrue = '$userOK->false' ;
					}
					console.debug('$userOK: '+ $userOK );
					if($userOK == true) {
						jQuery('#h_user').val(jQuery('#user').val());
						jQuery('#h_password').val(jQuery('#password').val());
						jQuery('#h_nome').val(jQuery('#nome').val());
						jQuery('#h_email').val(jQuery('#email').val());
						jQuery('#h_sexo').val(jQuery('#sexo').val());
						jQuery('#h_nascimento').val(jQuery('#nascimento').val());
						jQuery('#h_fone').val(jQuery('#fone1').val());
						jQuery('#h_celular').val(jQuery('#fone2').val());   
						isStepValid = true ; 
					} else {
						isStepValid = false ;
						showWizardMessage('Existem problemas com seu cadastro') ;   
					}

				}

			}
			if(jQuery('#h_action').val()==4) {
				isStepValid = false;
			}
		}
		//ENDERECAMENTO
		if(stepnumber==2){
			if(jQuery('#numero').val()=='' || jQuery('#logradouro').val()=='' || jQuery('#bairro').val()=='' || jQuery('#uf').val()==''){
				isStepValid = false
				var $msg = "Você precisa informar todos os campos no endereçamento!" ;
				alert($msg);
				showWizardMessage($msg) ;  
			} else if(jQuery('#h_valor_frete').val()==0 && jQuery('#h_tipo_treino').val()=='M1') {
				isStepValid = false
				var $msg = "Precisamos realizar uma conexão com os Correios antes de continuar. Informe seu CEP e clique em 'Verificar'." ;
				alert($msg);
				showWizardMessage('Não houve conexão com os Correios') ;  
			} else {
				jQuery('#h_logradouro').val(jQuery('#logradouro').val());
				jQuery('#h_numero').val(jQuery('#numero').val());
				jQuery('#h_complemento').val(jQuery('#complemento').val());
				jQuery('#h_bairro').val(jQuery('#bairro').val());
				jQuery('#h_cidade').val(jQuery('#cidade').val());
				jQuery('#h_uf').val(jQuery('#uf').val());
				jQuery('#h_cep').val(jQuery('#cep').val());  
				//jQuery('#h_valor_cobrado').val(parseFloat(jQuery('#valor_total').html).toFixed(2))
			}
		}

		return isStepValid;    
	}

	function validateAllSteps(){
		var isStepValid = true;
		// all step validation logic    
		return isStepValid;
	}      

	function showWizardMessage($msg){
		// You can call this line wherever to show message inside the wizard
		jQuery('#wizard').smartWizard('showMessage',$msg);
	}

	function print_register(){
		w=window.open();
		w.document.write(jQuery('#registro').html());
		w.print();
		w.close();
	}

	jQuery(function(e) {
		jQuery('input[type="text"]').setMask();
	});   

	jQuery.mask.masks.cpf = {mask: 'cpf'} ;
	jQuery.mask.masks.cep = {mask: 'cep'} ;
	jQuery.mask.masks.cel = {mask: '(99)99999-9999'} ;
	//jQuery.mask.masks.n_cup = {mask: '****-****-****-****'} ;
	//jQuery.mask.masks.in_promocode = {mask: '99999'} ;
	jQuery('#in_promocode').setMask('a9a9a').val('') ;
	jQuery('#numero_cupom').setMask('*****-*****-*****-*****').val('');
	jQuery('#fone1').setMask({
		mask:'(99)9999-9999',
		onInvalid:function(c,nKey){

		},
		onValid: function(c,nKey){

		},
		onOverflow: function(c,nKey){
			jQuery(this).setMask('(99)99999-9999');
		}
	});

	jQuery('#fone2').setMask('(99)9999-9999') ;
	
	if(jQuery('#h_tipo_treino').val()=='MTA') { jQuery('#div_promocode').show();} else {jQuery('#promocode').hide();}

	//VERIFICAÇÃO DO CPF
	jQuery("#bt_checar_cpf").click(function() {
		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/getEvoInfo.php"; // the script where you handle the form input
		jQuery.ajax({
			type: "POST",
			url: url,
			data:{cpf:jQuery('#cpf').val(),programa:jQuery('#h_sigla').val(),treino:jQuery('#h_tipo_treino').val()}, // serializes the form's elements.
			dataType: "json" ,
			beforeSend: function(load) {
				jQuery('#avatar').attr('src','http://177.154.135.250/~body2013/_ferramentas/calendario/images/user.png');
				jQuery('#div_checa_voce').hide() ;
				jQuery('#dv_avatar').hide() ;
				jQuery('#div_create_user').hide();
				jQuery('#frame_cadastro').fadeIn(100) ;
				jQuery('#div_cadastro').html("<div style='margin-top:30px;margin-left:45%;'><img src='http://177.154.135.250/~body2013/_ferramentas/calendario/images/cloud.gif' /></div>");
			} ,
			success: function(data) {
				resetUser();   
				//TEM CADASTRO NO SITE
				if(data[0]==1){
					jQuery('#avatar').attr('src',data[4]);
					var $html = '<div style="margin-left:30px;"><p>Olá! Já te identificamos e agora você pode prosseguir com sua inscrição. Clique em \'Proximo\'...<br/><span style="font-size:18px;"><strong>'+data[2]+'<br/>'+data[3]+'</strong></span></div><div class="note" style="margin-left:30px;"><strong>Se estes dados não são seus </strong><a href="#">clique aqui.</a></div>' ;
					jQuery('#div_cadastro').html($html) ;
					jQuery('#frame_cadastro').show();
					jQuery('#h_action').val(0);
					jQuery('#h_cpf').val(jQuery('#cpf').val().replace(/[\.-]/g, ""));
					jQuery('#h_siteid').val(data['1']);
					jQuery('#h_nome').val(data['2']);
					jQuery('#h_email').val(data['3']);
					jQuery('#h_sexo').val(data[5]['2']);
					jQuery('#h_nascimento').val(data[5]['3']);
					jQuery('#h_fone').val(data[5]['6']);
					jQuery('#h_celular').val(data[5]['7']);

					//CPF VALIDO  MAS SEM CADASTRO 
				} else if(data[0]== 0) {
					jQuery('#frame_cadastro').hide();
					jQuery('#div_checa_voce').hide() ;
					jQuery('#dv_avatar').hide() ;
					jQuery('#div_create_user').show();
					jQuery('#h_action').val(1);
					jQuery('#h_cpf').val(jQuery('#cpf').val());
					jQuery('#avatar').attr('src',data[2]);

					//CPF INVALIDO
				} else if(data[0]==2) {
					jQuery('#avatar').attr('src',data[2]);
					jQuery('#div_create_user').hide();
					jQuery('#frame_cadastro').show();
					jQuery('#div_cadastro').html(data[1]);
					jQuery('#h_cpf').val('');

					//CPF EXISTE NO EVO MAS NÃO TEM CADASTRO NO SITE
				} else if(data[0]==3) {
					jQuery('#avatar').attr('src',data[4]);
					var $html = '<div class="alert" style="margin-left:30px;">Se você não é <strong>'+data[2]+' </strong><a href="">clique aqui.</a></div>' ;
					jQuery('#div_checa_voce').html($html) ;
					jQuery('#frame_cadastro').hide();
					jQuery('#div_checa_voce').show() ;
					jQuery('#dv_avatar').show() ;
					jQuery('#div_create_user').show();
					jQuery('#nome').val(data[2]);
					jQuery('#nome').attr('disabled','disabled');
					jQuery('#email').val(data[3]);
					jQuery('#h_action').val(1);
					jQuery('#h_cpf').val(jQuery('#cpf').val());
					jQuery('#avatar').attr('src',data[4]);
					
					//MTA NÃO AUTORIZADO
				} else if(data[0]==4) {
					$url = "<a href='index.php?option=com_jumi&fileid=75&t=1&p="+jQuery('#h_sigla').val()+"'><img src='"+data[2]+"' /></a>" ;
					jQuery('#frame_cadastro').html($url);
					jQuery('#h_cpf').val(4) ; //ABORTA A NAVEGACAO
					jQuery('#h_action').val(4);

				}
				//jQuery('#div_cadastro').html(data);
				//jQuery('#parcelas_boleto').hide();    

			} ,
			error: function (request, status, error) 
			{
				resetUser();
				jQuery('#div_cadastro').html('<div style="color: red">Não foi possivel executar o login.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
				console.debug(error) ;
			}
		});

		return false; // avoid to execute the actual submit of the form.
	});

	//VERIFICACAO DO CODIGO DO PROMOTOR
	jQuery("#bt_aplicar_promo").click(function() {

		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/getCodigoPromotor.php"; // the script where you handle the form input.

		jQuery.ajax({
			type: "POST",
			url: url,
			data: jQuery("#frm_promocode").serialize(), // serializes the form's elements.
			dataType: "json" ,
			beforeSend: function(load) {
				jQuery('#img_load').show();
			} ,
			success: function(data)
			{
				jQuery('#img_load').hide();
				if(data[0]=='np') {
					jQuery('#promocode').val('CODIGO INVÁLIDO') ;   
					jQuery('#h_promocode').val('np');
				} else {
					jQuery('#cupom').remove();  
					jQuery('#div_promocode').html('<span style="color:ForestGreen;font-weight:bold;">CODIGO VÁLIDO! O BÔNUS FOI APLICADO COM SUCESSO.</span>');
					jQuery('#valor_desconto').html(data[1]);
					jQuery('#codigo_do_promotor').html(data[0])
					jQuery('#h_promocode').val(data[0]);
					var $total = parseFloat(jQuery('#valor_total').html());
					var $treino = parseFloat(jQuery('#valor_treino').html());
					var $desconto = parseFloat(data[1]);
					$total = $total - $desconto ;
					$treino = $treino - $desconto ;
					jQuery('#valor_total').html($total.toFixed(2));
					jQuery('#h_valor_cobrado').val($total.toFixed(2));
					jQuery('#valor_treino').html($treino.toFixed(2));

					//APLICA DESCONTOS AOS VALORES DE REFERENCIA
					$V1 = parseFloat(jQuery('#h_valor1').val())- $desconto ;
					$V2 = parseFloat(jQuery('#h_valor2').val())- $desconto ;
					jQuery('#h_valor1').val($V1.toFixed(2)) ;
					jQuery('#h_valor2').val($V2.toFixed(2)) ;

				}
				//jQuery('#parcelas_boleto').hide();  
				return false;  

			} ,
			error: function (request, status, error) 
			{
				jQuery('#img_load').hide();
				jQuery('#div_promocode').html('<div style="color: red"><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
				jQuery('#h_promocode').val('np') ;
			}
		});

		return false; // avoid to execute the actual submit of the form.
	}); 

	//VERIFICAÇÃO DO CEP
	jQuery("#bt_checar_cep").click(function() {

		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/getCorreios.php"; // the script where you handle the form input.

		jQuery.ajax({
			type: "POST",
			url: url,
			data: jQuery("#frm_checar_cep").serialize(), // serializes the form's elements.
			dataType: "json" ,
			beforeSend: function(load) {
				jQuery('#erro_correios').hide()
				jQuery('#div_result_correios').hide();
				jQuery('#cog').fadeIn(100);
			} ,
			success: function(data)
			{          
				
				if(data[4]==0 || data[3]=='' || data[2]=='') {
					jQuery('#cog').hide();
					jQuery('#erro_correios').show()
				}   else {
					if(data[0]===false) {
						alert('INFORME UM CEP VÁLIDO ANTES DE PROSSEGUIR')  ;	
						jQuery('#cog').hide();
					} else {
						jQuery('#cog').hide();
						jQuery('#div_result_correios').show()  ;
						//jQuery('#div_result_correios').html(data);
						jQuery('#logradouro').val(data[0]);
						jQuery('#bairro').val(data[1]);
						jQuery('#cidade').val(data[2]);
						jQuery('#uf').val(data[3]);
						//FRETE SOMENTE PARA TREINAMENTOS INICIAIS E WORKSHOP
						if(jQuery('#h_tipo_treino').val()!='M1' && jQuery('#h_tipo_treino').val()!='WS'){
							data[4] = 0;
						}	
						$valores = ValorTransacao(data[4],true) ;
						jQuery('#valor_frete').html(data[4]);
						jQuery('#h_valor_frete').val(data[4]);		
						jQuery('#valor_treino').html($valores[0]);
						jQuery('#valor_certificacao').html($valores[1]);
						jQuery('#valor_total').html($valores[2]) ;        
						jQuery('#h_valor_cobrado').val($valores[2]);
						jQuery('#numero').focus();
					}
				} 
			},
			error: function (request, status, error) 
			{
				jQuery('#cog').hide();

				jQuery('#div_result_correios').html('<div style="color: red">Não foi possivel executar a busca.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
				jQuery('#div_result_correios').show();
				console.debug(error) ;
			}
		});

		return false; // avoid to execute the actual submit of the form.
	});
	
	//IMPRIME O COMPROVANTE DE REGISTRO
	jQuery('#img_print').click(function(){
	   print_register(); 
	});

	//CONTROLA ALTERACAO DO CEP SEM FAZER CHAMADA AO CORREIO
	jQuery('#cep').keyup(function(d){
		//LIMPA JÁ E SOMENTE NA PRIMEIRA LETRA
		jQuery('#logradouro').val('');
		jQuery('#bairro').val('');
		jQuery('#cidade').val('');
		jQuery('#uf').val('');
		jQuery('#valor_frete').html('0');
		jQuery('#valor_treino').html('0');
		jQuery('#valor_certificacao').html('0');
		jQuery('#valor_total').html('0') ; 
		jQuery('#h_valor_cobrado').val(0);
	});

	//CONTROLA ALTERACOES DO CPF SEM FAZER A CHAMADA DA ROTINA
	jQuery('#cpf').keyup(function(f){
		//LIMPA JÁ E SOMENTE NA PRIMEIRA LETRA
		jQuery('#h_cpf').val('') ;
	});


	//ESCOLHA DA FORMA DE PAGTO
	jQuery('div[gap*=forma_pagto]').click(function(e){
		jQuery('#h_formaPagto').val(jQuery(this).attr('id'));
		var $the_image = "http://177.154.135.250/~body2013/_ferramentas/calendario/images/"+jQuery(this).attr('id')+"_detalhes.png" ;
		if(jQuery(this).attr('id') =='cupom'){
			jQuery('#div_forma_pagto').hide() ;
			jQuery('#div_promocode').hide();
			jQuery('#div_form_cupom').show('fast');
		} else {
			jQuery('#div_forma_pagto').css("background-image","url("+$the_image+")") ;
			jQuery('#div_forma_pagto').show('fast') ;
			jQuery('#div_promocode').show('fast');
			jQuery('#div_form_cupom').hide();
			if(jQuery(this).attr('id') == 'boleto'){
				$frete = jQuery('#valor_frete').html();
				$valores = ValorTransacao($frete,true) ;
				jQuery('#valor_frete').html($frete);
				jQuery('#valor_treino').html($valores[0]);
				jQuery('#valor_certificacao').html($valores[1]);
				jQuery('#valor_total').html($valores[2]) ;
				jQuery('#h_valor_cobrado').val($valores[2]) ;


			} else {
				$frete = jQuery('#valor_frete').html();
				$valores = ValorTransacao($frete,false) ;
				jQuery('#valor_frete').html($frete);
				jQuery('#valor_treino').html($valores[0]);
				jQuery('#valor_certificacao').html($valores[1]);
				jQuery('#valor_total').html($valores[2]) ; 
				jQuery('#h_valor_cobrado').val($valores[2]) ;
			}
		}

	});

	//VALIDA CUPOM E DESCONTO
	jQuery('#validar_cupom').click(function(c){
		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/validar_cupom.php"; // the script where you handle the form input.

		jQuery.ajax({
			type: "POST",
			url: url,
			data: jQuery("#frm_cupom").serialize(), // serializes the form's elements.
			dataType: "json" ,
			beforeSend: function(load) {
				jQuery('#response_cupom').html("<div style='margin-top:30px;width:200px;' align='center'><img src='http://177.154.135.250/~body2013/_ferramentas/calendario/images/cloud.gif' /></div>");
			} ,
			success: function(data)
			{

				jQuery('#response_cupom').html(data[1]);
				if(data[0]==1) {
					jQuery('#h_cupom').val(data[2]);
				}
				//jQuery('#parcelas_boleto').hide();  

			} ,
			error: function (request, status, error) 
			{
				jQuery('#response_cupom').html('<div style="color: red">Não foi possivel executar o login.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
				console.debug(error) ;
			}
		});

		return false; // avoid to execute the actual submit of the form.

	});

	//FINALIZA O PROCESSO E ENTREGA O BOTÃO DE PAGAMENTO
	function finishHIM(){

		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/inscricao_treino.php"; // the script where you handle the form input.
		jQuery.ajax({
			type: "POST",
			url: url,
			data: jQuery("#frm_resume").serialize(), // serializes the form's elements.
			dataType: "html" ,
			beforeSend: function(load) {


				//jQuery('#img_load').show();
			} ,
			success: function(data)
			{
				if(jQuery('#h_formaPagto').val()=='cupom'){ 
					jQuery('#tudo_certo').hide();
					jQuery('#hpagto').html('Parabéns! Você já está inscrito(a).');
				}
				
				jQuery('#pagamento').append(data);
				jQuery('#h_finalizado').val('1');
				//event.preventDefault();
				jQuery('#wizard').fadeOut('fast');
				jQuery('#final_step').fadeIn('fast');
				jQuery.unblockUI(); 
			} ,
			error: function (request, status, error) 
			{
				jQuery('#pagamento').html('<div style="color: red"><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
				jQuery('#h_finalizado').val('0');
				jQuery('#wizard').unblock();
			}
		});
	}

	//VERIFICAÇÃO DA EXISTENCIA DE NOME DE USUARIO E EMAIL PARA NOVOS USERS
	function checkNewUser() {
		var url = "http://177.154.135.250/~body2013/_ferramentas/calendario/services/validar_user_email.php";
		var $username= jQuery('#user').val();
		var $email = jQuery('#email').val();
		jQuery.ajax({
			type: "POST",
			async: false,
			url: url,
			data: { username:$username,email:$email },
			dataType: "json" ,
			beforeSend: function(load) {
				jQuery('#user_loader').show();
				jQuery('#email_loader').show();
			} ,
			success: function(data)
			{
				console.debug(data);
				jQuery('#user_loader').hide();
				jQuery('#email_loader').hide();
				jQuery('#user').css('border-left', 0) ;
				jQuery('#email').css('border-left', 0) ;
				$ret = true
				if(data[2]===false){
					
					jQuery('#user').css('border-left','solid 2px red') ;
					alert('O nome de usuário que você está tentando cadastrar já consta em nosso site, relacionado a um CPF diferente do que você informou. Se você já se cadastrou anteriormente no site seu CPF pode estar em branco em seu perfil. \n\nSão suas opções: alterar o nome de usuário neste cadastramento ou se você já se cadastrou anteriormente em nosso site, edite o número de CPF em seu Perfil no site.') ;
					$ret = false ;
				}

				if(data[3]===false){
					
					jQuery('#email').css('border-left','solid 2px red') ;
					alert('O email que você está tentando cadastrar já consta em nosso site, relacionado a um CPF diferente ao que você informou. Se você já se cadastrou anteriormente no site seu CPF pode estar em branco em seu perfil. \n\nSão suas opções: alterar o email neste cadastramento ou se você já se cadastrou anteriormente em nosso site, edite o número de CPF em seu Perfil no site.') ;
					$ret = false ;
				} 
			} ,
			error: function (request, status, error) 
			{
				jQuery('#user_loader').hide();
				jQuery('#email_loader').hide();
				console.debug(error);
				$dev = false;
				return $dev;
			}
		});
		return $ret ;
	}


}); 

//MOSTRA SOMENTE DEPOIS QUE TUDO CARREGOU
jQuery(window).load(function() { 

	jQuery('#main_cog').hide();
	jQuery('#wizard').fadeIn('fast');
	//ABRE COM FOCO NO CPF
	jQuery('#cpf').focus();
});