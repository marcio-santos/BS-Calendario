jQuery(document).ready(function() {

jQuery.noConflict() ;

//ENVIA O EMAIL PARA IG
    $("#submit").click(function(event) {
        event.preventDefault();
        var url = "_ferramentas/calendario/services/email_ig.php"; // the script where you handle the form input
        $.ajax({
            type: "POST",
            url: url,
            data: $("#ig_contact").serialize(), // serializes the form's elements.
            dataType: "json" ,
            beforeSend: function(load) {
                $.blockUI({ message: "<div style='margin-top:30px;margin-left:auto;margin-right:auto;display:block;'><img src='_ferramentas/calendario/images/cloud.gif' /></div>" });
            } ,
            success: function(data) {
                if(data[0]==0) {
                    $.unblockUI(); 
                   $('#ig_contact')[0].reset();
                   alert('Envio efetuado com sucesso!');     
                } else if (data[0]==1){ 
                    $.unblockUI(); 
                   alert('Existem campos sem preenchimento!');
                } else if (data[0]==2){ 
                    $.unblockUI(); 
                   alert('Houve um problema enviando seu email.\nPor favor tente novamente mais tarde.');
                }
               

            } ,
            error: function (request, status, error) 
            {
                resetUser();
                $('#div_cadastro').html('<div style="color: red">Ocorreu um erro inesperado.<br/><pre>'+ request.responseText + '<br/>' + status+'<br/>'+error+'</pre></div>');
                console.debug(error) ;
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });
    

});