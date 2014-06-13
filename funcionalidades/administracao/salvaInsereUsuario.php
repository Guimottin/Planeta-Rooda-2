<?php
require('../../cfg.php');
require('../../bd.php');
require('../../turma.class.php');
require('../../funcoes_aux.php');

$q = new conexao();

$associacao = $_POST['associacao'];
$codTurma = $_POST['turmaLista'];//para saber por qual turma o usuario acessou o sistema
$turma = new Turma("",0,"",0,0,0,0);
$turma->openTurma($codTurma);

$alunos = explode(';', $_POST['ids_alunos']);

$numeroAlunos = sizeof($alunos);

$parteDinamica = array();

for($i=0; $i<$numeroAlunos; $i++){
	$codUsuario = $q->sanitizaString($alunos[$i]);
    $usuario = new Usuario();
    $usuario->openUsuario($codUsuario);

    if($usuario->pertenceTurma($codTurma)){
        $q->solicitar("UPDATE TurmasUsuario
                          SET associacao='$associacao'
                            WHERE codTurma = '$codTurma' AND codUsuario = '$codUsuario'");
    }
    else{
        $q->solicitar("INSERT INTO TurmasUsuario(codTurma, codUsuario, associacao)
				VALUES
				    ('$codTurma',
				    '$codUsuario',
				    '$associacao')");
    }

}

magic_redirect("insereUsuario.php?turma=".$codTurma);