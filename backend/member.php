<?php
class Member {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($username, $email, $password, $photo = null) {
        $stmt = $this->pdo->prepare("INSERT INTO members 
            (username, email, password_hash, profile_photo) 
            VALUES (:username, :email, :password_hash, :photo)");
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $hash,
            ':photo' => $photo
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM members ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function search($term) {
        $stmt = $this->pdo->prepare("SELECT * FROM members 
                                   WHERE username LIKE :term OR email LIKE :term");
        $stmt->execute([':term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
?>