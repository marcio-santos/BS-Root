<?php

##############################################################
#                         CONFIGURAÇÕES
##############################################################

//$retorno_site = 'http://www.exemplo.com.br/compra_efedutada.html';  // Site para onde o usuário vai ser redirecionado
require_once('trailer.php') ;


function getW12id($UserID)
  {
    
    $db =& JFactory::getDBO();
    
      $query = "SELECT cpf FROM wow_users_details WHERE userid=".$UserID;
      $db->setQuery($query);
      $w12id = $db->loadResult();
      
      $query = "SELECT tipo FROM wow_users_details WHERE userid=".$UserID;
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
            "http://177.154.134.90:8084/WCF/Clientes/wcfClientes.svc?wsdl" 
        ); 
    $params = array('IdClienteW12'=>229, 'IdFilial'=>1, 'CpfCnpj'=>$InfoCli[0], 'TipoCliente'=>$InfoCli[1]); 
    $webService = $client->ListarClienteCPFCNPJ($params); 
    $wsResult = $webService->ListarClienteCPFCNPJResult; 


// Recupera o IdCliente
 $w12Id = $wsResult->ID_CLIENTE;


//LandingPage para o Retorno
$session = JFactory::getSession();
$vBack2 = $session->get('vBack', NULL, 'bodysystems');

$retorno_token = '29CAD7DE9DD346FB9C47D22C29C91D2A'; // Token gerado pelo PagSeguro BodySystems
/*
Treinamento
29CAD7DE9DD346FB9C47D22C29C91D2A

WS
137B7343D18249A8A5759209FE79C37F

Geral
4CBE496B22D44822926DBFF356E57C7D
*/



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
    
 $volta = explode(";",$_POST['Referencia']);
 
 //Caso seja compra corporativa, o trailer ser� retornado na 4 posi��o do array
 
 
 
 // Recebendo Dados
 $TransacaoID = $_POST['TransacaoID'];
 $VendedorEmail  = $_POST['VendedorEmail'];
 $Referencia = $volta[0]; // $_POST['Referencia'];
 $IdCliente = $volta[1]; //
 $TipoFrete = $_POST['TipoFrete'];
 $ValorFrete = $_POST['ValorFrete'];
 $Extras = $_POST['Extras'];
 $Anotacao = $_POST['Anotacao'];
 $TipoPagamento = utf8_encode($_POST['TipoPagamento']);
 $StatusTransacao = utf8_encode($_POST['StatusTransacao']);
 $CliNome = utf8_encode($_POST['CliNome']);
 $CliEmail = $_POST['CliEmail'];
 $CliEndereco = utf8_encode($_POST['CliEndereco']);
 $CliNumero = $_POST['CliNumero'];
 $CliComplemento = $_POST['CliComplemento'];
 $CliBairro = utf8_encode($_POST['CliBairro']);
 $CliCidade = utf8_encode($_POST['CliCidade']);
 $CliEstado = utf8_encode($_POST['CliEstado']);
 $CliCEP = $_POST['CliCEP'];
 $CliTelefone = $_POST['CliTelefone'];
 $NumItens = $_POST['NumItens'];
 $ProdID = $_POST['ProdID_1'];
 $ProdDescricao = utf8_encode($_POST['ProdDescricao_1']);
 $ProdVal = str_replace('.','',$_POST['ProdValor_1']);
 $ProdValor = str_replace(',','.',$ProdVal) ;
 $Parcelas = $_POST['Parcelas'];
 $PromoID = $volta[0];
 $userid =  $volta[2];
 $Trailer = $volta[3];
 

$query = "REPLACE INTO PagSeguroTransacoes (id,userid,IdCliente,TransacaoID,VendedorEmail,Referencia,TipoFrete,ValorFrete,Extras,TipoPagamento,StatusTransacao,CliNome,CliEmail,CliEndereco,CliNumero,CliComplemento,CliBairro,CliCidade,CliEstado,CliCEP,CliTelefone,NumItens,ProdID,ProdDescricao,ProdValor,Parcelas,PromoID,Trailer,Data) VALUES (NULL,'$userid','$IdCliente','$TransacaoID','$VendedorEmail','$Referencia','$TipoFrete','$ValorFrete','$Extras','$TipoPagamento','$StatusTransacao','$CliNome','$CliEmail','$CliEndereco','$CliNumero','$CliComplemento','$CliBairro','$CliCidade','$CliEstado','$CliCEP','$CliTelefone','$NumItens',
'$ProdID','$ProdDescricao','$ProdValor','$Parcelas','$PromoID','$Trailer',now())" ;

    
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
    $stuck = $mRow." -> ".$value."<br/>" ;
}


$fData = strftime("%Y%m%d %H%M%S", strtotime($Row->Data)) ;

//echo "<h1>Esta é a Data: ".$Row->Data."</h1>" ;
//echo "<h1>Esta é a data Certa: ".$fData."</h1>" ;

 $TransacaoID = $Row->TransacaoID;
 $IdCliente = $Row->IdCliente; //$w12Id ; 
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
 $trailer = $w12Id;
 $data = $Row->Data ;
 $PromoID = $Row->Referencia;

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
            IdPromotor=> $PromoID
            );

   
      
    if($confirma) {
    //Chama o servico em si

    $client = new SoapClient("http://177.154.134.90:8084/WCF/PagSeguro/wcfPagSeguro.svc?wsdl" ); 
    $webService = $client->LogarTransacao($params); 

    }
    
    /* if(!$confirma) {
    //Chama o servico em si

    $client = new SoapClient("http://th52248.cloudth.com.br/W12_ERP/WCF/PagSeguro/wcfPagSeguro.svc?wsdl" ); 
    $webService = $client->LogarTransacao($params); 

    } */
    
    //Volta para a LandingPage Correta.    
    //Controla os eventos
    //die() ;



switch($vBack2) {

    Case 'Compra Corporativa' :
        $myURI='http://bodysystems.net/corpbuy/pagto_concluido.php' ;
        Break;
        
    Case 'Treinamento' :
        $myURI='http://bodysystems.net/pagtoT_concluido.php' ;
        Break;
    Case 'Workshop' :
        $myURI='http://bodysystems.net/pagtoW_concluido.php'; 
        Break;
        
    Case 'Gestor' :
        $myURI='http://bodysystems.net/pagtoT_concluido.php' ;
        Break;        
}

Header( "Location: $myURI") ;


exit();
    
} catch (Exception $e) {
    $msg = time().' - '.$e->getMessage();
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    
    $thefile =  'transacoes.log' ;
    file_put_contents ($thefile, $msg, FILE_APPEND | LOCK_EX);
    die();
}    
    
//Para trabalhar dentro do joomla
/*$mUser = $user->name.' é do tipo '.gettype($user->name).' com tamanho '.strlen($vTransa).' o ultimo ID: '.$vTransa ;
$retorno_site = JRoute::_($myURI, false);  // Site para onde o usu?rio vai ser redirecionado
$app =& JFactory::getApplication();
$app->redirect($retorno_site); */


?>