<?php
require_once '../db.php';

$comments = $pdo->query("SELECT * FROM comments WHERE status='pending'");
?>

<?php foreach ($comments as $c): ?>

<p><?= $c['content'] ?></p>

<a href="approve.php?id=<?= $c['id'] ?>">OK</a>

<?php endforeach; ?>
