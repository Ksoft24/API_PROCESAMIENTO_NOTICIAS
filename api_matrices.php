<?php
// Incluir la clase Database para la conexión
require_once 'database.php';

// Definir cabeceras para permitir peticiones desde otros dominios
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar si el método de la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Decodificar el cuerpo de la solicitud POST (asumiendo que los datos se envían en formato JSON)
    $data = json_decode(file_get_contents("php://input"));

    // Verificar si se recibieron todos los datos necesarios
    if (!empty($data->id_analisis) && !empty($data->descripcion) && !empty($data->impacto) && 
        !empty($data->probabilidad) && !empty($data->nivelriesgo) && !empty($data->consecuencias) && 
        !empty($data->acciones)) {
        
        try {
            // Preparar la consulta SQL de inserción (sin el campo fecha)
            $query = "INSERT INTO MATRICES (id_analisis, descripcion, impacto, probabilidad, nivelriesgo, consecuencias, acciones) 
                      VALUES (:id_analisis, :descripcion, :impacto, :probabilidad, :nivelriesgo, :consecuencias, :acciones)";
            
            // Preparar la declaración SQL
            $stmt = $db->prepare($query);

            // Asignar los valores
            $stmt->bindParam(":id_analisis", $data->id_analisis);
            $stmt->bindParam(":descripcion", $data->descripcion);
            $stmt->bindParam(":impacto", $data->impacto);
            $stmt->bindParam(":probabilidad", $data->probabilidad);
            $stmt->bindParam(":nivelriesgo", $data->nivelriesgo);
            $stmt->bindParam(":consecuencias", $data->consecuencias);
            $stmt->bindParam(":acciones", $data->acciones);

            // Ejecutar la declaración
            if ($stmt->execute()) {
                // Respuesta de éxito con el ID creado
                http_response_code(201); // Código 201: Creado
                echo json_encode(array("message" => "Matriz creada exitosamente."));
            } else {
                // Respuesta de error si no se pudo ejecutar la consulta
                http_response_code(503); // Código 503: Servicio no disponible
                echo json_encode(array("message" => "No se pudo crear la matriz."));
            }
        } catch (PDOException $e) {
            // Manejo de excepción en caso de error en la base de datos
            http_response_code(500); // Código 500: Error interno del servidor
            echo json_encode(array("message" => "Error en el servidor: " . $e->getMessage()));
        }
    } else {
        // Respuesta de error si faltan datos
        http_response_code(400); // Código 400: Solicitud incorrecta
        echo json_encode(array("message" => "Faltan datos requeridos."));
    }
} else {
    // Respuesta de error si el método no es POST
    http_response_code(405); // Código 405: Método no permitido
    echo json_encode(array("message" => "Método no permitido. Usa POST."));
}
?>
