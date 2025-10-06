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

// LÓGICA DE MÉTRICAS (Mantenida en PHP para carga instantánea)
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
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Resumen General y Métricas</h1>
            
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
                <h3 class="text-xl font-semibold mb-4">Distribución de Citas por Estado (Datos Dinámicos)</h3>
                <canvas id="citasChart" class="h-80"></canvas> 
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Llamada AJAX a data.php para obtener datos del gráfico
            fetch('data.php')
                .then(response => response.json())
                .then(data => {
                    // Solo dibujamos si la respuesta es exitosa y contiene datos
                    if (data.status === 'success' && data.chart_data.length > 0) {
                        
                        // Preparar datos para el gráfico
                        const labels = data.chart_data.map(item => item.estado.charAt(0).toUpperCase() + item.estado.slice(1));
                        const counts = data.chart_data.map(item => parseInt(item.count));
                        
                        // Definir colores basados en el estado (manual)
                        const backgroundColors = labels.map(label => {
                            if (label === 'Pendiente') return 'rgba(251, 191, 36, 0.7)'; // Amarillo
                            if (label === 'Confirmada') return 'rgba(34, 197, 94, 0.7)'; // Verde
                            if (label === 'Cancelada') return 'rgba(239, 68, 68, 0.7)'; // Rojo
                            return 'rgba(156, 163, 175, 0.7)'; // Gris (Default)
                        });

                        // Inicializar el Gráfico
                        new Chart(document.getElementById('citasChart'), {
                            type: 'doughnut', 
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: '# Citas',
                                    data: counts,
                                    backgroundColor: backgroundColors,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'top' },
                                    title: { display: false }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar datos del gráfico:', error);
                });
        });
    </script>
</body>
</html>