<?php
	
	
		
		const DB_SERVER = "127.0.0.1";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "escuela1_gimnasio";

		
		
		


		function cursos_usuarios(){
			$mysqli = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB);

			
			//$query="SELECT usuarios_cursos.id AS usuarioscursosid, cursos.id AS cursoid, cursos.nombre, usuarios.id AS usuarioid, usuarios.user
					// FROM usuarios_cursos 
					// INNER JOIN usuarios 
					// 	ON usuarios_cursos.id_usuario = usuarios.id 
					// INNER JOIN cursos 
					// 	ON usuarios_cursos.id_curso = cursos.id";

			$query = "select * from cursos where baja=0";
			$salida=array();
			$r = $mysqli->query($query) or die($mysqli->error.__LINE__);
       		
			$i=0;
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$row['profesores']='';
					$result[] =array_map('utf8_encode', $row);
				}	
			}
			
			$i=0;
			echo "<pre>";
				//print_r($result);
				echo "<hr>";
			foreach ($result as $key => $value) {
				// echo "\n";
				//echo "id: ".$value['id']."\n" ; //. "<br> valor: " . print_r($value);
				$id=$value['id'];
				// print_r($value[0]);
				// echo "<hr>";
				array_push ($salida, $value);
				
				$query = "SELECT usuarios_cursos.id, usuarios_cursos.id_usuario,usuarios_cursos.id_curso, usuarios.user
							FROM usuarios_cursos 
							INNER JOIN usuarios 
							ON usuarios_cursos.id_usuario = usuarios.id 
							where id_curso=$id";

				$r2 = $mysqli->query($query) or die($mysqli->error.__LINE__);
				if($r2->num_rows > 0){
					while($row2 = $r2->fetch_assoc()){
						// $salida[$id]['profesores']=array();
						// array_push ($salida[$id]['profesores'], $row2);
						echo "------";
						// echo $row2['user'];
						echo "]]" . (string) is_string(1) . "[[<br>";
						print_r($row2);
						// $salida[$i]['profesores'][]=array($row2);
						$salida[$i]['profesores'][]=array_map('utf8_encode', $row2);
					}
				}
				$i++;
			}
			
			 //print_r($salida);
			echo "</pre>";

		}

function garcha(){	
			$mysqli = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB);
			$meses=array("ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov","dic");
			$anio = "2017";

			$salida=array();
			//$result=array();
			for ($mes=0; $mes <= 11; $mes++) { 
				$query="SELECT Sum(servicios_r.importe) AS total
						FROM servicios_r
						WHERE MONTH(servicios_r.fecha) = $mes AND  YEAR(servicios_r.fecha) = $anio
						GROUP BY  MONTH(servicios_r.fecha)";
				
				$r = $mysqli->query($query) or die($mysqli->error.__LINE__);

				if($r->num_rows > 0){
					while($row = $r->fetch_assoc()){
						$result= array("mes"=>$meses[$mes],"total"=>$row['total']);
					}	
				} else {
					$result=array("mes"=>$meses[$mes],"total"=>"0");
				}
				$salida[]= $result;
				$result=array();
			}

			echo "<pre>";
			print_r($salida);
			echo "</pre>";

			//$this->response('',204);	// If no records "No Content" status
		}



// garcha();
cursos_usuarios();
?>
	
	

