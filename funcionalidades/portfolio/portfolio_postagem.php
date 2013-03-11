<?php
require("../../reguaNavegacao.class.php");
require("../../usuarios.class.php");
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Planeta ROODA 2.0</title>
<link type="text/css" rel="stylesheet" href="../../planeta.css" />
<link type="text/css" rel="stylesheet" href="portfolio.css" />
<script type="text/javascript" src="../../jquery.js"></script>
<script type="text/javascript" src="../../jquery-ui-1.8.1.custom.min.js"></script>
<script type="text/javascript" src="../../planeta.js"></script>
<script type="text/javascript" src="portfolio.js"></script>
<script type="text/javascript" src="../../postagem_wysiwyg.js"></script>
<script type="text/javascript" src="../lightbox.js"></script>

<!--[if IE 6]>
<script type="text/javascript" src="planeta_ie6.js"></script>
<![endif]-->
<?php
require_once("../../bd.php");
require_once("../../cfg.php");
require_once("../../funcoes_aux.php");

$projeto_id	= isset($_GET['projeto_id'])	? $_GET['projeto_id']	: NULL;
$post_id	= isset($_GET['post_id'])		? $_GET['post_id']		: 0;

if (!is_numeric($projeto_id) or !is_numeric($post_id))die("</head>\n<body>\n<h2><center>WARNING WARNING WILL ROBINSON<br />\nA ID DO PROJETO ESTA INCORRETA PROVAVELMENTE PORQUE FOI DIGITADA A MAO\n</h2></center>\n</html>");

$update = isset($_GET['update']) ? "1" : "0";

$funcionalidade_id = TIPOPORTFOLIO;
$funcionalidade_tipo = $projeto_id;

$turma = is_numeric($_GET['turma']) ? $_GET['turma'] : die("</head>\n<body>\n<h2><center>A id da turma precisa estar setada para acessar, por favor volte.\n</h2></center>\n</html>");

$perm = checa_permissoes(TIPOPORTFOLIO, $turma);
if($perm == false){
	die("Desculpe, mas o Portfolio esta desabilitado para esta turma.");
}
?>

<script language="javascript">
function ajusta_img(){
	if (navigator.appVersion.substr(0,3) == "4.0"){ //versao do ie 7
		$('#cont_img3').css('width','436px');
		$('#cont_img3').css('padding-right','20px');
		$('#cont_img').css('height','170px');
	}
}

var objContent;

function Init() {
	var ua = navigator.appName; 
	if(ua == "Netscape")
		objContent = document.getElementById('text_post').contentDocument;
	else
		objContent = document.getElementById('text_post').document;
	objContent.designMode = "On";
	
	objContent.body.style.fontFamily = 'Verdana';
	objContent.body.style.fontSize = '11px';
}
</script>
</head>

<body onload="atualiza('ajusta()');inicia(); checar(); ajusta_img(); Init(); fakeFile('botao_upload_frame', 'arquivo_frame', 'falso_frame'); fakeFile('botao_upload_frame_ins','arquivo_frame_ins', 'falso_frame_ins');">
	<div id="descricao"></div>
	
	<div id="fundo_lbox"></div>
	
	<div id="light_box" class="bloco">
		<img src="../../images/botoes/bt_fechar.png" class="fechar_coments" onmousedown="abreFechaLB()" />
<?php
if($_SESSION['user']->podeAcessar($perm['portfolio_adicionarArquivos'], $turma))
{
?>
		<div id="imagem_lbox">
			<h1>INSERIR IMAGEM</h1>
			<ul class="sem_estilo" style="line-height:25px">
				<li><input type="radio" id="troca_img1" class="select_img" name="select_img" checked="checked" onclick="modo=1"/>Procurar no Computador</li>
				<li><input type="radio" id="troca_img2" class="select_img" name="select_img" onclick="modo=2"/>Imagem da Web</li>
				<li><input type="radio" id="troca_img3" class="select_img" name="select_img" onclick="modo=3"/>Procurar nas imagens já enviadas</li>
				<li>
					<div id="cont_img">
						<ul id="cont_img1">
							<form method="post" enctype="multipart/form-data" action="../../uploadImage.php?funcionalidade_id=<?=$funcionalidade_id?>&amp;funcionalidade_tipo=<?=$funcionalidade_tipo?>" target="alvoAJAXins">
								<input type="hidden" name="MAX_FILE_SIZE" value="2000000" /> 
								<input name="userfile" type="file" id="arquivo_frame_ins" class="upload_file" style="" onchange="trocador('falso_frame_ins', 'arquivo_frame_ins')" />
								<input name="falso" type="text" id="falso_frame_ins" />
								<img src="../../images/botoes/bt_procurar_arquivo.png" id="botao_upload_frame_ins" />
								<input type="submit" name="upload" value="upload!" />
							</form><br />
							<iframe id="alvoAJAXins" name="alvoAJAXins" style="display: none;" src=""></iframe>
							<iframe id="editavel" name="editavel" frameborder="0" src="">Por favor, atualize seu navegador.</iframe>
						</ul>
						<ul id="cont_img2">
							<li><input type="text" value="http://" id="imagefromurl" /></li>
							<li style="margin-top:-5px">Endereço da imagem</li>
						</ul>
						<div id="cont_img3">
						<table width="100%">
							<tr>
<?php
							//	Dumpando a lista de imagens que tem no blog

							$consulta = new conexao();

							/*\
							 *	SELECT arquivo FROM $tabela_arquivos WHERE tipo LIKE 'image/%'
							 *	Pega o BLOB de todas as imagens pra dar resize.
							\*/
							global $tabela_arquivos;
							$consulta->solicitar("SELECT arquivo_id FROM $tabela_arquivos WHERE tipo LIKE 'image/%'");

							for($i=0 ; $i<count($consulta->itens);$i++) {
								$id = $consulta->resultado['arquivo_id']; 
								if ($i % 5 == 0 && $i != 0) { echo "</tr><tr>"; } // 5 imagens por linha, sabe.
?>
							<td><? echo '<div class="img_enviadas" id="galeria'.$id.'" ><img src="../../image_output.php?file='.$id.'" onClick="fromgallery('.$id.')"/>'; ?></div></td>
<?php
								$consulta->proximo();
							}
						?>
							</tr>
						</table>
						</div>
					</div>
				</li>
				<li>
					<div align="right" onclick="addImage()"><img src="../../images/botoes/bt_confir_pq.png" /></div>
				</li>
			</ul>
		</div>
		
		<div id="arquivo_lbox">
			<h1>ANEXAR ARQUIVO</h1>
			<ul class="sem_estilo" style="line-height:25px">
				<li><input type="radio" id="troca_arq1" class="select_arq" name="select_arq" checked="checked" />Procurar no Computador</li>
				<li><input type="radio" id="troca_arq2" class="select_arq" name="select_arq"/>Procurar nos arquivos já enviados</li>
				<li>
					<div id="cont_arq">
						<ul id="cont_arq1">
							<li id="procurar_arq">
								Adicionar novo arquivo:
								<form method="post" enctype="multipart/form-data" action="../../uploadImage.php?funcionalidade_id=<?=$funcionalidade_id?>&amp;funcionalidade_tipo=<?=$funcionalidade_tipo?>" target="alvoAJAX">
									<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
									<input type="hidden" name="gambiarra" value="3337333" />
									<input name="userfile" type="file" id="arquivo_frame" class="upload_file" style="" onchange="trocador('falso_frame', 'arquivo_frame')" />
									<input name="falso" type="text" id="falso_frame" />
									<img src="../../images/botoes/bt_procurar_arquivo.png" id="botao_upload_frame" />
									<input type="submit" name="upload" value="upload!" />
								</form>
								<iframe id="alvoAJAX" name="alvoAJAX" src="" style="display: none;"></iframe>
								<iframe id="previewarquivos" name="previewarquivos" src="" frameborder="0"></iframe>
						</ul>
						<ul id="cont_arq2">
<?php
							$consulta = new conexao();
							$consulta->solicitar("SELECT nome,arquivo_id FROM $tabela_arquivos WHERE funcionalidade_tipo='$funcionalidade_tipo' AND funcionalidade_id='$funcionalidade_id'");

							for($i=0 ; $i<$consulta->registros;$i++) {
?>
								<li class="enviado<?=($i % 2) + 1?>"><input type="checkbox" id="file<?=$consulta->resultado['arquivo_id']?>" onclick="addRemove(<?=$consulta->resultado['arquivo_id']?>, '<?=$consulta->resultado['nome']?>')" /><?=$consulta->resultado['nome']?></li>
<?php
								$consulta->proximo();
							}
?>
						</ul>
					</div>
				</li>
				<li>
					<div align="right"><input type="image" src="../../images/botoes/bt_confir_pq.png" /></div>
				</li>
			</ul>
		</div>
<?php
}

if($_SESSION['user']->podeAcessar($perm['portfolio_adicionarLinks'], $turma))
{
?>
		<div id="link_lbox">
			<h1>INSERIR LINK</h1>
			<ul class="sem_estilo">
				<li>Texto a ser exibido: <input id="addlinktext" type="text" /></li>
				<li style="margin-bottom:172px">Link para: <input id="addlinkurl" type="text" value="http://" /></li>
				<li>
					<div align="right"><img src="../../images/botoes/bt_confir_pq.png" alt="Confirmar" onclick="addLink()" /></div>
				</li>
			</ul>
		</div>
<?php
}
?>
	</div>
	
	<div id="topo">
		<div id="centraliza_topo">
			<?php 
				$regua = new reguaNavegacao();
				$regua->adicionarNivel("Portfólio", "portfolio_inicio.php", false);
				$regua->adicionarNivel("Postagem");
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
					 <div id="personagem"><img src="../../images/desenhos/ajudante.png" height=145 align="left" alt="Ajudante" /></div>
					<div id="rel"><p id="balao">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
					Etiam eget ligula eu lectus lobortis condimentum. Aliquam nonummy auctor massa. Pellentesque 
					habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p></div>
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
		<form name="fConteudo" id="postFormId" action="formProcessing.php?turma=<?=$turma?>" onsubmit="return gravaConteudo()" method="post">
			<input type="hidden" name="text" value="" />
			<div class="bts_cima">
				<a href="portfolio_projeto.php?projeto_id=<?=$projeto_id?>&amp;turma=<?=$turma?>" align="left" >
					<img src="../../images/botoes/bt_cancelar.png" border="0" align="left"/>
				</a>
				<input type="image" id="responder_topico" src="../../images/botoes/bt_confirm.png" align="right"/>
			</div>
			<div id="info_post" class="bloco">
				<h1>NOVA POSTAGEM</h1>
				<ul class="sem_estilo">
					<li>Título</li>
					<li><input name="titulo_post" type="text" class="port_info"/></li>
					<li>Tags <span class="exemplo">(Escreva as tags separadas por ponto e vírgula. Ex: Matemática; Português; Artes)</span></li>
					<li><input name="tags_post" type="text" class="port_info"/></li>
						<li style="height:22px; margin-bottom:4px; margin-top:10px">
							<div class="tool_bt" id="alt_negrito"><img src="../../images/botoes/tool_negrito.png" onClick="doBold()" /></div>
							<div class="tool_bt" id="alt_italico"><img src="../../images/botoes/tool_italico.png" onClick="doItalic()" /></div>
							<div class="tool_bt" id="alt_sublinhado"><img src="../../images/botoes/tool_sublinhado.png" onClick="doUnderline()" /></div>
							<div class="tool_bt" id="alt_tamanho"><img src="../../images/botoes/tool_tamanho.png" onClick="doSize()" /></div>
<?php
if($_SESSION['user']->podeAcessar($perm['portfolio_adicionarLinks'], $turma))
{
?>
							<div class="tool_bt" id="alt_link"><img src="../../images/botoes/tool_link.png" /></div>
<?php
}

if($_SESSION['user']->podeAcessar($perm['portfolio_adicionarArquivos'], $turma))
{
?>
							<div class="tool_bt" id="alt_arquivo"><img src="../../images/botoes/tool_arquivo.png" /></div>
							<div class="tool_bt" id="alt_imagem"><img src="../../images/botoes/tool_imagem.png" /></div>
<?php
}
?>
						</li>
					<li><iframe id="text_post" width="100%"></iframe></li>
					<input type="hidden" name="projeto_id" value="<?=$projeto_id?>"> <!--Para posterior edição-->
					<input type="hidden" name="post_id" value="<?=$post_id?>">
					<input type="hidden" name="update" value="<?=$update?>">
				</ul>
			</div>
			<div class="bts_baixo">
				<a href="portfolio_projeto.php?projeto_id=<?=$_GET['projeto_id']?>&amp;turma=<?=$turma?>" align="left" >
					<img src="../../images/botoes/bt_cancelar.png" border="0" align="left"/>
				</a>
				<input type="image" onClick="postForm.submit()" src="../../images/botoes/bt_confirm.png" align="right"/>
			</div>
		</form>
		</div><!-- Fecha Div conteudo -->
		</div><!-- Fecha Div conteudo_meio -->
		<div id="conteudo_base">
		</div><!-- para a imagem de fundo da base -->
			
	</div><!-- fim da geral -->


</body>
</html>


