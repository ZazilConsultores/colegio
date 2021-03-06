<?php
/**
 * 
 */
interface Encuesta_Interfaces_IMateria {
	
	public function obtenerMateria($idMateria);
	public function obtenerMaterias($idCiclo,$idGrado);
	public function obtenerMateriasGrado($idGrado);
	public function obtenerMateriasGrupo($idCiclo,$idGrado);
	
	public function crearMateria(array $materia);
	public function editarMateria($idMateria, array $materia);
	
}
