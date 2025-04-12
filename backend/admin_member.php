<?php
require_once __DIR__ . 'database.php';
require_once __DIR__ . 'member.php';

// Start session and check admin access
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /login.php');
    exit;
}

$member = new Member($pdo);
$searchTerm = $_GET['search'] ?? '';
$members = $searchTerm ? $member->search($searchTerm) : $member->getAll();

include '_header.php';
?>

<div class="container">
    <h2>Member Management</h2>
    
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by username or email" value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?= $member['id'] ?></td>
                <td>
                    <?php if ($member['profile_photo']): ?>
                    <img src="/assets/uploads/<?= htmlspecialchars($member['profile_photo']) ?>" 
                         alt="Profile Photo" width="50" class="rounded-circle">
                    <?php endif; ?>
                    <?= htmlspecialchars($member['username']) ?>
                </td>
                <td><?= htmlspecialchars($member['email']) ?></td>
                <td><?= date('M j, Y', strtotime($member['created_at'])) ?></td>
                <td>
                    <a href="view-member.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '_footer.php'; ?>