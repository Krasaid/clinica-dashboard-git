<?php
// data.php (API para métricas del gráfico)

// Configurar encabezados para devolver JSON
header('Content-Type: application/json');
// Incluir la conexión a la base de datos
include('db_connection.php');

// LÓGICA: Obtener Citas por Estado (para el gráfico)
$citas_estado_result = $conn->query("SELECT estado, COUNT(id) as count FROM citas GROUP BY estado");

// Inicializar un array vacío si la consulta falla o no tiene resultados
$citas_data = $citas_estado_result ? $citas_estado_result->fetch_all(MYSQLI_ASSOC) : [];

// Estructurar la respuesta
$response = [
    'status' => 'success',
    'chart_data' => $citas_data
];

echo json_encode($response);
$conn->close();
?>