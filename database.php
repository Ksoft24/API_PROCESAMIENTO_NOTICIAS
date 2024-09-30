<?php
class Database {
    private $host = 'localhost';  // Cambia si usas otro host
    private $db_name = 'hudbay';  // Cambia por el nombre de tu base de datos
    private $username = 'root';  // Cambia por tu nombre de usuario de la base de datos
    private $password = '';  // Cambia por tu contraseña de la base de datos
    private $conn;

    // Método para obtener la conexión
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            // Establecemos el modo de errores de PDO a excepción
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Definimos el charset para evitar problemas con caracteres especiales
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
