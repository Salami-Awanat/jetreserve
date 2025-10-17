<?php
class Vol {
    private $conn;
    private $table_name = "vols";

    public $id_vol;
    public $id_compagnie;
    public $depart;
    public $arrivee;
    public $date_depart;
    public $date_arrivee;
    public $prix;
    public $escales;
    public $classe;
    public $nom_compagnie;
    public $code_compagnie;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Rechercher des vols
    public function search($from, $to, $date, $passengers = 1) {
        $query = "SELECT v.*, c.nom_compagnie, c.code_compagnie 
                  FROM " . $this->table_name . " v
                  JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                  WHERE v.depart LIKE :from 
                  AND v.arrivee LIKE :to 
                  AND DATE(v.date_depart) = :date 
                  ORDER BY v.prix ASC";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindValue(':from', '%' . $from . '%');
        $stmt->bindValue(':to', '%' . $to . '%');
        $stmt->bindValue(':date', $date);

        $stmt->execute();
        return $stmt;
    }

    // Récupérer un vol par ID
    public function getById($id_vol) {
        $query = "SELECT v.*, c.nom_compagnie, c.code_compagnie 
                  FROM " . $this->table_name . " v
                  JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                  WHERE v.id_vol = :id_vol";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_vol', $id_vol);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les vols
    public function getAll() {
        $query = "SELECT v.*, c.nom_compagnie, c.code_compagnie 
                  FROM " . $this->table_name . " v
                  JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                  ORDER BY v.date_depart ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?>