<?php


//Public URLs
$urlNegocie = 'https://broker.negociecoins.com.br/api/v3';
$urlTem = 'https://broker.tembtc.com.br/api/v3';


$keyNegocie = "";
$secretNegocie = "";

$keyTem = "";
$secretTem = "";

$negocie = getTickerNegocie();
$tem = getTickerTem();

$spread = ((($negocie*100)/$tem)-100);



if( isset($_GET["hash"]) )
{


	$keyNegocie = $_GET["negocieAppId"];
	$secretNegocie = $_GET["negocieSecret"];

	$keyTem = $_GET["temAppId"];
	$secretTem = $_GET["temSecret"];
	$spreadBlock = $_GET["spread"];



	echo "Ticker Negocie: R$ " . $negocie . "<br/>";
	echo "Ticker TemBTC: R$ " . $tem . "<br/>";



	echo "SPREAD: " . strval($spread) . "%<br/>";
	echo "SPREAD-FEE: " . strval($spread-1.5) . "%<br/>";


	if($spread < $spreadBlock )
	{
		echo "O spread atual não é compativel com o spread escolhido, vamos aguardar! Ou você pode ajustar o mesmo! Spread Atual " .$spread  . ", spread limite  " . $spreadBlock ;
		return;

	}

	if($_GET["operation"] == "brl")	
	{
		if(getBalanceNegocie("BTC") > 0)
		{
			echo "Vamos vender tudo imediatamente!";
			$body = array("pair" => "BTCBRL", "type" => "sell", "price" =>  getTickerNegocie() , "volume" =>  number_format(getBalanceNegocie("BTC"),8)) ;
			$json_data = json_encode($body);
			echo $json_data;
			echo post($urlNegocie,$keyNegocie,$secretNegocie,"user/order",$json_data);	
			
		}
	}

	if($_GET["operation"] == "btc")	
	{
		if(getBalanceTem("BRL") > 10)
		{

			$totalbtcbuy = getBalanceTem("BRL")  / (getTickerTem()+5);
			if($totalbtcbuy > 0)
			if($totalbtcbuy < 20)
			{
				
				$body = array("pair" => "BTCBRL", "type" => "buy", "price" =>  getTickerTem() , "volume" =>  $totalbtcbuy) ;
				$json_data = json_encode($body);
				
				echo post($urlTem,$keyTem,$secretTem,"user/order",$json_data);				
			}				
		}		
	}

	echo "<hr>";
	echo "<b>SALDOS</b><br/>";
	echo "<b>Negocie Coins</b><br/>";
	echo "BTC: " . getBalanceNegocie("BTC") . " BTC <br/>";
	echo "BRL: R$ " . getBalanceNegocie("BRL") . "  <br/>";

	echo "<br/>";

	echo "<b>TEMBTC<b><br/>";
	echo "BTC: " . getBalanceTem("BTC") . " BTC <br/>";
	echo "BRL: R$ " . getBalanceTem("BRL") . "  <br/>";

	echo "<br/>AVISOS<br/>";
	if($_GET["operation"] == "brl")
	{
		if(getBalanceTem("BRL") > 10)
		{
			echo "<br><h1>ATENÇÃO SEU CICLO COMEÇOU! COMPRE NA TEMBTC E TRANSFIRA PARA A NEGOCIE COINS, O ROBÔ IRÁ VENDER AUTOMATICAMENTE QUANDO CHEGAR LÁ!</h1><iframe type='text/html' src='https://www.youtube.com/embed/-ePDPGXkvlw?autoplay=1' frameborder='0' allow='autoplay'></iframe>";
		}
		if(getBalanceNegocie("BRL") > 10)
		{
			echo "<br><h1>ATENÇÃO SEU CICLO FINALIZOU! TRANSFIRA O SEU SALDO DA NEGOCIE PARA A TEMBTC!</h1><iframe type='text/html' src='https://www.youtube.com/embed/-ePDPGXkvlw?autoplay=1' frameborder='0' allow='autoplay'></iframe>";
		}
	}


	if($_GET["operation"] == "btc")
	{
		if(getBalanceTem("BTC") > 0)
		{
			echo "<br><h1>ATENÇÃO SEU CICLO FINALIZOU! TRANSFIRA O SEU SALDO DA TEMBTC PARA A NEGOCIE!</h1><iframe type='text/html' src='https://www.youtube.com/embed/-ePDPGXkvlw?autoplay=1' frameborder='0' allow='autoplay'></iframe>";
		}
		if(getBalanceNegocie("BTC") > 0)
		{
			echo "<br><h1>ATENÇÃO SEU CICLO COMEÇOU! VENDA NA NEGOCIE E TRANSFIRA PARA A TEMBTC, O ROBÔ IRÁ COMPRAR AUTOMATICAMENTE QUANDO CHEGAR LÁ!</h1><iframe type='text/html' src='https://www.youtube.com/embed/-ePDPGXkvlw?autoplay=1' frameborder='0' allow='autoplay'></iframe>";
		}
	}

	return;
}










function getTickerNegocie()
{
	$jsondata = file_get_contents("https://broker.negociecoins.com.br/api/v3/btcbrl/ticker");
	$result = json_decode($jsondata,true);
	return $result["last"];
}

function getTickerTem()
{
	$jsondata = file_get_contents("https://broker.tembtc.com.br/api/v3/btcbrl/ticker");
	$result = json_decode($jsondata,true);
	return $result["last"];
}


function amx_authorization_header($id, $key, $function, $method, $body) {
$url1 = 'https:\\broker.negociecoins.com.br/tradeapi/v1/' . $function; //URL + Função Ex: user/balance
date_default_timezone_set('America/Sao_Paulo'); //Setando Timezone para BR
$url = strtolower(urlencode($url1)); //Colocando a URL em Minusculo e fazendo o encode
$content = empty($body) ? '' : base64_encode(md5($body, true)); //se body está vazio, deixa ''(nulo, senão usa o base64_encode()
$time = time(); //Tempo
$nonce = uniqid(); //É gerado um número randonico aleatório, para compor o cabeçalho AMX.
$data = implode('', [$id, strtoupper($method), $url, $time, $nonce, $content]); //String sem separação com as variáveis que estão sendo mostradas
$secret = base64_decode($key); //decode no password vetor de byte
$signature = base64_encode(hash_hmac('sha256', $data, $secret, true)); //Encode na signature utilizando hash_hmac
return 'amx ' . implode(':', [$id, $signature, $nonce, $time]); //retorna a header
} 
function get($url, $id, $key, $function, $body = null) {
	$method = 'GET';
	$result = amx_authorization_header($url,$id, $key, $function, 'GET', $body = null);
	return request($url,$result, $function, $method, $body = null);
}


function post($url,$id, $key, $function, $body) {
	$method = 'POST';
	$result = amx_authorization_header($url,$id, $key, $function, $method, $body);
	return request($url,$result, $function, $method, $body);
}

function request($url, $header, $function, $method, $data) {
	$url = $url . $function;

	if ($method == 'GET' || $method == 'DELETE') {
		$context = stream_context_create(array(
			'http' => array(
			'header' => "Authorization: " . $header,
			)));
		} else {
		$context = stream_context_create(array(
		'http' => array(
		'method' => 'POST',
		'header' => "Authorization: " . $header . "\r\n" . 'content-length: ' . strlen($data) . "\r\n" . "content-type: application/json; charset=UTF-8" . "\r\n",
		'content' => $data,
		)));
	}

	$result = file_get_contents($url, false, $context);
	return $result;
}



function getBalanceNegocie($coin)
{ 
	global $urlNegocie,$keyNegocie,$secretNegocie;
	$jsondata = get($urlNegocie,$keyNegocie,$secretNegocie,"user/balance");
	$result = json_decode($jsondata,true);
	foreach($result as $item) {
		if($coin == "BRL") 
			return $item[0]["available"];
		if($coin == "BTC")
			return $item[1]["available"]; 
	}
}

function getBalanceTem($coin)
{ 
	global $urlTem,$keyTem,$secretTem;
	$jsondata = get($urlTem,$keyTem,$secretTem,"user/balance");
	$result = json_decode($jsondata,true);
	foreach($result as $item) {
		if($coin == "BRL") 
			return $item[0]["available"];
		if($coin == "BTC")
			return $item[1]["available"]; 
	}
}




?>



<html>
<head>
	<title>GutoSchiavon - BOT OPENSOURCE</title>
	
	<script
  src="https://code.jquery.com/jquery-3.4.0.js"
  integrity="sha256-DYZMCC8HTC+QDr5QNaIcfR7VSPtcISykd+6eSmBW5qo="
  crossorigin="anonymous"></script>
</head>
<body>


<form id="formGuto">
<h1>Painel de operação - GutoSchiavon v0.0.0.1</h1><br/>

<br/>
<b>Donate: 3FbzWqtR24kQck2FL1ts9DBV8E2541xoC7</b> 
<br/> <a href="https://github.com/MatheusGrijo/ARBITRAGE_NEGOCIE_TEMBTC">GITHUB</a></b> <br/>
<br/>


<br/><b>::Configurações de api</b><br/>
Appid Negocie:  <input type="text" id="negocieAppId" name="negocieAppId"><br/>
Secret Negocie:  <input type="text" id="negocieSecret" name="negocieSecret"><br/>

Appid TemBTC:  <input type="text" id="temAppId" name="temAppId"><br/>
Secret TemBTC:  <input type="text" id="temSecret" name="temSecret"><br/>

<br/><b>::Você pretende acumular?</b><br/>
<input type="radio" name="operation" value="brl" /> Acumular brl
<input type="radio" name="operation" value="btc"/>Acumular bitcoin
 


<br/><b>::Spread minimo para executar</b><br/>
Porcentagem minima:  <input type="text" id="spread" name="spread" value="0.2"><br/>





<hr>

<div id="ativar">
	<a href="javascript:magic();">ATIVAR BOT</a>
</div>

<div id="result"></div>

<script>
	function magic()
	{

		$( "#ativar" ).hide();
		$( "#result" ).html( "Por favor, aguarde alguns segundos...<br/><img src='http://2.bp.blogspot.com/-eyOStwXVIbQ/VRKwfeCXhOI/AAAAAAAAPE0/FuvOhM5_s9I/s1600/Minions%2B22.gif' />"  );
		window.setInterval('api()', 60000);

		
	}

	function api()
	{
			$.post( "guto.php?hash=1&negocieAppId=" + encodeURIComponent($("#negocieAppId").val()) + "&negocieSecret=" + encodeURIComponent($("#negocieSecret").val())  + "&temAppId=" + encodeURIComponent($("#temAppId").val())   + "&temSecret=" + encodeURIComponent($("#temSecret").val())  + "&operation=" + $( "input[type=radio][name=operation]:checked" ).val()  + "&spread=" + $("#spread").val()  , function( data ) {
  			$( "#result" ).html( data );
		});
	}
</script>

</form>
</body>
</html>

