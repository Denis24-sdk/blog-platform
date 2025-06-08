<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$selectedCategory = isset($_GET['category']) ? (int) $_GET['category'] : null;
$selectedSubcategory = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : null;

$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$subcategoriesStmt = $pdo->query("SELECT id, category_id, name FROM subcategories ORDER BY name");
$allSubcategories = $subcategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$where = [];
$params = [];

if ($selectedCategory) {
  $where[] = "q.category_id = :category_id";
  $params[':category_id'] = $selectedCategory;
}
if ($selectedSubcategory) {
  $where[] = "q.subcategory_id = :subcategory_id";
  $params[':subcategory_id'] = $selectedSubcategory;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
  SELECT q.id, q.title, q.body, q.created_at, q.user_id, u.username,
         c.name AS category_name, s.name AS subcategory_name,
         (SELECT COUNT(*) FROM comments cm WHERE cm.question_id = q.id) AS comments_count
  FROM questions q
  JOIN users u ON q.user_id = u.id
  JOIN categories c ON q.category_id = c.id
  LEFT JOIN subcategories s ON q.subcategory_id = s.id
  $whereSql
  ORDER BY q.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Рекомендации - Форум</title>
  <link rel="stylesheet" href="styles/questions.css" />

</head>

<body>
  <?php include 'menu.php'; ?>

  <div class="container-recommend">
    <h2 style="margin-top: -0.4rem;">Вопросы</h2>

    <form method="GET" class="filters" id="filtersForm">
      <div class="filters-row">
        <select name="category" id="category" autocomplete="off">
          <option value="">-- Все категории --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filters-row">
        <select name="subcategory" id="subcategory" autocomplete="off" style="display:none;">
          <option value="">-- Все подкатегории --</option>
        </select>
      </div>

      <button type="submit" class="btn-compleat-filters">Применить</button>
    </form>

    <?php if (empty($questions)): ?>
      <p>Пока нет вопросов. Будьте первым!</p>
    <?php else: ?>
      <div class="questions-wrapper">
        <?php foreach ($questions as $q): ?>
          <a href="question.php?id=<?= $q['id'] ?>" class="question-link" tabindex="0">
            <div class="question">
              <h3><?= htmlspecialchars($q['title']) ?></h3>
              <div class="author">
                <?= htmlspecialchars($q['username']) ?>,
                <?= htmlspecialchars($q['created_at']) ?><br>
                <small>
                  <?= htmlspecialchars($q['category_name']) ?>
                  <?= $q['subcategory_name'] ? ' / ' . htmlspecialchars($q['subcategory_name']) : '' ?>
                </small><br>
                <small style="color: rgb(106, 125, 162);">Комментарии: <?= $q['comments_count'] ?></small>
              </div>

              <p><?= nl2br(htmlspecialchars($q['body'])) ?></p>

              <?php if ($q['user_id'] == $_SESSION['user_id']): ?>
                <form method="POST" action="delete_question.php" onsubmit="return confirm('Удалить этот вопрос?');"
                  tabindex="-1">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <button type="submit" class="btn-delete" aria-label="Удалить вопрос">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                      <path d="M3 6h18v2H3V6zm2 3h14l-1.5 12.5a1 1 0 01-1 .5H8a1 1 0 01-1-.5L5 9zm5 3v6h2v-6h-2z" />
                    </svg>
                    Удалить
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

  <script>
    const allSubcategories = <?= json_encode($allSubcategories, JSON_UNESCAPED_UNICODE) ?>;
    const selectedCategory = <?= json_encode($selectedCategory) ?>;
    const selectedSubcategory = <?= json_encode($selectedSubcategory) ?>;

    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const subcategoryLabel = document.getElementById('subcategory-label');

    function updateSubcategories() {
      const catId = categorySelect.value;
      subcategorySelect.innerHTML = '<option value="">-- Все подкатегории --</option>';

      if (!catId) {
        subcategorySelect.style.display = 'none';
        if(subcategoryLabel) subcategoryLabel.style.display = 'none';
        subcategorySelect.value = '';
        return;
      }

      const filteredSubs = allSubcategories.filter(s => s.category_id == catId);

      if (filteredSubs.length === 0) {
        subcategorySelect.style.display = 'none';
        if(subcategoryLabel) subcategoryLabel.style.display = 'none';
        subcategorySelect.value = '';
        return;
      }

      filteredSubs.forEach(sub => {
        const option = document.createElement('option');
        option.value = sub.id;
        option.textContent = sub.name;
        if (selectedSubcategory && selectedSubcategory == sub.id) {
          option.selected = true;
        }
        subcategorySelect.appendChild(option);
      });

      subcategorySelect.style.display = 'inline-block';
      if(subcategoryLabel) subcategoryLabel.style.display = 'inline-block';
    }

    categorySelect.addEventListener('change', () => {
      subcategorySelect.value = '';
      updateSubcategories();
    });

    updateSubcategories();
  </script>
</body>

</html>

