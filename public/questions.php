<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];

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
         (SELECT COUNT(*) FROM comments cm WHERE cm.question_id = q.id) AS comments_count,
         (SELECT COUNT(*) FROM question_likes ql WHERE ql.question_id = q.id) AS likes_count,
         (SELECT COUNT(*) FROM question_likes ql WHERE ql.question_id = q.id AND ql.user_id = :user_id) AS user_liked
  FROM questions q
  JOIN users u ON q.user_id = u.id
  JOIN categories c ON q.category_id = c.id
  LEFT JOIN subcategories s ON q.subcategory_id = s.id
  $whereSql
  ORDER BY q.created_at DESC
";

$params[':user_id'] = $userId;

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
          <div class="question" style="position: relative;">
            <a href="question.php?id=<?= $q['id'] ?>" class="question-link" tabindex="0"
              style="display: block; padding-right: 60px;">
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
            </a>

            <button class="btn-like" data-question-id="<?= $q['id'] ?>"
              aria-pressed="<?= $q['user_liked'] ? 'true' : 'false' ?>" type="button" aria-label="Поставить лайк">
              <svg class="like-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"
                fill="<?= $q['user_liked'] ? '#ff4c4c' : 'none' ?>" stroke="#ff4c4c" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path
                  d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                </path>
              </svg>
              <span class="likes-count"><?= $q['likes_count'] ?></span>
            </button>

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

    function updateSubcategories() {
      const catId = categorySelect.value;
      subcategorySelect.innerHTML = '<option value="">-- Все подкатегории --</option>';

      if (!catId) {
        subcategorySelect.style.display = 'none';
        subcategorySelect.value = '';
        return;
      }

      const filteredSubs = allSubcategories.filter(s => s.category_id == catId);

      if (filteredSubs.length === 0) {
        subcategorySelect.style.display = 'none';
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
    }

    categorySelect.addEventListener('change', () => {
      subcategorySelect.value = '';
      updateSubcategories();
    });

    updateSubcategories();

    // Лайки
    document.querySelectorAll('.btn-like').forEach(button => {
      button.addEventListener('click', async () => {
        const questionId = button.dataset.questionId;
        const isLiked = button.getAttribute('aria-pressed') === 'true';

        if (isLiked) {
          alert('Вы уже поставили лайк этому вопросу.');
          return;
        }

        try {
          const response = await fetch('like_question.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ question_id: questionId })
          });

          const result = await response.json();

          if (result.success) {
            button.setAttribute('aria-pressed', 'true');
            const countSpan = button.querySelector('.likes-count');
            countSpan.textContent = result.likes_count;
            button.querySelector('.like-icon').setAttribute('fill', '#ff4c4c');
          } else {
            alert(result.message || 'Ошибка при постановке лайка');
          }
        } catch (e) {
          alert('Ошибка сети, попробуйте позже');
        }
      });
    });
  </script>
</body>

</html>