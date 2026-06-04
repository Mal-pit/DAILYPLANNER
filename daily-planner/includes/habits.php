<?php
require_once 'includes/config.php';

// ADD
if (isset($_POST['add_habit'])) {
    $name = $_POST['name'];
    $icon = $_POST['icon'] ?: '✅';
    $target = $_POST['target'];
    $category = $_POST['category'];
    $user_id = 1;

    mysqli_query($conn, "INSERT INTO habits (user_id,name,icon,target,category) 
    VALUES ('$user_id','$name','$icon','$target','$category')");

    header("Location: habits.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Habits</title>
</head>
<body>

<h2>Add Habit</h2>
<form method="POST">
<input type="text" name="name" placeholder="Habit Name" required>
<input type="text" name="icon" placeholder="Icon">
<input type="number" name="target" placeholder="Target" required>
<input type="text" name="category" placeholder="Category">
<button name="add_habit">Add</button>
</form>

<h2>Habit List</h2>

<?php
$result = mysqli_query($conn, "SELECT * FROM habits");
while ($row = mysqli_fetch_assoc($result)) {
?>
<div style="border:1px solid #000;margin:10px;padding:10px;">
<h3><?= $row['icon']." ".$row['name']; ?></h3>
<p>Target: <?= $row['target']; ?></p>
<p>Category: <?= $row['category']; ?></p>

<a href="edit.php?id=<?= $row['id']; ?>">Edit</a>
<a href="delete.php?id=<?= $row['id']; ?>">Delete</a>
</div>
<?php } ?>

</body>
</html>
