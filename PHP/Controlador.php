<?php
require_once 'Conexion.lib.php';
require_once 'Interfaz.php';
if(isset($_SESSION['autenticacion']) && $_SESSION['autenticacion'] == 1) {
    echo $sistema;
    return;
}
$sistema = "";
$cnn = conexion();

if(isset($_POST['mostrarLogin'])) {
    echo mostrarLogin();
}

if(isset($_POST['signin'])) {
    $login = $cnn->query("SELECT * FROM empleado WHERE usuario_emp = '". $_POST['username'] ."' AND contrasenia_emp = '". $_POST['password']. "'");
    if($login->num_rows > 0) {
        echo menu($login);
    }
    else {
        echo "Nombre de usuario o contraseña incorrectos";
    }
}

//CREACIÓN DE UN NUEVO EMPLEADO
if(isset($_POST['nuevoUsuario'])) {
    require_once 'Persona.php';
    require_once 'Usuario.php';
    date_default_timezone_set("America/Mexico_City");
    $per = new Persona();
    $user = new Usuario();
    $per->setNombre($_POST['nombre']);
    $per->setPaterno($_POST['paterno']);
    $per->setMaterno($_POST['materno']);
    $per->setSexo($_POST['sexo']);
    $per->setTelefono($_POST['telefono']);
    $per->setCorreo($_POST['correo']);
    $tipo = "";
    if (isset($_FILES["fotoPerfil"]["tmp_name"]) && $_FILES["fotoPerfil"]["tmp_name"] != "") {
        $archivo = $_FILES["fotoPerfil"]["tmp_name"];
        $tipo = $_FILES["fotoPerfil"]["type"];
        $tam = $_FILES['fotoPerfil']["size"];

        $fp = fopen($archivo, "rb");
        $contenido = fread($fp, $tam);
        fclose($fp);
        $contenido = addslashes($contenido);
        $per->setFotoPerfil($contenido);
    }
    $persona = $cnn->query("INSERT INTO persona values(null,'". $per->getPaterno(). "','".$per->getMaterno(). "','".$per->getNombre(). "','".$per->getSexo(). "',".$per->getTelefono(). ",'".$per->getCorreo(). "','". $per->getFotoPerfil(). "', '". $tipo ."')");
    $consult = $cnn->query("SELECT max(id_per) as id FROM persona");
    $id = $consult->fetch_array(MYSQLI_ASSOC)['id'];
    $user->setNombreUsuario($_POST['username']);
    $user->setContrasenia($_POST['password']);
    $user->setFechaRegistro(date("Y-m-d"));
    $user->setPuesto($_POST['repeat']);

    $nuevoUsuario = $cnn->query("INSERT INTO empleado VALUES(null, '". $user->getNombreUsuario() ."','". $user->getContrasenia() ."','". $user->getPuesto() ."', 'Contratado', '". $user->getFechaRegistro(). "', ". $id .")");
}

if(isset($_POST['verUsuarios'])) {
    $consult = $cnn->query("SELECT nombre_per as nombre, ap_per as paterno, am_per as materno, e.id_emp as empleado, p.id_per as persona, foto_per as perfil, fechain_emp as fecha, puesto_emp as puesto, status_emp as statusemp FROM empleado e join persona p on e.cve_per = p.id_per WHERE e.status_emp = 'Contratado'");
    if($consult->num_rows>0) { echo verUsuarios($consult); }
    else { echo "Sin datos"; }
}

if(isset($_POST['detalleUsuario'])) {
    $consult = $cnn->query("SELECT p.id_per as persona, ap_per as paterno, am_per as materno, nombre_per as nombre, sexo_per as sexo, correo_per as correo, telefono_per as telefono, foto_per as foto, usuario_emp as usuario, fechain_emp as fecha, puesto_emp as puesto FROM empleado e join persona p on e.cve_per = p.id_per WHERE e.id_emp = ". $_POST['detalleUsuario']);
    if($consult->num_rows>0) {
        echo detalleUsuario($consult);
    }
}

//MODIFICAR EL USUARIO: VISTA.
if(isset($_POST['vistaModificar'])) {
    $consult = $cnn->query("SELECT p.id_per as persona, ap_per as paterno, am_per as materno, nombre_per as nombre, sexo_per as sexo, correo_per as correo, telefono_per as telefono, e.puesto_emp as puesto FROM empleado e join persona p on e.cve_per = p.id_per WHERE e.id_emp = ". $_POST['vistaModificar']);
    if($consult->num_rows>0) { echo vistaModificar($consult); }
}

if(isset($_POST['modificar'])) {
    require_once 'Persona.php';
    date_default_timezone_set("America/Mexico_City");
    $per = new Persona();
    $per->setNombre($_POST['nombre']);
    $per->setPaterno($_POST['paterno']);
    $per->setMaterno($_POST['materno']);
    $per->setSexo($_POST['sexo']);
    $per->setTelefono($_POST['telefono']);
    $per->setCorreo($_POST['correo']);
    $tipo = "";
    $cadArchi = "";
    if (isset($_FILES["fotoPerfil"]["tmp_name"]) && $_FILES["fotoPerfil"]["tmp_name"] != "") {
        $archivo = $_FILES["fotoPerfil"]["tmp_name"];
        $tipo = $_FILES["fotoPerfil"]["type"];
        $tam = $_FILES['fotoPerfil']["size"];

        $fp = fopen($archivo, "rb");
        $contenido = fread($fp, $tam);
        fclose($fp);
        $contenido = addslashes($contenido);
        $cadArchi = "foto_per='$contenido', tipoarchivo_per='$tipo',";
    }
    $persona = $cnn->query("UPDATE persona SET $cadArchi ap_per = '". $per->getPaterno(). "', am_per = '".$per->getMaterno(). "', nombre_per = '".$per->getNombre(). "', sexo_per = '".$per->getSexo(). "', telefono_per = ".$per->getTelefono(). ", correo_per = '".$per->getCorreo(). "' WHERE id_per = ". $_POST['modificar']);
    $cnn->query("UPDATE empleado SET puesto_emp = '". $_POST['puesto']. "' WHERE cve_per = ". $_POST['modificar']);
}

if(isset($_POST['interfazUsuario'])) {
    echo interfazUsuario();
}

if(isset($_POST['eliminarEmpleado'])) {
    $cnn->query("UPDATE empleado SET status_emp = 'Despedido' WHERE id_emp = ". $_POST['eliminarEmpleado']);
}

if(isset($_POST['venderProductos'])) {
    echo interfazVenderProducto();
}

if(isset($_POST['agregar'])) {
    $consultaProducto = $cnn->query("SELECT * FROM producto WHERE codigo_pro = '". $_POST['codigoBarras']. "' AND status_pro = 1");
    if($consultaProducto->num_rows>0) { echo true; }
    else { echo false; }
}

if(isset($_POST['agregarProducto'])) {
    $consultaProducto = $cnn->query("SELECT * FROM producto WHERE codigo_pro = '". $_POST['codigoBarras']. "'");
    echo agregarProducto($consultaProducto);
}

if(isset($_POST['comprarContado'])) {
    echo vistaComprarContado();
}

if(isset($_POST['clientesRegistrados'])) {
    $consult = $cnn->query("SELECT nombre_per as nombre, ap_per as paterno, am_per as materno, c.id_cli as cliente, p.id_per as persona, foto_per as perfil FROM cliente c join persona p on c.cve_per = p.id_per WHERE c.tipo_cli = 'Cliente particular'");
    if($consult->num_rows > 0) { echo clientes($consult); }
    else { echo 0; }
}

if(isset($_POST['pagarContado'])) {
    $idCliente = $cnn->query("SELECT id_cli as id FROM cliente WHERE tipo_cli = 'Público general' LIMIT 1");
    if($idCliente->num_rows > 0) {
        $idCliente = $idCliente->fetch_array(MYSQLI_ASSOC)['id'];
        date_default_timezone_set("America/Mexico_City");
        $ticket = $cnn->query("INSERT INTO ticket VALUES(null, '". date("Y-m-d"). "', ". $_POST['total'] .", ". $_POST['empleado']. ", ". $idCliente. ")");
        echo $ticket;
    }
}

if(isset($_POST['comprarCredito'])) {
    echo vistaComprarCredito();
}

if(isset($_POST['pagarRegistro'])) {
    require_once 'Persona.php';
    require_once 'Cliente.php';
    date_default_timezone_set("America/Mexico_City");
    $per = new Persona();
    $cliente = new Cliente();
    $per->setNombre($_POST['nombre']);
    $per->setPaterno($_POST['paterno']);
    $per->setMaterno($_POST['materno']);
    $per->setSexo($_POST['sexo']);
    $per->setTelefono($_POST['telefono']);
    $per->setCorreo($_POST['correo']);
    $tipo = "";
    if (isset($_FILES["fotoPerfil"]["tmp_name"]) && $_FILES["fotoPerfil"]["tmp_name"] != "") {
        $archivo = $_FILES["fotoPerfil"]["tmp_name"];
        $tipo = $_FILES["fotoPerfil"]["type"];
        $tam = $_FILES['fotoPerfil']["size"];

        $fp = fopen($archivo, "rb");
        $contenido = fread($fp, $tam);
        fclose($fp);
        $contenido = addslashes($contenido);
        $per->setFotoPerfil($contenido);
    }
    $persona = $cnn->query("INSERT INTO persona values(null,'". $per->getPaterno(). "','".$per->getMaterno(). "','".$per->getNombre(). "','".$per->getSexo(). "',".$per->getTelefono(). ",'".$per->getCorreo(). "','". $per->getFotoPerfil(). "', '". $tipo ."')");
    $consult = $cnn->query("SELECT max(id_per) as id FROM persona");
    $id = $consult->fetch_array(MYSQLI_ASSOC)['id'];
    $cliente->setFechaRegistro(date("Y-m-d"));
    $cliente->setHoraRegistro(date("H:i:s"));
    $cliente->setTipoCliente("Cliente particular");
    $nuevoCliente = $cnn->query("INSERT INTO cliente VALUES(null, '". $cliente->getFechaRegistro(). "', '". $cliente->getHoraRegistro(). "', '". $cliente->getTipoCliente(). "', $id)");
    $idCliente = $cnn->query("SELECT max(id_cli) as id FROM cliente LIMIT 1");
    if($idCliente->num_rows > 0) {
        $idCliente = $idCliente->fetch_array(MYSQLI_ASSOC)['id'];
        date_default_timezone_set("America/Mexico_City");
        $ticket = $cnn->query("INSERT INTO ticket VALUES(null, '". date("Y-m-d"). "', ". $_POST['total'] .", ". $_POST['empleado']. ", ". $idCliente. ")");
        echo $ticket;
    }
}

if(isset($_POST['renglonticket'])) {
    $idTicket = $cnn->query("SELECT max(id_tic) as id FROM ticket");
    $idProducto = $cnn->query("SELECT id_pro as id FROM producto WHERE codigo_pro = '". $_POST['codigobarras']. "'");
    if($idTicket->num_rows > 0 && $idProducto->num_rows > 0) {
        $idTicket = $idTicket->fetch_array(MYSQLI_ASSOC)['id'];
        $idProducto = $idProducto->fetch_array(MYSQLI_ASSOC)['id'];
        $renglonticket = $cnn->query("INSERT INTO renglonticket VALUES(null, ". $_POST['cantidad']. ", ". $_POST['precio']. ", $idProducto, $idTicket)");
        echo $renglonticket;
    }
}

if(isset($_POST['pagarClienteExistente'])) {
    date_default_timezone_set("America/Mexico_City");
    $ticket = $cnn->query("INSERT INTO ticket VALUES(null, '". date("Y-m-d"). "', ". $_POST['total'] .", ". $_POST['empleado']. ", ". $_POST['pagarClienteExistente']. ")");
    echo $ticket;
}

if(isset($_POST['agregarcreditonuevo'])) {
    require_once "Credito.php";
    date_default_timezone_set("America/Mexico_City");
    $credito = new Credito($_POST['pagoinicial'], "En crédito");
    $idTicket = $cnn->query("SELECT max(id_tic) AS id FROM ticket");
    $idTicket = $idTicket->fetch_array(MYSQLI_ASSOC)['id'];
    $creditoAgregado = $cnn->query("INSERT INTO credito VALUES(null, ". $credito->getPagoInicial(). ", '". $credito->getStatus() ."', $idTicket)");
    echo $creditoAgregado;
}

if(isset($_POST['clientesRegistradosCredito'])) {
    $consult = $cnn->query("SELECT nombre_per as nombre, ap_per as paterno, am_per as materno, c.id_cli as cliente, p.id_per as persona, foto_per as perfil FROM cliente c join persona p on c.cve_per = p.id_per WHERE c.tipo_cli = 'Cliente particular'");
    if($consult->num_rows > 0) { echo clientesCredito($consult); }
    else { echo 0; }
}

if(isset($_POST['pagarClienteExistenteCredito'])) {
    date_default_timezone_set("America/Mexico_City");
    $ticket = $cnn->query("INSERT INTO ticket VALUES(null, '". date("Y-m-d"). "', ". $_POST['total'] .", ". $_POST['empleado']. ", ". $_POST['pagarClienteExistenteCredito']. ")");
    echo $ticket;
}

if(isset($_POST['vercredito'])) {
    $creditos = $cnn->query("SELECT cli.id_cli as cliente, nombre_per as persona,
    nombre_pro as producto, fechaventa_tic as fechacompra, 
    total_tic as total, pagoinicial as inicial, 
    total_tic - pagoinicial as restante,
    c.id_cre as credito
    FROM credito c join ticket t
    on c.cve_tic = t.id_tic join renglonticket rt
    on rt.cve_tic = t.id_tic join producto p
    on rt.cve_pro = p.id_pro join cliente cli
    on t.cve_cli = cli.id_cli join persona per
    on cli.cve_per = per.id_per
    WHERE c.status_cre = 'En crédito'
    group by t.id_tic;");
    if($creditos->num_rows > 0) {
        echo mostrarCreditos($creditos);
    }
    else { echo "Sin datos"; }
}

if(isset($_POST['detallescredito'])) {
    $creditos = $cnn->query("SELECT cre.id_cre as credito, rt.cve_tic as ticket, cantidad_ren as cantidad,
    nombre_pro as producto, total_tic - pagoinicial as restante, cantidad_ren * preciov_ren as TotalProducto, preciov_ren as preciounidad
    FROM credito cre join ticket t
    on cre.cve_tic = t.id_tic join renglonticket rt
    on rt.cve_tic = t.id_tic join producto p
    on rt.cve_pro = p.id_pro WHERE cre.id_cre = ". $_POST['detallescredito'] . "");
    if($creditos->num_rows > 0) {
        echo detallesCredito($creditos);
    }
    else { echo "Sin datos"; }
}