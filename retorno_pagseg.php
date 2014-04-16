<?php

##############################################################
#                         CONFIGURAÇÕES
##############################################################

//$retorno_site = 'http://www.exemplo.com.br/compra_efedutada.html';  // Site para onde o usuário vai ser redirecionado
require_once('trailer.php') ;


function getW12id($UserID)
  {
    
    $db =& JFactory::getDBO();
    
      $query = "SELECT value FROM jos_community_fields_values WHERE field_id=23  AND value<>'' AND user_id=".$UserID;
      $db->setQuery($query);
      $w12id = $db->loadResult();
      
      $query = "SELECT value FROM jos_community_fields_values WHERE field_id=21  AND value<>'' AND user_id=".$UserID;
      $db->setQuery($query);
      $w12ProfileT = $db->loadResult();


      $vReturn = array($w12id,$w12ProfileT); 
       
       unset($db) ;
       return $vReturn;
        
 
      }


global $db ;
global $w12Id ;

$db =& JFactory::getDBO();

//informações do cliente operando
 //id logado
$user =& JFactory::getUser(); 
$vUser = $user->id ;
$InfoCli = getW12id($vUser);


$client = new 
        SoapClient( 
            "http://th52248.cloudth.com.br/W12_ERP/WCF/Clientes/wcfClientes.svc?wsdl" 
        ); 
    $params = array('IdClienteW12'=>229, 'IdFilial'=>1, 'CpfCnpj'=>$InfoCli[0], 'TipoCliente'=>$InfoCli[1]); 
    $webService = $client->ListarClienteCPFCNPJ($params); 
    $wsResult = $webService->ListarClienteCPFCNPJResult; 


// Recupera o IdCliente
 $w12Id = $wsResult->ID_CLIENTE;


//LandingPage para o Retorno
$session = JFactory::getSession();
$vBack2 = $session->get('vBack', NULL, 'bodysystems');

$retorno_token = '4CBE496B22D44822926DBFF356E57C7D'; // Token gerado pelo PagSeguro BodySystems
//$retorno_token = '25A90E310C004CADA4B81FD9689251E1'; // Token gerado pelo PagSeguro Aesir


function pgs_log($msg)
{
  $thefile =  'evo_id.log' ;
  file_put_contents ($thefile, $msg);
  
}


if ($_POST) {
  pgs_log($_POST['TransacaoID']);
}






//$retorno_host = 'localhost'; // Local da base de dados MySql
//$retorno_database = 'basededados'; // Nome da base de dados MySql
//$retorno_usuario = 'usuario'; // Usuario com acesso a base de dados MySql
//$retorno_senha = 'senha';  // Senha de acesso a base de dados MySql

try {
###############################################################
#              NÃO ALTERE DESTA LINHA PARA BAIXO
################################################################

//$lnk = mysql_connect($retorno_host, $retorno_usuario, $retorno_senha) or die ('Nao foi possível conectar ao MySql: ' . mysql_error());
//mysql_select_db($retorno_database, $lnk) or die ('Nao foi possível ao banco de dados selecionado no MySql: ' . mysql_error());    

// Validando dados no PagSeguro

$PagSeguro = 'Comando=validar';
$PagSeguro .= '&Token=' . $retorno_token; 
$Cabecalho = "Retorno PagSeguro";

foreach ($_POST as $key => $value)
{
 $value = urlencode(stripslashes($value));
 $PagSeguro .= "&$key=$value";
}

if (function_exists('curl_exec'))
{
 $curl = true;
}
elseif ( (PHP_VERSION >= 4.3) && ($fp = @fsockopen ('ssl://pagseguro.uol.com.br', 443, $errno, $errstr, 30)) )
{
 $fsocket = true;
}
elseif ($fp = @fsockopen('pagseguro.uol.com.br', 80, $errno, $errstr, 30))
{
 $fsocket = true;
}

if ($curl == true)
{
 $ch = curl_init();

 curl_setopt($ch, CURLOPT_URL, 'https://pagseguro.uol.com.br/Security/NPI/Default.aspx');
 curl_setopt($ch, CURLOPT_POST, true);
 curl_setopt($ch, CURLOPT_POSTFIELDS, $PagSeguro);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_HEADER, false);
 curl_setopt($ch, CURLOPT_TIMEOUT, 30);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  curl_setopt($ch, CURLOPT_URL, 'https://pagseguro.uol.com.br/Security/NPI/Default.aspx');
  $resp = curl_exec($ch);

 curl_close($ch);
 $confirma = (strcmp ($resp, "VERIFICADO") == 0);
}
elseif ($fsocket == true)
{
 $Cabecalho  = "POST /Security/NPI/Default.aspx HTTP/1.0\r\n";
 $Cabecalho .= "Content-Type: application/x-www-form-urlencoded\r\n";
 $Cabecalho .= "Content-Length: " . strlen($PagSeguro) . "\r\n\r\n";

 if ($fp || $errno>0)
 {
    fputs ($fp, $Cabecalho . $PagSeguro);
    $confirma = false;
    $resp = '';
    while (!feof($fp))
    {
       $res = @fgets ($fp, 1024);
       $resp .= $res;
       if (strcmp ($res, "VERIFICADO") == 0)
       {
          $confirma=true;
          break;
       }
    }
    fclose ($fp);
 }
 else
 {
    echo "$errstr ($errno)<br />\n";
 }
}

if ($confirma) {
   
 // Recebendo Dados
 $TransacaoID = $_POST['TransacaoID'];
 $VendedorEmail  = $_POST['VendedorEmail'];
 global $Referencia ;
 $Referencia = $_POST['Referencia'];
 $TipoFrete = $_POST['TipoFrete'];
 $ValorFrete = $_POST['ValorFrete'];
 $Extras = $_POST['Extras'];
 $Anotacao = $_POST['Anotacao'];
 $TipoPagamento = $_POST['TipoPagamento'];
 $StatusTransacao = $_POST['StatusTransacao'];
 $CliNome = $_POST['CliNome'];
 $CliEmail = $_POST['CliEmail'];
 $CliEndereco = $_POST['CliEndereco'];
 $CliNumero = $_POST['CliNumero'];
 $CliComplemento = $_POST['CliComplemento'];
 $CliBairro = $_POST['CliBairro'];
 $CliCidade = $_POST['CliCidade'];
 $CliEstado = $_POST['CliEstado'];
 $CliCEP = $_POST['CliCEP'];
 $CliTelefone = $_POST['CliTelefone'];
 $NumItens = $_POST['NumItens'];
 $ProdID = $_POST['ProdID_1'];
 $ProdDescricao = $_POST['ProdDescricao_1'];
 $ProdValor = $_POST['ProdValor_1'];
 $Parcelas = $_POST['Parcelas'];
 
 

$query = "REPLACE INTO PagSeguroTransacoes (id,IdCliente,TransacaoID,VendedorEmail,Referencia,TipoFrete,ValorFrete,Extras,Anotacao,TipoPagamento,StatusTransacao,CliNome,CliEmail,CliEndereco,CliNumero,CliComplemento,CliBairro,CliCidade,CliEstado,CliCEP,CliTelefone,NumItens,ProdID,ProdDescricao,ProdValor,Parcelas,Data) VALUES (NULL,'$Referencia','$TransacaoID','$VendedorEmail','$Referencia','$TipoFrete','$ValorFrete','$Extras','$Anotacao', '$TipoPagamento','$StatusTransacao','$CliNome','$CliEmail','$CliEndereco','$CliNumero','$CliComplemento','$CliBairro','$CliCidade','$CliEstado','$CliCEP','$CliTelefone','$NumItens',
'$ProdID','$ProdDescricao','$ProdValor','$Parcelas',now())" ;

    
$db->setQuery($query);
$db->query();
   
}

unset($db) ;
$db =& JFactory::getDBO();

sleep(3) ;
$thefile =  'evo_id.log' ;
$TrID = file_get_contents ($thefile);



//echo "<h1>Esta é a Transa $TrID</h1>" ;

//RECUPERA INFO DA TRANSACAO NO DB
$query = "SELECT * FROM PagSeguroTransacoes WHERE TransacaoID =".$db->Quote($TrID) ; //$vTrailer ;


$db->setQuery($query) ;
$Row = $db->loadObject();

foreach ($Row as $mRow=>$value) {
    //echo $mRow." -> ".$value."<br/>" ;
}


$fData = strftime("%Y%m%d %H%M%S", strtotime($Row->Data)) ;

//echo "<h1>Esta é a Data: ".$Row->Data."</h1>" ;
//echo "<h1>Esta é a data Certa: ".$fData."</h1>" ;

 $TransacaoID = $Row->TransacaoID;
 $IdCliente = $w12Id ; 
 $VendedorEmail  = $Row->VendedorEmail;
 $Referencia = $Row->Referencia; 
 $TipoFrete = $Row->TipoFrete;
 $ValorFrete = $Row->ValorFrete;
 $Extras = $Row->Extras;
 $Anotacao = $Row->Anotacao;
 $TipoPagamento = $Row->TipoPagamento;
 $StatusTransacao = $Row->StatusTransacao;
 $CliNome = $Row->CliNome;
 $CliEmail = $Row->CliEmail;
 $CliEndereco = $Row->CliEndereco;
 $CliNumero = $Row->CliNumero;
 $CliComplemento = $Row->CliComplemento;
 $CliBairro = $Row->CliBairro;
 $CliCidade = $Row->CliCidade;
 $CliEstado = $Row->CliEstado;
 $CliCEP = $Row->CliCEP;
 $CliTelefone = $Row->CliTelefone;
 $NumItens = $Row->NumItens;
 $ProdID = $Row->ProdID;
 $ProdDescricao = $Row->ProdDescricao;
 $ProdValor = $Row->ProdValor;
 $Parcelas = $Row->Parcelas;
 $trailer = $vTrailer ;
 $data = $Row->Data ;

 //CHAMA O SERVIÇO DO EVO
 
$params = array( 
        IdCliente=> $IdCliente,
        IdTransacao=> $TransacaoID,
        VendedorEmail=> $VendedorEmail,
        Referencia=> $Referencia,
        FreteTipo=> $TipoFrete,
        FreteValor=> $ValorFrete,
        Extras=> $Extras,
        Anotacao=> $Anotacao,
        TipoPagamento=> $TipoPagamento,
        StatusTransacao=> $StatusTransacao,
        ClienteNome=> $CliNome,
        ClienteEmail=> $CliEmail,
        ClienteEndereco=> $CliEndereco,
        ClienteNumero=> $CliNumero, 
        ClienteComplemento=> $CliComplemento,
        ClienteBairro=> $CliBairro,
        ClienteCidade=> $CliCidade, 
        ClienteEstado=> $CliEstado,
        ClienteCep=> $CliCEP,
        ClienteTelefone=> $CliTelefone,
        QuantidadeItens=> $NumItens,
        Data=> $fData ,
        Status=> $Status,
        ProdutoId=> $ProdID,
        ProdutoDescricao=> $ProdDescricao ,
        ProdutoValor=> $ProdValor,
        ProdutoQuantidade=> $NumItens,
        ProdutoFrete=> $ValorFrete ,
        Parcelas=> $Parcelas,
        Trailer => $trailer,
        );
 
// echo var_dump ($params) ;
 
if(!$confirma) {
//Chama o servico em si
$client = new SoapClient("http://th52248.cloudth.com.br/W12_ERP/WCF/PagSeguro/wcfPagSeguro.svc?wsdl" ); 
$webService = $client->LogarTransacao($params); 

}
//Volta para a LandingPage Correta.    
//Controla os eventos
//die() ;

switch($vBack2) {

    Case 'Compra Corporativa' :
        $myURI='http://principal.bodysystems.net/site/corpbuy/pagto_concluido.php' ;
        Break;
        
    Case 'Treinamento' :
        $myURI='http://principal.bodysystems.net/site/pagtoT_concluido.php' ;
        Break;
    Case 'Workshop' :
        $myURI='http://principal.bodysystems.net/site/pagtoT_concluido.php'; 
        Break;
        
    Case 'Gestor' :
        $myURI='http://principal.bodysystems.net/site/pagtoT_concluido.php' ;
        Break;        
}

Header( "Location: $myURI") ;


exit();
    
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}    
    
//Para trabalhar dentro do joomla
/*$mUser = $user->name.' é do tipo '.gettype($user->name).' com tamanho '.strlen($vTransa).' o ultimo ID: '.$vTransa ;
$retorno_site = JRoute::_($myURI, false);  // Site para onde o usu?rio vai ser redirecionado
$app =& JFactory::getApplication();
$app->redirect($retorno_site); */


?>