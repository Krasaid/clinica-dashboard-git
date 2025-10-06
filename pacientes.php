<?php
// pacientes.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include('db_connection.php');

// Lógica de AGREGAR PACIENTE (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_paciente'])) {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $apellido, $email);
    $stmt->execute();
    $stmt->close();
    header("Location: pacientes.php");
    exit;
}

// Lógica de LEER PACIENTES (READ)
$result = $conn->query("SELECT id, nombre, apellido, email FROM pacientes ORDER BY id DESC");
$pacientes = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes | Clínica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <aside class="w-64 bg-gray-800 h-screen p-4 text-white">
            <h1 class="text-xl font-bold mb-6">Clínica Dashboard</h1>
            <p class="text-sm text-gray-400 mb-4">Módulo Pacientes</p>
            <ul>
                <li class="mb-2"><a href="dashboard.php" class="block p-2 rounded hover:bg-gray-700">Inicio</a></li>
                <li class="mb-2"><a href="pacientes.php" class="block p-2 rounded bg-gray-700">Pacientes</a></li>
                <li class="mb-2"><a href="logout.php" class="block p-2 rounded hover:bg-red-700 bg-red-500">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Pacientes</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-semibold mb-4">Agregar Nuevo Paciente</h2>
                <form action="pacientes.php" method="POST" class="grid grid-cols-3 gap-4">
                    <input type="text" name="nombre" placeholder="Nombre" required class="p-2 border rounded">
                    <input type="text" name="apellido" placeholder="Apellido" required class="p-2 border rounded">
                    <input type="email" name="email" placeholder="Email" class="p-2 border rounded">
                    <button type="submit" name="add_paciente" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded col-span-1">Agregar</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Lista de Pacientes (Total: <?php echo count($pacientes); ?>)</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pacientes as $paciente): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $paciente['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $paciente['nombre'] . ' ' . $paciente['apellido']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $paciente['email']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Editar</a> | 
                                <a href="#" class="text-red-600 hover:text-red-900">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>