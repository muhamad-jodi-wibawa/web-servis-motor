<?php
require_once 'config/koneksi.php';
$res = mysqli_query($conn, "SELECT * FROM users");
echo "<h3>Daftar User di Database:</h3>";
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id_user'] . " | Username: " . $row['username'] . " | Role: " . $row['role'] . "<br>";
}
?>