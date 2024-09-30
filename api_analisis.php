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
    if (!empty($data->id_usuario) && !empty($data->fechahora)) {
        try {
            // Preparar la consulta SQL de inserción
            $query = "INSERT INTO ANALISIS (id_usuario, fechahora) VALUES (:id_usuario, :fechahora)";
            
            // Preparar la declaración SQL
            $stmt = $db->prepare($query);

            // Asignar los valores
            $stmt->bindParam(":id_usuario", $data->id_usuario);
            $stmt->bindParam(":fechahora", $data->fechahora);

            // Ejecutar la declaración
            if ($stmt->execute()) {
                // Obtener el último ID insertado
                $id_creado = $db->lastInsertId();

                // Respuesta de éxito con el ID creado
                http_response_code(201); // Código 201: Creado
                echo json_encode(array("message" => "Análisis creado exitosamente.", "id_creado" => $id_creado));
            } else {
                // Respuesta de error si no se pudo ejecutar la consulta
                http_response_code(503); // Código 503: Servicio no disponible
                echo json_encode(array("message" => "No se pudo crear el análisis."));
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