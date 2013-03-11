<?php
/*
*	Sistema do blog
*
*/
//$tabela_usuarios = "usuarios";
require_once("funcoes_aux.php");
require_once("cfg.php");
require_once("bd.php");
require_once("class/planeta.php");


class Usuario { //estrutura para o item post do blog
	var $id = 0;
	var $user = "";
	var $pass = "";
	var $birthday = "";
	var $name = "";
	var $email = "";
	var $personagemId = 0;
	var $nivel = 0;
	var $turmas = array();
	var $nivelAbsoluto = 0;
	private $dataUltimoLogin;
	
	function Usuario($id=0, $user="", $pass="", $birthday="", $name="", $email="", $personagem_id=0, $nivel=-1){
		$this->id = $id;
		$this->user = $user;
		$this->pass = $pass;
		$this->birthday = $birthday;
		$this->name = $name;
		$this->email = $email;
		$this->personagemId = $personagem_id;
		$this->nivel = $nivel;
	}
	
	// Recebe como parametro um id (inteiro maior que 0)
	// Segundo parametro n�o � usado, n�o removo por medo de quebrar algo.
	public function openUsuario($param , $param2="") {
		global $tabela_usuarios; global $tabela_turmasUsuario;
		$q = new conexao();
		$niveis = new conexao();

		$id = $param;
		$q->solicitar("SELECT * 
					  FROM $tabela_usuarios JOIN personagens ON usuario_personagem_id = personagem_id
					  WHERE usuario_id = '$id'");
		$numItens= count($q->itens);
		if($numItens == 0)
			return "Usuario inexistente (Id=$id)" ;
	
		$this->popular($q->resultado);
		
		// Agora preparamos para setar o n�vel
		
		$this->nivel = array();
		
		$niveis->solicitar("SELECT codTurma,associacao FROM $tabela_turmasUsuario WHERE codUsuario = ".$q->itens[0]['usuario_id']);
		for($i=0; $i < $niveis->registros; $i++){
			$this->setNivel($niveis->itens[$i]['codTurma'], $niveis->itens[$i]['associacao'] != 0 ? $niveis->itens[$i]['associacao'] : $this->getNivelAbsoluto());
		}
		return false;
	}

	/**
	*											 GETTERS & SETTERS
	*/
	private function setId($id)						{$this->id = $id;}
	private function setUser($user)					{$this->user = $user;}
	private function setPass($pass)					{$this->pass = $pass;}
	private function setBirthday($birthday)			{$this->birthday = $birthday;}
	private function setName($name)					{$this->name = $name;}
	private function setEmail($email)				{$this->email = $email;}
	private function setPersonagemId($personagemId)	{$this->personagemId = $personagemId;}
	private function setNivelAbsoluto($nivel)		{$this->nivelAbsoluto = $nivel;}
	private function setNivel($turma, $valor){
		if(!isset($this->nivel[$turma])){
			$this->nivel[$turma] = array();
		}
		array_push($this->nivel[$turma], $valor);
	}
	
	public function getId()			{return $this->id;}
	public function getUser()		{return $this->user;}
	public function getPass()		{return $this->pass;}
	public function getBirthday()	{return $this->birthday;}
	public function getName()		{return $this->name;}
	public function getEmail()		{return $this->email;}
	public function getPersonagemId(){return $this->personagemId;}
	private function getNivel($turma){return isset($this->nivel[$turma]) ? $this->nivel[$turma] : array($this->getNivelAbsoluto());} // $turma � o id da turma no banco de dados
	public function getNivelAbsoluto(){return $this->nivelAbsoluto;}
	public function getDataUltimoLogin(){return $this->dataUltimoLogin;}
	
	/**
	* Popula este usu�rio com o resultado de uma consulta no BD.
	*
	* @param Array<String,String> $resultadoBD Resultado da consulta correspondente a um usu�rio. Os nomes das colunas devem ser preservados.
	*/
	private function popular($resultadoBD){
		$this->setId($resultadoBD['usuario_id']);
		$this->setUser($resultadoBD['usuario_login']);
		$this->setPass($resultadoBD['usuario_senha']);
		$this->setBirthday($resultadoBD['usuario_data_aniversario']);
		$this->setName($resultadoBD['usuario_nome']);
		$this->setEmail($resultadoBD['usuario_email']);
		$this->setPersonagemId($resultadoBD['usuario_personagem_id']);
		$this->setNivelAbsoluto($resultadoBD['usuario_nivel']);
		//$this->dataUltimoLogin = $resultadoBD['personagem_ultimo_acesso'];
	}

	/*
	* Busca no BD usu�rios com nome parecido ao dado e os retorna em um array.
	*
	* @param String nome Nome que � substring dos nomes dos usu�rios que devem ser retornados.
	* @return Array<Usuario> Todos os usu�rios que t�m o nome dado.
	*/
	public static function buscaPorNome($nome){
		$resultados = array();
		$conexao = new conexao();
		$conexao->solicitar("SELECT * FROM usuarios WHERE usuario_nome LIKE '%".$nome."%'");
		for($i=0; $i<$conexao->registros; $i++){
			array_push($resultados, new Usuario());
			$resultados[$i]->popular($conexao->resultado);
			$conexao->proximo();
		}
		return $resultados;
	}
	
	/**
	* @return as ids no BD de todas as turmas deste usu�rio em um array.
	*/
	public function getTurmas(){
		$turmas = array();
		$conexaoTurmas = new conexao();
		$conexaoTurmas->solicitar("SELECT TU.codTurma, T.codTurma 
									FROM TurmasUsuario AS TU, Turmas AS T
									WHERE TU.codUsuario = '".($this->id)."'
										OR T.profResponsavel = '".($this->id)."'");
		for($i=0; $i<$conexaoTurmas->registros; $i++){
			$turmas[$i] = $conexaoTurmas->resultado['codTurma'];
			$conexaoTurmas->proximo();
		}
		
		$turmasSemDuplicatas = array_unique($turmas);
		
		return $turmasSemDuplicatas;
	}
	
	/*
	* @return Todos os planetas que o usu�rio pode acessar, em um array com objetos da classe Planeta.
	*/
	public function getPlanetasQuePodeAcessar(){
		$planetasQuePodeAcessar = array();
		$conexaoIdsPlanetas = new conexao();
		$conexaoIdsPlanetas->solicitar("SELECT P.*
										FROM TurmasUsuario AS TU JOIN Planetas AS P ON P.Turma = TU.codTurma
										WHERE TU.codUsuario = ".($this->id)."
										GROUP BY P.Nome");
		$planetasInseridos = 0;
		for($i=0; $i<$conexaoIdsPlanetas->registros; $i++){
			$planeta = Planeta::getPorId($conexaoIdsPlanetas->resultado['Id']);
			$planetaExiste = $planeta != null;
			if($planetaExiste){
				$planetasQuePodeAcessar[$planetasInseridos] = $planeta;
				$planetasInseridos++;
			}
			$conexaoIdsPlanetas->proximo();
		}
		return $planetasQuePodeAcessar;
	}
	
	/**
	* @param 	int	$nivelDeCorte	N�vel que servir� para pesquisar as turmas. � importante que este n�vel seja at�mico e n�o a soma de v�rios n�veis.
	* @return 	Array<Turma>		As turmas em que o usu�rio desempenha (no m�ximo) o papel _nivelDeCorte.
	*								Isto �:
	*									se _nivelDeCorte = aluno, retornar� somente turmas em que o usu�rio � somente aluno.
	*									se _nivelDeCorte = monitor, retornar� somente turmas em que o usu�rio � aluno e monitor ou somente monitor.
	*									se _nivelDeCorte = professor, retornar� somente turmas em que o usu�rio � aluno, monitor e professor, ou aluno e professor, ou monitor e professor.
	*/
	public function buscaTurmasComNivel(/*int*/ $nivelDeCorte){
		global $nivelAluno;
		global $nivelMonitor;
		global $nivelProfessor;
		
		$conexaoTurmas = new conexao();
		$conexaoTurmas->solicitar("SELECT *
								FROM TurmasUsuario
								WHERE codUsuario = ".$this->getId()."
								GROUP BY codTurma");
		$turmasUsuario = array();
		
		for($i=0; $i<$conexaoTurmas->registros; $i++){
			$associacaoDefinida = isset($conexaoTurmas->resultado['associacao']) && $conexaoTurmas->resultado['associacao'] != '';
			$ehNoMaximoAluno = $conexaoTurmas->resultado['associacao'] == strval($nivelAluno);
			$ehNoMaximoMonitor = $conexaoTurmas->resultado['associacao'] == strval($nivelMonitor) 
				|| $conexaoTurmas->resultado['associacao'] == strval($nivelAluno+$nivelMonitor);
			$ehNoMaximoProfessor = $conexaoTurmas->resultado['associacao'] == strval($nivelProfessor) 
				|| $conexaoTurmas->resultado['associacao'] == strval($nivelAluno+$nivelMonitor+$nivelProfessor)
				|| $conexaoTurmas->resultado['associacao'] == strval($nivelMonitor+$nivelProfessor)
				|| $conexaoTurmas->resultado['associacao'] == strval($nivelAluno+$nivelProfessor);
			$turma = new turma();
			$turma->openTurma($conexaoTurmas->resultado['codTurma']);
			if($associacaoDefinida && $nivelDeCorte == $nivelAluno && $ehNoMaximoAluno){
				array_push($turmasUsuario, $turma);
			} else if($associacaoDefinida && $nivelDeCorte == $nivelMonitor && $ehNoMaximoMonitor){
				array_push($turmasUsuario, $turma);
			} else if($associacaoDefinida && $nivelDeCorte == $nivelProfessor && $ehNoMaximoProfessor){
				array_push($turmasUsuario, $turma);
			}
			
			$conexaoTurmas->proximo();
		}
		
		return $turmasUsuario;
	}
	
	/*
	* @return Planeta de seu quarto.
	*/
	public function getIdTerrenoQuarto(){
		$conexaoQuarto = new conexao();
		$conexaoQuarto->solicitar("SELECT T.*
								   FROM terrenos AS T JOIN usuarios AS U ON T.terreno_id = U.quarto_id
								   WHERE U.usuario_id = ".($this->id));
		return $conexaoQuarto->resultado['terreno_id'];
	}
	
	public function podeAcessar($cutoff, $turma){ // cutoff = ponto de corte, o bitmap de niveis que podem acessar
		$temPermissao = false;
		if ($this->isAdmin()){
			return true;
		}
		$niveisNaTurma = $this->getNivel($turma);
		$cutoff = (int) $cutoff;
		for($i=0; $i<count($niveisNaTurma); $i++){
			$nivelTurma = (int) $niveisNaTurma[$i];
			$temPermissao = $temPermissao || (0 < ($nivelTurma & $cutoff));
		}
		return $temPermissao;
	}
	public function isAdmin(){return $this->getNivelAbsoluto() & 1;}
}
