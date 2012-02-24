<?php
	// http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	// @author MrHus
	function endsWith($haystack, $needle){
	    $length = strlen($needle);
	    $start  = $length * -1; //negative
	    return (substr($haystack, $start) === $needle);
	}


	// Página que contém os itinerários de ônibus de SJCampos	
	$url="http://servicos.sjc.sp.gov.br/st/horarioitinerarioonibus/index.aspx";
	$arquivo=file_get_contents($url);		// Salva conteúdo da página na variável

	function getViewState($arquivo){
		preg_match_all( "/\<input type=\"hidden\" name=\"__VIEWSTATE\" id=\"__VIEWSTATE\" value=\"(.*?)\" \/\>/",  $arquivo, $__VIEWSTATE );
		$__VIEWSTATE= urlencode($__VIEWSTATE[1][0]);
		return $__VIEWSTATE;
	}

	function getEventValidation($arquivo){
		preg_match_all("/\<input type=\"hidden\" name=\"__EVENTVALIDATION\" id=\"__EVENTVALIDATION\" value=\"(.*?)\" \/\>/",$arquivo,$__EVENTVALIDATION);
		$__EVENTVALIDATION=urlencode($__EVENTVALIDATION[1][0]);
		return $__EVENTVALIDATION;
	}
	

	function getPagina($url,$postData,$verbose=false){

		$opcoes = array(
			CURLOPT_RETURNTRANSFER => true, 		// return web page
			CURLOPT_HEADER => true, 			// don't return headers
			CURLOPT_FOLLOWLOCATION => true, 		// follow redirects
			CURLOPT_ENCODING => "", 			// handle all encodings
			CURLOPT_USERAGENT => "BusBot", 			// who am i
			CURLOPT_CONNECTTIMEOUT => 120, 			// timeout on connect
			CURLOPT_TIMEOUT => 1120, 			// timeout on response
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => 	"_EVENTTARGET=".	$postData['EVENTTARGET'].
						"&__EVENTARGUMENT=".	$postData['EVENTARGUMENT'].
						"&__VIEWSTATE=".	$postData['VIEWSTATE'].
						"&__EVENTVALIDATION=".	$postData['EVENTVALIDATION'].
						"&rbTipoPesquisa=".	$postData['rbTipoPesquisa'].
						"&txtPesquisa=".	$postData['txtPesquisa'].
						"&btnPesquisar=".	$postData['btnPesquisar']
		);		
		$ch = curl_init($url);
		curl_setopt_array($ch,$opcoes);
		curl_setopt($ch, CURLOPT_VERBOSE, $verbose);		// Aciona o método verbose, útil para debug
		$resultado = curl_exec($ch);
		curl_close($ch);
		return $resultado;
	}

	function getAllPostBackCodes($paginaInicial){
		// Exemplos de links contendo itinerários
		// Onibus 	<a href="javascript:__doPostBack('dtgLista$ctl32$ctl00','')">
		// Alternativo 	<a href="javascript:__doPostBack('dtgListaAlternativo$ctl35$ctl00','')">
		// A intenção aqui é ter acesso aos códigos de post back, como dtgLista$ctl32 e dtgListaAlternativo$ctl35
		// A segunda parte do código, logo após a segunda $, ($ctl00) diz respeito apenas a uma configuração que decide se será exibido itinerário $ctl00 ou mapa $ctl01
		$padraoPostBacks="/\<a href=\"javascript:(.*?)\"\>/i";
		preg_match_all($padraoPostBacks,$paginaInicial,$postBacks);
		$postBacks=$postBacks[1];				//Pega somente o valor interno capturado			
		$postBackCodes=array();
		foreach($postBacks as $postBack){
			//Exemplo de $postBack: __doPostBack('dtgLista$ctl23$ctl00','')
			$postBackCode=explode("'",$postBack);
			$postBackCode=$postBackCode[1];
			// Exemplo de $postBackCode: dtgLista$ctl23$ctl00
			if(endsWith($postBackCode,"0")){	// Só salva os que possuem final 0
				$postBackCode=explode("$",$postBackCode);
				$postBackCode=$postBackCode[0]."\$".$postBackCode[1];
				// Exemplo de $postBackCode: dtgLista$ctl23
				array_push($postBackCodes,$postBackCode);
			}
		}		

		return $postBackCodes;
	}

	/* Executa a raspagem	===================================================		*/
	
	
	$viewState=getViewState($arquivo);
	$eventValidation=getEventValidation($arquivo);
	// Dados que são usadas no primeiro post, para acessar a página principal contendo links para todos os itinerários
	$postData=array(
		'EVENTTARGET'=>		'',
		'EVENTARGUMENT'=>	'',
		'VIEWSTATE'=>		$viewState,
		'EVENTVALIDATION'=>	$eventValidation,
		'rbTipoPesquisa'=>	'rbNomeLinha',
		'txtPesquisa'=>		'',
		'btnPesquisar'=>	'Pesquisar'
	);

	// Salva a página inicial com os links para todos os itinerários	
	$paginaInicial=getPagina($url,$postData);

	// Salva uma lista contendo todos os postBacks de todos os itinerários
	$postBacks=getAllPostBackCodes($paginaInicial);

	// Cria novos EventValidation e ViewState baseados no novo estado da página
	$internoEV=getEventValidation($paginaInicial);
	$internoVS=getViewState($paginaInicial);

	foreach($postBacks as $postBack){
		$novasOpcoes = array(
			CURLOPT_RETURNTRANSFER => true, 		// return web page
			CURLOPT_HEADER => false, 			// don't return headers
			CURLOPT_FOLLOWLOCATION => true, 		// follow redirects
			CURLOPT_ENCODING => "", 			// handle all encodings
			CURLOPT_USERAGENT => "BusBot", 			// who am i
			CURLOPT_CONNECTTIMEOUT => 120, 			// timeout on connect
			CURLOPT_TIMEOUT => 1120, 			// timeout on response
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => 	"__EVENTTARGET=".urlencode($postBack."\$ctl00")."&__EVENTARGUMENT=&__VIEWSTATE=".$internoVS."&__EVENTVALIDATION=".$internoEV."&rbTipoPesquisa=rbNomeLinha&txtPesquisa="
		);
		$ch = curl_init($url);
		curl_setopt_array($ch,$novasOpcoes);
		$paginaInterna = curl_exec($ch);
		curl_close($ch);
		
		//<span id="lblIncluidoEm">Inclu&iacute;dos em: 15/04/2011</span>
		$padraoUltimaAtualizacao="/\<span id=\"lblIncluidoEm\"\>(.*?)<\/span\>/i";
		preg_match_all($padraoUltimaAtualizacao,$paginaInterna,$ultimaAtualizacao);
		$ultimaAtualizacao=str_replace("Inclu&iacute;dos em:","",$ultimaAtualizacao[1][0]);
		
		//<span id="lblNumeroLinha">101</span>
		$padraoNumero="/\<span id=\"lblNumeroLinha\"\>(.*?)\<\/span\>/i";
		preg_match_all($padraoNumero,$paginaInterna,$numero);
		$numero=$numero[1][0];

		//<span id="lblNomeLinha">REPRESA - TERMINAL URBANO CENTRAL (RADIAL) O.S.O 035</span>
		$padraoNome="/\<span id=\"lblNomeLinha\"\>(.*?)\<\/span\>/i";
		preg_match_all($padraoNome,$paginaInterna,$nome);
		$nome=$nome[1][0];
		
		//<span id="lblSentido">TERMINAL URBANO CENTRAL / REPRESA</span>
		$padraoSentido="/\<span id=\"lblSentido\">(.*?)\<\/span\>/i";
		preg_match_all($padraoSentido,$paginaInterna,$sentido);
		$sentido=$sentido[1][0];


		//<span id="lblItinerario">(.*?)</span>
		$padraoItinerario="/\<span id=\"lblItinerario\"\>(.*?)\<\/span>/i";
		preg_match_all($padraoSentido,$paginaInterna,$itinerario);
		$itinerario=$itinerario[1][0];		

		//<span id="lblResultado">strong><br><br>SEGUNDA A SEXTA</strong><strong>SÁBADO</strong><strong><br><br>DOMINGO E FERIADO</strong></span>
		$padraoHorarioCompleto="/\<span id=\"lblResultado\"\>(.*?)\<\/span>/i";
		preg_match_all($padraoHorarioCompleto,$paginaInterna,$horarioCompleto);
		$horarioCompleto=$horarioCompleto[1][0];		
		$padraoNomesHorarios="/\<strong\>(.*?)\<\/strong\>/i";
		$tags=array("<br>","<strong>","</strong>","<br/>");
		preg_match_all($padraoNomesHorarios,$horarioCompleto,$nomesHorariosTags);
		$horarioCompleto=str_replace($tags,"",$horarioCompleto);

		// Exemplo:
		//		[0] => SEGUNDA A SEXTA
		//		[1] => SÁBADO
		//		[2] => DOMINGO E FERIADO

		$nomesHorarios=array();
		foreach($nomesHorariosTags[1] as $nomeHorario){			
			array_push($nomesHorarios,str_replace($tags,"",$nomeHorario));
		}
		$horariosDivididos=preg_split("/".implode("|",$nomesHorarios)."/i",$horarioCompleto);

		$horarios=array();
		for($i=1;$i<count($horariosDivididos);$i++){
			$horarios[$nomesHorarios[$i-1]]=$horariosDivididos[$i];
		}

		// Impressão para testes
		echo "Número da linha: ".$numero."\n";		
		echo "Nome da linha: ".$nome."\n";		
		echo "Última atualização: ".$ultimaAtualizacao."\n";
		echo "Sentido: ".$sentido."\n";
		echo "Itinerário: ".$itinerario."\n";
		print_r($horarios);
		echo "\n\n";


	}



?>
