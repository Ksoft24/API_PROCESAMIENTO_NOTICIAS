<?php
// Incluir la clase Database para la conexión
require_once 'database.php';

// Definir cabeceras para permitir peticiones desde otros dominios
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Definir la ruta de la carpeta donde se almacenarán los documentos
$documentos_dir = 'DOCUMENTOS/';

// Verificar si el método de la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Decodificar el cuerpo de la solicitud POST (asumiendo que los datos se envían en formato JSON)
    $data = json_decode(file_get_contents("php://input"));

    // Verificar si se recibieron todos los datos necesarios
    if (!empty($data->id_analisis) && !empty($data->nombredocumento) && !empty($data->base64)) {
        try {
            // Primero, insertamos el registro en la base de datos
            $query = "INSERT INTO DOCUMENTOS (id_analisis, nombredocumento, ruta) VALUES (:id_analisis, :nombredocumento, '')";
            
            // Preparar la declaración SQL
            $stmt = $db->prepare($query);

            // Asignar los valores
            $stmt->bindParam(":id_analisis", $data->id_analisis);
            $stmt->bindParam(":nombredocumento", $data->nombredocumento);

            // Ejecutar la declaración
            if ($stmt->execute()) {
                // Obtener el ID del documento recién creado
                $id_creado = $db->lastInsertId();

                // Guardar el archivo base64 en la carpeta de DOCUMENTOS
                $decoded_file = base64_decode($data->base64);

                // Definir la ruta completa del archivo
                $file_path = $documentos_dir . $id_creado . ".pdf"; // Asumiendo que son archivos PDF

                // Guardar el archivo en la ruta especificada
                if (file_put_contents($file_path, $decoded_file) !== false) {
                    // Actualizar la ruta en la base de datos con el nombre del archivo
                    $update_query = "UPDATE DOCUMENTOS SET ruta = :ruta WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':ruta', $file_path);
                    $update_stmt->bindParam(':id', $id_creado);
                    $update_stmt->execute();

                    // Responder con el ID creado y confirmación
                    http_response_code(201); // Código 201: Creado
                    echo json_encode(array("message" => "Documento creado y almacenado exitosamente.", "id_creado" => $id_creado));
                } else {
                    // Si no se pudo guardar el archivo
                    http_response_code(500); // Código 500: Error interno del servidor
                    echo json_encode(array("message" => "No se pudo almacenar el archivo."));
                }
            } else {
                // Respuesta de error si no se pudo ejecutar la consulta
                http_response_code(503); // Código 503: Servicio no disponible
                echo json_encode(array("message" => "No se pudo crear el documento."));
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
