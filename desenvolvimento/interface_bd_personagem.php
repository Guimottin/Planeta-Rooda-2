<?
session_start();
header('Content-Type: text/html; charset=utf-8');
//arquivos necess�rios para o funcionamento
require_once("../cfg.php");
require_once("../bd.php");
//require_once("../usuarios.class.php");
require("../funcoes_aux.php");

/*
			Montar mensagem que descrever� a localiza��o do terreno em que se encontra o personagem.
*/
function montarMensagemDescricaoTerreno($id_planeta_param){
	//Encontrar o planeta atual.
	$pesquisaPlaneta = new conexao();
	$pesquisaPlaneta->solicitar("SELECT * FROM Planetas WHERE Id=$id_planeta_param");

	$nome = $pesquisaPlaneta->resultado['Nome'];
	$tipo_planeta = $pesquisaPlaneta->resultado['Tipo'];
	$ids_planeta_pai_nao_formatados = $pesquisaPlaneta->resultado['IdsPais'];
	$ids_planeta_pai_em_array = explode(",", $ids_planeta_pai_nao_formatados);
	$id_planeta_pai = $ids_planeta_pai_em_array[0];
	
	switch($tipo_planeta){	
		case 1://ano
				if($nome == "" or !isset($nome) or $nome == null){
					return "Ano N.I.";
				} else {
					return "Ano ".$nome;
				}
		break;
		case 2://turma
				$mensagemAtehAgora = montarMensagemDescricaoTerreno($id_planeta_pai);
				if($nome == "" or !isset($nome) or $nome == null){
					return $mensagemAtehAgora." Turma N.I.";
				} else {
					return $mensagemAtehAgora." Turma ".$nome;
				}
		break;
	}
}

$action = isset($_GET['action']) ? $_GET['action'] : false;
if(!$action)
	$action = $_POST['action'];
	
switch ($action) {
	case 1:
	/*---------------------------------------------------
	*	Envia as principais variaveis do sistema para a inicializa��o -  primeira parte - variaveis sistema
	---------------------------------------------------*/  
		$usuario_id		= $_SESSION['SS_usuario_id'];
		$personagem_id	= $_SESSION['SS_personagem_id'];
		$terreno_id		= isset($_SESSION['SS_terreno_id']) ? $_SESSION['SS_terreno_id'] : $_GET['terreno_id_tela_inicial_geral'];
		
		//$usuario = new Usuario();
		//$usuario->OpenUsuario($usuario_id);
		//$idTerrenoQuarto = $usuario->getPlanetaQuarto();
	
		//procura todos os dados do terreno atual
		$pesquisa0 = new conexao();
		$pesquisa0->solicitar("SELECT * FROM `$tabela_terrenos` WHERE terreno_id='$terreno_id'");
	
		$terreno_grupo_id = $pesquisa0->resultado['terreno_grupo_id'];
		$terreno_nome   = $pesquisa0->resultado['terreno_nome'];
		$terreno_chat = $pesquisa0->resultado['chat_id'];
		$terreno_solo = $pesquisa0->resultado['terreno_solo'];
		
		$pesquisaPermissaoEdicao = new conexao();
		$pesquisaPermissaoEdicao->solicitar("SELECT *
											 FROM Planetas
											 WHERE Id = $terreno_grupo_id");
		$terreno_permissao_edicao = $pesquisaPermissaoEdicao->resultado['edicao'];
	
		//atualiza os dados do personagem conforme o terreno atual
		$now = date("Y-m-j H:i:s"); //0000-00-00 00:00:00
	
		$updateUltimoTerreno = new conexao();
		$updateUltimoTerreno->solicitar("SELECT * FROM $tabela_personagens WHERE personagem_id='$personagem_id'");
		$terrenoUltimoLogin = $updateUltimoTerreno->resultado['personagem_terreno_id'];
		if($terrenoUltimoLogin == $terreno_id){
			$terrenoUltimoLogin = $updateUltimoTerreno->resultado['personagem_ultimo_terreno_id'];
		}
		$update1 = new conexao();
		$update1->solicitar("UPDATE `$tabela_personagens` 
							SET personagem_terreno_id=$terreno_id, 
								personagem_ultimo_terreno_id=$terrenoUltimoLogin, 
								personagem_ultimo_acesso='$now' 
							WHERE personagem_id = '$personagem_id'");
	
		//procura todos os dados do personagem atual
		$pesquisa1 = new conexao();
		$pesquisa1->solicitar("SELECT * FROM `$tabela_personagens` WHERE personagem_id ='$personagem_id'");
	
		$personagem_nome = $pesquisa1->resultado['personagem_nome'];
		$personagem_avatar_1 = $pesquisa1->resultado['personagem_avatar_1'];
		$personagem_cor_texto = $pesquisa1->resultado['personagem_cor_texto'];
		$personagem_ultimo_terreno_id = $pesquisa1->resultado['personagem_ultimo_terreno_id'];
		$personagem_posicao_x = $pesquisa1->resultado['personagem_posicao_x'];
		$personagem_posicao_y = $pesquisa1->resultado['personagem_posicao_y'];
		$personagem_velocidade = $pesquisa1->resultado['personagem_velocidade'];
		$personagem_linha_chat = $pesquisa1->resultado['personagem_linha_chat'];
		$personagem_fala = $pesquisa1->resultado['personagem_fala'];
		$personagem_animacao = $pesquisa1->resultado['personagem_animacao'];
		$personagem_cabelos = $pesquisa1->resultado['personagem_cabelos'];
		$personagem_olhos = $pesquisa1->resultado['personagem_olhos'];
		$personagem_cor_pele = $pesquisa1->resultado['personagem_cor_pele'];
		$personagem_cor_cinto = $pesquisa1->resultado['personagem_cor_cinto'];
		$personagem_cor_luvas_botas = $pesquisa1->resultado['personagem_cor_luvas_botas'];
		$personagem_chat_id = $pesquisa1->resultado['chat_id'];
			
		//procura dados dos terrenos vizinhos
		$pesquisa2 = new conexao();
		$pesquisa2->solicitar("SELECT * FROM `$tabela_terrenos` WHERE (terreno_grupo_id=$terreno_grupo_id) ORDER by terreno_indice");
		
	
		$pesquisa2->ultimo();
		$terreno_id_ultima = $pesquisa2->resultado['terreno_id'];
		$terreno_nome_ultima = $pesquisa2->resultado['terreno_nome'];
	
		$pesquisa2->primeiro();
		$terreno_id_primeira = $pesquisa2->resultado['terreno_id'];
		$terreno_nome_primeira = $pesquisa2->resultado['terreno_nome'];
	
		for($q=0;$q<$pesquisa2->registros;$q++){
			//print_r($pesquisa2->resultado);
			if($pesquisa2->resultado['terreno_id'] == 5){/*$terreno_id) {*/
				$pesquisa2->anterior();
				$oeste = $pesquisa2->resultado['terreno_id'];
				$nome_oeste = $pesquisa2->resultado['terreno_nome'];
				if($oeste != "") {
					$pesquisa2->proximo();
				}
				$pesquisa2->proximo();
				$leste = $pesquisa2->resultado['terreno_id'];
				$nome_leste = $pesquisa2->resultado['terreno_nome'];
				if($oeste=="") {
					$oeste = $terreno_id_ultima;
					$nome_oeste = $terreno_nome_ultima;
				}
				if($leste=="") {
					$leste = $terreno_id_primeira;
					$nome_leste = $terreno_nome_primeira;
				}
			}
			$pesquisa2->proximo();
		}
	
		//procura todos os dados do personagem atual
		$pesquisa4 = new conexao();
		$pesquisa4->solicitar("SELECT * FROM `$tabela_usuarios` WHERE usuario_id ='$usuario_id'");
		
		$usuario_grupos = 1; //$pesquisa4->resultado['usuario_grupos'];
		$usuario_nivel = $pesquisa4->resultado['usuario_nivel'];
		$usuario_grupo_base = 1; //$pesquisa4->resultado['usuario_grupo_base'];
		$usuario_quarto_id = $pesquisa4->resultado['quarto_id'];
	
		//o terreno_grupo_id � o id do planeta que cont�m o terreno, n�o sendo necess�ria uma nova pesquisa aqui.
		$mensagemLocalizacao = montarMensagemDescricaoTerreno($terreno_grupo_id);
		
		$conexao_planeta = new conexao();
		$conexao_planeta->solicitar("SELECT * FROM Planetas WHERE Id=$terreno_grupo_id");
		$planeta_tipo = $conexao_planeta->resultado['Tipo'];
		$planeta_aparencia = $conexao_planeta->resultado['Aparencia'];
		$ehQuarto = ($terreno_solo == 6);
		
		$conexao_escola = new conexao();
		$conexao_escola->solicitar("SELECT * FROM Escolas");
		$nomeEscola = $conexao_escola->resultado['nome'];
		
		//turma
		$conexao_turma = new conexao();
		$conexao_turma->solicitar("SELECT TT.* 
									FROM $tabela_terrenos AS T JOIN Planetas AS P ON T.terreno_grupo_id=P.Id 
															   JOIN $tabela_turmas AS TT ON TT.nomeTurma = P.Nome
									WHERE T.terreno_id = $terreno_id");
		$turma_id = $conexao_turma->resultado['codTurma'];
		$nomeTurma = $conexao_turma->resultado['nomeTurma'];
		
		//permiss�es
		$conexao_permissoes = new conexao();
		$conexao_permissoes->solicitar("SELECT FT.* 
										FROM $tabela_terrenos AS T JOIN Planetas AS P ON T.terreno_grupo_id=P.Id 
																   JOIN $tabela_turmas AS TT ON TT.nomeTurma = P.Nome
																   JOIN FuncionalidadesTurma AS FT ON TT.codTurma = FT.codTurma
										WHERE T.terreno_id = $terreno_id");
		$permissao_batePapo 	   = ($conexao_permissoes->resultado['batePapo']		== 'd'? 'false' : 'true');
		$permissao_biblioteca 	   = ($conexao_permissoes->resultado['biblioteca']		== 'd'? 'false' : 'true');
		$permissao_blog 		   = ($conexao_permissoes->resultado['blog']			== 'd'? 'false' : 'true');
		$permissao_portfolio 	   = ($conexao_permissoes->resultado['portfolio']		== 'd'? 'false' : 'true');
		$permissao_forum 		   = ($conexao_permissoes->resultado['forum']			== 'd'? 'false' : 'true');
		$permissao_planetaArte 	   = ($conexao_permissoes->resultado['planetaArte']		== 'd'? 'false' : 'true');
		$permissao_planetaPergunta = ($conexao_permissoes->resultado['planetaPergunta']	== 'd'? 'false' : 'true');
		$permissao_aulas 		   = ($conexao_permissoes->resultado['aulas']			== 'd'? 'false' : 'true');
		
		$conexao_permissoes->solicitar("SELECT GT.* 
										FROM $tabela_terrenos AS T JOIN Planetas AS P ON T.terreno_grupo_id=P.Id 
																   JOIN $tabela_turmas AS TT ON TT.nomeTurma = P.Nome
																   JOIN GerenciamentoTurma AS GT ON TT.codTurma = GT.codTurma
										WHERE T.terreno_id = $terreno_id");
		$habilitado_chatTerrenoParaAlunos	 = (($conexao_permissoes->resultado['comunicador_terreno'] & $nivelAluno)   != $nivelAluno?   'false' : 'true');
		$habilitado_chatTerrenoParaMonitores = (($conexao_permissoes->resultado['comunicador_terreno'] & $nivelMonitor) != $nivelMonitor? 'false' : 'true');
		$habilitado_chatTurmaParaAlunos		 = (($conexao_permissoes->resultado['comunicador_turma']   & $nivelAluno)   != $nivelAluno?   'false' : 'true');
		$habilitado_chatTurmaParaMonitores	 = (($conexao_permissoes->resultado['comunicador_turma']   & $nivelMonitor) != $nivelMonitor? 'false' : 'true');
		$habilitado_chatAmigoParaAlunos		 = (($conexao_permissoes->resultado['comunicador_amigo']   & $nivelAluno)   != $nivelAluno?   'false' : 'true');
		$habilitado_chatAmigoParaMonitores	 = (($conexao_permissoes->resultado['comunicador_amigo']   & $nivelMonitor) != $nivelMonitor? 'false' : 'true');
		$habilitado_chatPrivadoParaAlunos	 = (($conexao_permissoes->resultado['comunicador_privado'] & $nivelAluno)   != $nivelAluno?   'false' : 'true');
		$habilitado_chatPrivadoParaMonitores = (($conexao_permissoes->resultado['comunicador_privado'] & $nivelMonitor) != $nivelMonitor? 'false' : 'true');
		
		//echo $conexao_permissoes->resultado['comunicador_terreno'].' resultado = '.(($conexao_permissoes->resultado['comunicador_terreno'] & $nivelAluno) != $nivelAluno)."<BR>";
		//echo $conexao_permissoes->resultado['comunicador_turma'].' resultado = '.(($conexao_permissoes->resultado['comunicador_turma'] & $nivelAluno) != $nivelAluno)."<BR>";
		//echo $conexao_permissoes->resultado['comunicador_amigo'].' resultado = '.(($conexao_permissoes->resultado['comunicador_amigo'] & $nivelAluno) != $nivelAluno)."<BR>";
		//echo $conexao_permissoes->resultado['comunicador_privado'].' resultado = '.(($conexao_permissoes->resultado['comunicador_privado'] & $nivelAluno) != $nivelAluno)."<BR>";
		
		/*---------------------------------------------------
		*	Impress�o dos dados pesquisados
		---------------------------------------------------*/
		$now = comum_arrumar_data_hora($now);
		
		// TODO: PEGAR NOME DO COL�GIO
		$colegio = "Col�gio do Planeta Rooda 2"; // DEBUG!
		
		$dados_exportar = "&turma=".$nomeTurma;
		$dados_exportar.= "&colegio=".$colegio;
		$dados_exportar.= "&mensagemLocalizacao=".$mensagemLocalizacao;
		
		//turma
		$dados_exportar.= "&turma_id=".$turma_id;
		
		//permiss�es
		$dados_exportar.= "&permissao_batePapo=".$permissao_batePapo;
		$dados_exportar.= "&permissao_biblioteca=".$permissao_biblioteca;
		$dados_exportar.= "&permissao_blog=".$permissao_blog;
		$dados_exportar.= "&permissao_portfolio=".$permissao_portfolio;
		$dados_exportar.= "&permissao_forum=".$permissao_forum;
		$dados_exportar.= "&permissao_planetaArte=".$permissao_planetaArte;
		$dados_exportar.= "&permissao_planetaPergunta=".$permissao_planetaPergunta;
		$dados_exportar.= "&permissao_aulas=".$permissao_aulas;
		$dados_exportar.= "&habilitado_chatTerrenoParaAlunos=".$habilitado_chatTerrenoParaAlunos;
		$dados_exportar.= "&habilitado_chatTerrenoParaMonitores=".$habilitado_chatTerrenoParaMonitores;
		$dados_exportar.= "&habilitado_chatTurmaParaAlunos=".$habilitado_chatTurmaParaAlunos;
		$dados_exportar.= "&habilitado_chatTurmaParaMonitores=".$habilitado_chatTurmaParaMonitores;
		$dados_exportar.= "&habilitado_chatAmigoParaAlunos=".$habilitado_chatAmigoParaAlunos;
		$dados_exportar.= "&habilitado_chatAmigoParaMonitores=".$habilitado_chatAmigoParaMonitores;
		$dados_exportar.= "&habilitado_chatPrivadoParaAlunos=".$habilitado_chatPrivadoParaAlunos;
		$dados_exportar.= "&habilitado_chatPrivadoParaMonitores=".$habilitado_chatPrivadoParaMonitores;
		
		//planeta
		$dados_exportar.= "&planeta_tipo=".$planeta_tipo;
		$dados_exportar.= "&planeta_aparencia=".$planeta_aparencia;
		$dados_exportar.= "&ehQuarto=".$ehQuarto;
		
		//personagem
		$dados_exportar.= "&personagem_id=".$personagem_id;
		$dados_exportar.= "&personagem_avatar_1=".$personagem_avatar_1;
		$dados_exportar.= "&personagem_linha_chat=".$personagem_linha_chat;
		$dados_exportar.= "&personagem_fala=".$personagem_fala;
		$dados_exportar.= "&personagem_contatos="."hm";//$contatos; // DEBUG
		$dados_exportar.= "&personagem_animacao=".$personagem_animacao;
		$dados_exportar.= "&personagem_cabelos=".$personagem_cabelos;
		$dados_exportar.= "&personagem_olhos=".$personagem_olhos;
		$dados_exportar.= "&personagem_cor_pele=".$personagem_cor_pele;
		$dados_exportar.= "&personagem_cor_cinto=".$personagem_cor_cinto;
		$dados_exportar.= "&personagem_cor_luvas_botas=".$personagem_cor_luvas_botas;
		$dados_exportar.= "&personagem_posicao_x=".$personagem_posicao_x;
		$dados_exportar.= "&personagem_posicao_y=".$personagem_posicao_y;
		$dados_exportar.= "&personagem_cor_texto=".$personagem_cor_texto;
		$dados_exportar.= "&personagem_velocidade=".$personagem_velocidade;
		$dados_exportar.= "&personagem_chat_id=".$personagem_chat_id;
		$dados_exportar.= "&ultimo_terreno_id=".$personagem_ultimo_terreno_id;
		
		//usu�rio
		$dados_exportar.= "&usuario_id=".$usuario_id;
		$dados_exportar.= "&quarto_id=".$usuario_quarto_id;
		$dados_exportar.= "&usuario_nivel=".$usuario_nivel;
		$dados_exportar.= "&usuario_grupo_base=".$usuario_grupo_base;
		$dados_exportar.= "&ultima_atualizacao=".$now;
		
		$dados_exportar = $dados_exportar; //?????????????????????????????????????
		$dados_exportar.= "&personagem_nome=".$personagem_nome;
		$dados_exportar.= "&nomeEscola=".$nomeEscola;
		
		echo "$dados_exportar";
	break;

	case 4:
	/*---------------------------------------------------
	*	Envia as principais variaveis do sistema para a inicializa��o -  segunda parte - posi��es dos objetos
	---------------------------------------------------*/
		$terreno_personagem_id=$_SESSION['SS_terreno_id'];
		$personagem_id = $_POST['personagem_id'];
		$personagem_animacao	= "default";

		//atualiza a anima��o de entrada do personagem - Diogo - 02.08.11
		$bd = new conexao();
		$bd->solicitar("UPDATE $tabela_personagens SET personagem_animacao='$personagem_animacao' WHERE personagem_id=$personagem_id");

		//procura todos os dados do terreno atual e de seus vizinhos
		$pesquisaTerrenosVizinhos = new conexao();
		$pesquisaTerrenosVizinhos->solicitar("SELECT * FROM $tabela_terrenos WHERE terreno_id=$terreno_personagem_id");
		$planeta_personagem_id = $pesquisaTerrenosVizinhos->resultado['terreno_grupo_id'];
		$pesquisaPlaneta = new conexao();
		$pesquisaPlaneta->solicitar("SELECT * FROM Planetas WHERE Id = $planeta_personagem_id");
		$tipoPlaneta = $pesquisaPlaneta->resultado['aparencia'];
		$mensagemLocalizacao = montarMensagemDescricaoTerreno($planeta_personagem_id);
		$pesquisaTerrenosVizinhos->solicitar("SELECT * FROM $tabela_terrenos 
											WHERE terreno_grupo_id=$planeta_personagem_id
											ORDER BY terreno_indice ASC");

		$pesquisaObjetosTerreno = new conexao();
		for($k=0; $k<$pesquisaTerrenosVizinhos->registros; $k++){
			if($pesquisaTerrenosVizinhos->resultado['terreno_id'] == $terreno_personagem_id){
				$indice_planeta_terreno_personagem = $k;
			}
			$terreno_id = $pesquisaTerrenosVizinhos->resultado['terreno_id'];
			$pesquisaObjetosTerreno->solicitar("SELECT * FROM `$tabela_objetos` WHERE objeto_terreno_id='$terreno_id'");
			for($i=0;$i<$pesquisaObjetosTerreno->registros;$i++) {
				$objeto_id					= $pesquisaObjetosTerreno->resultado['objeto_id'];
				$objeto_movieclip			= $pesquisaObjetosTerreno->resultado['objeto_movieclip'];
				$objeto_frame				= $pesquisaObjetosTerreno->resultado['objeto_frame'];
				$objeto_link				= $pesquisaObjetosTerreno->resultado['objeto_link'];
				$objeto_fala				= $pesquisaObjetosTerreno->resultado['objeto_fala'];
				$objeto_terreno_posicao_x	= $pesquisaObjetosTerreno->resultado['objeto_terreno_posicao_x'];
				$objeto_terreno_posicao_y	= $pesquisaObjetosTerreno->resultado['objeto_terreno_posicao_y'];
				$objeto_permissao_ver		= $pesquisaObjetosTerreno->resultado['objeto_permissao_ver'];
				$objeto_permissao_acessar	= $pesquisaObjetosTerreno->resultado['objeto_permissao_acessar'];
				$dados .= '&objeto_movieclip'			.$k.','.$i.'='.$objeto_movieclip;
				$dados .= '&objeto_frame'				.$k.','.$i.'='.$objeto_frame;
				$dados .= '&objeto_link'				.$k.','.$i.'='.$objeto_link;
				$dados .= '&objeto_fala'				.$k.','.$i.'='.$objeto_fala;
				$dados .= '&objeto_terreno_posicao_x'	.$k.','.$i.'='.$objeto_terreno_posicao_x;
				$dados .= '&objeto_terreno_posicao_y'	.$k.','.$i.'='.$objeto_terreno_posicao_y;
				$dados .= '&objeto_permissao_ver'		.$k.','.$i.'='.$objeto_permissao_ver;
				$dados .= '&objeto_permissao_acessar'	.$k.','.$i.'='.$objeto_permissao_acessar;
				$dados .= '&objeto_id'					.$k.','.$i.'='.$objeto_id; 
			
				$pesquisaObjetosTerreno->proximo();
			}
			$terreno_id 				= $pesquisaTerrenosVizinhos->resultado['terreno_id'];
			$terreno_nome 				= $pesquisaTerrenosVizinhos->resultado['terreno_nome'];
			$terreno_chat 				= $pesquisaTerrenosVizinhos->resultado['chat_id'];
			$terreno_permissaoEditar 	= $pesquisaTerrenosVizinhos->resultado['terreno_permissao_edicao'];
			
			$dados = "";
			$dados .= '&numero_objetos_no_terreno'				.$k.'='.$pesquisaObjetosTerreno->registros; 
			$dados .= '&terreno_id'								.$k.'='.$terreno_id; 
			$dados .= '&terreno_nome'							.$k.'='.$terreno_nome; 
			$dados .= '&terreno_solo'							.$k.'='.$tipoPlaneta; 
			$dados .= '&terreno_chat'							.$k.'='.$terreno_chat; 
			$dados .= '&terreno_permissaoEditar'				.$k.'='.$terreno_permissaoEditar; 
			
			$pesquisaTerrenosVizinhos->proximo();
		}

		// impress�o dos dados pesquisados
		$objetosNoTerreno  = $pesquisaObjetosTerreno->registros;
		/*$dados  = $dados . '&indice_planeta_terreno_personagem=' . $indice_planeta_terreno_personagem;*/
		$dados  = $dados . '&numeroTerrenos=' . $pesquisaTerrenosVizinhos->registros;
		$dados  = $dados . '&mensagemLocalizacao=' . $mensagemLocalizacao;
		echo "$dados";
		
	break;

	case 6:
	/*---------------------------------------------------
	*	Chama aplica��o - 13/01/09 - eD
	---------------------------------------------------*/
		$_SESSION['SS_link_pai'] = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; //vari�vel que cont�m o endere�o da p�gina que chamou a aplica��o
	break;

	case 26:
	/*---------------------------------------------------
	*	Reseta a anima��o de entrada para default. - Diogo - 02.08.11
	---------------------------------------------------*/
		$personagem_id				= $_POST["personagem_id"];
		$personagem_animacao		= "default";
	
		$bd = new conexao();
		$bd->solicitar("UPDATE $tabela_personagens SET personagem_animacao='$personagem_animacao' WHERE personagem_id=$personagem_id");
	break;

	case 27:
	/*---------------------------------------------------
	*	Aviso para os ops de que o terreno foi editado. - Diogo - 19.08.11
	---------------------------------------------------*/
		$terreno_id = $_POST['terreno_id'];
		$terrenoStatus = new conexao();
		$terrenoStatus->solicitar("UPDATE `$tabela_terrenos` SET terreno_status='ok' WHERE terreno_id='$terreno_id'");
	break;
}
