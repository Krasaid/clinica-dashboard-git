<?php
// dashboard.php

// 1. GESTIÓN DE SESIÓN Y AUTENTICACIÓN
session_start();
// Si el usuario no está logueado, redirigir a la página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Incluir la conexión a la base de datos.
// Se asume que 'db_connection.php' inicializa la variable $conn (objeto mysqli).
include('db_connection.php');

// Obtener la información del usuario desde la sesión
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];

// Inicialización de variables para manejo de errores
$total_pacientes = 'N/A';
$citas_pendientes = 'N/A';
$citas_mes = 'N/A';
$chart_data = ['labels' => [], 'data' => []];

// 2. LÓGICA DE MÉTRICAS Y GRÁFICOS
try {
    // A. Métricas de tarjetas
    $result_pacientes = $conn->query("SELECT COUNT(id) AS total FROM pacientes");
    $total_pacientes = $result_pacientes ? $result_pacientes->fetch_assoc()['total'] : 0;

    $result_pendientes = $conn->query("SELECT COUNT(id) AS total FROM citas WHERE estado = 'pendiente'");
    $citas_pendientes = $result_pendientes ? $result_pendientes->fetch_assoc()['total'] : 0;

    $result_mes = $conn->query("SELECT COUNT(id) AS total FROM citas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())");
    $citas_mes = $result_mes ? $result_mes->fetch_assoc()['total'] : 0;

    // B. Lógica de datos para el gráfico (Últimos 6 meses)
    $meses_sql = [];
    $meses_traducciones = [
        'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr', 
        'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago', 
        'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
    ];
    $db_counts = [];

    // 1. Preparar las etiquetas (últimos 6 meses)
    for ($i = 5; $i >= 0; $i--) {
        $timestamp = strtotime("-$i months");
        $mes_year = date('Y-m', $timestamp);
        $mes_label_en = date('M', $timestamp);
        
        $meses_sql[] = $mes_year;
        $chart_data['labels'][] = $meses_traducciones[$mes_label_en] ?? $mes_label_en;
    }

    // 2. Consultar el conteo de citas para ese periodo
    $sql_chart = "
        SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(id) as total_citas
        FROM citas
        WHERE fecha >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH) AND estado = 'completada'
        GROUP BY mes
        ORDER BY mes
    ";
    
    $result_chart = $conn->query($sql_chart);

    if ($result_chart) {
        while ($row = $result_chart->fetch_assoc()) {
            $db_counts[$row['mes']] = (int)$row['total_citas'];
        }
    }

    // 3. Mapear los conteos de la base de datos a los 6 meses requeridos (0 si no hay datos)
    $final_counts = [];
    foreach ($meses_sql as $m) {
        $final_counts[] = $db_counts[$m] ?? 0;
    }
    $chart_data['data'] = $final_counts;

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Las variables ya están en 'N/A' o []
} finally {
    // Cerrar la conexión
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Clínica</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script> 
    <style>
        /* Estilo para asegurar que el canvas sea responsivo en altura */
        .chart-container {
            height: 320px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    <div class="flex">
        <!-- Barra Lateral de Navegación -->
        <aside class="w-64 bg-gray-800 h-screen p-4 text-white shadow-2xl sticky top-0">
            <h1 class="text-2xl font-extrabold mb-6 text-indigo-400 border-b border-gray-700 pb-3">Clínica Dashboard</h1>
            <p class="text-sm text-gray-400 mb-6 pb-4">Bienvenido, <span class="font-semibold text-white"><?php echo htmlspecialchars($userName); ?></span> (<?php echo htmlspecialchars($userRole); ?>)</p>
            
            <!-- Menú de Navegación -->
            <ul>
                <li class="mb-2">
                    <a href="dashboard.php" class="flex items-center p-3 rounded-lg font-medium transition duration-150 bg-gray-700 text-white shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                        Inicio
                    </a>
                </li>
                <li class="mb-2">
                    <a href="pacientes.php" class="flex items-center p-3 rounded-lg font-medium transition duration-150 hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                        Pacientes
                    </a>
                </li>
                <li class="mb-2">
                    <a href="citas.php" class="flex items-center p-3 rounded-lg font-medium transition duration-150 hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
                        Citas
                    </a>
                </li> 
                <li class="mt-8 pt-4 border-t border-gray-700">
                    <a href="logout.php" class="flex items-center justify-center p-3 rounded-lg font-semibold transition duration-150 hover:bg-red-600 bg-red-500 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" /></svg>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Contenido Principal -->
        <main class="flex-1 p-8">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-10">Resumen General</h1>
            
            <!-- Tarjetas de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-blue-500 hover:shadow-2xl transition duration-300 transform hover:scale-[1.01]">
                    <p class="text-sm font-semibold text-blue-500 uppercase">Total Pacientes</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2"><?php echo $total_pacientes; ?></p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-yellow-500 hover:shadow-2xl transition duration-300 transform hover:scale-[1.01]">
                    <p class="text-sm font-semibold text-yellow-600 uppercase">Citas Pendientes</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2"><?php echo $citas_pendientes; ?></p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-green-500 hover:shadow-2xl transition duration-300 transform hover:scale-[1.01]">
                    <p class="text-sm font-semibold text-green-600 uppercase">Citas en el Mes</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2"><?php echo $citas_mes; ?></p>
                </div>
            </div>

            <!-- Gráfico de Métricas -->
            <div class="bg-white p-8 rounded-xl shadow-xl">
                <h3 class="text-2xl font-bold mb-6 text-gray-700 border-b pb-2">Citas Completadas (Últimos 6 Meses)</h3>
                <div class="chart-container">
                    <canvas id="citasChart"></canvas>
                </div>
            </div>

        </main>
    </div>
    
    <!-- Script de Chart.js y Datos Dinámicos -->
    <script>
        // Los datos dinámicos generados por PHP
        const chartLabels = <?php echo json_encode($chart_data['labels']); ?>; 
        const chartData = <?php echo json_encode($chart_data['data']); ?>; 
        
        const ctx = document.getElementById('citasChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Citas Completadas',
                    data: chartData, 
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)', // Blue
                        'rgba(249, 115, 22, 0.7)', // Orange
                        'rgba(34, 197, 94, 0.7)', // Green
                        'rgba(239, 68, 68, 0.7)', // Red
                        'rgba(168, 85, 247, 0.7)', // Purple
                        'rgba(251, 191, 36, 0.7)' // Yellow
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)', 
                        'rgba(249, 115, 22, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(251, 191, 36, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6 // Bordes redondeados para las barras
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        bodyFont: {
                            size: 14
                        },
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        // Asegurar que el eje Y muestre números enteros
                        ticks: {
                            callback: function(value) {
                                if (value % 1 === 0) return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
