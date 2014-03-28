<?php

include ('../../../exec_in_joomla.inc') ;

$username = $_POST['username'] ;
$email = $_POST['email'] ;
  
$db = &JFactory::getDBO();

//VERIFICA O NOME DE USUARIO
$query = 'SELECT count(*) FROM wow_users WHERE username LIKE '.$db->Quote($username) ;
file_put_contents('query_logar.log',$query);
$db->setQuery($query) ;
$res_username = $db->loadResult() ;

if($res_username == 0) {
 
    $ret_username = true ;
    
} else {
    
    $ret_username =  false ;
}


//VERIFICA O EMAIL CADASTRADO
$query = 'SELECT count(*) FROM wow_users WHERE email LIKE '.$db->Quote($email) ;
$db->setQuery($query) ;
$res_email = $db->loadResult() ;

if($res_email == 0) {
 
    $ret_email = true ;
    
} else {
    
    $ret_email =  false ;
}

$check = array($username,$email,$ret_username,$ret_email) ;
echo json_encode($check) ;

  
?>
