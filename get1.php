<?php
include 'connection.php';

if (isset($_GET['publisher'])) {
    $publisher = $_GET['publisher'];
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "SELECT l.NAME, l.YEAR, l.ISBN, l.QUANTITY, l.LITERATE, 
                         GROUP_CONCAT(a.NAME SEPARATOR ', ') AS AUTHORS
                  FROM literature l
                  LEFT JOIN book_authrs ba ON l.Id = ba.FID_BOOK
                  LEFT JOIN author a ON ba.FID_AUTH = a.Id
                  WHERE l.PUBLISHER = :publisher
                  GROUP BY l.Id
                  ORDER BY l.NAME";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':publisher', $publisher, PDO::PARAM_STR);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($books) {
            echo "<h3>Книги видавництва: " . htmlspecialchars($publisher) . "</h3>";
            echo "<table border='1'>";
            echo "<tr style='background-color: Blue; color: white;'><th>Назва</th><th>Рік</th><th>ISBN</th><th>Кількість</th><th>Жанр</th><th>Автор</th></tr>";
            foreach ($books as $book) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($book['NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($book['YEAR']) . "</td>";
                echo "<td>" . htmlspecialchars($book['ISBN']) . "</td>";
                echo "<td>" . htmlspecialchars($book['QUANTITY']) . "</td>";
                echo "<td>" . htmlspecialchars($book['LITERATE']) . "</td>";
                echo "<td>" . htmlspecialchars($book['AUTHORS']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Книг цього видавництва не знайдено.</p>";
        }
    } catch (PDOException $e) {
        echo "Помилка запиту: " . $e->getMessage();
    }
} else {
    echo "<p>Не вибрано видавництво.</p>";
}
?>
