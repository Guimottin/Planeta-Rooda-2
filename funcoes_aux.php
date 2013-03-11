<?php
//---------------------------------------------------------------
//Funções Comuns
//---------------------------------------------------------------

	function resolucao($screen_res) {
		
		if($screen_res != "") { 
			$_SESSION['resolucao'] = $screen_res;
		}
			
		if(isset($_SESSION["resolucao"])){
			$screen_res = intval($_SESSION["resolucao"]);
		}else{
			echo "  <script language=\"javascript\">
					<!--
					location.href = 'index.php?".$_SERVER['QUERY_STRING']."&screen_res='+ screen.width + '&vert_res=' + screen.height;
					//-->
					</script>";
		}
		return $screen_res;
	}
	//função para arrumar data vinda do banco de dados
	function comum_arrumar_data($data) {
		$nova_data_array = explode("-",$data);
		$nova_data = $nova_data_array[2]."-".$nova_data_array[1]."-".$nova_data_array[0];
		return $nova_data;
	}
	
	//função para arrumar data vinda do banco de dados 2
	function comum_arrumar_data_hora($data) {
		$nova_data_array = explode(" ",$data);
		$nova_data_array_dia = explode("-",$nova_data_array[0]);
		$nova_data = $nova_data_array_dia[2]."-".$nova_data_array_dia[1]."-".$nova_data_array_dia[0]." ".$nova_data_array[1];
		return $nova_data;
	}
	
	// função usada para validar data
	function comum_conferir_data ($data) {
		$data_array = explode("-",$data); // BUM!
		$dia = $data_array[2];
		$mes = $data_array[1];
		$ano = $data_array[0];
		
		if ( (($ano % 4) == 0) && ($mes == 2) && ($dia > 29) )
		// se o mês for fevereiro e o ano for bissexto, dia não pode
		// ser maior que 29
		return 0;
		else if ( (($ano % 4) > 0) && ($mes == 2) && ($dia > 28) )
		// se o mês for fevereiro e o ano não for bissexto, dia não pode
		// ser maior que 28
		return 0;
		else if( (($mes == 4) || ($mes == 6) || ($mes == 9) || ($mes == 11) ) && ($dia == 31))
		// se o mês for Abril, Junho, Setembro ou Novembro, dia não pode ser 31
		return 0;
		else
		return 1;
	}
	
	//enviar email
	function comum_enviar_email ($destinatario,$assunto,$mensagem,$remetente) {

		$sucesso = mail($destinatario, $assunto, $mensagem, "From: $remetente");
		//echo $mensagem;
	
		if($destinatario=="" || $assunto=="" || $mensagem=="" || $remetente=="" || $sucesso==false) {
			$status=0;
		} else {
			$status=1;
		}
		
		return $status;
		
	}
	
	
	function fullUpper($string) { // MAGIA!!!!!!!
		return mb_strtoupper($string, 'UTF-8'); // Tinha uma gambiarra enorme aqui, mas o Vinadé me deu as manhas pra consertar
	}
	
	function alterna() { // Alterna entre 1 e 2
		static $foo = 2;
		
		return ($foo == 1 ? $foo = 2 : $foo = 1); // Quié? Funciona.
	}
	
	
	function checa_nivel ($userlevel, $minlevel) { 
		/*\
		 * Retorna 0/false para nível menor;
		 * 1/true se pertence ao nível;
		 * E "xyzzy" se pertence a níveis maiores mas não ao nível em questão.
		 *
		 * IMPORTANTE: 1 tem prioridade sobre "xyzzy".
		 *
		 * Confira igualdade com checa_nivel($a, $b) === 1 para garantir que o usuário PERTENÇA àquela classe.
		 * Use == caso seja somente necessário ver se ele é de um nível igual ou superior.
		\*/
		
		if (($userlevel & $minlevel) == true) { // Bit daquela classe tá setado?
			return 1;
		}
		
		if ($userlevel % $minlevel){ // Se pertence a classes de nivel mais alto
			return "xyzzy";
		}
		
		return false;
	}
	
	
	
	function conversor_pergunta ($string, $branco){
/*\
 *	Converte a string pro padrão de BD usado no Planeta Pergunta.
 *	Ah, e assim. Essas conversões são executadas na ordem, então ele mata todos os & antes de adicionar os seguintes.
 *	Tou matando os & por causa do &shy; e tais caracteres semi-bugados, e sabe... Usamos UTF-8, não precisa
 *	de entidade HTML, meu.
 *	Ah, esse ultimo que aparenta estar vazio (ou não, depende do editor de texto) é um &shy; vivo.
 *
 *	Por fim, caso o parâmetro "$branco" pareça estranho, veja ele em uso que é fácinho de entender
\*/
		$replacear	= array("&",	"¦",		"<",	"­");
		$com		= array("&amp;","&brvbar;",	"&lt;",	"NÃO.");
		
		return str_replace($replacear, $com, $string == "" ? $branco : $string);
	}


	function tempo_edicao_forum ($data){ // warning: wild gambi
		$bum		= explode(',', $data); // explode a data
		$datas		= explode('/', $bum[0]); // pega as datas, separadas em array
		$horas		= explode(' ', $bum[1]); // separa as horas e divide em horas e minutos
		$horas[0]	= trim($horas[0], 'h'); // contem só a hora, sem o h no fim
		$horas[1]	= trim($horas[1], 'min'); // contém só os minutos, sem o min
		
		/*\
		 * s'il vous plait pardonnez cette horreur terrible, mes amis
		 * $horas[0] = horas
		 * $horas[1] = minutos
		 * $datas[0] = dia
		 * $datas[1] = mes
		 * $datas[2] = ano
		\*/
		$tempo_post = mktime($horas[0], $horas[1], 0/*segundos não estão registrados no bd*/, $datas[1], $datas[0], $datas[2]);
		if ($tempo_post === false){
			echo "Avise um desenvolvedor que o erro 0x8BADF00D aconteceu no funcoes_aux. Isso significa que o relogio do servidor tem algum problema.";
			die();
			// Se algum dia alguém reportar isso, é porque a função mktime acima falhou. Boa sorte. Boa sorte mesmo.
		}
		$tempo_post+= 300; // 5 minutos no futuro, 300 segundos.
		$tempo_atual= time();
		
		// se o tempo atual é maior que os 5 minutos de edição, desista.
		if ($tempo_atual < $tempo_post) {return false;} else {return true;}
	}

	function dia_invalido($mes, $dia){ // retorna valor que evalua pra true em caso de falso
		switch ($mes){
			case 2:
				if ($dia > 28){return 28;}
				break;
		
			case 4:
			case 6:
			case 9:
			case 11:
				if ($dia > 30){return 30;}
				break;
			
			default:
				if ($dia > 31){return 31;}
				break;
		}
		
		return false; // sobreviveu
	}

	function nivel_existe($nivel){
		global $nivelAdmin;
		global $nivelCoordenador;
		global $nivelAluno;
		global $nivelProfessor;
		global $nivelVisitante;
		global $nivelMonitor;
		switch($nivel){
			case $nivelAdmin:
			case $nivelCoordenador:
			case $nivelAluno:
			case $nivelProfessor:
			case $nivelVisitante:
			case $nivelMonitor:
				return true;
			default:
				return false;
		}
		
	}

	function magic_redirect($extra){
		$host	= $_SERVER['HTTP_HOST']; // Pega o endereço do site, http://www.nuted.ufrgs.br/
		$uri	= rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // Pega o caminho relativo da pasta que o script tá rodando, planeta2/funcionalidades/portfolio
		header("Location: http://$host$uri/$extra"); // Magia.
	}

	function checa_permissoes($funcionalidade, $turma){
		global $tabela_permissoes;	global $tabela_controleFuncionalidades;
		
		switch($funcionalidade){
			case TIPOBLOG:
				$o_que_precisa_ser_selecionado = "blog";
				break;
			case TIPOPORTFOLIO:
				$o_que_precisa_ser_selecionado = "portfolio";
				break;
			case TIPOBIBLIOTECA:
				$o_que_precisa_ser_selecionado = "biblioteca";
				break;
			case TIPOPERGUNTA:
				$o_que_precisa_ser_selecionado = "planetaPergunta";
				break;
			case TIPOAULA:
				$o_que_precisa_ser_selecionado = "aulas";
				break;
			case TIPOCOMUNICADOR:
				$o_que_precisa_ser_selecionado = "batePapo";
				break;
			case TIPOFORUM:
				$o_que_precisa_ser_selecionado = "forum";
				break;
			case TIPOARTE:
				$o_que_precisa_ser_selecionado = "planetaArte";
				break;
			case TIPOPLAYER:
				$o_que_precisa_ser_selecionado = "planetaPlayer";
				break;
			default:
				$o_que_precisa_ser_selecionado = "NULL";
		}
		
		$q = new conexao();
		$q->solicitar("SELECT $o_que_precisa_ser_selecionado FROM $tabela_controleFuncionalidades WHERE codTurma=$turma");
		
		if($q->resultado[$o_que_precisa_ser_selecionado] === "h"){ // se a funcionalidade estiver habilitada...
			switch($funcionalidade){
				case TIPOBLOG:
					$o_que_precisa_ser_selecionado = "blog_inserirPost,blog_editarPost,blog_inserirComentarios,blog_excluirPost,blog_adicionarLinks,blog_adicionarArquivos";
					break;
				case TIPOPORTFOLIO:
					$o_que_precisa_ser_selecionado = "portfolio_visualizarPost,portfolio_inserirPost,portfolio_editarPost,portfolio_inserirComentarios,portfolio_excluirPost,portfolio_adicionarLinks,portfolio_adicionarArquivos";
					break;
				case TIPOBIBLIOTECA:
					$o_que_precisa_ser_selecionado = "biblioteca_enviarMateriais,biblioteca_editarMateriais,biblioteca_excluirArquivos,biblioteca_aprovarMateriais";
					break;
				case TIPOPERGUNTA:
					$o_que_precisa_ser_selecionado = "pergunta_criarQuestionario,pergunta_criarPergunta,pergunta_editarQuestionario,pergunta_editarPergunta,pergunta_deletarQuestionario,pergunta_deletarPergunta";
					break;
				case TIPOAULA:
					$o_que_precisa_ser_selecionado = "aulas_criarAulas, aulas_importarAulas, aulas_editarAulas";
					break;
				case TIPOCOMUNICADOR:
					$o_que_precisa_ser_selecionado = "comunicador_terreno,comunicador_turma,comunicador_privado,comunicador_amigo";
					break;
				case TIPOFORUM:
					$o_que_precisa_ser_selecionado = "forum_criarTopico,forum_editarTopico,forum_excluirTopico,forum_responderTopico,forum_editarResposta,forum_excluirResposta";
					break;
				case TIPOARTE:
					$o_que_precisa_ser_selecionado = "arte_criarDesenho,arte_comentarDesenho";
					break;
				case TIPOAULAS:
					$o_que_precisa_ser_selecionado = "aulas_criarAulas, aulas_editarAulas, aulas_importarAulas";
					break;
				case TIPOPLAYER:
					$o_que_precisa_ser_selecionado = "player_inserirVideos, player_deletarVideos, player_inserirComentario, player_deletarComentario";
					break;
				default:
					$o_que_precisa_ser_selecionado = "NULL";
			}
			
			$q->solicitar("SELECT $o_que_precisa_ser_selecionado FROM $tabela_permissoes WHERE codTurma=$turma");
			
			return $q->resultado;
		}else
			return false;
	}
	
function selecionaTurmas($t, $frase="SELECIONAR TURMA"){
	echo "	<div id=\"bloco_mensagens\" class=\"bloco\">
		<h1>$frase</h1>
		<div class=\"cor1\">
			<form name=\"troca_turma\" method=\"get\">
				<select style=\"vertical-align:middle\" name=\"turma\">";
	
	cospeSelectDeTurmas($t);
	
	echo "\n				</select>
				<img style=\"vertical-align:middle; height:25px; padding-left:100px; cursor:pointer\" src=\"../../images/botoes/bt_confirmar.png\" onclick=\"troca_turma.submit()\"/>
			</form>
		</div>
	</div>";
}

function cospeSelectDeTurmas($t=0){
	$nome = new conexao();
	global $tabela_turmas;
	$usuario = new usuario();
	$usuario->openUsuario($_SESSION['SS_usuario_id']);
	$turmas = $usuario->getTurmas();
	/*foreach($_SESSION['SS_turmas'] as $turma){*/
	foreach($turmas as $turma){
		$nome->solicitar("SELECT nomeTurma FROM $tabela_turmas WHERE codTurma = $turma");
		if ($turma == $t){
			echo "\n					<option selected value=\"$turma\">".$nome->resultado['nomeTurma']."</option>";
		}else{
			echo "\n					<option value=\"$turma\">".$nome->resultado['nomeTurma']."</option>";
		}
	}
}
?>