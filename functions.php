<?php
require_once 'db.php';

function NewTodo($todo)
{
  global $pdo;

  $sql = "INSERT INTO todo_items (content) VALUES (?)";
  $stmt = $pdo->prepare($sql);

  if ($stmt->execute([$todo])) {
    return true;
  } else {
    return false;
  }
}


function ShowTodos()
{
  global $pdo;

  $sql = "SELECT * FROM todo_items";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  return $stmt->fetchAll();
}

function DeleteToDo($id)
{
  global $pdo;

  $sql = "DELETE FROM todo_items WHERE id = ?";
  $stmt = $pdo->prepare($sql);

  return $stmt->execute([$id]);
}

function EditToDo($todo, $id)
{
  global $pdo;

  $sql = "UPDATE todo_items SET content = ? WHERE id = ?";
  $stmt = $pdo->prepare($sql);

  return $stmt->execute([$todo, $id]);
}

function ToggleTodoStatus($id, $status)
{
  global $pdo;
  $sql = "UPDATE todo_items SET done = ? WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  return $stmt->execute([$status, $id]);
}
