<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

// Check admin access
auth_user();
auth('admin');

$searchTerm = $_GET['search'] ?? '';

try {
    if ($searchTerm) {
        $stm = $_db->prepare("SELECT * FROM users WHERE role = 'member' AND (name LIKE :search OR email LIKE :search)");
        $stm->execute([':search' => "%$searchTerm%"]);
    } else {
        $stm = $_db->prepare("SELECT * FROM users WHERE role = 'member'");
        $stm->execute();
    }
    $members = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching members.";
    $members = [];
}
?>
<?php include_once '../pages/admin/admin_header.php'; ?>

<div class="container">
    <h2>Member Management</h2>
    
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by name or email" value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?= $member->user_id ?></td>
                <td>
                    <?php if ($member->avatar): ?>
                    <img src="../img/avatar/<?= htmlspecialchars($member->avatar) ?>" 
                         alt="Profile Photo" width="50" class="rounded-circle">
                    <?php endif; ?>
                    <?= htmlspecialchars($member->name) ?>
                </td>
                <td><?= htmlspecialchars($member->email) ?></td>
                <td><?= date('M j, Y', strtotime($member->created_at)) ?></td>
                <td>
                    <a href="view-member.php?id=<?= $member->user_id ?>" class="btn btn-info btn-sm">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php include_once '../pages/admin/admin_footer.php'; ?>
</div>