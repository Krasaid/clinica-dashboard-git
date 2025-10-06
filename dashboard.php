<?php
// dashboard.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include('db_connection.php');

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];

// LÓGICA DE MÉTRICAS
$total_pacientes = $conn->query("SELECT COUNT(id) AS total FROM pacientes")->fetch_assoc()['total'];
$citas_pendientes = $conn->query("SELECT COUNT(id) AS total FROM citas WHERE estado = 'pendiente'")->fetch_assoc()['total'];
$citas_mes = $conn->query("SELECT COUNT(id) AS total FROM citas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Clínica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script> 
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <aside class="w-64 bg-gray-800 h-screen p-4 text-white">
            <h1 class="text-xl font-bold mb-6">Clínica Dashboard</h1>
            <p class="text-sm text-gray-400 mb-4">Bienvenido, <?php echo $userName; ?> (<?php echo $userRole; ?>)</p>
            <ul>
                <li class="mb-2"><a href="dashboard.php" class="block p-2 rounded bg-gray-700">Inicio</a></li>
                <li class="mb-2"><a href="pacientes.php" class="block p-2 rounded hover:bg-gray-700">Pacientes</a></li>
                <li class="mb-2"><a href="citas.php" class="block p-2 rounded hover:bg-gray-700">Citas</a></li>
                <li class="mb-2"><a href="logout.php" class="block p-2 rounded hover:bg-red-700 bg-red-500">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Resumen General</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                    <p class="text-gray-500">Total Pacientes</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $total_pacientes; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                    <p class="text-gray-500">Citas Pendientes</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $citas_pendientes; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                    <p class="text-gray-500">Citas en el Mes</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $citas_mes; ?></p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4">Métricas del Mes (Ejemplo Estático)</h3>
                <canvas id="citasChart" class="h-80"></canvas>
            </div>

        </main>
    </div>
    
    <script>
        const ctx = document.getElementById('citasChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: '# Citas',
                    data: [12, 19, 3, 5, 2, 3], // Simulación de datos
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>