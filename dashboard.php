<?php
// dashboard.php

session_start();
// REDIRECCIÓN: Si no hay sesión, regresa al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Clínica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <aside class="w-64 bg-gray-800 h-screen p-4 text-white">
            <h1 class="text-xl font-bold mb-6">Clínica Dashboard</h1>
            <p class="text-sm text-gray-400 mb-4">Bienvenido, <?php echo $userName; ?> (<?php echo $userRole; ?>)</p>
            <ul>
                <li class="mb-2"><a href="dashboard.php" class="block p-2 rounded bg-gray-700">Inicio</a></li>
                <li class="mb-2"><a href="logout.php" class="block p-2 rounded hover:bg-red-700 bg-red-500">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Resumen General</h1>
            <p class="text-gray-600">Este dashboard ahora está protegido por login y sesiones. Módulos y métricas serán implementados a continuación.</p>
        </main>
    </div>
</body>
</html>