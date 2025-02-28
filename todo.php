<?php
require_once 'db.php';
require_once 'functions.php';

$errors = [];
if (ShowTodos()) {
  $todos = ShowTodos();
} else {
  $errors[] = "Något gick fel, försök igen!";
}

// LÄGG TILL NY TODO
if (isset($_POST["add_todo"])) {
  if (!isset($_POST["todo-input"]) || trim($_POST["todo-input"]) === "") {
    $errors[] = "Alla fält måste fyllas i!";
  } else {
    $todo = htmlspecialchars(trim($_POST["todo-input"]));
    if (NewTodo($todo)) {
      header('Location: todo.php');
      exit;
    } else {
      $errors[] = "Något gick fel, försök igen!";
    }
  }
}

// TA BORT EN TODO
if (isset($_GET["delete"])) {
  if (!isset($_GET["delete"]) || !ctype_digit($_GET["delete"])) {
    $errors[] = "Inget giltigt ID angivet!";
  } else {
    $id = (int) $_GET["delete"];
    if ($id <= 0) {
      $errors[] = "Inget giltigt ID angivet!";
    } else {
      if (DeleteToDo($id)) {
        header('Location: todo.php');
        exit;
      } else {
        $errors[] = "Något gick fel, försök igen!";
      }
    }
  }
}

// ÄNDRA EN TODO
if (isset($_POST["edit-todo"])) {
  if (!isset($_POST["id"]) || !ctype_digit($_POST["id"])) {
    $errors[] = "Inget giltigt ID angivet!";
  } else {
    $id = (int) $_POST["id"];
    $todo = trim($_POST["edit-text"]);

    if ($id <= 0) {
      $errors[] = "Inget giltigt ID angivet!";
    } elseif (empty($todo)) {
      $errors[] = "Alla fält måste fyllas i!";
    } else {
      EditToDo(htmlspecialchars($todo), $id);
      header('Location: todo.php');
      exit;
    }
  }
}


// MARKERA TODO SOM KLAR
if (isset($_GET["toggle"])) {
  if (!ctype_digit($_GET["toggle"])) {
    $errors[] = "Inget giltigt ID angivet!";
  } else {
    $id = (int)$_GET["toggle"];

    global $pdo;
    $sql = "SELECT done FROM todo_items WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $todo = $stmt->fetch();

    if (!$todo) {
      $errors[] = "Uppgiften hittades inte!";
    } else {
      $currentStatus = $todo["done"];
      if ($currentStatus !== "yes" && $currentStatus !== "no") {
        $errors[] = "Felaktigt statusvärde i databasen!";
      } else {
        $newStatus = ($currentStatus === "yes") ? "no" : "yes";

        ToggleTodoStatus($id, $newStatus);
        header('Location: todo.php');
        exit;
      }
    }
  }
}

?>


<!DOCTYPE html>
<html lang="sv">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Marcus ToDo App</title>
</head>

<body>
  <h1 class="title">MARCUS TODO APP</h1>
  <?php
  if ($errors) {
    foreach ($errors as $error) {
      echo "<p class='error-message'>$error</p>";
    }
  }
  ?>
  <div class="input-container">
    <form action="" method="POST">
      <input type="text" class="todo-input" name="todo-input" placeholder="Ange en ny todo och tryck på enter" required>
      <button type="submit" class="add-button" name="add_todo">LÄGG TILL</button>
    </form>
  </div>
  <?php foreach ($todos as $todo): ?>
    <div class="todo-list">
      <div class="todo-item">
        <input type="checkbox" class="todo-checkbox"
          onChange="window.location.href='todo.php?toggle=<?= $todo['id'] ?>'"
          <?= $todo["done"] === "yes" ? "checked" : "" ?>>

        <?php if (isset($_GET["edit"]) && (int)$_GET["edit"] === (int)$todo["id"]): ?>
          <form method="POST" action="">
            <input type="hidden" name="id" value="<?= htmlspecialchars($todo["id"]) ?>">
            <input type="text" name="edit-text" value="<?= htmlspecialchars($todo["content"]) ?>" required>
            <button type="submit" name="edit-todo">Spara</button>
          </form>
        <?php else: ?>
          <span class="<?= $todo["done"] === "yes" ? 'todo-item-checked' : '' ?>"> <?= htmlspecialchars($todo["content"]) ?> </span>
          <div class="actions">
            <button class="edit-button">
              <a href="todo.php?edit=<?= $todo["id"] ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
            </button>
            <button class="delete-button">
              <a href="todo.php?delete=<?= $todo["id"] ?>" onclick="return confirm('Är du säker på att du vill ta bort denna todo?')">
                <i class="fa-solid fa-trash"></i>
              </a>
            </button>
          </div>
        <?php endif; ?>
      </div>

    </div>
  <?php endforeach; ?>
</body>

</html>