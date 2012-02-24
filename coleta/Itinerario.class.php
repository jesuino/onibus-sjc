<?php
	public class Itinerario{
		private $numero;
		private $nome;
		private $sentido;
		private $itinerario;
		private $ultimaAtualizacao;
		private $horarioSegundaASexta;
		private $horarioSabado;
		private $horarioDomingoEFeriado;
		private $horarioSabadoDomingoEFeriado;


		public function getNumero(){
			return $this->numero;
		}

		public function setNumero($numero){
			$this->numero = $numero;
		}

		public function getNome(){
			return $this->nome;
		}

		public function setNome($nome){
			$this->nome = $nome;
		}

		public function getSentido(){
			return $this->sentido;
		}

		public function setSentido($sentido){
			$this->sentido = $sentido;
		}

		public function getItinerario(){
			return $this->itinerario;
		}

		public function setItinerario($itinerario){
			$this->itinerario = $itinerario;
		}

		public function getUltimaAtualizacao(){
			return $this->ultimaAtualizacao;
		}

		public function setUltimaAtualizacao($ultimaAtualizacao){
			$this->ultimaAtualizacao = $ultimaAtualizacao;
		}

		public function getHorarioSegundaASexta(){
			return $this->horarioSegundaASexta;
		}

		public function setHorarioSegundaASexta($horarioSegundaASexta){
			$this->horarioSegundaASexta = $horarioSegundaASexta;
		}

		public function getHorarioSabado(){
			return $this->horarioSabado;
		}

		public function setHorarioSabado($horarioSabado){
			$this->horarioSabado = $horarioSabado;
		}

		public function getHorarioDomingoEFeriado(){
			return $this->horarioDomingoEFeriado;
		}

		public function setHorarioDomingoEFeriado($horarioDomingoEFeriado){
			$this->horarioDomingoEFeriado = $horarioDomingoEFeriado;
		}

		public function getHorarioSabadoDomingoEFeriado(){
			return $this->horarioSabadoDomingoEFeriado;
		}

		public function setHorarioSabadoDomingoEFeriado($horarioSabadoDomingoEFeriado){
			$this->horarioSabadoDomingoEFeriado = $horarioSabadoDomingoEFeriado;
		}

	}
?>
