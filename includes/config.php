<?php
// =============================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// Edite as informações abaixo conforme sua hospedagem
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // ← Altere aqui
define('DB_PASS', '');        // ← Altere aqui
define('DB_NAME', 'financas');         // ← Altere aqui (nome do banco)

define('APP_NAME', 'NogFinance');
define('APP_VERSION', '1.0');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Conexão PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['erro' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Funções auxiliares
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
