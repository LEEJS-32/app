<?php
require_once __DIR__ . 'database.php';
require_once __DIR__ . 'member.php';

// Check admin access
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /login.php');
    exit;
}

$memberId = $_GET['id'] ?? 0;
$member = (new Member($pdo))->findById($memberId);

if (!$member) {
    header('Location: members.php');
    exit;
}

include '../includes/templates/header.php';
?>

<div class="container">
    <h2>Member Details</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if ($member['profile_photo']): ?>
            <img src="/assets/uploads/<?= htmlspecialchars($member['profile_photo']) ?>" 
                 alt="Profile Photo" width="150" class="rounded-circle mb-3">
            <?php endif; ?>
            
            <dl class="row">
                <dt class="col-sm-3">ID:</dt>
                <dd class="col-sm-9"><?= $member['id'] ?></dd>
                
                <dt class="col-sm-3">Username:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($member['username']) ?></dd>
                
                <dt class="col-sm-3">Email:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($member['email']) ?></dd>
                
                <dt class="col-sm-3">Joined:</dt>
                <dd class="col-sm-9"><?= date('M j, Y H:i', strtotime($member['created_at'])) ?></dd>
            </dl>
            
            <a href="members.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>