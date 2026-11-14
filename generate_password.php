
<?php

echo "<h2>Admin Password Hash</h2>";

echo password_hash(
    "Admin123",
    PASSWORD_DEFAULT
);

echo "<h2>Trainer Password Hash</h2>";

echo password_hash(
    "Trainer123",
    PASSWORD_DEFAULT
);

?>
