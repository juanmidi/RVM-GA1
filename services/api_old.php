<?php
 	require_once("Rest.inc.php");
	require_once("connection.php");

	class API extends REST {
	
		public $data = "";
		
		// const DB_SERVER = "127.0.0.1";
		// const DB_USER = "root";
		// const DB_PASSWORD = "";
		// // const DB = "escuela1_gimnasio";
		// const DB = "popo";

		// const DB_SERVER = "127.0.0.1";
		// const DB_USER = "escuela1";
		// const DB_PASSWORD = "eXYumn4693";
		// const DB = "escuela1_gimnasio";
		// const DB = "escuela1_desarrollo";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
				
		private function login(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$tmp = json_decode(file_get_contents("php://input"),true);
			$user = $tmp['user'];
			$pass = $tmp['pass'];

			if(!empty($user) and !empty($pass)){
					//$query="SELECT uid, name, email FROM users WHERE email = '$email' AND password = '".md5($password)."' LIMIT 1";
					
					$query="SELECT * FROM usuarios WHERE user = '$user' AND pass = '$pass' LIMIT 1";
					$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
					$result = array();
					if($r->num_rows > 0) {
						//$result = $r->fetch_assoc();
						$result = array_map('utf8_encode', $r->fetch_assoc());	

						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
			}
			
			$error = array('status' => "Failed", "msg" => "Usuarios o contraseña no válida");
			$this->response($this->json($error), 400);
		}
		
		private function alumnos(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT c.id, CONCAT (c.apellido, ', ', c.nombre) AS alumno  FROM alumnos c order by alumno";
			// $this->mysqli->set_charset("utf8");
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					 $result[] = array_map('utf8_encode', $row);
				}
				//echo print_r($result);
				//echo json_encode($result);
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function alumno(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){	
				//$query="SELECT distinct c.apellido, c.nombre, c.dni, c.domicilio, c.localidad, c.id_curso, c.tel_fijo, c.celular FROM alumnos c where c.id=$id";
				
				$query="SELECT `id`, `id_familia`, `responsable_nombre`, `adulto_responsable_dni`, `apellido`, `nombre`, `dni`, `fecha_nac`, `domicilio`, `localidad`, `id_curso`, `tel_fijo`, `celular`, `observaciones`, `es_hermano`, `baja`, `una_vez_sem` FROM alumnos where `id`= $id";

				//$query = "SELECT alumnos.id, alumnos.id_familia, alumnos.apellido, alumnos.nombre, alumnos.dni, alumnos.fecha_nac, alumnos.domicilio, alumnos.localidad, alumnos.id_curso, alumnos.observaciones, alumnos.es_hermano, alumnos.baja, alumnos.una_vez_sem, familia.familia, familia.tel_fijo, familia.cel, familia.baja
				//	FROM familia INNER JOIN alumnos ON familia.id = alumnos.id_familia
				//	WHERE alumnos.id = $id";

				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();
				  $result = $this->utf8_converter($result);
					// echo print_r($result);
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function insertAlumno(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$customer = json_decode(file_get_contents("php://input"),true);

			//$column_names = array('customerName', 'email', 'city', 'address', 'country');

			$column_names = array('id_familia', 'responsable_nombre', 'adulto_responsable_dni', 'apellido', 'nombre', 'dni', 'fecha_nac', 'domicilio', 'localidad', 'id_curso', 'tel_fijo', 'celular', 'observaciones', 'es_hermano', 'baja', 'una_vez_sem');
			$keys = array_keys($customer);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					//$$desired_key = $customer[$desired_key];
					$$desired_key = utf8_decode($customer[$desired_key]);
				}
				$columns = $columns.$desired_key.',';

				if($desired_key == 'id_familia'){
					$tmp=0;
				} elseif ($desired_key == 'fecha_nac') {
					$tmp='0000-00-00';
				} 

				elseif ($desired_key == 'es_hermano') {
					$tmp=0;
				} 

				elseif ($desired_key == 'una_vez_sem') {
					$tmp=0;
				}

				elseif ($desired_key == 'baja') {
					$tmp=0;
				}

				else {	
					$tmp="'".$$desired_key."'";
				}
				$values = $values. $tmp . ",";

			}
			$query = "INSERT INTO alumnos (".trim($columns,',').") VALUES(".trim($values,',').")";
			
			// $success = array('status' => "Success", "msg" => "Customer Created Successfully.", "id" => $id, "columns" => $columns, "values" => $values, "keys" => $keys);
			// $this->response($this->json($success),200);
			
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				//devuelve el id del registro insertado
				$id = $this->mysqli->insert_id;
				$success = array('status' => "Success", "msg" => "Customer Created Successfully.", "id" => $id, "columns" => $columns, "values" => $values);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
				
		}

		
		private function updateAlumno(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$customer = json_decode(file_get_contents("php://input"),true);
			$id = (int)$customer['id'];
			//$column_names = array('id_familia', 'apellido', 'nombre', 'dni', 'fecha_nac', 'domicilio', 'localidad', 'id_curso', 'observaciones', 'es_hermano', 'baja', 'una_vez_sem');
			
			/*
			$column_names = array('id_familia', 'apellido', 'nombre', 'dni', 'fecha_nac', 'domicilio', 'localidad', 'id_curso', 'observaciones', 'es_hermano', 'baja', 'una_vez_sem');
			

			//'responsable_nombre', 'adulto_responsable_dni', 'tel_fijo', 'celular', 

			$keys = array_keys($customer['customer']);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ 
			  if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = utf8_decode($customer['customer'][$desired_key]);
				}
				//$columns = $columns.$desired_key."='".$$desired_key."',";
			//}

			//------------
			if($desired_key == 'id_familia'){
					$tmp=$$desired_key;

				} elseif ($desired_key == 'es_hermano') {
					$tmp=$$desired_key;
				} 

				elseif ($desired_key == 'una_vez_sem') {
					$tmp=$$desired_key;
				}

				elseif ($desired_key == 'id_curso') {
					$tmp=$$desired_key;
				}

				elseif ($desired_key == 'baja') {
					$tmp=$$desired_key;
				}

				else {	
					$tmp="'".$$desired_key."'";
				}
				$columns = $columns. $tmp . ",";
			}
			//----------------

			$query = "UPDATE alumnos SET ".trim($columns,',')." WHERE id=$id";

			*/

			$id_familia=(int)$customer['customer']['id_familia'];
			$apellido =utf8_decode($customer['customer']['apellido']);
			$nombre =utf8_decode($customer['customer']['nombre']);
			$dni=$customer['customer']['dni'];
			$fecha_nac=$customer['customer']['fecha_nac'];
			$domicilio =utf8_decode($customer['customer']['domicilio']);
			$localidad =utf8_decode($customer['customer']['localidad']);
			$id_curso =(int)$customer['customer']['id_curso'];
			$observaciones =utf8_decode($customer['customer']['observaciones']);
			$es_hermano =(int)$customer['customer']['es_hermano'];
			$baja =(int)$customer['customer']['baja'];
			$una_vez_sem	=(int)$customer['customer']['una_vez_sem'];

			if ($fecha_nac == "") {
				$fecha_nac = "0000-00-00";
			}
				
			$query="UPDATE alumnos SET 
				id_familia=$id_familia, 
				apellido='$apellido', 
				nombre='$nombre', 
				dni='$dni', 
				fecha_nac='$fecha_nac', 
				domicilio='$domicilio', 
				localidad='$localidad', 
				id_curso=$id_curso, 
				observaciones='$observaciones', 
				es_hermano=$es_hermano, 
				baja=$baja, 
				una_vez_sem=$una_vez_sem 
				WHERE id=$id";

			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array("id" => $id, "columns" => $columns, "values" => $values);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// "No Content" status
		}
		
		private function deleteAlumno(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="DELETE FROM alumnos WHERE id = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Successfully deleted one record.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}

		private function darDeBajaAlumno(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="UPDATE alumnos SET baja=1 WHERE id=$id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Se actualizó el registro.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);
		}	

		private function recibo(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$id = (int)$this->_request['id'];
			$mes = (int)$this->_request['mes'];

			//$query="SELECT 	descrip  FROM `servicios_r` WHERE `id_alumno` = $id AND `id_mes` = $mes";
			$query="SELECT concat(apellido,', ', nombre) AS nombrealumno, descrip, 
				preciounit, recargo, descuento, importe, servicios_r.id, 'false' as seleccionado, 'false' as editing
				FROM servicios_r INNER JOIN alumnos ON servicios_r.id_alumno = alumnos.id 
				WHERE `id_alumno` = $id AND `id_mes` <= $mes AND `pago` = 0 
				ORDER BY `id_mes`, `anio`";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					 $result[] = array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}


		private function facturacion(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			//*********************************************
			$fecha = (string)$this->_request['fecha'];
			// $newDate = date("Y-m-d", strtotime($fecha) );
			//*********************************************
			// $fecha_hora_1 = (string)$fecha . " 00:00:00";
			// $fecha_hora_2 = (string)$fecha . " 23:59:59";

			//$query="SELECT 	descrip  FROM `servicios_r` WHERE `id_alumno` = $id AND `id_mes` = $mes";
			
			$query="SELECT concat(apellido,', ', nombre) AS nombrealumno, descrip, 
				preciounit, recargo, descuento, importe
				FROM servicios_r INNER JOIN alumnos ON servicios_r.id_alumno = alumnos.id 
				WHERE `pago` = 1 AND fecha = '$fecha'"; //BETWEEN '$fecha_hora_1' AND '$fecha_hora_2'";


			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status

		}

		private function morosos(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$mes = (int)$this->_request['mes'];

			$query="SELECT concat(apellido,', ', nombre) AS nombrealumno, descrip, 
				preciounit, recargo, descuento, importe, LPAD(id_mes,2,'0') as mes, anio,servicios_r.id, alumnos.id as alumno_id
				FROM servicios_r INNER JOIN alumnos ON servicios_r.id_alumno = alumnos.id 
				WHERE `id_mes` <= $mes AND `pago` = 0 
				ORDER BY nombrealumno";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function nota_moroso(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$id = (int)$this->_request['id'];
			$mes = (int)$this->_request['mes'];

			$query="SELECT concat(apellido,', ', nombre) AS nombrealumno, descrip, 
				preciounit, recargo, descuento, importe, LPAD(id_mes,2,'0') as mes, anio,servicios_r.id, alumnos.id as alumno_id
				FROM servicios_r INNER JOIN alumnos ON servicios_r.id_alumno = alumnos.id 
				WHERE `id_mes` <= $mes AND `pago` = 0 AND servicios_r.id_alumno = $id
				ORDER BY nombrealumno";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function sistema(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$query="SELECT * FROM sistema";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200);
			}
			$this->response('',204);
		}


		private function deleteRecibo(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="DELETE FROM servicios_r WHERE id = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Se ha borrado el registro");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}


		private function updateMoroso(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$data = json_decode(file_get_contents("php://input"),true);
			$id = $data['id'];
			$fecha = $data['fecha'];
			$longitud = count($id);
 			for($x = 0; $x < $longitud; $x++) {
				$query = "UPDATE servicios_r SET pago=1, fecha='$fecha[$x]' WHERE id=$id[$x]";
				if(!empty($id[$x]))
					$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			}
		}


		private function cursos(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT * FROM `cursos` WHERE `baja`= 0";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function profe_cursos(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$userid = (int)$this->_request['userid'];
			
			$query="SELECT c.id, c.nombre FROM cursos c
						INNER JOIN usuarios_cursos uc
						ON uc.id_curso = c.id 
					WHERE  uc.id_usuario = $userid";


			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] = array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200);
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function profes(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$query="SELECT CONCAT(nombre,' ',apellido) AS nombreprofe FROM usuarios";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] = array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200);
			}
			$this->response('',204);	// If no records "No Content" status
		}


		//NO USADO*****************************************************
		private function familias(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$idAlumno = $this->_request['idalumno'];

			$query="SELECT * FROM `familia` WHERE `baja`= 0";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					//$result[] = $row;
					 $result[] =array_map('utf8_encode', $row);
				}	
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}


		private function insertFamilia(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$data = json_decode(file_get_contents("php://input"),true);
			$familia = $data['familia'];
			$query = "INSERT INTO familia (familia) VALUES ('$familia');";
			if(!empty($data)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$query= "SELECT @@identity AS id";
				
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0){
					while($row = $r->fetch_assoc()){
						 $id = $row['id'];
					}
				}
				// $id = $this->mysqli->mysql_insert_id();
				$result = array('status' => "Success", "msg" => "Se insertó la familia.", "id" => $id);
				$this->response($this->json($result),200);
			}else
				$this->response('',204);	//"No Content" status
		}


private function generarDeudaAlumno(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$id = (int)$this->_request['id'];

			$query = "select `futbol`, `inscripcion`, `dto_una_vez_sem`, `dto_hermano` from sistema";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					 $result[] =array_map('utf8_encode', $row);
				}	
			}

			if($id == 0) {
				//genera deuda al alumno nuevo
				$query = "SELECT CONCAT(alumnos.apellido, ', ', alumnos.nombre) AS nombre_al, cursos.nombre AS curso,
				alumnos.es_hermano AS eshermano, alumnos.una_vez_sem AS unavezsem
				FROM alumnos INNER JOIN cursos ON alumnos.id_curso = cursos.id
				WHERE alumnos.id = $id";
				
			} elseif ($id > 0) {
				//genera deuda al alumnos por su id
				$query = "SELECT CONCAT(alumnos.apellido, ', ', alumnos.nombre) AS nombre_al, cursos.nombre AS curso,
				alumnos.es_hermano AS eshermano, alumnos.una_vez_sem AS unavezsem
				FROM alumnos INNER JOIN cursos ON alumnos.id_curso = cursos.id
				WHERE alumnos.id = $id";
			} else {
				// si es undefined
			}

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0){
				$result2 = array();
				while($row = $r->fetch_assoc()){
					 $result2[] = array_map('utf8_encode', $row);
				}	
			}
			

			//concepto *** INSCRIPCIÓN ***
			$id_concepto=1;
			$alumno=$result2[0]['nombre_al'];
			$mes=3;
			$anio=date('Y');
			$fecha_tmp=date_create($anio."-". $mes ."-01");
			$mes_anio=date_format($fecha_tmp,"M/Y");
			$descrip="Inscripción (" . $mes_anio . ") - Curso: " .$result2[0]['curso'];
			$descrip=utf8_decode($descrip);

			$descuento_hermano=$result[0]['dto_hermano'];
			$descuento_una_vez_sem=$result[0]['dto_una_vez_sem'];

			$preciounit=$result[0]['inscripcion'];
			$importe=$preciounit;
			
			$sql = "INSERT INTO servicios_r (cantidad, id_concepto, descrip, id_mes, anio, id_alumno, preciounit, recargo, descuento, importe, pago) 
						SELECT 1, $id_concepto, '$descrip', $mes, '$anio', $id, $preciounit, 0, 0, $importe,0
						FROM dual
						WHERE NOT EXISTS (
							SELECT * FROM servicios_r 
							WHERE id_concepto=$id_concepto AND
								id_mes=$mes AND
								anio='$anio' AND
								id_alumno=$id)";

			$r = $this->mysqli->query($sql); // or die($this->mysqli->error.__LINE__);

			

			//concepto *** CUOTA ***
			$id_concepto=2; 
			//**********************

			$es_hermano=(int)$result2[0]['eshermano'];
			$una_vez_sem=(int)$result2[0]['unavezsem'];

			if($es_hermano==1){
				$descuento=$descuento_hermano;
			}elseif($una_vez_sem==1){
				$descuento=$descuento_una_vez_sem;
			}else{
				$descuento='0';
			}

			$preciounit=$result[0]['futbol'];
			$importe=(float)$preciounit - (float)$descuento;
			
			for ($mes=3; $mes <= 12 ; $mes++) { 
				$fecha_tmp=date_create($anio . "-". $mes ."-01");
				$mes_anio=date_format($fecha_tmp,"M/Y");
				$descrip="Fútbol (" . $mes_anio . ") - Curso: " .$result2[0]['curso'];
				$descrip=utf8_decode($descrip);
				
				$sql = "INSERT INTO servicios_r (cantidad, id_concepto, descrip, id_mes, anio, id_alumno, preciounit, recargo, descuento, importe, pago) 
							SELECT 1, $id_concepto, '$descrip', $mes, '$anio', $id, $preciounit, 0, $descuento, $importe, 0
							FROM dual
							WHERE NOT EXISTS (
								SELECT * FROM servicios_r 
								WHERE id_concepto=$id_concepto AND
									id_mes=$mes AND
									anio='$anio' AND
									id_alumno=$id)";

				// $sql = "INSERT INTO servicios_r (cantidad, id_concepto, descrip, id_mes, anio, id_alumno, preciounit, recargo, descuento, importe, pago) 
				// 				VALUES (1, $id_concepto, '$descrip', $mes, '$anio', $id, $preciounit, 0, $descuento, $importe,0)";

				$r = $this->mysqli->query($sql); // or die($this->mysqli->error.__LINE__);
			}

			
}

private function borrarDeudaAlumno(){
		if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$idAlumno = (int)$this->_request['id'];
			$anio=date("Y");
			if($idAlumno > 0){				
				$query="DELETE FROM servicios_r WHERE id_alumno = $idAlumno AND anio='$anio' AND pago=0";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Registros borrados correctamente.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}

	
private function updateRecibo(){
		//------------------------//
		// en desarrollo
		// actualiza los pagos
		//------------------------//
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$data = json_decode(file_get_contents("php://input"),true);

			//$this->response($this->json($data),200);
			$num_rec=(int)$data['num_rec'];
			date_default_timezone_set("America/Argentina/Buenos_Aires");
			$fecha = date("Y-m-d");
			foreach ($data['data'] as $key ) {
				if ($key['seleccionado']=='true') {
					echo $key['descrip'] . "\n";
					$id = $key['id'];
					echo $id;
					$query = "UPDATE servicios_r SET pago=1, fecha='$fecha', id_recibo=$num_rec WHERE id=$id";
					if(!empty($id))
						$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				}
			
			}

		}

		private function numeros(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT MAX(id_recibo) AS num_rec FROM servicios_r";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] =array_map('utf8_encode', $row);
				}
				$this->response($this->json($result[0]), 200); // send user details
			}
		}

		private function pasar_lista(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$id = (int)$this->_request['id'];
			$fecha = (string)$this->_request['fecha'];

			//$query="SELECT cursos.id AS idcurso, cursos.nombre, alumnos.id AS idalumno, CONCAT (alumnos.apellido, ', ', alumnos.nombre) AS nombrealumno
			//		FROM alumnos INNER JOIN cursos ON alumnos.id_curso = cursos.id
			//		WHERE cursos.id = $id";

			if($id > 0 && $fecha !== '') {
				$query="SELECT cursos.id AS idcurso, cursos.nombre, alumnos.id  AS idalumno , CONCAT (alumnos.apellido, ', ', alumnos.nombre) AS nombrealumno, 
				(select presente from presentes where alumno_id = alumnos.id and fecha='$fecha') AS presente
				FROM alumnos INNER JOIN cursos ON alumnos.id_curso = cursos.id
				WHERE cursos.id = $id
				ORDER BY nombrealumno";

				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				
				if($r->num_rows > 0){
					$result = array();
					while($row = $r->fetch_assoc()){
						$result[] =array_map('utf8_encode', $row);
					}
					$this->response($this->json($result), 200); // send user details
				}
			}
		}

		private function presente(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$idalumno = (int)$this->_request['idalumno'];
			$fecha = (string)$this->_request['fecha'];
			$estado = (int)$this->_request['estado'];
			$idcurso = (int)$this->_request['idcurso'];

			if ($estado==1){
				$query="INSERT INTO presentes (alumno_id, fecha, presente, curso_id)
						VALUES ($idalumno, '$fecha', $estado, $idcurso)";
			} else {
				$query="DELETE FROM presentes 
					where alumno_id=$idalumno AND fecha='$fecha' AND presente=1 AND curso_id=$idcurso";	
			}

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
		}


		private function cursos_usuarios(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}

			$query = "select * from cursos where baja=0";
			$salida=array();
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
       		
			$i=0;
			if($r->num_rows > 0){
			$result = array();
			while($row = $r->fetch_assoc()){
					$row=array_map('utf8_encode', $row);
					$row['profesores']=array();
					array_push($result, $row);
				}	
			}
			
			$i=0;

			foreach ($result as $key => $value) {
				$id=$value['id'];
				array_push ($salida, $value);
				
				$query = "SELECT usuarios_cursos.id, usuarios_cursos.id_usuario,
							usuarios_cursos.id_curso, usuarios.user, usuarios.apellido ,usuarios.nombre
							FROM usuarios_cursos 
							INNER JOIN usuarios 
							ON usuarios_cursos.id_usuario = usuarios.id 
							where id_curso=$id";

				$r2 = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r2->num_rows > 0){
					while($row2 = $r2->fetch_assoc()){
						// $salida[$i]['profesores'][]=array_map('utf8_encode', $row2);
						//$salida[$i]['profesores'][]=array_map('utf8_encode', $row2);
						array_push($salida[$i]['profesores'],array_map('utf8_encode', $row2));
					}
				}
				$i++;
			}
		$this->response($this->json($salida), 200);
	}


		private function facturacion_mensual(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$meses=array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
						 "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre","Diciembre");
			$anio = (int)$this->_request['anio'];
			$salida=array();
			for ($mes=1; $mes <= 12; $mes++) { 
				$query="SELECT Sum(importe) AS total
						FROM servicios_r
						WHERE MONTH(fecha) = $mes AND  YEAR(fecha) = $anio AND pago=1
						GROUP BY  MONTH(fecha)";
				
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				if($r->num_rows > 0){
					while($row = $r->fetch_assoc()){
						$result= array("mes"=>$meses[$mes-1],"total"=>$row['total']);
					}	
				} else {
					$result=array("mes"=>$meses[$mes-1],"total"=>"0");
				}
				$salida[]= $result;
				$result=array();
			}
			$this->response($this->json($salida), 200); // send user details


			//$this->response('',204);	// If no records "No Content" status
		}



	private function version(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
				$query="SELECT version FROM sistema";
				
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				if($r->num_rows > 0){
					while($row = $r->fetch_assoc()){
						$result= $row;
					}
				} else {
					$result="";
				}
			$this->response($this->json($result), 200);
		}

	private function notificaciones(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
				$query="SELECT notification_msg FROM sistema";
				
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				$result = array();
				if($r->num_rows > 0){
					while($row = $r->fetch_assoc()){
						$result[] = array_map('utf8_encode', $row);
					}
				} else {
					$result="";
				}
			$this->response($this->json($result), 200);
		}

		private function update_notification(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="UPDATE usuarios SET notification_show = 0 WHERE id = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Se actualizó el registro.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);
		}

		private function presentespormes(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$idcurso = (int)$this->_request['idcurso'];
			$mes = (int)$this->_request['mes'];
			
			$query="SELECT notification_msg FROM sistema";

			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			$result = array();
			if($r->num_rows > 0){
				while($row = $r->fetch_assoc()){
					$result[] = array_map('utf8_encode', $row);
				}
			} else {
				$result="";
			}
			$this->response($this->json($result), 200);
		}

		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data, JSON_UNESCAPED_UNICODE);
			}
		}

		private function utf8_converter($array)
		{
		    array_walk_recursive($array, function(&$item, $key){
		        if(!mb_detect_encoding($item, 'utf-8', true)){
		                $item = utf8_encode($item);
		        }
		    });
		 
		    return $array;
		}

	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>