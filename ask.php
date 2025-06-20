<?php
require_once __DIR__ . '/config/config.php';

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/ask.css">
</head>

<body>
  <?php include 'menu.php'; ?>

  <div class="container">
    <h2>Задать вопрос</h2>
    
    <div class="ask-card">
      <?php if ($message): ?>
        <div class="message">
          <i class="fas fa-exclamation-circle"></i>
          <span><?= htmlspecialchars($message) ?></span>
        </div>
      <?php endif; ?>
      
      <form method="post" action="ask.php" novalidate>
        <div>
          <label for="title">
            <i class="fas fa-heading"></i> Название вопроса:
          </label>
          <input id="title" type="text" name="title" 
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                 placeholder="Введите заголовок вашего вопроса" required>
        </div>
        
        <div>
          <label for="body">
            <i class="fas fa-align-left"></i> Текст вопроса:
          </label>
          <textarea id="body" name="body" 
                    placeholder="Опишите ваш вопрос подробнее..." 
                    required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
        </div>
        
        <div class="filters-row">
          <label for="category_id">
            <i class="fas fa-folder"></i> Категория:
          </label>
          <select id="category_id" name="category_id" required>
            <option value="">-- Выберите категорию --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" 
                <?= isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="filters-row">
          <label for="subcategory_id">
            <i class="fas fa-tag"></i> Подкатегория:
          </label>
          <select id="subcategory_id" name="subcategory_id" required>
            <option value="">-- Выберите подкатегорию --</option>
            <!-- Опции подкатегорий будут динамически добавлены JS -->
          </select>
        </div>
        
        <button type="submit">
          <i class="fas fa-paper-plane"></i> Отправить вопрос
        </button>
      </form>
    </div>
  </div>

  <script>
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
            
            // Восстановление выбранного значения при ошибке
            <?php if (!empty($_POST['subcategory_id'])): ?>
              if (<?= json_encode($_POST['subcategory_id']) ?> == sub.id) {
                option.selected = true;
              }
            <?php endif; ?>
            
            subcategorySelect.appendChild(option);
          }
        });
      }

      categorySelect.addEventListener('change', updateSubcategories);

      // При загрузке страницы сразу обновляем подкатегории, если есть выбранная категория
      updateSubcategories();
    });
  </script>
</body>
</html>