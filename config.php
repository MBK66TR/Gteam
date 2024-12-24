<?php
session_start();

class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        try {
            $this->db = new PDO('sqlite:' . __DIR__ . '/gamestore.db');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Tabloları oluştur
            $this->createTables();
        } catch(PDOException $e) {
            die("Veritabanı Hatası (Kod: " . $e->getCode() . "): " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    private function createTables() {
        try {
            // Geçici tablo oluştur
            $this->db->exec("CREATE TABLE IF NOT EXISTS games_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                genre TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                image_url TEXT,
                publisher TEXT NOT NULL,
                release_date DATE,
                added_by INTEGER,
                FOREIGN KEY (added_by) REFERENCES users(id)
            )");

            // Mevcut verileri geçici tabloya kopyala
            $this->db->exec("INSERT OR IGNORE INTO games_temp SELECT * FROM games");
            
            // Eski tabloyu sil
            $this->db->exec("DROP TABLE IF EXISTS games");
            
            // Geçici tabloyu yeni isimle yeniden adlandır
            $this->db->exec("ALTER TABLE games_temp RENAME TO games");
            
            echo "Veritabanı tabloları başarıyla güncellendi!";
            
        } catch(PDOException $e) {
            die("Veritabanı Hatası: " . $e->getMessage() . " (Kod: " . $e->getCode() . ")");
        }
    }
}

// Oturum kontrolü için yardımcı fonksiyon
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Admin kontrolü için yardımcı fonksiyon
function checkAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: index.php');
        exit();
    }
}
?> 