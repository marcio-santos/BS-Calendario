<?php
  
  /** Error reporting */
    error_reporting(E_ALL);

    /** Include path **/
    ini_set('include_path', ini_get('include_path').';../Classes/');

    include ('exec_in_joomla.inc') ;
    include ('rodada.inc') ;
    /** PHPExcel */
    require 'PHPExcel.php';

    /** PHPExcel_Writer_Excel2007 */
    require 'PHPExcel/Writer/Excel2007.php';

     //====CONFIGURA O WS ======
     if(isset($_GET['ws'])) {
        $rodada_ws = $_GET['ws'] ;
    } else {
      
        $rodada_ws=4;
    }
    
    if(isset($_GET['yr'])) {
        $gAno = $_GET['yr'] ;
    } else {
      
        $gAno=date('y',time()) ;
    }    //=========================
    //=========================

    $WS = getEventos($rodada_ws,$gAno) ;
    $W_datas = getDatasRodada($rodada_ws,$gAno) ;




    //=======================================================================================
    //NOMEIA EVENTOS PELO EVO
    //========================================================================================


    $client = new 
    SoapClient( 
    "http://177.154.134.90:8084/WCF/_BS/wcfBS.svc?wsdl" 
    ); 
    $params = array('IdClienteW12'=>229, 'IdTipoTreinamento'=>7, 'Inicio'=>$W_datas[0] , 'Fim'=>$W_datas[1], 'Estado'=>'', 'Programa'=>''); 
    $webService = $client->ListarTreinamentosWebsite($params); 
    $wsResult = $webService->ListarTreinamentosWebsiteResult->VOBS; 
    
    $Stack = array();
    $Stack_CO = array();
    $Stack_EV = array();
    $Stack_LD = array() ;
    
    $aTotal = array();

    foreach($wsResult as $obj) {

        if(strlen($obj->CIDADE)!=0) {

            $Stack[$obj->ID_TREINAMENTO] = array() ; 
            $Stack_CO[$obj->ID_TREINAMENTO] = array() ; 
            $Stack_EV[$obj->ID_TREINAMENTO] = array() ; 
            $Stack_LD[$obj->ID_TREINAMENTO] = array() ;
             
            $Cidade[$obj->ID_TREINAMENTO] = $obj->CIDADE ;
            $aTotal[$obj->ID_TREINAMENTO] =0 ;

        } else {
            echo $obj->ID_TREINAMENTO ." = ".$obj->ESTADO ;
            die() ;
            $Stack[$obj->ID_TREINAMENTO] = $obj->ESTADO ; 
            $aTotal[$obj->ID_TREINAMENTO] =0 ;
        }
    }
     
    $db = &JFactory::getDBO(); 
    $query_bs = "SELECT cnab FROM `boletos_bs` WHERE SUBSTR(cnab,2,5) IN ($WS) ORDER BY SUBSTR(cnab,2,5) ASC" ;
    $query_ps ="SELECT ProdID FROM `PagSeguroTransacoes` WHERE SUBSTR(ProdID,2,5) IN ($WS) ORDER BY SUBSTR(ProdID,2,5) ASC" ;
    
    
    
    $db->setQuery($query_bs) ;
    $result_bs = $db->loadRowList();
    
    $db->setQuery($query_ps) ;
    $result_ps = $db->loadRowList();
    
    //BOLETO
    
    foreach($result_bs as $row) {
        $row_ex = explode("|",$row[0]) ;
        $eventoid = substr($row_ex[0],1,5);
        $line = $row_ex[2] ;
        $despacho = $row_ex[3] ;
        $prog_array = explode(";",$line) ;
        foreach($prog_array as $prog) {
            switch ($despacho) {
                Case 'CO' :
                    array_push($Stack_CO[$eventoid], $prog);
                    break;
                Case 'EV' :
                    array_push($Stack_EV[$eventoid], $prog);
                    break;
                Case 'LD' :
                    array_push($Stack_LD[$eventoid], $prog);
                    break;
            }
            array_push($Stack[$eventoid], $prog);
            } 
            
    }
  
    
        
    //PAGSEGURO
     foreach($result_ps as $row) {
        $row_ex = explode("|",$row[0]) ;
        $eventoid = substr($row_ex[0],1,5);
        $line = $row_ex[2] ;
        $despacho = $row_ex[3] ;
     
        $prog_array = explode(";",$line) ;
        foreach($prog_array as $prog) {
            switch ($despacho) {
                Case 'CO' :
                    array_push($Stack_CO[$eventoid], $prog);
                    break;
                Case 'EV' :
                    array_push($Stack_EV[$eventoid], $prog);
                    break;
                Case 'LD' :
                    array_push($Stack_LD[$eventoid], $prog);
                    break;
            }
            array_push($Stack[$eventoid], $prog);
             }
    
    }
    
    //CABEÇALHO DA PLANILHA
    $i=1 ;
    $colA = 'A'.$i ;
    $colB = 'B'.$i ;
    $colC = 'C'.$i ;
    $colD = 'D'.$i ;
    $colE = 'E'.$i ;
    $colF = 'F'.$i ;
    $colG = 'G'.$i ;
    $colH = 'H'.$i ;
    $colI = 'I'.$i ;
    $colJ = 'J'.$i ;
    $colK = 'K'.$i ;
    $colL = 'L'.$i ;
   
   /*
    echo "<pre>" ;
    
    echo 'TOTAL<BR/>';
    foreach($Stack as $key=>$value){
        $Cidade_Evento = $Cidade[$key];
        $Array_Evento[$Cidade_Evento] = array();
        $Array_Evento[$Cidade_Evento] = array_count_values($value);
    }
    echo "<hr/>" ;
    */
    
    //echo "CORREIO<BR/>";
    foreach($Stack_CO as $key=>$value){
        $Cidade_Evento = $Cidade[$key];
        $Array_Evento[$Cidade_Evento] = array();
        //$Array_Evento[$Cidade_Evento] = array('Correio') ;
        //$Array_Evento[$Cidade_Evento]['Correio'] = array();
        $Array_Evento[$Cidade_Evento]['Correio'] = array_count_values($value);
    }
    //echo "<hr/>" ;
    //echo "EVENTO<BR/>";
    foreach($Stack_EV as $key=>$value){
        $Cidade_Evento = $Cidade[$key];
        //$Array_Evento[$Cidade_Evento] = array('Evento') ;
        //$Array_Evento[$Cidade_Evento]['Evento'] = array();
        $Array_Evento[$Cidade_Evento]['Evento'] = array_count_values($value);
    }
    //echo "<hr/>" ;
    //echo "LIDER<BR/>";
    foreach($Stack_LD as $key=>$value){
        $Cidade_Evento = $Cidade[$key];
        //$Array_Evento[$Cidade_Evento] = array('Lider') ;
        //$Array_Evento[$Cidade_Evento]['Lider'] = array();
        $Array_Evento[$Cidade_Evento]['Lider'] = array_count_values($value);
    }
    
    
  /*  
    
    print_r($Array_Evento);
    //echo "Array_Evento['Fortaleza']['Correio']['BP']<br/>" ;  
    //echo "<h1>".$Array_Evento['Fortaleza']['Correio']['BP']."</h1>" ;
    foreach($Array_Evento as $key=>$value) {
        echo "Cidade:".$key."<br/>";
        foreach($value as $envio=>$mvalue) {
            echo "Envio: ".$envio."<br/>";
            foreach($mvalue as $prog=>$pvalue) {
                echo "<i>".$prog."</i>: ".$pvalue."<br/>" ;
            }
        }
    }
    
    echo "<hr/>" ;
    echo "</pre>" ;
    die();
  */
    
    $Total = count($result_bs)+count($result_ps) ;
    
    $objPHPExcel = new PHPExcel();
    $validLocale = PHPExcel_Settings::setLocale('pt-br');

    // Set properties
    //echo date('H:i:s') . " Set properties<br/>";
    $objPHPExcel->getProperties()->setCreator("Marcio Santos");
    $objPHPExcel->getProperties()->setLastModifiedBy("Sistema OnLine");
    $objPHPExcel->getProperties()->setTitle("Planilha de Análise");
    $objPHPExcel->getProperties()->setSubject("Análise de Workshop");
    $objPHPExcel->getProperties()->setDescription("Documento gerado automaticamente pelo Site BodySystems.");

  
    //TITULO
    $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20) ;
    $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()->setARGB('494529');
  
    //SUBTITULO
    
    $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
    $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()->setARGB('948A54');
    
    $i=1; $colB = 'B'.$i ;
    $objPHPExcel->getActiveSheet()->getStyle($colB)->getFont()->setSize(18) ;
    $objPHPExcel->getActiveSheet()->SetCellValue($colB, 'INVENTÁRIO LOGÍSTICO');
    $i=2; $colB = 'B'.$i ;
    $objPHPExcel->getActiveSheet()->SetCellValue($colB, $rodada_ws."º WorkShop de 20".$gAno);
    
//+====================================================================================================================
  // AREA DE REPETIÇÃO
  //+====================================================================================================================  
    //DADOS
    foreach($Array_Evento as $vWS=>$value) {
    $colB = 'B'.$i=$i+1;
    $cel_fmt = 'A'.$i.":".'L'.$i ;
    
    //CIDADE
    $objPHPExcel->getActiveSheet()->getStyle($cel_fmt)->getFont()->setSize(14) ;
    $objPHPExcel->getActiveSheet()->getStyle($cel_fmt)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
    $objPHPExcel->getActiveSheet()->getStyle($cel_fmt)->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()->setARGB('000000');
    
    
    $objPHPExcel->getActiveSheet()->SetCellValue($colB, $vWS);
    
    $i++ ;
    $colBA = 'B'.$i ;
    $colBB = 'C'.$i ;
    $colBC = 'D'.$i ;
    $colBJ = 'E'.$i ;
    $colBP = 'F'.$i ;
    $colBS = 'G'.$i ;
    $colBV = 'H'.$i ;
    $colCX = 'I'.$i ;
    $colPJ = 'J'.$i ;
    $colRPM = 'K'.$i ;
    $colSB = 'L'.$i ;
    $colFMT = $colBA.":".$colSB;
    $objPHPExcel->getActiveSheet()->getStyle($colFMT)->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()->setARGB('CFB53B');
    
    //linha de títulos de programas
    $objPHPExcel->getActiveSheet()->SetCellValue($colBA, 'BA');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBB, 'BB');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBC, 'BC');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBJ, 'BJ');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBP, 'BP');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBS, 'BS');
    $objPHPExcel->getActiveSheet()->SetCellValue($colBV, 'BV');
    $objPHPExcel->getActiveSheet()->SetCellValue($colCX, 'CX');
    $objPHPExcel->getActiveSheet()->SetCellValue($colPJ, 'PJ');
    $objPHPExcel->getActiveSheet()->SetCellValue($colRPM, 'RPM');
    $objPHPExcel->getActiveSheet()->SetCellValue($colSB, 'SB');
    
    //coluna dos despachos
    $i++; $colA = 'A'.$i ; $linha_evento = $i ;
    $objPHPExcel->getActiveSheet()->SetCellValue($colA, 'EVENTO');
    $i++; $colA = 'A'.$i ; $linha_lider = $i ;
    $objPHPExcel->getActiveSheet()->SetCellValue($colA, 'LÍDER');
    $i++; $colA = 'A'.$i ; $linha_correio = $i ;
    $objPHPExcel->getActiveSheet()->SetCellValue($colA, 'CORREIO');
    
    
    /*
    foreach($Stack_EV AS $key=>$value) {
        
        $mrow = array_count_values($value);
   
        $i++;
        $colB = 'B'.$i ;
        $colC = 'C'.$i ;
        $colD = 'D'.$i ;
        $colE = 'E'.$i ;
        $colF = 'F'.$i ;
        $colG = 'G'.$i ;
        $colH = 'H'.$i ;
        $colI = 'I'.$i ;
        $colJ = 'J'.$i ;
        $colK = 'K'.$i ;
        $colL = 'L'.$i ;

        $objPHPExcel->getActiveSheet()->SetCellValue($colA, $Cidade[$key]);
        if (array_key_exists('BA',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colB, $mrow['BA']); }
        if (array_key_exists('BB',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colC, $mrow['BB']);}
        if (array_key_exists('BC',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colD, $mrow['BC']);}
        if (array_key_exists('BJ',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colE, $mrow['BJ']);}
        if (array_key_exists('BP',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colF, $mrow['BP']);}
        if (array_key_exists('BS',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colG, $mrow['BS']);}
        if (array_key_exists('BV',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colH, $mrow['BV']);}
        if (array_key_exists('CX',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colI, $mrow['CX']);}
        if (array_key_exists('PJ',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colJ, $mrow['PJ']);}
        if (array_key_exists('RPM',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colK, $mrow['RPM']);}
        if (array_key_exists('SB',$mrow)) { $objPHPExcel->getActiveSheet()->SetCellValue($colL, $mrow['SB']);}
    
    
    }
    */
    
        
        
        
        foreach($value AS $despacho){
            /*
            echo "<pre>" ;
            print_r($value['Lider']) ;
            echo "<br/>" ;
            print_r($despacho['Evento']) ;
            echo "</pre>" ;
            die();
            */
           
            $colBA = 'B'.$linha_correio ;
            $colBB = 'C'.$linha_correio ;
            $colBC = 'D'.$linha_correio ;
            $colBJ = 'E'.$linha_correio ;
            $colBP = 'F'.$linha_correio ;
            $colBS = 'G'.$linha_correio ;
            $colBV = 'H'.$linha_correio ;
            $colCX = 'I'.$linha_correio ;
            $colPJ = 'J'.$linha_correio ;
            $colRPM = 'K'.$linha_correio ;
            $colSB = 'L'.$linha_correio ;
            
            
            if (array_key_exists('BA',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBA, $value['Correio']['BA']); }
            if (array_key_exists('BB',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBB, $value['Correio']['BB']); }
            if (array_key_exists('BC',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBC, $value['Correio']['BC']); }
            if (array_key_exists('BJ',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBJ, $value['Correio']['BJ']); }
            if (array_key_exists('BP',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBP, $value['Correio']['BP']); }
            if (array_key_exists('BS',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBS, $value['Correio']['BS']); }
            if (array_key_exists('BV',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBV, $value['Correio']['BV']); }
            if (array_key_exists('CX',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colCX, $value['Correio']['CX']); }
            if (array_key_exists('PJ',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colPJ, $value['Correio']['PJ']); }
            if (array_key_exists('RPM',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colRPM, $value['Correio']['RPM']);}
            if (array_key_exists('SB',$value['Correio'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colSB, $value['Correio']['SB']); }
            
            $colBA = 'B'.$linha_evento ;
            $colBB = 'C'.$linha_evento ;
            $colBC = 'D'.$linha_evento ;
            $colBJ = 'E'.$linha_evento ;
            $colBP = 'F'.$linha_evento ;
            $colBS = 'G'.$linha_evento ;
            $colBV = 'H'.$linha_evento ;
            $colCX = 'I'.$linha_evento ;
            $colPJ = 'J'.$linha_evento ;
            $colRPM = 'K'.$linha_evento ;
            $colSB = 'L'.$linha_evento ;
            
            
            if (array_key_exists('BA',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBA, $value['Evento']['BA']); }
            if (array_key_exists('BB',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBB, $value['Evento']['BB']); }
            if (array_key_exists('BC',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBC, $value['Evento']['BC']); }
            if (array_key_exists('BJ',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBJ, $value['Evento']['BJ']); }
            if (array_key_exists('BP',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBP, $value['Evento']['BP']); }
            if (array_key_exists('BS',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBS, $value['Evento']['BS']); }
            if (array_key_exists('BV',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBV, $value['Evento']['BV']); }
            if (array_key_exists('CX',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colCX, $value['Evento']['CX']); }
            if (array_key_exists('PJ',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colPJ, $value['Evento']['PJ']); }
            if (array_key_exists('RPM',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colRPM, $value['Evento']['RPM']);}
            if (array_key_exists('SB',$value['Evento'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colSB, $value['Evento']['SB']); } 
            
            
            $colBA = 'B'.$linha_lider ;
            $colBB = 'C'.$linha_lider ;
            $colBC = 'D'.$linha_lider ;
            $colBJ = 'E'.$linha_lider ;
            $colBP = 'F'.$linha_lider ;
            $colBS = 'G'.$linha_lider ;
            $colBV = 'H'.$linha_lider ;
            $colCX = 'I'.$linha_lider ;
            $colPJ = 'J'.$linha_lider ;
            $colRPM = 'K'.$linha_lider ;
            $colSB = 'L'.$linha_lider ;
            
            
            if (array_key_exists('BA',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBA, $value['Lider']['BA']); }
            if (array_key_exists('BB',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBB, $value['Lider']['BB']); }
            if (array_key_exists('BC',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBC, $value['Lider']['BC']); }
            if (array_key_exists('BJ',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBJ, $value['Lider']['BJ']); }
            if (array_key_exists('BP',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBP, $value['Lider']['BP']); }
            if (array_key_exists('BS',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBS, $value['Lider']['BS']); }
            if (array_key_exists('BV',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colBV, $value['Lider']['BV']); }
            if (array_key_exists('CX',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colCX, $value['Lider']['CX']); }
            if (array_key_exists('PJ',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colPJ, $value['Lider']['PJ']); }
            if (array_key_exists('RPM',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colRPM, $value['Lider']['RPM']);}
            if (array_key_exists('SB',$value['Lider'])) { $objPHPExcel->getActiveSheet()->SetCellValue($colSB, $value['Lider']['SB']); }
               
        }
        
        
    }
    
    /*
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
    */
    
    // Rename sheet
    //echo date('H:i:s') . " Rename sheet<br/>\t";
    $objPHPExcel->getActiveSheet()->setTitle('Logística WS');


    // Save Excel 2007 file
    //echo date('H:i:s') . " Write to Excel2007 format<br/>";
/*

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);


// Redirect output to a clientâ€™s web browser (Excel2007)
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="expedicao.xlsx"');

header('Cache-Control: max-age=0');


//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


    
    
    //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

    // Echo done
    //echo date('H:i:s') . " Done writing file.\r<br/>";
    //echo "<hr/>" ;
    //echo "<input type='submit' value='Download da Planilha de Expedi&ccedil;&atilde;o' onClick=window.location.href='download_file.php?fp=xls_expedicao.xlsx' /><br/>" ;
*/
 // Rename sheet
    //echo date('H:i:s') . " Rename sheet<br/>\t";
    $objPHPExcel->getActiveSheet()->setTitle('Logística WS');


    // Save Excel 2007 file
    //echo date('H:i:s') . " Write to Excel2007 format<br/>";


    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    $objWriter->save('expedicao.xlsx') ;
    // Echo done
    //echo date('H:i:s') . " Done writing file.\r<br/>";
    //echo "<hr/>" ;
    
    //DOWNLOAD MAIS FÁCIL
   header('Location: http://principal.bodysystems.net/site/expedicao.xlsx')
   
    //echo "<input type='submit' value='Download de Expedição' onClick=window.location.href='download_file.php?fp=xls_expedicao.xlsx' /><br/>" ;
    
    
?>
