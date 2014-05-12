<?php 
require('../../cfg.php');
require('../../bd.php');
require('../../turma.class.php');
require('../../planeta.class.php');
require('../../terreno.class.php');

$q = new conexao();

$nomeTurma = $_POST['turma'];
$descricao = $_POST['descricao']; 

$idProfResponsavel = $_POST['idProfResponsavel'];
$aparenciaPlaneta = $_POST['tipoTerreno'];

$novoTerrenoPrincipal = new Terreno(0,0,0); //cria o terrenoPrincipal a ser atribuido ao novoPlaneta.
$novoTerrenoPatio = new Terreno(0,0,1); //cria o terrenoPatio a ser atribuido ao novoPlaneta.

$novoTerrenoPrincipal->salvar();
$novoTerrenoPatio->salvar();

$novoPlaneta = new Planeta($aparenciaPlaneta,0,$novoTerrenoPrincipal->getId(),$novoTerrenoPatio->getId());
$novoPlaneta->salvar();

$novaTurma = new Turma($nomeTurma,$idProfResponsavel,$descricao,0,0,0,$novoPlaneta->getId());
$novaTurma->salvar();

$alunos = explode(';', $_POST['ids_alunos']);

$numeroAlunos = sizeof($alunos);

$parteDinamica = array();
for($i=0; $i<$numeroAlunos; $i++){
	$codUsuario = $q->sanitizaString($alunos[$i]);
	$parteDinamica[$i] = "('".$novaTurma->getId()."', '$codUsuario', 16)";//$novaTurma->getId() subistituindo $codTurma
}

$q->solicitar("INSERT INTO TurmasUsuario(codTurma, codUsuario, associacao)
				VALUES".implode(',', $parteDinamica));
				