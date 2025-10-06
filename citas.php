<?php
// citas.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include('db_connection.php');

// Lógica de AGREGAR CITA (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_cita'])) {
    $paciente_id = $_POST['paciente_id'] ?? 0;
    $fecha_hora = $_POST['fecha_hora'] ?? '';
    $motivo = $_POST['motivo'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO citas (paciente_id, fecha, motivo) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $paciente_id, $fecha_hora, $motivo);
    $stmt->execute();
    $stmt->close();
    header("Location: citas.php");
    exit;
}

// Lógica de LEER CITAS (READ - usando JOIN)
$sql_citas = "SELECT c.id, c.fecha, c.motivo, c.estado, p.nombre, p.apellido 
              FROM citas c 
              JOIN pacientes p ON c.paciente_id = p.id
              ORDER BY c.fecha DESC";
$result_citas = $conn->query($sql_citas);
$citas = $result_citas->fetch_all(MYSQLI_ASSOC);

// Obtener lista de pacientes para el formulario
$result_pacientes = $conn->query("SELECT id, nombre, apellido FROM pacientes ORDER BY nombre ASC");
$pacientes = $result_pacientes->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas | Clínica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <aside class="w-64 bg-gray-800 h-screen p-4 text-white">
            <h1 class="text-xl font-bold mb-6">Clínica Dashboard</h1>
            <p class="text-sm text-gray-400 mb-4">Módulo Citas</p>
            <ul>
                <li class="mb-2"><a href="dashboard.php" class="block p-2 rounded hover:bg-gray-700">Inicio</a></li>
                <li class="mb-2"><a href="pacientes.php" class="block p-2 rounded hover:bg-gray-700">Pacientes</a></li>
                <li class="mb-2"><a href="citas.php" class="block p-2 rounded bg-gray-700">Citas</a></li> <li class="mb-2"><a href="logout.php" class="block p-2 rounded hover:bg-red-700 bg-red-500">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Citas</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-semibold mb-4">Agendar Nueva Cita</h2>
                <form action="citas.php" method="POST" class="grid grid-cols-4 gap-4">
                    <select name="paciente_id" required class="p-2 border rounded">
                        <option value="">Seleccionar Paciente</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre'] . ' ' . $p['apellido']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="datetime-local" name="fecha_hora" required class="p-2 border rounded">
                    <input type="text" name="motivo" placeholder="Motivo de la Cita" required class="p-2 border rounded">
                    <button type="submit" name="add_cita" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Agendar</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Próximas Citas (Total: <?php echo count($citas); ?>)</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $cita['nombre'] . ' ' . $cita['apellido']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $cita['motivo']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?php echo $cita['estado']; ?></span>
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