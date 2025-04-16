<?php
include 'connection.php';

$callback = isset($_GET['callback']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['callback']) : null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['author_id'])) {
        $response = ["error" => "Будь ласка, виберіть автора."];
    } else {
        $author_id = (int) $_GET['author_id'];

        $author_stmt = $pdo->prepare("SELECT NAME FROM author WHERE Id = :author_id");
        $author_stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);
        $author_stmt->execute();
        $author = $author_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$author) {
            $response = ["error" => "Автор не знайдений."];
        } else {
            $stmt = $pdo->prepare("
                SELECT l.NAME, l.YEAR, l.ISBN, l.QUANTITY, l.LITERATE
                FROM literature l
                LEFT JOIN book_authrs ba ON l.Id = ba.FID_BOOK
                LEFT JOIN author a ON ba.FID_AUTH = a.Id
                WHERE a.Id = :author_id
                GROUP BY l.Id
                ORDER BY l.NAME
            ");
            $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = ["author_name" => $author['NAME'], "books" => $books];
        }
    }

    if ($callback) {
        header('Content-Type: application/javascript');
        echo $callback . '(' . json_encode($response) . ');';
    } else {
        header('Content-Type: application/json');
        echo json_encode($response);
    }

} catch (PDOException $e) {
    $error = ["error" => "Помилка запиту: " . $e->getMessage()];
    if ($callback) {
        header('Content-Type: application/javascript');
        echo $callback . '(' . json_encode($error) . ');';
    } else {
        header('Content-Type: application/json');
        echo json_encode($error);
    }
}
?>
