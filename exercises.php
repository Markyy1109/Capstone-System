
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/functions.php";

requireLogin();
requireRole(["member"]);

$pageTitle = "Exercise Management";
$activePage = "exercises";


$formData = [
    "name" => "",
    "category" => "",
    "description" => "",
    "instructions" => ""
];

$errors = [];
$isEditing = isset($_GET["edit"]);
$editId = $isEditing ? (int)$_GET["edit"] : 0;


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    
    verifyCsrf();

    $action = $_POST["action"] ?? "";

    
    if ($action === "delete") {

        $id = (int)($_POST["id"] ?? 0);

        if ($id <= 0) {
            setMessage("Invalid exercise ID.");
        } else {

            try {
                $stmt = $conn->prepare(
                    "DELETE FROM exercises WHERE id = ?"
                );

                $stmt->bind_param("i", $id);
                $stmt->execute();

                setMessage(
                    $stmt->affected_rows > 0
                    ? "Exercise deleted successfully."
                    : "Exercise record was not found."
                );

                $stmt->close();

            } catch (mysqli_sql_exception $e) {
                setMessage("Unable to delete this exercise.");
            }
        }

        header("Location: exercises.php");
        exit();
    }

    
    if ($action === "add" || $action === "update") {

        $id = (int)($_POST["id"] ?? 0);

        $formData["name"] = trim($_POST["name"] ?? "");
        $formData["category"] = trim($_POST["category"] ?? "");
        $formData["description"] = trim($_POST["description"] ?? "");
        $formData["instructions"] = trim($_POST["instructions"] ?? "");

        $name = $formData["name"];
        $category = $formData["category"];
        $description = $formData["description"];
        $instructions = $formData["instructions"];

        
        if ($name === "") {
            $errors[] = "Exercise name is required.";
        }

        if ($category === "") {
            $errors[] = "Exercise category is required.";
        }

        
        if (strlen($name) > 120) {
            $errors[] = "Exercise name must not exceed 120 characters.";
        }

        if (strlen($category) > 80) {
            $errors[] = "Exercise category must not exceed 80 characters.";
        }

        if (strlen($description) > 65535) {
            $errors[] = "Description is too long.";
        }

        if (strlen($instructions) > 65535) {
            $errors[] = "Instructions are too long.";
        }

        
        if (empty($errors)) {

            
            $descriptionValue = $description === ""
                ? null
                : $description;

            $instructionsValue = $instructions === ""
                ? null
                : $instructions;

            try {

                if ($action === "add") {

                    
                    $stmt = $conn->prepare(
                        "INSERT INTO exercises
                        (name, category, description, instructions)
                        VALUES (?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "ssss",
                        $name,
                        $category,
                        $descriptionValue,
                        $instructionsValue
                    );

                    $stmt->execute();
                    $stmt->close();

                    setMessage("New exercise added successfully.");

                } elseif ($action === "update" && $id > 0) {

                    
                    $stmt = $conn->prepare(
                        "UPDATE exercises SET
                            name = ?,
                            category = ?,
                            description = ?,
                            instructions = ?
                         WHERE id = ?"
                    );

                    $stmt->bind_param(
                        "ssssi",
                        $name,
                        $category,
                        $descriptionValue,
                        $instructionsValue,
                        $id
                    );

                    $stmt->execute();
                    $stmt->close();

                    setMessage("Exercise updated successfully.");

                } else {
                    $errors[] = "Invalid exercise action.";
                }

                if (empty($errors)) {
                    header("Location: exercises.php");
                    exit();
                }

            } catch (mysqli_sql_exception $e) {
                $errors[] = "Database error. Please check the information and try again.";
            }
        }

       
        $isEditing = ($action === "update");
        $editId = $id;
    }
}


if ($_SERVER["REQUEST_METHOD"] !== "POST" && $isEditing) {

    if ($editId <= 0) {
        setMessage("Invalid exercise ID.");
        header("Location: exercises.php");
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT name, category, description, instructions
         FROM exercises
         WHERE id = ?"
    );

    $stmt->bind_param("i", $editId);
    $stmt->execute();

    $result = $stmt->get_result();
    $exercise = $result->fetch_assoc();

    $stmt->close();

    if (!$exercise) {
        setMessage("Exercise record not found.");
        header("Location: exercises.php");
        exit();
    }

    $formData = $exercise;
}


$search = trim($_GET["search"] ?? "");
$keyword = "%" . $search . "%";

$stmt = $conn->prepare(
    "SELECT id, name, category, description,
            instructions, created_at
     FROM exercises
     WHERE name LIKE ? OR category LIKE ?
     ORDER BY id DESC"
);

$stmt->bind_param("ss", $keyword, $keyword);
$stmt->execute();

$exercises = $stmt->get_result();


$totalExercises = $conn->query(
    "SELECT COUNT(*) AS total FROM exercises"
)->fetch_assoc()["total"];

$totalCategories = $conn->query(
    "SELECT COUNT(DISTINCT category) AS total FROM exercises"
)->fetch_assoc()["total"];


require_once __DIR__ . "/headers.php";
?>

<style>


.exercises-page {
    color: #f5f5f5;
}

.exercises-page .exercise-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}

.exercises-page .exercise-heading h1 {
    color: #d4af37;
    margin: 0 0 7px;
}

.exercises-page .exercise-heading p {
    color: #aaa;
    margin: 0;
}

.exercises-page .gold-btn {
    display: inline-block;
    background: #d4af37;
    color: #111;
    border: none;
    padding: 11px 18px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
}

.exercises-page .gold-btn:hover {
    background: #f0cf63;
}

.exercises-page .exercise-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.exercises-page .exercise-stat {
    background: #171717;
    border: 1px solid #333;
    border-left: 4px solid #d4af37;
    border-radius: 9px;
    padding: 18px;
}

.exercises-page .exercise-stat span {
    display: block;
    color: #aaa;
    font-size: 13px;
    margin-bottom: 8px;
}

.exercises-page .exercise-stat strong {
    color: #fff;
    font-size: 27px;
}

.exercises-page .exercise-panel {
    background: #171717;
    border: 1px solid #333;
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 22px;
}

.exercises-page .exercise-panel h2 {
    color: #d4af37;
    font-size: 19px;
    margin-top: 0;
}

.exercises-page .exercise-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.exercises-page .form-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.exercises-page .form-group.full-width {
    grid-column: 1 / -1;
}

.exercises-page .form-group label {
    color: #ddd;
    font-size: 13px;
    font-weight: bold;
}

.exercises-page .form-group input,
.exercises-page .form-group textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 11px;
    border: 1px solid #444;
    border-radius: 6px;
    background: #242424;
    color: #fff;
    outline: none;
    font-family: Arial, sans-serif;
}

.exercises-page .form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.exercises-page .form-group input:focus,
.exercises-page .form-group textarea:focus {
    border-color: #d4af37;
}

.exercises-page .form-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 5px;
}

.exercises-page .cancel-btn {
    display: inline-block;
    padding: 11px 18px;
    border-radius: 6px;
    background: #444;
    color: #fff;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.exercises-page .search-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.exercises-page .search-form input {
    flex: 1;
    min-width: 180px;
    padding: 11px;
    background: #242424;
    color: #fff;
    border: 1px solid #444;
    border-radius: 6px;
}

.exercises-page .table-responsive {
    width: 100%;
    overflow-x: auto;
}

.exercises-page table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
}

.exercises-page th {
    background: #242424;
    color: #d4af37;
    text-align: left;
    padding: 13px;
    font-size: 13px;
}

.exercises-page td {
    padding: 13px;
    border-bottom: 1px solid #333;
    color: #eee;
    font-size: 13px;
    vertical-align: top;
}

.exercises-page tr:hover td {
    background: #202020;
}

.exercises-page .category-label {
    display: inline-block;
    background: #3a321b;
    color: #e5c45b;
    border: 1px solid #695722;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
}

.exercises-page .action-links {
    display: flex;
    gap: 8px;
    align-items: center;
}

.exercises-page .edit-link {
    color: #e5c45b;
    text-decoration: none;
    font-weight: bold;
}

.exercises-page .delete-btn {
    background: #9d3030;
    color: white;
    border: none;
    padding: 7px 10px;
    border-radius: 5px;
    cursor: pointer;
}

.exercises-page .delete-btn:hover {
    background: #c43b3b;
}

.exercises-page .delete-form {
    display: inline;
}

.exercises-page .error-box {
    background: #4b2020;
    color: #ffdada;
    border: 1px solid #a94442;
    padding: 12px 16px;
    border-radius: 7px;
    margin-bottom: 18px;
}

.exercises-page .empty-message {
    text-align: center;
    color: #aaa;
    padding: 25px;
}

.exercises-page .description-cell {
    max-width: 220px;
    white-space: normal;
    overflow-wrap: anywhere;
}

@media (max-width: 700px) {
    .exercises-page .exercise-stats {
        grid-template-columns: 1fr;
    }

    .exercises-page .exercise-form {
        grid-template-columns: 1fr;
    }

    .exercises-page .form-group.full-width,
    .exercises-page .form-actions {
        grid-column: auto;
    }

    .exercises-page .exercise-panel {
        padding: 15px;
    }
}
</style>

<div class="exercises-page">

    
    <div class="exercise-heading">

        <div>
            <h1>Exercise Management</h1>
            <p>Manage the exercise library of Jobowtak Fitness GYM.</p>
        </div>

        <a href="exercises.php#exerciseForm" class="gold-btn">
            + Add New Exercise
        </a>

    </div>

    
    <div class="exercise-stats">

        <div class="exercise-stat">
            <span>Total Exercises</span>
            <strong><?= number_format((int)$totalExercises) ?></strong>
        </div>

        <div class="exercise-stat">
            <span>Total Categories</span>
            <strong><?= number_format((int)$totalCategories) ?></strong>
        </div>

    </div>

    
    <?php if (!empty($errors)): ?>

        <div class="error-box">
            <strong>Please correct the following:</strong>

            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= clean($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

    
    <div class="exercise-panel" id="exerciseForm">

        <h2>
            <?= $isEditing ? "Edit Exercise" : "Add New Exercise" ?>
        </h2>

        <form method="POST" action="" class="exercise-form">

            <input
                type="hidden"
                name="csrf"
                value="<?= clean(csrfToken()) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?= $isEditing ? "update" : "add" ?>"
            >

            <?php if ($isEditing): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$editId ?>"
                >

            <?php endif; ?>

            <div class="form-group">
                <label for="name">Exercise Name *</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="120"
                    value="<?= clean($formData["name"]) ?>"
                    placeholder="Example: Push Up"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Exercise Category *</label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    maxlength="80"
                    value="<?= clean($formData["category"]) ?>"
                    placeholder="Example: Strength Training"
                    required
                >
            </div>

            <div class="form-group full-width">
                <label for="description">Exercise Description</label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Enter a short description of the exercise..."
                ><?= clean($formData["description"]) ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="instructions">Exercise Instructions</label>

                <textarea
                    id="instructions"
                    name="instructions"
                    placeholder="Explain the steps for performing this exercise..."
                ><?= clean($formData["instructions"]) ?></textarea>
            </div>

            <div class="form-actions">

                <button type="submit" class="gold-btn">
                    <?= $isEditing ? "Update Exercise" : "Save Exercise" ?>
                </button>

                <?php if ($isEditing): ?>

                    <a href="exercises.php" class="cancel-btn">
                        Cancel Editing
                    </a>

                <?php else: ?>

                    <button type="reset" class="cancel-btn">
                        Clear Form
                    </button>

                <?php endif; ?>

            </div>

        </form>

    </div>

    
    <div class="exercise-panel">

        <h2>Registered Exercises</h2>

        
        <form method="GET" action="exercises.php" class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search exercise name or category..."
                value="<?= clean($search) ?>"
            >

            <button type="submit" class="gold-btn">
                Search
            </button>

            <a href="exercises.php" class="cancel-btn">
                Reset
            </a>

        </form>

        <div class="table-responsive">

            <table>

                <thead>
                    <tr>
                        <th>Exercise Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Instructions</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($exercises->num_rows > 0): ?>

                        <?php while ($row = $exercises->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= clean($row["name"]) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="category-label">
                                        <?= clean($row["category"]) ?>
                                    </span>
                                </td>

                                <td class="description-cell">
                                    <?= clean($row["description"]) ?>
                                </td>

                                <td class="description-cell">
                                    <?= clean($row["instructions"]) ?>
                                </td>

                                <td>
                                    <?= clean(date(
                                        "M d, Y",
                                        strtotime($row["created_at"])
                                    )) ?>
                                </td>

                                <td>

                                    <div class="action-links">

                                        <a
                                            href="exercises.php?edit=<?= (int)$row["id"] ?>#exerciseForm"
                                            class="edit-link"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="exercises.php"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this exercise?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf"
                                                value="<?= clean(csrfToken()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$row["id"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="empty-message">
                                No exercises found. Add a new exercise or change your search.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php
$stmt->close();
require_once __DIR__ . "/includes/footer.php";
?>
