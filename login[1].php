<?php
// login.php - CON GENERACIÓN AUTOMÁTICA DE HASHES
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// ✅ USUARIOS CON CONTRASEÑAS EN TEXTO (se hashean automáticamente)
$users_plain = [
    'admin' => [
        'password_plain' => 'admin123', // Se convierte a hash automáticamente
        'role' => 'administrador',
        'nombre' => 'Administrador Principal'
    ],
    'usuario1' => [
        'password_plain' => 'clave123',
        'role' => 'usuario', 
        'nombre' => 'Usuario Demo'
    ],
    'piloto' => [
        'password_plain' => 'f1racing2024',
        'role' => 'piloto',
        'nombre' => 'Piloto Oficial'
    ]
];

// ✅ CONVERTIR A HASHS AUTOMÁTICAMENTE
$users = [];
foreach ($users_plain as $username => $data) {
    $users[$username] = [
        'password' => password_hash($data['password_plain'], PASSWORD_DEFAULT),
        'role' => $data['role'],
        'nombre' => $data['nombre']
    ];
}


$action = $_GET['action'] ?? '';

// ✅ FUNCIÓN PARA VALIDAR Y SANITIZAR ENTRADAS
function validarEntrada($dato) {
    $dato = trim($dato ?? '');
    $dato = htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    return $dato;
}

if ($action === 'login') {
    $username = validarEntrada($_POST['username'] ?? '');
    $password = validarEntrada($_POST['password'] ?? '');
    
    // ✅ VALIDACIÓN BÁSICA
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false, 
            'message' => '❌ Usuario y contraseña son requeridos'
        ]);
        exit;
    }
    
    // ✅ VERIFICACIÓN SEGURA CON HASH
    if (isset($users[$username])) {
        // ✅ VERIFICAR CONTRASEÑA CON password_verify()
        if (password_verify($password, $users[$username]['password'])) {
            // ✅ LOGIN EXITOSO
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $users[$username]['role'];
            $_SESSION['nombre'] = $users[$username]['nombre'];
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            
            echo json_encode([
                'success' => true, 
                'message' => '✅ ¡Login exitoso! Redirigiendo al sistema...',
                'role' => $users[$username]['role'],
                'username' => $username
            ]);
            
        } else {
            // ❌ CONTRASEÑA INCORRECTA
            echo json_encode([
                'success' => false, 
                'message' => '❌ Usuario o contraseña incorrectos'
            ]);
        }
    } else {
        // ❌ USUARIO NO EXISTE
        echo json_encode([
            'success' => false, 
            'message' => '❌ Usuario o contraseña incorrectos'
        ]);
    }
}
else if ($action === 'logout') {
    // ✅ CERRAR SESIÓN
    session_destroy();
    echo json_encode([
        'success' => true, 
        'message' => '✅ Sesión cerrada correctamente'
    ]);
}
else if ($action === 'check') {
    // ✅ VERIFICAR ESTADO DE SESIÓN
    echo json_encode([
        'authenticated' => ($_SESSION['authenticated'] ?? false),
        'user' => ($_SESSION['user'] ?? ''),
        'role' => ($_SESSION['role'] ?? ''),
        'nombre' => ($_SESSION['nombre'] ?? '')
    ]);
}
else {
    echo json_encode([
        'success' => false, 
        'message' => '❌ Acción no válida'
    ]);
}
?>