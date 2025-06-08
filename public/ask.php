<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$message = '';

// Загружаем категории и подкатегории
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$subcategories = $pdo->query("SELECT id, category_id, name FROM subcategories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');
  $category_id = $_POST['category_id'] ?? '';
  $subcategory_id = $_POST['subcategory_id'] ?? '';

  // Проверяем заполнение всех полей
  if ($title === '' || $body === '' || $category_id === '' || $subcategory_id === '') {
    $message = 'Пожалуйста, заполните все поля.';
  } else {
    // Вставляем вопрос в базу с категориями
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, title, body, category_id, subcategory_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $body, $category_id, $subcategory_id]);
    header('Location: questions.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title>Задать вопрос - Форум</title>
  <link rel="stylesheet" href="styles/ask.css">

  <style>
    /* стили для фильтров */
    .filters {
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .filters-row {
      display: flex;
      gap: 16px;
      align-items: center;
    }

    .filters select {
      padding: 6px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      background: #2a2a40;
      color: #e0e7ff;
      font-size: 1rem;
      min-width: 180px;
    }

    #subcategory_id {
      margin-bottom: 0.8rem;
    }
  </style>

  <script>
    // JS для динамического обновления подкатегорий
    document.addEventListener('DOMContentLoaded', function () {
      const categories = <?= json_encode($categories) ?>;
      const subcategories = <?= json_encode($subcategories) ?>;
      const categorySelect = document.getElementById('category_id');
      const subcategorySelect = document.getElementById('subcategory_id');

      function updateSubcategories() {
        const selectedCategoryId = categorySelect.value;
        // Очищаем подкатегории
        subcategorySelect.innerHTML = '<option value="">-- Выберите подкатегорию --</option>';
        if (!selectedCategoryId) return;

        // Добавляем подкатегории, соответствующие выбранной категории
        subcategories.forEach(sub => {
          if (sub.category_id == selectedCategoryId) {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            subcategorySelect.appendChild(option);
          }
        });

        // Если у вас был выбран subcategory_id при ошибке валидации, восстановим выбор
        <?php if (!empty($_POST['subcategory_id'])): ?>
          const prevSubcat = <?= json_encode($_POST['subcategory_id']) ?>;
          subcategorySelect.value = prevSubcat;
        <?php endif; ?>
      }

      categorySelect.addEventListener('change', updateSubcategories);

      // При загрузке страницы сразу обновляем подкатегории, если есть выбранная категория
      updateSubcategories();

      // Восстановим выбранную категорию при ошибке валидации
      <?php if (!empty($_POST['category_id'])): ?>
        categorySelect.value = <?= json_encode($_POST['category_id']) ?>;
      <?php endif; ?>
    });
  </script>
</head>

<body>
  <?php include 'menu.php'; ?>

  <div class="container">
    <h2>Задать вопрос</h2>
    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post" action="ask.php" novalidate>
      <label for="title">Название вопроса:</label>
      <input id="title" type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>

      <label for="body">Текст вопроса:</label>
      <textarea id="body" name="body" class="textarea-text"
        required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>

      <div class="filters">
        <div class="filters-row">
          <select id="category_id" name="category_id" required>
            <option value="">-- Выберите категорию --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filters-row">
          <select id="subcategory_id" name="subcategory_id" required>
            <option value="">-- Выберите подкатегорию --</option>
            <!-- Опции подкатегорий будут динамически добавлены JS -->
          </select>
          </di>
        </div>


        <button type="submit">Отправить</button>
    </form>
    <p><a href="index.php" class="btn-secondary">Назад</a></p>
  </div>

</body>

</html>