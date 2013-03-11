<?php
session_start();

require("../../cfg.php");
require("../../bd.php");
require("../../funcoes_aux.php");
require("../../usuarios.class.php");
require("comentario.class.php");
require("desenho.class.php");
require("../../reguaNavegacao.class.php");

$post_id = 1; //TODO: DEBUG
$user_id = $_SESSION['SS_usuario_id'];
$turma = isset($_GET['turma'])?$_GET['turma']:0;
$ARTE = new Arte($user_id, $turma);




function proximo_ano () { // A SER USADO SOMENTE NOS OPTIONS LÁ EMBAIXO
	$limite = new conexao();
	global $tabela_ArteDesenhos;
	$limite->solicitar("SELECT DISTINCT DATE_FORMAT(Data, '%Y') AS Data FROM $tabela_ArteDesenhos");
	
	for ($i=0; $i < $limite->registros; $i++){
		$ano = $limite->resultado['Data'];
		echo "								<option value=\"$ano\">$ano</option>\n";
		$limite->proximo();
	}
}

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Planeta ROODA 2.0</title>
<link type="text/css" rel="stylesheet" href="../../planeta.css" />
<link type="text/css" rel="stylesheet" href="../portfolio/portfolio.css" />
<link type="text/css" rel="stylesheet" href="../forum/forum.css" />
<link type="text/css" rel="stylesheet" href="../blog/blog.css" />
<link type="text/css" rel="stylesheet" href="arte.css" />
<script type="text/javascript" src="../../jquery.js"></script>
<script type="text/javascript" src="../../planeta.js"></script>
<script type="text/javascript" src="p_arte.js"></script>
<script type="text/javascript" src="../blog/blog.js"></script>
<script type="text/javascript" src="../lightbox.js"></script>
<!--[if IE 6]>
<script type="text/javascript" src="planeta_ie6.js"></script>
<![endif]-->

</head>

<body onload="atualiza('ajusta()');inicia();">
<div id="descricao"></div>
<div id="fundo_lbox"></div>
<div id="light_box" class="bloco">
	<h1>COMENTÁRIOS</h1>
	<img src="../../images/botoes/bt_fechar.png" class="fechar_coments" onmousedown="abreFechaLB()" />
	<div class="recebe_coments">
	<ul class="sem_estilo" id="ie_coments">
		<ul>
		<li class="tabela_blog">

		</li>
		<li class="tabela_blog">

		</li>
	</ul>
		<li id="novo_coment">
			POSTAR NOVO COMENTÁRIO
		</li>
		<li>
			<textarea class="msg_dimensao" rows="10"></textarea>
		</li>
		<li>
			<div class="enviar" align="right">
				<img src="../../images/botoes/bt_confir_pq.png" />
			</div>
		</li>
	</ul>
	</div>
</div>


<div id="topo">
	<div id="centraliza_topo">
		<?php 
			$regua = new reguaNavegacao();
			$regua->adicionarNivel("Arte");
			$regua->imprimir();
		?>
		<p id="bt_ajuda"><span class="troca">OCULTAR AJUDANTE</span><span style="display:none" class="troca">CHAMAR AJUDANTE</span></p>
	</div>
</div>

<div id="geral">

<!-- **************************
			cabecalho
***************************** -->
<div id="cabecalho">
	<div id="ajuda">
		<div id="ajuda_meio">
			<div id="ajudante">
				<div id="personagem"></div>
				<div id="rel"><p id="balao">
		O Planeta Arte é um espaço pessoal utilizado para a criação de desenhos simples. Nele, você pode salvar seus desenhos para acessá-los quando quiser, também podendo comentar e visualizar desenhos dos seus colegas.</p></div>
			</div>
		</div>
		<div id="ajuda_base"></div>
	</div>
</div><!-- fim do cabecalho -->

<div id="conteudo_topo"></div><!-- para a imagem de fundo do topo -->
<div id="conteudo_meio"><!-- para a imagem de fundo do meio -->

<!-- **************************
			conteudo
***************************** -->
	<div id="conteudo"><!-- tem que estar dentro da div 'conteudo_meio' -->
		<div id="esq">
			<a href="planeta_arte_desenho.php?turma=<?php echo $turma;?>"><img style="margin-bottom:30px" src="../../images/botoes/bt_criar_desenho.png" /></a>
			<div id="projetos" class="bloco">
				<h1>
					<div class="abas_port aberto" id="aba_andamento"> MEUS DESENHOS</div>
					<div class="abas_port fechado" id="aba_encerrado"> DESENHOS DOS COLEGAS</div>
				</h1>
				
				
				<div id="proj_andamento">
<?php
$ARTE->meusDesenhos();

for ($i = 0; $i < $ARTE->contador; $i++){
	$cor = "cor".(($i%2)+1);
	$id = $ARTE->desenhos[$i]->id;
	$tid = $ARTE->desenhos[$i]->turma;

	$parametros = "desenho=".$id;
	$parametros = $parametros."&amp;turma=".$tid;
	$parametros = $parametros."&amp;existente=1";

	$data = $ARTE->desenhos[$i]->data;
	$titulo =  $ARTE->desenhos[$i]->titulo;
?>
<div class="<?php echo $cor; ?>">
	<ul class="sem_estilo">
		<a href="planeta_arte_desenho.php?<?php echo $parametros;?>"><div id="imagem" class="lista_imagem">
		<?php
		  //<div id="imagem" class="lista_imagem"></div>
		  echo $ARTE->desenhos[$i]->visualizar(80,0,"border:0;");
		?>
		</div>
		</a>
		</li>
		<li class="texto_port"><a href="planeta_arte_desenho.php?<?php echo $parametros;?>"><span class="dados"><?php echo $titulo; ?></span></a></li>
		<li><span class="dados">Data:</span><span class="valor"><?php echo $data;?></span></li>
		<a onmousedown="loadComentarios('light_box', 'comentarios.php', 'post_id=<?php echo $id ?>');abreFechaLB()" class="encerrar">[Ver comentários]</a>
		<a class="excluir" href="#<?php echo $id; ?>">[Excluir desenho]</a>
	</ul>
</div>
<?php
}
?>
				</div><!-- fim da div proj_andamento -->
				
				
				
				<div id="proj_encerrados">
<?php
$ARTE->desenhosDosColegas();

for ($i = 0; $i < $ARTE->contador; $i++){
	$cor = "cor".(($i%2)+1);
	if (isset($ARTE->desenhos[$i]->id)){
		$id = $ARTE->desenhos[$i]->id;
		$tid = $ARTE->desenhos[$i]->turma;
		$data = $ARTE->desenhos[$i]->data;
		$autor = $ARTE->desenhos[$i]->criador->nome;
		$titulo =  $ARTE->desenhos[$i]->titulo;
		$parametros = "desenho=".$id;
		$parametros = $parametros."&amp;turma=".$tid;
		$parametros = $parametros."&amp;existente=1";
?>
	<div class="<?php echo $cor; ?>">
		<ul class="sem_estilo">
			<a href="planeta_arte_desenho.php?<?php echo $parametros;?>"><div id="imagem" class="lista_imagem">
			<?php
			  //<div id="imagem" class="lista_imagem"></div>
			  echo $ARTE->desenhos[$i]->visualizar(80,0,"border:0;");
			?>
			</div>
			</a>
			<li class="texto_port"><a href="planeta_arte_desenho.php?<?php echo $parametros;?>"><span class="dados"><?php echo $titulo; ?></span></a></li>
			<li class="texto_port"><span class="dados">Autor:</span><span class="valor"><?php echo $autor; ?></span></li>
			<li><span class="dados">Data:</span><span class="valor"><?php echo $data; ?></span></li>
			<a onmousedown="loadComentarios('light_box', 'comentarios.php', 'post_id=<?php echo $id ?>');abreFechaLB()" class="encerrar">[Ver comentários]</a>
		</ul>
	</div>

<?php
	}
}
?>

			</div><!-- fim da div proj_encerrados -->
		</div><!-- fim da div projetos -->
	</div>
		<div class="bts_baixo">
			<img align="left" src="../../images/botoes/bt_voltar.png"/>
		</div>
	</div><!-- Fecha Div conteudo -->
	</div><!-- Fecha Div conteudo_meio -->	
	<div id="conteudo_base">
	</div><!-- para a imagem de fundo da base -->
	</div><!-- fim da geral -->
</body>
</html>