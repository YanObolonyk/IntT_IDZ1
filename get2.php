<?php
include 'connection.php';
header('Content-Type: text/xml; charset=UTF-8'); // заголовок для XML-відповіді

echo "<?xml version='1.0' encoding='UTF-8'?>"; 
echo "<books>"; // починаємо XML-документ

if (isset($_GET['start_year']) && isset($_GET['end_year'])) {
    $start_year = (int) $_GET['start_year'];
    $end_year = (int) $_GET['end_year'];
    
    if ($start_year <= $end_year && $start_year >= 1900 && $end_year <= 2100) {
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "SELECT l.YEAR, l.NAME, l.ISBN, l.QUANTITY, l.LITERATE, 
                           GROUP_CONCAT(a.NAME SEPARATOR ', ') AS AUTHORS
                    FROM literature l
                    LEFT JOIN book_authrs ba ON l.Id = ba.FID_BOOK
                    LEFT JOIN author a ON ba.FID_AUTH = a.Id
                    WHERE YEAR BETWEEN :start_year AND :end_year
                    GROUP BY l.Id
                    ORDER BY l.YEAR";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':start_year', $start_year, PDO::PARAM_INT);
            $stmt->bindParam(':end_year', $end_year, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<book>";
                echo "<year>" . htmlspecialchars($row['YEAR']) . "</year>";
                echo "<name>" . htmlspecialchars($row['NAME']) . "</name>";
                echo "<isbn>" . htmlspecialchars($row['ISBN']) . "</isbn>";
                echo "<quantity>" . htmlspecialchars($row['QUANTITY']) . "</quantity>";
                echo "<literate>" . htmlspecialchars($row['LITERATE']) . "</literate>";
                echo "<authors>" . htmlspecialchars($row['AUTHORS']) . "</authors>";
                echo "</book>";
            }
        } catch (PDOException $e) {
            echo "<error>" . htmlspecialchars($e->getMessage()) . "</error>";
        }
    } else {
        echo "<error>Невірний діапазон років. Перевірте введені дані.</error>";
    }
} else {
    echo "<error>Будь ласка, введіть початковий та кінцевий рік.</error>";
}

echo "</books>"; // закриваємо XML-документ
?>
