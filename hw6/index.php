<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск файлов</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Поиск файлов</h1>
    <form method="POST" action="">
        <label for="directory">Введите путь к папке:</label>
        <input type="text" name="directory" id="directory" required placeholder="Пример: /var/www/html или C:/Users/">

        <label for="file_mask">Маска файлов (например, *.txt):</label>
        <input type="text" name="file_mask" id="file_mask" required>

        <label for="search_text">Текст для поиска в файлах (опционально):</label>
        <input type="text" name="search_text" id="search_text">

        <button type="submit">Искать</button>
    </form>

    <div class="search-results">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $directory = $_POST['directory'];
            $fileMask = $_POST['file_mask'];
            $searchText = isset($_POST['search_text']) ? $_POST['search_text'] : '';

            if (!is_dir($directory)) {
                echo "<div class='error-message'>Указанный путь к папке не существует или недоступен.</div>";
                exit;
            }

            $results = searchFiles($directory, $fileMask, $searchText);
            $maxResults = 100;

            if (empty($results)) {
                echo "<div class='no-results'>Ничего не найдено. Проверьте путь и маску файлов.</div>";
            } else {
                $displayResults = array_slice($results, 0, $maxResults);
                echo "<h2>Результаты поиска:</h2>";
                echo "<ul class='results'>";
                foreach ($displayResults as $result) {
                    echo "<li>{$result}</li>";
                }
                echo "</ul>";

                if (count($results) > $maxResults) {
                    echo "<p class='more-results'>Показаны первые $maxResults результатов.</p>";
                }
            }
        }

        function searchFiles($directory, $fileMask, $searchText = '') {
            $foundFiles = [];
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

            $regexMask = '/' . str_replace(['*', '?'], ['.*', '.'], preg_quote($fileMask, '/')) . '$/i';

            foreach ($files as $file) {
                if ($file->isFile() && preg_match($regexMask, $file->getFilename())) {
                    $filePath = $file->getPathname();

                    if ($searchText) {
                        $content = file_get_contents($filePath);
                        if (stripos($content, $searchText) !== false) {
                            $positions = findPositions($content, $searchText);
                            $foundFiles[] = "$filePath - Найдено в позициях: " . implode(', ', $positions);
                        }
                    } else {
                        $foundFiles[] = $filePath;
                    }
                }
            }

            return $foundFiles;
        }

        function findPositions($content, $searchText) {
            $positions = [];
            $offset = 0;
            while (($pos = stripos($content, $searchText, $offset)) !== false) {
                $positions[] = $pos;
                $offset = $pos + strlen($searchText);
            }
            return $positions;
        }
        ?>
    </div>
</div>
</body>
</html>
