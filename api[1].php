<?php
// Configuración de headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos
$host = 'sql107.infinityfree.com'; 
$dbname = 'if0_40115675_f1_racing'; 
$username = 'if0_40115675'; 
$password = 'Alph4290604';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Error de conexión BD: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la base de datos']);
    exit;
}

// ✅ FUNCIÓN DE VALIDACIÓN SEGURA
function validarEntrada($dato, $tipo = 'texto') {
    if ($dato === null) return '';
    
    $dato = trim($dato);
    
    switch($tipo) {
        case 'entero':
            return intval($dato);
        case 'decimal':
            return floatval($dato);
        case 'fecha':
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $dato) ? $dato : '';
        case 'licencia':
            return preg_match('/^FIA-\d+$/', $dato) ? $dato : '';
        case 'chasis':
            return preg_match('/^CH-\w+$/', $dato) ? $dato : '';
        case 'tiempo':
            return preg_match('/^\d{2}:\d{2}:\d{2}$/', $dato) ? $dato : null;
        default:
            return htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    }
}

// ========== FUNCIONES PARA EQUIPO ==========
function getEquipo($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM EQUIPO ORDER BY id_equipo LIMIT 1");
        $equipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $equipo]);
    } catch(PDOException $e) {
        error_log("Error en getEquipo: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener equipo']);
    }
}

function insertEquipo($pdo) {
    try {
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $pais = validarEntrada($_POST['pais'] ?? '');
        $fundacion = validarEntrada($_POST['fundacion'] ?? '0', 'entero');
        $presupuesto_anual = validarEntrada($_POST['presupuesto_anual'] ?? '0', 'decimal');
        $director_tecnico = validarEntrada($_POST['director_tecnico'] ?? '');
        $sede_principal = validarEntrada($_POST['sede_principal'] ?? '');

        if (empty($nombre) || empty($pais) || $fundacion < 1950 || $presupuesto_anual < 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "INSERT INTO EQUIPO (nombre, pais, fundacion, presupuesto_anual, director_tecnico, sede_principal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $pais, $fundacion, $presupuesto_anual, $director_tecnico, $sede_principal]);

        echo json_encode(['success' => true, 'message' => 'Equipo insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertEquipo: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al insertar equipo: ' . $e->getMessage()]);
    }
}

function updateEquipo($pdo) {
    try {
        $id_equipo = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $pais = validarEntrada($_POST['pais'] ?? '');
        $fundacion = validarEntrada($_POST['fundacion'] ?? '0', 'entero');
        $presupuesto_anual = validarEntrada($_POST['presupuesto_anual'] ?? '0', 'decimal');
        $director_tecnico = validarEntrada($_POST['director_tecnico'] ?? '');
        $sede_principal = validarEntrada($_POST['sede_principal'] ?? '');

        if ($id_equipo <= 0 || empty($nombre) || empty($pais)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE EQUIPO SET nombre = ?, pais = ?, fundacion = ?, presupuesto_anual = ?, 
                director_tecnico = ?, sede_principal = ? WHERE id_equipo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $pais, $fundacion, $presupuesto_anual, $director_tecnico, $sede_principal, $id_equipo]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Equipo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el equipo para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateEquipo: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar equipo: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA PILOTOS ==========
function getPilotos($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM PILOTO ORDER BY id_piloto");
        $pilotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $pilotos]);
    } catch(PDOException $e) {
        error_log("Error en getPilotos: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener pilotos']);
    }
}

function getPiloto($pdo) {
    try {
        $id_piloto = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        if ($id_piloto <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de piloto inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM PILOTO WHERE id_piloto = ?");
        $stmt->execute([$id_piloto]);
        $piloto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($piloto) {
            echo json_encode(['success' => true, 'data' => $piloto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Piloto no encontrado']);
        }
    } catch(PDOException $e) {
        error_log("Error en getPiloto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener piloto']);
    }
}

function insertPiloto($pdo) {
    try {
        $id_equipo = validarEntrada($_POST['id_equipo'] ?? '1', 'entero');
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $nacionalidad = validarEntrada($_POST['nacionalidad'] ?? '');
        $fecha_nacimiento = validarEntrada($_POST['fecha_nacimiento'] ?? '', 'fecha');
        $licencia_fia = validarEntrada($_POST['licencia_fia'] ?? '', 'licencia');
        $tipo_piloto = validarEntrada($_POST['tipo_piloto'] ?? 'Titular');
        $numero_competition = !empty($_POST['numero_competition']) ? 
            validarEntrada($_POST['numero_competition'], 'entero') : null;
        $experiencia_anos = validarEntrada($_POST['experiencia_anos'] ?? '0', 'entero');
        $sueldo_base = validarEntrada($_POST['sueldo_base'] ?? '0', 'decimal');
        $fecha_contrato_inicio = validarEntrada($_POST['fecha_contrato_inicio'] ?? '', 'fecha');
        $fecha_contrato_fin = validarEntrada($_POST['fecha_contrato_fin'] ?? '', 'fecha');

        if (empty($nombre) || empty($nacionalidad) || empty($fecha_nacimiento) || empty($licencia_fia)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
            return;
        }

        $sql = "INSERT INTO PILOTO (id_equipo, nombre, nacionalidad, fecha_nacimiento, licencia_fia, tipo_piloto, 
                numero_competition, experiencia_anos, sueldo_base, fecha_contrato_inicio, fecha_contrato_fin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_equipo, $nombre, $nacionalidad, $fecha_nacimiento, $licencia_fia, $tipo_piloto, 
            $numero_competition, $experiencia_anos, $sueldo_base, $fecha_contrato_inicio, $fecha_contrato_fin
        ]);

        echo json_encode(['success' => true, 'message' => 'Piloto insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertPiloto: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Error: La licencia FIA o número de competencia ya existe']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar piloto: ' . $e->getMessage()]);
        }
    }
}

function updatePiloto($pdo) {
    try {
        $id_piloto = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $id_equipo = validarEntrada($_POST['id_equipo'] ?? '1', 'entero');
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $nacionalidad = validarEntrada($_POST['nacionalidad'] ?? '');
        $fecha_nacimiento = validarEntrada($_POST['fecha_nacimiento'] ?? '', 'fecha');
        $licencia_fia = validarEntrada($_POST['licencia_fia'] ?? '', 'licencia');
        $tipo_piloto = validarEntrada($_POST['tipo_piloto'] ?? 'Titular');
        $numero_competition = !empty($_POST['numero_competition']) ? 
            validarEntrada($_POST['numero_competition'], 'entero') : null;
        $experiencia_anos = validarEntrada($_POST['experiencia_anos'] ?? '0', 'entero');
        $sueldo_base = validarEntrada($_POST['sueldo_base'] ?? '0', 'decimal');
        $fecha_contrato_inicio = validarEntrada($_POST['fecha_contrato_inicio'] ?? '', 'fecha');
        $fecha_contrato_fin = validarEntrada($_POST['fecha_contrato_fin'] ?? '', 'fecha');

        if ($id_piloto <= 0 || empty($nombre) || empty($nacionalidad) || empty($licencia_fia)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE PILOTO SET id_equipo = ?, nombre = ?, nacionalidad = ?, fecha_nacimiento = ?, licencia_fia = ?, 
                tipo_piloto = ?, numero_competition = ?, experiencia_anos = ?, sueldo_base = ?, 
                fecha_contrato_inicio = ?, fecha_contrato_fin = ? WHERE id_piloto = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_equipo, $nombre, $nacionalidad, $fecha_nacimiento, $licencia_fia, $tipo_piloto, 
            $numero_competition, $experiencia_anos, $sueldo_base, $fecha_contrato_inicio, 
            $fecha_contrato_fin, $id_piloto
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Piloto actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el piloto para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updatePiloto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar piloto: ' . $e->getMessage()]);
    }
}

function deletePiloto($pdo) {
    try {
        $id_piloto = validarEntrada($_REQUEST['id'] ?? '0', 'entero');

        if ($id_piloto <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de piloto inválido']);
            return;
        }

        $sql = "DELETE FROM PILOTO WHERE id_piloto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_piloto]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Piloto eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el piloto para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deletePiloto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar piloto: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA AUTOS ==========
function getAutos($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM AUTO ORDER BY chasis_auto");
        $autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $autos]);
    } catch(PDOException $e) {
        error_log("Error en getAutos: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener autos']);
    }
}

function getAuto($pdo) {
    try {
        $chasis = validarEntrada($_GET['chasis'] ?? '', 'chasis');
        
        if (empty($chasis)) {
            echo json_encode(['success' => false, 'message' => 'Chasis inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM AUTO WHERE chasis_auto = ?");
        $stmt->execute([$chasis]);
        $auto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($auto) {
            echo json_encode(['success' => true, 'data' => $auto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Auto no encontrado']);
        }
    } catch(PDOException $e) {
        error_log("Error en getAuto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener auto']);
    }
}

function insertAuto($pdo) {
    try {
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $id_equipo = validarEntrada($_POST['id_equipo'] ?? '1', 'entero');
        $modelo = validarEntrada($_POST['modelo'] ?? '');
        $motor = validarEntrada($_POST['motor'] ?? '');
        $año = validarEntrada($_POST['ano'] ?? '0', 'entero');
        $tipo_auto = validarEntrada($_POST['tipo_auto'] ?? 'Competencia');
        $numero_competition = !empty($_POST['numero_competition']) ? 
            validarEntrada($_POST['numero_competition'], 'entero') : null;
        $estado_actual = validarEntrada($_POST['estado_actual'] ?? 'Disponible');
        $fecha_fabricacion = validarEntrada($_POST['fecha_fabricacion'] ?? '', 'fecha');
        $especificaciones_tecnicas = validarEntrada($_POST['especificaciones_tecnicas'] ?? '');

        if (empty($chasis_auto) || empty($modelo) || empty($motor) || $año < 2000) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "INSERT INTO AUTO (chasis_auto, id_equipo, modelo, motor, año, tipo_auto, numero_competition, 
                estado_actual, fecha_fabricacion, especificaciones_tecnicas) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $chasis_auto, $id_equipo, $modelo, $motor, $año, $tipo_auto, 
            $numero_competition, $estado_actual, $fecha_fabricacion, $especificaciones_tecnicas
        ]);

        echo json_encode(['success' => true, 'message' => 'Auto insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertAuto: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Error: El chasis ya existe']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar auto: ' . $e->getMessage()]);
        }
    }
}

function updateAuto($pdo) {
    try {
        $chasis_original = validarEntrada($_GET['chasis'] ?? '', 'chasis');
        
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $id_equipo = validarEntrada($_POST['id_equipo'] ?? '1', 'entero');
        $modelo = validarEntrada($_POST['modelo'] ?? '');
        $motor = validarEntrada($_POST['motor'] ?? '');
        $año = validarEntrada($_POST['ano'] ?? '0', 'entero');
        $tipo_auto = validarEntrada($_POST['tipo_auto'] ?? 'Competencia');
        $numero_competition = !empty($_POST['numero_competition']) ? 
            validarEntrada($_POST['numero_competition'], 'entero') : null;
        $estado_actual = validarEntrada($_POST['estado_actual'] ?? 'Disponible');
        $fecha_fabricacion = validarEntrada($_POST['fecha_fabricacion'] ?? '', 'fecha');
        $especificaciones_tecnicas = validarEntrada($_POST['especificaciones_tecnicas'] ?? '');

        if (empty($chasis_original) || empty($chasis_auto) || empty($modelo) || empty($motor)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE AUTO SET chasis_auto = ?, id_equipo = ?, modelo = ?, motor = ?, año = ?, tipo_auto = ?, 
                numero_competition = ?, estado_actual = ?, fecha_fabricacion = ?, especificaciones_tecnicas = ? 
                WHERE chasis_auto = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $chasis_auto, $id_equipo, $modelo, $motor, $año, $tipo_auto, 
            $numero_competition, $estado_actual, $fecha_fabricacion, $especificaciones_tecnicas, $chasis_original
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Auto actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el auto para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateAuto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar auto: ' . $e->getMessage()]);
    }
}

function deleteAuto($pdo) {
    try {
        $chasis = validarEntrada($_REQUEST['chasis'] ?? '', 'chasis');

        if (empty($chasis)) {
            echo json_encode(['success' => false, 'message' => 'Chasis inválido']);
            return;
        }

        $sql = "DELETE FROM AUTO WHERE chasis_auto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$chasis]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Auto eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el auto para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteAuto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar auto: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA MANTENIMIENTO ==========
function getMantenimiento($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM MANTENIMIENTO ORDER BY fecha_mantenimiento DESC");
        $mantenimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $mantenimiento]);
    } catch(PDOException $e) {
        error_log("Error en getMantenimiento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener mantenimientos']);
    }
}

function getMantenimientoById($pdo) {
    try {
        $id_mantenimiento = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        if ($id_mantenimiento <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de mantenimiento inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM MANTENIMIENTO WHERE id_mantenimiento = ?");
        $stmt->execute([$id_mantenimiento]);
        $mantenimiento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mantenimiento) {
            echo json_encode(['success' => true, 'data' => $mantenimiento]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mantenimiento no encontrado']);
        }
    } catch(PDOException $e) {
        error_log("Error en getMantenimientoById: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener mantenimiento']);
    }
}

function insertMantenimiento($pdo) {
    try {
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $fecha_mantenimiento = validarEntrada($_POST['fecha_mantenimiento'] ?? '', 'fecha');
        $tipo = validarEntrada($_POST['tipo'] ?? 'Preventivo');
        $descripcion = validarEntrada($_POST['descripcion'] ?? '');
        $duracion_horas = validarEntrada($_POST['duracion_horas'] ?? '0', 'decimal');
        $costo_estimado = validarEntrada($_POST['costo_estimado'] ?? '0', 'decimal');
        $tecnico_responsable = validarEntrada($_POST['tecnico_responsable'] ?? '');

        if (empty($chasis_auto) || empty($fecha_mantenimiento) || empty($descripcion) || empty($tecnico_responsable)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
            return;
        }

        $sql = "INSERT INTO MANTENIMIENTO (chasis_auto, fecha_mantenimiento, tipo, descripcion, 
                duracion_horas, costo_estimado, tecnico_responsable) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $chasis_auto, $fecha_mantenimiento, $tipo, $descripcion,
            $duracion_horas, $costo_estimado, $tecnico_responsable
        ]);

        echo json_encode(['success' => true, 'message' => 'Mantenimiento insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertMantenimiento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al insertar mantenimiento: ' . $e->getMessage()]);
    }
}

function updateMantenimiento($pdo) {
    try {
        $id_mantenimiento = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $fecha_mantenimiento = validarEntrada($_POST['fecha_mantenimiento'] ?? '', 'fecha');
        $tipo = validarEntrada($_POST['tipo'] ?? 'Preventivo');
        $descripcion = validarEntrada($_POST['descripcion'] ?? '');
        $duracion_horas = validarEntrada($_POST['duracion_horas'] ?? '0', 'decimal');
        $costo_estimado = validarEntrada($_POST['costo_estimado'] ?? '0', 'decimal');
        $tecnico_responsable = validarEntrada($_POST['tecnico_responsable'] ?? '');

        if ($id_mantenimiento <= 0 || empty($chasis_auto) || empty($fecha_mantenimiento) || empty($descripcion)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE MANTENIMIENTO SET chasis_auto = ?, fecha_mantenimiento = ?, tipo = ?, descripcion = ?, 
                duracion_horas = ?, costo_estimado = ?, tecnico_responsable = ? 
                WHERE id_mantenimiento = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $chasis_auto, $fecha_mantenimiento, $tipo, $descripcion,
            $duracion_horas, $costo_estimado, $tecnico_responsable, $id_mantenimiento
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Mantenimiento actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el mantenimiento para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateMantenimiento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar mantenimiento: ' . $e->getMessage()]);
    }
}

function deleteMantenimiento($pdo) {
    try {
        $id_mantenimiento = validarEntrada($_REQUEST['id'] ?? '0', 'entero');

        if ($id_mantenimiento <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de mantenimiento inválido']);
            return;
        }

        $sql = "DELETE FROM MANTENIMIENTO WHERE id_mantenimiento = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_mantenimiento]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Mantenimiento eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el mantenimiento para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteMantenimiento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar mantenimiento: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA CIRCUITOS ==========
function getCircuitos($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM CIRCUITO ORDER BY nombre");
        $circuitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $circuitos]);
    } catch(PDOException $e) {
        error_log("Error en getCircuitos: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener circuitos']);
    }
}

function getCircuito($pdo) {
    try {
        $id_circuito = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        if ($id_circuito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de circuito inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM CIRCUITO WHERE id_circuito = ?");
        $stmt->execute([$id_circuito]);
        $circuito = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($circuito) {
            echo json_encode(['success' => true, 'data' => $circuito]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Circuito no encontrado']);
        }
    } catch(PDOException $e) {
        error_log("Error en getCircuito: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener circuito']);
    }
}

function insertCircuito($pdo) {
    try {
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $pais = validarEntrada($_POST['pais'] ?? '');
        $longitud_km = validarEntrada($_POST['longitud_km'] ?? '0', 'decimal');
        $record_vuelta = validarEntrada($_POST['record_vuelta'] ?? null, 'tiempo');
        $tipo_circuito = validarEntrada($_POST['tipo_circuito'] ?? 'Permanente');
        $numero_curvas = validarEntrada($_POST['numero_curvas'] ?? '0', 'entero');
        $capacidad_espectadores = validarEntrada($_POST['capacidad_espectadores'] ?? null, 'entero');

        if (empty($nombre) || empty($pais) || $longitud_km <= 0 || $numero_curvas <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "INSERT INTO CIRCUITO (nombre, pais, longitud_km, record_vuelta, tipo_circuito, 
                numero_curvas, capacidad_espectadores) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $pais, $longitud_km, $record_vuelta, $tipo_circuito,
            $numero_curvas, $capacidad_espectadores
        ]);

        echo json_encode(['success' => true, 'message' => 'Circuito insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertCircuito: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al insertar circuito: ' . $e->getMessage()]);
    }
}

function updateCircuito($pdo) {
    try {
        $id_circuito = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $pais = validarEntrada($_POST['pais'] ?? '');
        $longitud_km = validarEntrada($_POST['longitud_km'] ?? '0', 'decimal');
        $record_vuelta = validarEntrada($_POST['record_vuelta'] ?? null, 'tiempo');
        $tipo_circuito = validarEntrada($_POST['tipo_circuito'] ?? 'Permanente');
        $numero_curvas = validarEntrada($_POST['numero_curvas'] ?? '0', 'entero');
        $capacidad_espectadores = validarEntrada($_POST['capacidad_espectadores'] ?? null, 'entero');

        if ($id_circuito <= 0 || empty($nombre) || empty($pais) || $longitud_km <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE CIRCUITO SET nombre = ?, pais = ?, longitud_km = ?, record_vuelta = ?, 
                tipo_circuito = ?, numero_curvas = ?, capacidad_espectadores = ? 
                WHERE id_circuito = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $pais, $longitud_km, $record_vuelta, $tipo_circuito,
            $numero_curvas, $capacidad_espectadores, $id_circuito
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Circuito actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el circuito para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateCircuito: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar circuito: ' . $e->getMessage()]);
    }
}

function deleteCircuito($pdo) {
    try {
        $id_circuito = validarEntrada($_REQUEST['id'] ?? '0', 'entero');

        if ($id_circuito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de circuito inválido']);
            return;
        }

        $sql = "DELETE FROM CIRCUITO WHERE id_circuito = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_circuito]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Circuito eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el circuito para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteCircuito: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar circuito: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA CARRERAS ==========
function getCarreras($pdo) {
    try {
        $stmt = $pdo->query("SELECT c.*, cir.nombre as nombre_circuito FROM CARRERA c 
                            JOIN CIRCUITO cir ON c.id_circuito = cir.id_circuito 
                            ORDER BY c.fecha DESC");
        $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $carreras]);
    } catch(PDOException $e) {
        error_log("Error en getCarreras: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener carreras']);
    }
}

function getCarrera($pdo) {
    try {
        $id_carrera = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        if ($id_carrera <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de carrera inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM CARRERA WHERE id_carrera = ?");
        $stmt->execute([$id_carrera]);
        $carrera = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($carrera) {
            echo json_encode(['success' => true, 'data' => $carrera]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Carrera no encontrada']);
        }
    } catch(PDOException $e) {
        error_log("Error en getCarrera: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener carrera']);
    }
}

function insertCarrera($pdo) {
    try {
        $id_circuito = validarEntrada($_POST['id_circuito'] ?? '0', 'entero');
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $fecha = validarEntrada($_POST['fecha'] ?? '', 'fecha');
        $temporada = validarEntrada($_POST['temporada'] ?? '0', 'entero');
        $vueltas_planificadas = validarEntrada($_POST['vueltas_planificadas'] ?? '0', 'entero');
        $condiciones_climaticas = validarEntrada($_POST['condiciones_climaticas'] ?? 'Despejado');

        if (empty($nombre) || empty($fecha) || $id_circuito <= 0 || $temporada < 2020) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "INSERT INTO CARRERA (id_circuito, nombre, fecha, temporada, vueltas_planificadas, condiciones_climaticas) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_circuito, $nombre, $fecha, $temporada, $vueltas_planificadas, $condiciones_climaticas
        ]);

        echo json_encode(['success' => true, 'message' => 'Carrera insertada correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertCarrera: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al insertar carrera: ' . $e->getMessage()]);
    }
}

function updateCarrera($pdo) {
    try {
        $id_carrera = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $id_circuito = validarEntrada($_POST['id_circuito'] ?? '0', 'entero');
        $nombre = validarEntrada($_POST['nombre'] ?? '');
        $fecha = validarEntrada($_POST['fecha'] ?? '', 'fecha');
        $temporada = validarEntrada($_POST['temporada'] ?? '0', 'entero');
        $vueltas_planificadas = validarEntrada($_POST['vueltas_planificadas'] ?? '0', 'entero');
        $condiciones_climaticas = validarEntrada($_POST['condiciones_climaticas'] ?? 'Despejado');

        if ($id_carrera <= 0 || empty($nombre) || empty($fecha) || $id_circuito <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE CARRERA SET id_circuito = ?, nombre = ?, fecha = ?, temporada = ?, 
                vueltas_planificadas = ?, condiciones_climaticas = ? WHERE id_carrera = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_circuito, $nombre, $fecha, $temporada, $vueltas_planificadas, $condiciones_climaticas, $id_carrera
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Carrera actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la carrera para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateCarrera: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar carrera: ' . $e->getMessage()]);
    }
}

function deleteCarrera($pdo) {
    try {
        $id_carrera = validarEntrada($_REQUEST['id'] ?? '0', 'entero');

        if ($id_carrera <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de carrera inválido']);
            return;
        }

        $sql = "DELETE FROM CARRERA WHERE id_carrera = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_carrera]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Carrera eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la carrera para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteCarrera: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar carrera: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA PARTICIPACIONES ==========
function getParticipaciones($pdo) {
    try {
        $stmt = $pdo->query("SELECT p.*, pil.nombre as nombre_piloto, a.modelo, car.nombre as nombre_carrera 
                            FROM PARTICIPACION p
                            JOIN PILOTO pil ON p.id_piloto = pil.id_piloto
                            JOIN AUTO a ON p.chasis_auto = a.chasis_auto
                            JOIN CARRERA car ON p.id_carrera = car.id_carrera
                            ORDER BY p.fecha_creacion DESC");
        $participaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $participaciones]);
    } catch(PDOException $e) {
        error_log("Error en getParticipaciones: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener participaciones']);
    }
}

function getParticipacion($pdo) {
    try {
        $id_participacion = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        if ($id_participacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de participación inválido']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM PARTICIPACION WHERE id_participacion = ?");
        $stmt->execute([$id_participacion]);
        $participacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($participacion) {
            echo json_encode(['success' => true, 'data' => $participacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Participación no encontrada']);
        }
    } catch(PDOException $e) {
        error_log("Error en getParticipacion: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener participación']);
    }
}

function insertParticipacion($pdo) {
    try {
        $id_piloto = validarEntrada($_POST['id_piloto'] ?? '0', 'entero');
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $id_carrera = validarEntrada($_POST['id_carrera'] ?? '0', 'entero');
        $posicion_salida = validarEntrada($_POST['posicion_salida'] ?? null, 'entero');
        $estrategia_neumaticos = validarEntrada($_POST['estrategia_neumaticos'] ?? '');

        if ($id_piloto <= 0 || empty($chasis_auto) || $id_carrera <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "INSERT INTO PARTICIPACION (id_piloto, chasis_auto, id_carrera, posicion_salida, estrategia_neumaticos) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_piloto, $chasis_auto, $id_carrera, $posicion_salida, $estrategia_neumaticos
        ]);

        echo json_encode(['success' => true, 'message' => 'Participación insertada correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertParticipacion: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al insertar participación: ' . $e->getMessage()]);
    }
}

function updateParticipacion($pdo) {
    try {
        // IMPORTANTE: El ID viene por GET, no por POST
        $id_participacion = validarEntrada($_GET['id'] ?? '0', 'entero');
        
        $id_piloto = validarEntrada($_POST['id_piloto'] ?? '0', 'entero');
        $chasis_auto = validarEntrada($_POST['chasis_auto'] ?? '', 'chasis');
        $id_carrera = validarEntrada($_POST['id_carrera'] ?? '0', 'entero');
        $posicion_salida = validarEntrada($_POST['posicion_salida'] ?? null, 'entero');
        $estrategia_neumaticos = validarEntrada($_POST['estrategia_neumaticos'] ?? '');

        if ($id_participacion <= 0 || $id_piloto <= 0 || empty($chasis_auto) || $id_carrera <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $sql = "UPDATE PARTICIPACION SET id_piloto = ?, chasis_auto = ?, id_carrera = ?, 
                posicion_salida = ?, estrategia_neumaticos = ? WHERE id_participacion = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_piloto, 
            $chasis_auto, 
            $id_carrera, 
            $posicion_salida, 
            $estrategia_neumaticos, 
            $id_participacion  // ¡Este es el ID que va al final!
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Participación actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la participación para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateParticipacion: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar participación: ' . $e->getMessage()]);
    }
}

function deleteParticipacion($pdo) {
    try {
        $id_participacion = validarEntrada($_REQUEST['id'] ?? '0', 'entero');

        if ($id_participacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de participación inválido']);
            return;
        }

        $sql = "DELETE FROM PARTICIPACION WHERE id_participacion = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_participacion]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Participación eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la participación para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteParticipacion: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar participación: ' . $e->getMessage()]);
    }
}

// ========== FUNCIONES PARA RESULTADOS ==========
// ========== FUNCIONES PARA RESULTADOS ==========
function getResultados($pdo) {
    try {
        $stmt = $pdo->query("SELECT r.*, p.id_piloto, p.nombre as nombre_piloto, c.nombre as nombre_carrera 
                            FROM RESULTADO r
                            JOIN PARTICIPACION part ON r.id_participacion = part.id_participacion
                            JOIN PILOTO p ON part.id_piloto = p.id_piloto
                            JOIN CARRERA c ON part.id_carrera = c.id_carrera
                            ORDER BY c.fecha DESC, r.tipo_sesion");
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $resultados]);
    } catch(PDOException $e) {
        error_log("Error en getResultados: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener resultados']);
    }
}

function getResultado($pdo) {
    try {
        $id_participacion = validarEntrada($_GET['id'] ?? '0', 'entero');
        $tipo_sesion = validarEntrada($_GET['tipo'] ?? '');
        
        if ($id_participacion <= 0 || empty($tipo_sesion)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM RESULTADO WHERE id_participacion = ? AND tipo_sesion = ?");
        $stmt->execute([$id_participacion, $tipo_sesion]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            echo json_encode(['success' => true, 'data' => $resultado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resultado no encontrado']);
        }
    } catch(PDOException $e) {
        error_log("Error en getResultado: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener resultado']);
    }
}

function insertResultado($pdo) {
    try {
        $id_participacion = validarEntrada($_POST['id_participacion'] ?? '0', 'entero');
        $tipo_sesion = validarEntrada($_POST['tipo_sesion'] ?? '');
        
        // Los demás campos pueden ser NULL o vacíos
        $posicion_final = validarEntrada($_POST['posicion_final'] ?? null, 'entero');
        $puntos_obtenidos = validarEntrada($_POST['puntos_obtenidos'] ?? '0', 'entero');
        $tiempo_total = validarEntrada($_POST['tiempo_total'] ?? null);
        $vueltas_completadas = validarEntrada($_POST['vueltas_completadas'] ?? '0', 'entero');
        $estado_carrera = validarEntrada($_POST['estado_carrera'] ?? 'Completado');
        $motivo_abandono = validarEntrada($_POST['motivo_abandono'] ?? '');
        $vuelta_rapida = validarEntrada($_POST['vuelta_rapida'] ?? null);

        // Solo validar los campos obligatorios
        if ($id_participacion <= 0 || empty($tipo_sesion)) {
            echo json_encode(['success' => false, 'message' => 'Los campos Participación y Tipo de Sesión son obligatorios']);
            return;
        }

        $sql = "INSERT INTO RESULTADO (id_participacion, tipo_sesion, posicion_final, puntos_obtenidos, 
                tiempo_total, vueltas_completadas, estado_carrera, motivo_abandono, vuelta_rapida) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_participacion, $tipo_sesion, $posicion_final, $puntos_obtenidos,
            $tiempo_total, $vueltas_completadas, $estado_carrera, $motivo_abandono, $vuelta_rapida
        ]);

        echo json_encode(['success' => true, 'message' => 'Resultado insertado correctamente']);
    } catch(PDOException $e) {
        error_log("Error en insertResultado: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Error: Ya existe un resultado para esta participación y tipo de sesión']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar resultado: ' . $e->getMessage()]);
        }
    }
}

function updateResultado($pdo) {
    try {
        // IMPORTANTE: Los parámetros vienen por GET
        $id_participacion = validarEntrada($_GET['id'] ?? '0', 'entero');
        $tipo_sesion = validarEntrada($_GET['tipo'] ?? '');
        
        // Los datos vienen por POST
        $posicion_final = validarEntrada($_POST['posicion_final'] ?? null, 'entero');
        $puntos_obtenidos = validarEntrada($_POST['puntos_obtenidos'] ?? '0', 'entero');
        $tiempo_total = validarEntrada($_POST['tiempo_total'] ?? null);
        $vueltas_completadas = validarEntrada($_POST['vueltas_completadas'] ?? '0', 'entero');
        $estado_carrera = validarEntrada($_POST['estado_carrera'] ?? 'Completado');
        $motivo_abandono = validarEntrada($_POST['motivo_abandono'] ?? '');
        $vuelta_rapida = validarEntrada($_POST['vuelta_rapida'] ?? null);

        // Solo validar los parámetros de búsqueda
        if ($id_participacion <= 0 || empty($tipo_sesion)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos para actualizar']);
            return;
        }

        $sql = "UPDATE RESULTADO SET posicion_final = ?, puntos_obtenidos = ?, tiempo_total = ?, 
                vueltas_completadas = ?, estado_carrera = ?, motivo_abandono = ?, vuelta_rapida = ? 
                WHERE id_participacion = ? AND tipo_sesion = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $posicion_final, $puntos_obtenidos, $tiempo_total, $vueltas_completadas,
            $estado_carrera, $motivo_abandono, $vuelta_rapida, $id_participacion, $tipo_sesion
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Resultado actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el resultado para actualizar']);
        }
    } catch(PDOException $e) {
        error_log("Error en updateResultado: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar resultado: ' . $e->getMessage()]);
    }
}

function deleteResultado($pdo) {
    try {
        $id_participacion = validarEntrada($_REQUEST['id'] ?? '0', 'entero');
        $tipo_sesion = validarEntrada($_REQUEST['tipo'] ?? '');

        if ($id_participacion <= 0 || empty($tipo_sesion)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        $sql = "DELETE FROM RESULTADO WHERE id_participacion = ? AND tipo_sesion = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_participacion, $tipo_sesion]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Resultado eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el resultado para eliminar']);
        }
    } catch(PDOException $e) {
        error_log("Error en deleteResultado: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar resultado: ' . $e->getMessage()]);
    }
}

// ========== MANEJADOR PRINCIPAL DE ACCIONES ==========
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Manejar preflight CORS
if ($method === 'OPTIONS') {
    exit(0);
}

// Permitir DELETE sin verificar método específico
if ($method === 'DELETE') {
    switch($action) {
        case 'delete_piloto':
            deletePiloto($pdo);
            break;
        case 'delete_auto':
            deleteAuto($pdo);
            break;
        case 'delete_mantenimiento':
            deleteMantenimiento($pdo);
            break;
        case 'delete_circuito':
            deleteCircuito($pdo);
            break;
        case 'delete_carrera':
            deleteCarrera($pdo);
            break;
        case 'delete_participacion':
            deleteParticipacion($pdo);
            break;
        case 'delete_resultado':
            deleteResultado($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción DELETE no válida']);
    }
    exit;
}

// Para otros métodos (GET, POST)
switch($action) {
    // EQUIPO
    case 'get_equipo':
        getEquipo($pdo);
        break;
    case 'insert_equipo':
        insertEquipo($pdo);
        break;
    case 'update_equipo':
        updateEquipo($pdo);
        break;
        
    // PILOTOS
    case 'get_pilotos':
        getPilotos($pdo);
        break;
    case 'get_piloto':
        getPiloto($pdo);
        break;
    case 'insert_piloto':
        insertPiloto($pdo);
        break;
    case 'update_piloto':
        updatePiloto($pdo);
        break;
    case 'delete_piloto':
        deletePiloto($pdo);
        break;
        
    // AUTOS
    case 'get_autos':
        getAutos($pdo);
        break;
    case 'get_auto':
        getAuto($pdo);
        break;
    case 'insert_auto':
        insertAuto($pdo);
        break;
    case 'update_auto':
        updateAuto($pdo);
        break;
    case 'delete_auto':
        deleteAuto($pdo);
        break;
        
    // MANTENIMIENTO
    case 'get_mantenimiento':
        getMantenimiento($pdo);
        break;
    case 'get_mantenimiento_by_id':
        getMantenimientoById($pdo);
        break;
    case 'insert_mantenimiento':
        insertMantenimiento($pdo);
        break;
    case 'update_mantenimiento':
        updateMantenimiento($pdo);
        break;
    case 'delete_mantenimiento':
        deleteMantenimiento($pdo);
        break;
        
    // CIRCUITOS
    case 'get_circuitos':
        getCircuitos($pdo);
        break;
    case 'get_circuito':
        getCircuito($pdo);
        break;
    case 'insert_circuito':
        insertCircuito($pdo);
        break;
    case 'update_circuito':
        updateCircuito($pdo);
        break;
    case 'delete_circuito':
        deleteCircuito($pdo);
        break;
        
    // CARRERAS
    case 'get_carreras':
        getCarreras($pdo);
        break;
    case 'get_carrera':
        getCarrera($pdo);
        break;
    case 'insert_carrera':
        insertCarrera($pdo);
        break;
    case 'update_carrera':
        updateCarrera($pdo);
        break;
    case 'delete_carrera':
        deleteCarrera($pdo);
        break;
        
    // PARTICIPACIONES
    case 'get_participaciones':
        getParticipaciones($pdo);
        break;
    case 'get_participacion':
        getParticipacion($pdo);
        break;
    case 'insert_participacion':
        insertParticipacion($pdo);
        break;
    case 'update_participacion':
        updateParticipacion($pdo);
        break;
    case 'delete_participacion':
        deleteParticipacion($pdo);
        break;
        
    // RESULTADOS
    case 'get_resultados':
        getResultados($pdo);
        break;
    case 'get_resultado':
        getResultado($pdo);
        break;
    case 'insert_resultado':
        insertResultado($pdo);
        break;
    case 'update_resultado':
        updateResultado($pdo);
        break;
    case 'delete_resultado':
        deleteResultado($pdo);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida: ' . $action]);
}
?>