<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id_user;
    public $nom;
    public $prenom;
    public $email;
    public $telephone;
    public $password;
    public $role;
    public $statut;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Inscription utilisateur
    public function register() {
        // Vérifier d'abord si l'email existe déjà
        if ($this->emailExists($this->email)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET nom=:nom, prenom=:prenom, email=:email, 
                  telephone=:telephone, password=:password, 
                  role='client', statut='actif'";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        
        // Hasher le mot de passe
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Liaison des paramètres
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Connexion utilisateur
    public function login($email, $password) {
        $query = "SELECT id_user, nom, prenom, email, password, role, statut 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND statut = 'actif'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Vérifier si l'email existe
    public function emailExists($email) {
        $query = "SELECT id_user FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Récupérer un utilisateur par ID
    public function getById($id_user) {
        $query = "SELECT id_user, nom, prenom, email, telephone, role, statut, date_creation 
                  FROM " . $this->table_name . " 
                  WHERE id_user = :id_user";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $id_user);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le profil utilisateur
    public function updateProfile($id_user, $nom, $prenom, $telephone) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nom=:nom, prenom=:prenom, telephone=:telephone 
                  WHERE id_user = :id_user";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':id_user', $id_user);
        
        return $stmt->execute();
    }

    // Changer le mot de passe
    public function changePassword($id_user, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password 
                  WHERE id_user = :id_user";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id_user', $id_user);
        
        return $stmt->execute();
    }
}
?>