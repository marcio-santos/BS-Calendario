
$(document).ready(function() {

    //jQuery.noConflict() ;

    //CARREGA O DATEPICKER
    //DATEPICKER

    //RESET DOS DADOS DE USUARIO
    function resetUser() {
        $('#h_siteid').val('')
        $('#h_user').val('');$('#user').val('');
        $('#h_password').val('');$('#password').val('');
        $('#h_nome').val('');$('#nome').val('');
        $('#h_email').val('');$('#email').val('');
        $('#h_sexo').val('');$('#sexo').val('0');
        $('#h_nascimento').val('');$('#nascimento').val('');
        $('#h_fone').val('');$('#fone1').val('');
        $('#h_celular').val('');$('#fone2').val('');
        return false;
    }

    //ESTRUTURA DO FORMULARIO
    $('#wizard').smartWizard({  
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
            $valor_curso = $('#h_valor2').val() ;
        } else {
            $valor_curso = $('#h_valor1').val() ;
        }

        var $frete_gratis = $('#h_frete_gratis').val();
        var $bonus_frete = 0;
        if($frete_gratis==1){
            $bonus_frete = $frete;
            $frete = '0.00';
        } else {
            $bonus_frete = '0';
        }

        var $valor_certificacao = $('#h_certificacao').val();
        var $valor_total = parseFloat($valor_curso) + parseFloat($frete)+parseFloat($valor_certificacao);
        $valor_total = $valor_total.toFixed(2);

        //--------------------------------  

        var $ret = new Array($valor_curso,$valor_certificacao,$valor_total,$frete,$bonus_frete) ;  
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
        if(step_num==1) { $('#cpf').focus()} ;
        if(step_num==2) { $('#cep').focus()} ;
        if(step_num==3) { $('#boleto').focus()} ;
    }

    function onFinishCallback(){

        if(!$('#agree').is(':checked')) {
            isStepValid = false
            var $msg = "Você precisa concordar com os termos para prosseguir!" ;
            alert($msg);
            showWizardMessage($msg) ;
        } else {	

            $.blockUI({ 
                message: '<h2>Processando...</h2>', 
                css: { border: '2px solid #333' } 
            }); 
            $('.buttonFinish').addClass('buttonDisabled') ;
            $('#resume').html('');
            /*
            $('#frm_resume').children().each(
            function(){
            if($(this).attr('id') == 'h_evento_descricao' || $(this).attr('id') == 'h_transacao' ||
            $(this).attr('id') == 'h_evento_descricao'  || $(this).attr('id') == 'h_nome'  || $(this).attr('id') == 'h_email') {
            $('#resume').append('<strong>'+$(this).val()+'</strong><br/>');
            }
            });
            */
            var $chl = encodeURIComponent($('#h_evento_descricao').val()+"\nInscrito(a):"+$('#h_nome').val()+"\nEmail: "+$('#h_email').val()+"\nInscrição\n"+$('#h_transacao').val());
            $url = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl="+$chl ;
            var $qrcode = "<img src="+$url+" />" ;

            $('#qrcode').append($qrcode);
            $('#resume').append('<span style="font-size:20px; background-color:yellow;margin-left:10px;margin-bottom:10px;line-height:22px;">'+$('#h_evento_descricao').val()+'</span><br/>');
            $('#resume').append('<span style="font-size:14px;margin-left:10px;">'+$('#h_nome').val()+'</span><br/>');
            $('#resume').append('<span style="font-size:14px;margin-left:10px;">'+$('#h_email').val()+'</span><hr/>');
            $('#resume').append('<span style="font-size:11px;margin-left:10px;">'+$('#h_transacao').val()+'</span>');

            //EXECUTA A CHAMADA AJAX PARA AS TAREFAS EM LOTE.
            if($('#h_finalizado').val(0)) {
                $('#h_finalizado').val(1)
                finishHIM();
            }

            //------------------------------------------------

            isStepValid = true
            /*
            if(validateAllSteps()){
            $('form').submit();
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
            if(!$('#agree').is(':checked') || !$('#agree_contrato').is(':checked')) {
                isStepValid = false
                var $msg = "Você precisa concordar com ambos os termos para prosseguir!" ;
                alert($msg);
                showWizardMessage($msg) ;
            } 
        }
        //DADOS PESSOAIS
        if(stepnumber == 1) {
            if($('#cpf').val() == '' || $('#h_cpf').val()=='' ) {
                var $msg = "Informe seu CPF e clique em 'VERIFICAR' antes de prosseguir!" ;
                alert($msg);
                showWizardMessage($msg) ;
                isStepValid = false;
            }
            if($('#h_cpf').val()==2) {
                isStepValid = false;
                resetUser();
                var $msg = "Você precisa informar um CPF válido e clicar em 'Verificar' antes de prosseguir!" ;
                alert($msg);
                showWizardMessage($msg) ;
            }
            if($('#h_action').val()==1) {
                if($('#nome').val()=='' || $('#user').val()=='' || $('#password').val()=='' || $('#email').val()=='' || $('#nascimento').val()=='' || $('#cod_area1').val()=='' || $('#fone1').val()=='' || $('#cod_area2').val()=='' || $('#fone2').val()=='') {
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

                    if($userOK == true) {
                        $('#h_user').val(jQuery('#user').val());
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
            if($('#h_action').val()==4) {
                isStepValid = false;
            }
        }
        //ENDERECAMENTO
        if(stepnumber==2){
            if($('#numero').val()=='' || $('#logradouro').val()=='' || $('#bairro').val()=='' || $('#uf').val()==''){
                isStepValid = false
                var $msg = "Você precisa informar todos os campos no endereçamento!" ;
                alert($msg);
                showWizardMessage($msg) ;  
            } else if($('#h_valor_frete').val()==0 && $('#h_bonus_frete').val()==0 && $('#h_tipo_treino').val()=='M1') {
                isStepValid = false
                var $msg = "Precisamos realizar uma conexão com os Correios antes de continuar. Informe seu CEP e clique em 'Verificar'." ;
                alert($msg);
                showWizardMessage('Não houve conexão com os Correios') ;  
            } else {
                $('#h_logradouro').val($('#logradouro').val());
                $('#h_numero').val($('#numero').val());
                $('#h_compl').val($('#complemento').val());
                $('#h_bairro').val($('#bairro').val());
                $('#h_cidade').val($('#cidade').val());
                $('#h_uf').val($('#uf').val());
                $('#h_cep').val($('#cep').val());  
                //$('#h_valor_cobrado').val(parseFloat($('#valor_total').html).toFixed(2))
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
        $('#wizard').smartWizard('showMessage',$msg);
    }

    function print_register(){
        w=window.open();
        w.document.write($('#registro').html());
        w.print();
        w.close();
    }

    $(function(e) {
        $('input[type="text"]').setMask();
    });   

    $.mask.masks.cpf = {mask: 'cpf'} ;
    $.mask.masks.cep = {mask: 'cep'} ;
    $.mask.masks.cel = {mask: 'phone'} ;


    //$.mask.masks.n_cup = {mask: '****-****-****-****'} ;
    //$.mask.masks.in_promocode = {mask: '99999'} ;
    $('#in_promocode').setMask('a9a9a').val('') ;
    $('#numero_cupom').setMask('*****-*****-*****-*****').val('');
    $('#fone1').setMask('(99) 999999999').val('');


    // $('#fone1').setMask({mask: 'phone'});
            /*
        ,
        onInvalid:function(c,nKey){

        },
        onValid: function(c,nKey){

        },
        onOverflow: function(c,nKey){
            $(this).setMask('(99)99999-9999');
        }
    });
    */

    $('#fone2').setMask('(99) 99999999').val('') ;

    if($('#h_tipo_treino').val()=='MTA') { $('#div_promocode').show();} else {$('#promocode').hide();}

    //VERIFICAÇÃO DO CPF
    $("#bt_checar_cpf").click(function() {
        var url = "http://bodysystems.net/_ferramentas/calendario/services/getEvoInfo.php"; // the script where you handle the form input
        $.ajax({
            type: "POST",
            url: url,
            data:{cpf:$('#cpf').val(),programa:jQuery('#h_sigla').val(),treino:jQuery('#h_tipo_treino').val()}, // serializes the form's elements.
            dataType: "json" ,
            beforeSend: function(load) {
                $('#avatar').attr('src','http://bodysystems.net/_ferramentas/calendario/images/user.png');
                $('#div_checa_voce').hide() ;
                $('#dv_avatar').hide() ;
                $('#div_create_user').hide();
                $('#frame_cadastro').fadeIn(100) ;
                $('#div_cadastro').html("<div style='margin-top:30px;margin-left:45%;'><img src='http://bodysystems.net/_ferramentas/calendario/images/cloud.gif' /></div>");
            } ,
            success: function(data) {
                resetUser();   
                //TEM CADASTRO NO SITE
                if(data[0]==1){
                    $('#avatar').attr('src',data[4]);
                    var $html = '<div style="margin-left:30px;"><p>Olá! Já te identificamos e agora você pode prosseguir com sua inscrição. Clique em \'Proximo\'...<br/><h3><strong>'+data[2]+'</strong><br/>'+data[3]+'</h3></div><div class="note" style="margin-left:30px;"><strong>Se estes dados não são seus </strong><a href="#">clique aqui.</a></div>' ;
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
                    $('#frame_cadastro').hide();
                    $('#div_checa_voce').hide() ;
                    $('#dv_avatar').hide() ;
                    $('#div_create_user').show();
                    $('#h_action').val(1);
                    $('#h_cpf').val($('#cpf').val());
                    $('#avatar').attr('src',data[2]);

                    //CPF INVALIDO
                } else if(data[0]==2) {
                    $('#avatar').attr('src',data[2]);
                    $('#div_create_user').hide();
                    $('#frame_cadastro').show();
                    $('#div_cadastro').html(data[1]);
                    $('#h_cpf').val('');

                    //CPF EXISTE NO EVO MAS NÃO TEM CADASTRO NO SITE
                } else if(data[0]==3) {
                    $('#avatar').attr('src',data[4]);
                    var $html = '<div class="alert" style="margin-left:30px;">Se você não é <strong>'+data[2]+' </strong><a href="">clique aqui.</a></div>' ;
                    $('#div_checa_voce').html($html) ;
                    $('#frame_cadastro').hide();
                    $('#div_checa_voce').show() ;
                    $('#dv_avatar').show() ;
                    $('#div_create_user').show();
                    $('#nome').val(data[2]);
                    $('#nome').attr('disabled','disabled');
                    $('#email').val(data[3]);
                    $('#h_action').val(1);
                    $('#h_cpf').val($('#cpf').val());
                    $('#avatar').attr('src',data[4]);

                    //MTA NÃO AUTORIZADO
                } else if(data[0]==4) {
                    $url = "<a href='http://bodysystems.net/index.php?option=com_jumi&fileid=75&t=1&p="+$('#h_sigla').val()+"'><img src='"+data[2]+"' /></a>" ;
                    $('#frame_cadastro').html($url);
                    $('#h_cpf').val(4) ; //ABORTA A NAVEGACAO
                    $('#h_action').val(4);

                }
                //$('#div_cadastro').html(data);
                //$('#parcelas_boleto').hide();    

            } ,
            error: function (request, status, error) 
            {
                resetUser();
                $('#div_cadastro').html('<div style="color: red">Não foi possivel executar o login.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                console.debug(error) ;
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });

    //VERIFICACAO DO CODIGO DO PROMOTOR
    $("#bt_aplicar_promo").click(function() {

        var url = "http://bodysystems.net/_ferramentas/calendario/services/getCodigoPromotor.php"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#frm_promocode").serialize(), // serializes the form's elements.
            dataType: "json" ,
            beforeSend: function(load) {
                $('#img_load').show();
            } ,
            success: function(data)
            {
                $('#img_load').hide();
                if(data[0]=='np') {
                    $('#promocode').val('CODIGO INVÁLIDO') ;   
                    $('#h_promocode').val('np');
                } else {
                    $('#cupom').remove();  
                    $('#div_promocode').html('<span style="color:ForestGreen;font-weight:bold;">CODIGO VÁLIDO! O BÔNUS FOI APLICADO COM SUCESSO.</span>');
                    $('#valor_desconto').html(data[1]);
                    $('#codigo_do_promotor').html(data[0])
                    $('#h_promocode').val(data[0]);
                    var $total = parseFloat($('#valor_total').html());
                    var $treino = parseFloat($('#valor_treino').html());
                    var $desconto = parseFloat(data[1]);
                    $total = $total - $desconto ;
                    $treino = $treino - $desconto ;
                    $('#valor_total').html($total.toFixed(2));
                    $('#h_valor_cobrado').val($total.toFixed(2));
                    $('#valor_treino').html($treino.toFixed(2));

                    //APLICA DESCONTOS AOS VALORES DE REFERENCIA
                    $V1 = parseFloat($('#h_valor1').val())- $desconto ;
                    $V2 = parseFloat($('#h_valor2').val())- $desconto ;
                    $('#h_valor1').val($V1.toFixed(2)) ;
                    $('#h_valor2').val($V2.toFixed(2)) ;

                }
                //$('#parcelas_boleto').hide();  
                return false;  

            } ,
            error: function (request, status, error) 
            {
                $('#img_load').hide();
                $('#div_promocode').html('<div style="color: red"><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                $('#h_promocode').val('np') ;
            }
        });

        return false; // avoid to execute the actual submit of the form.
    }); 

    //VERIFICAÇÃO DO CEP
    $("#bt_checar_cep").click(function() {

        var url = "http://bodysystems.net/_ferramentas/calendario/services/getCorreios.php"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#frm_checar_cep").serialize(), // serializes the form's elements.
            dataType: "json" ,
            beforeSend: function(load) {
                $('#erro_correios').hide()
                $('#div_result_correios').hide();
                $('#cog').fadeIn(100);
            } ,
            success: function(data)
            {          

                if(data[4]==0 || data[3]=='' || data[2]=='') {
                    $('#cog').hide();
                    jQuery('#erro_correios').show()
                }   else {
                    if(data[0]===false) {
                        alert('INFORME UM CEP VÁLIDO ANTES DE PROSSEGUIR')  ;	
                        $('#cog').hide();
                    } else {
                        $('#cog').hide();
                        $('#div_result_correios').show()  ;
                        //$('#div_result_correios').html(data);
                        $('#logradouro').val(data[0]);
                        $('#bairro').val(data[1]);
                        $('#cidade').val(data[2]);
                        $('#uf').val(data[3]);
                        //FRETE SOMENTE PARA TREINAMENTOS INICIAIS E WORKSHOP
                        if($('#h_tipo_treino').val()!='M1' && $('#h_tipo_treino').val()!='WS'){
                            data[4] = 0;
                        }	
                        $valores = ValorTransacao(data[4],true) ;
                        $('#valor_frete').html($valores[3]);
                        $('#h_valor_frete').val($valores[3]);		
                        $('#valor_treino').html($valores[0]);
                        $('#valor_certificacao').html($valores[1]);
                        $('#valor_total').html($valores[2]) ;        
                        $('#h_valor_cobrado').val($valores[2]);
                        $('#h_bonus_frete').val($valores[4]);
                        $('#numero').focus();
                    }
                } 
            },
            error: function (request, status, error) 
            {
                $('#cog').hide();

                $('#div_result_correios').html('<div style="color: red">Não foi possivel executar a busca.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                $('#div_result_correios').show();
                console.debug(error) ;
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });

    //IMPRIME O COMPROVANTE DE REGISTRO
    $('#img_print').click(function(){
        print_register(); 
    });

    //CONTROLA ALTERACAO DO CEP SEM FAZER CHAMADA AO CORREIO
    $('#cep').keyup(function(d){
        //LIMPA JÁ E SOMENTE NA PRIMEIRA LETRA
        $('#logradouro').val('');
        $('#bairro').val('');
        $('#cidade').val('');
        $('#uf').val('');
        $('#valor_frete').html('0');
        $('#valor_treino').html('0');
        $('#valor_certificacao').html('0');
        $('#valor_total').html('0') ; 
        $('#h_valor_cobrado').val(0);
    });

    //CONTROLA ALTERACOES DO CPF SEM FAZER A CHAMADA DA ROTINA
    $('#cpf').keyup(function(f){
        //LIMPA JÁ E SOMENTE NA PRIMEIRA LETRA
        $('#h_cpf').val('') ;
    });


    //ESCOLHA DA FORMA DE PAGTO
    $('div[gap*=forma_pagto]').click(function(e){
        $('#h_formaPagto').val($(this).attr('id'));
        var $the_image = "http://bodysystems.net/_ferramentas/calendario/images/"+$(this).attr('id')+"_detalhes.png" ;
        if($(this).attr('id') =='cupom'){
            $('#div_forma_pagto').hide() ;
            $('#div_promocode').hide();
            $('#div_form_cupom').show('fast');
        } else {
            $('#div_forma_pagto').css("background-image","url("+$the_image+")") ;
            $('#div_forma_pagto').show('fast') ;
            $('#div_promocode').show('fast');
            $('#div_form_cupom').hide();
            if($(this).attr('id') == 'boleto'){
                $frete = $('#valor_frete').html();
                $valores = ValorTransacao($frete,true) ;
                $('#valor_frete').html($frete);
                $('#valor_treino').html($valores[0]);
                $('#valor_certificacao').html($valores[1]);
                $('#valor_total').html($valores[2]) ;
                $('#h_valor_cobrado').val($valores[2]) ;


            } else {
                $frete = $('#valor_frete').html();
                $valores = ValorTransacao($frete,false) ;
                $('#valor_frete').html($frete);
                $('#valor_treino').html($valores[0]);
                $('#valor_certificacao').html($valores[1]);
                $('#valor_total').html($valores[2]) ; 
                $('#h_valor_cobrado').val($valores[2]) ;
            }
        }

    });

    //VALIDA CUPOM E DESCONTO
    $('#validar_cupom').click(function(c){
        var url = "http://bodysystems.net/_ferramentas/calendario/services/validar_cupom.php"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#frm_cupom").serialize(), // serializes the form's elements.
            dataType: "json" ,
            beforeSend: function(load) {
                $('#response_cupom').html("<div style='margin-top:30px;width:200px;' align='center'><img src='http://bodysystems.net/_ferramentas/calendario/images/cloud.gif' /></div>");
            } ,
            success: function(data)
            {

                $('#response_cupom').html(data[1]);
                if(data[0]==1) {
                    $('#h_cupom').val(data[2]);
                }
                //$('#parcelas_boleto').hide();  

            } ,
            error: function (request, status, error) 
            {
                $('#response_cupom').html('<div style="color: red">Não foi possivel executar o login.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                console.debug(error) ;
            }
        });

        return false; // avoid to execute the actual submit of the form.

    });

    //FINALIZA O PROCESSO E ENTREGA O BOTÃO DE PAGAMENTO
    function finishHIM(){

        if($('#h_cpf').val()=='') {
            alert('Houve um problema inesperado criando seu perfil! Por favor feche esta página e tente novamente.');
            $.unblockUI(); 
            location.reload();
            return;
        } else {
            var url = "http://bodysystems.net/_ferramentas/calendario/services/inscricao_treino.php"; // the script where you handle the form input.
            $.ajax({
                type: "POST",
                url: url,
                data: $("#frm_resume").serialize(), // serializes the form's elements.
                dataType: "html" ,
                beforeSend: function(load) {


                    //$('#img_load').show();
                } ,
                success: function(data)
                {
                    if($('#h_formaPagto').val()=='cupom'){ 
                        $('#tudo_certo').hide();
                        $('#hpagto').html('Parabéns! Você já está inscrito(a).');
                    }

                    $('#pagamento').append(data);
                    $('#h_finalizado').val('1');
                    //event.preventDefault();
                    $('#wizard').fadeOut('fast');
                    $('#efetua_pagto').fadeIn('fast');
                    $('#final_step').fadeIn('fast');
                    $.unblockUI(); 
                } ,
                error: function (request, status, error) 
                {
                    $('#pagamento').html('<div style="color: red"><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                    $('#h_finalizado').val('0');
                    $('#wizard').unblock();
                }
            });
            //envia email de confirmação
            var url_treino = "http://bodysystems.net/_ferramentas/calendario/services/email_confirmacao.php"; // the script where you handle the form input.
            var url_if = "http://bodysystems.net/_ferramentas/calendario/services/email_confirmacao_if.php";
            var $evto = $('#h_evento_descricao').val();
            var $nome = $('#h_nome').val();
            var $email = $('#h_email').val();
            var $fone = $('#h_celular').val();
            var url ="";
            if($('#h_sigla').val()=='IF') {
                url = url_if ;
            } else {
                url = url_treino;
            }

            $.ajax({
                type: "POST",
                url: url,
                data: {evento:$evto,nome:$nome,email:$email,fone:$fone}, 
                dataType: "json" ,
                beforeSend: function(load) {


                } ,
                success: function(data)
                {

                } ,
                error: function (request, status, error) 
                {

                }
            });
        }

    }

    //VERIFICAÇÃO DA EXISTENCIA DE NOME DE USUARIO E EMAIL PARA NOVOS USERS
    function checkNewUser() {
        var url = "http://bodysystems.net/_ferramentas/calendario/services/validar_user_email.php";
        var $username= $('#user').val();
        var $email = $('#email').val();
        $.ajax({
            type: "POST",
            async: false,
            url: url,
            data: { username:$username,email:$email },
            dataType: "json" ,
            beforeSend: function(load) {
                $('#user_loader').show();
                jQuery('#email_loader').show();
            } ,
            success: function(data)
            {

                $('#user_loader').hide();
                $('#email_loader').hide();
                $('#user').css('border-left', 0) ;
                $('#email').css('border-left', 0) ;
                $ret = true
                if(data[2]==false){

                    $('#user').css('border-left','solid 2px red') ;
                    alert('O nome de usuário que você está tentando cadastrar já consta em nosso site, relacionado a um CPF diferente do que você informou. Se você já se cadastrou anteriormente no site seu CPF pode estar em branco em seu perfil. \n\nSão suas opções: alterar o nome de usuário neste cadastramento ou se você já se cadastrou anteriormente em nosso site, edite o número de CPF em seu Perfil no site.') ;
                    $ret = false ;
                }

                if(data[3]==false){

                    $('#email').css('border-left','solid 2px red') ;
                    alert('O email que você está tentando cadastrar já consta em nosso site, relacionado a um CPF diferente ao que você informou. Se você já se cadastrou anteriormente no site seu CPF pode estar em branco em seu perfil. \n\nSão suas opções: alterar o email neste cadastramento ou se você já se cadastrou anteriormente em nosso site, edite o número de CPF em seu Perfil no site.') ;
                    $ret = false ;
                } 
            } ,
            error: function (request, status, error) 
            {
                $('#user_loader').hide();
                $('#email_loader').hide();
                console.debug(error);
                $dev = false;
                return $dev;
            }
        });
        return $ret ;
    }


}); 

//MOSTRA SOMENTE DEPOIS QUE TUDO CARREGOU
$(window).load(function() { 

    $('#main_cog').hide();
    $('#wizard').fadeIn('fast');
    //ABRE COM FOCO NO CPF
    $('#cpf').focus();
});