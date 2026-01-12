<?php
// generar_hashes.php
echo "<h2> GENERADOR DE HASHES REALES</h2>";

// 
$passwords = [
    'admin' => 'admin2025',
    'usuario1' => 'clave123', 
    'piloto' => 'f1racing2024'
];

echo "<h3> HASHS GENERADOS:</h3>";
foreach ($passwords as $usuario => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
    echo "<strong>Usuario: {$usuario}</strong><br>";
    echo "Contraseña: '{$password}'<br>";
    echo "<strong>Hash:</strong> {$hash}<br>";
    echo "Verificación: " . (password_verify($password, $hash) ? '✅ OK' : '❌ ERROR');
    echo "</div>";
}

echo "<h3> CÓDIGO PARA COPIAR EN login.php:</h3>";
echo "<pre style='background: #000; color: #0f0; padding: 15px;'>";
echo "\$users = [\n";
foreach ($passwords as $usuario => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "    '{$usuario}' => [\n";
    echo "        'password' => '{$hash}',\n";
    echo "        'role' => '{$usuario}',\n";
    echo "        'nombre' => 'Usuario {$usuario}'\n";
    echo "    ],\n";
}
echo "];";
echo "</pre>";
?>