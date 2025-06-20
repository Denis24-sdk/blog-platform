<?php
require_once __DIR__ . '/config/config.php';
if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}
$userId = $_SESSION['user_id'];
$selectedCategory = isset($_GET['category']) ? (int) $_GET['category'] : null;
$selectedSubcategory = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : null;
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['date', 'popularity']) ? $_GET['sort'] : 'date';
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
// Получаем общее количество пользователей
$totalUsersStmt = $pdo->query('SELECT COUNT(id) FROM users');
$totalUsers = (int) $totalUsersStmt->fetchColumn();
if ($totalUsers === 0) {
  $totalUsers = 1; // чтобы избежать деления на 0
}
// Определяем порядок сортировки
$orderBy = 'q.created_at DESC';
if ($sort === 'popularity') {
  $orderBy = 'likes_count DESC, q.created_at DESC';
}
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
  ORDER BY $orderBy
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
  <title>Вопросы - Форум</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/questions.css">
</head>

<body>
  <?php include 'menu.php'; ?>
  
  <div class="container-recommend">
    <h1>Вопросы сообщества</h1>
    
    <form method="GET" class="filters" id="filtersForm">
      <div class="filters-row top-row">
        <div class="select-wrapper">
          <select name="category" id="category" autocomplete="off">
            <option value="">-- Все категории --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="select-wrapper">
          <select name="subcategory" id="subcategory" autocomplete="off" style="display:none;">
            <option value="">-- Все подкатегории --</option>
          </select>
        </div>
        
        <button type="submit" class="btn-compleat-filters">
          <i class="fas fa-filter"></i> Применить
        </button>
      </div>
      
      <div class="filters-row sort-row">
        <div class="select-wrapper">
          <select name="sort" id="sort" autocomplete="off">
            <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Сортировать по дате</option>
            <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Сортировать по популярности</option>
          </select>
        </div>
      </div>
    </form>

    <?php if (empty($questions)): ?>
      <div class="no-questions">
        <i class="fas fa-inbox"></i>
        <h3>Вопросы не найдены</h3>
        <p>Попробуйте изменить параметры фильтрации или станьте первым, кто задаст вопрос!</p>
      </div>
    <?php else: ?>
      <div class="questions-wrapper">
        <?php foreach ($questions as $q):
          $likedClass = $q['user_liked'] ? 'interested' : '';
          ?>
          <div class="question-card" id="question-<?= $q['id'] ?>">
            <a href="question.php?id=<?= $q['id'] ?>" class="question-content">
              <div class="question-header">
                <h3 class="question-title">
                  <?= htmlspecialchars($q['title']) ?>
                </h3>
              </div>
              
              <div class="question-meta">
                <div class="meta-item">
                  <i class="fas fa-user"></i>
                  <span><?= htmlspecialchars($q['username']) ?></span>
                </div>
                
                <div class="meta-item">
                  <i class="fas fa-calendar"></i>
                  <span><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></span>
                </div>
                
                <div class="meta-item">
                  <i class="fas fa-folder-open"></i>
                  <span>
                    <?= htmlspecialchars($q['category_name']) ?>
                    <?php if ($q['subcategory_name']): ?>
                      / <?= htmlspecialchars($q['subcategory_name']) ?>
                    <?php endif; ?>
                  </span>
                </div>
                
                <div class="meta-item">
                  <i class="fas fa-comments"></i>
                  <span>Комментарии: <?= $q['comments_count'] ?></span>
                </div>
              </div>
              
              <div class="question-body">
                <?= nl2br(htmlspecialchars($q['body'])) ?>
              </div>
            </a>
            
            <div class="question-footer">
              <div class="question-actions">
                <button class="btn-interest <?= $q['user_liked'] ? 'interested' : '' ?>" 
                        data-question-id="<?= $q['id'] ?>"
                        type="button" 
                        aria-pressed="<?= $q['user_liked'] ? 'true' : 'false' ?>"
                        aria-label="Отметить как интересное">
                  <i class="fas fa-thumbs-up"></i>
                  <span>Интересно</span>
                </button>
                
                <div class="like-count" aria-live="polite" aria-atomic="true" data-question-id="<?= $q['id'] ?>">
                  <i class="fas fa-heart"></i>
                  <span><?= (int) $q['likes_count'] ?></span>
                </div>
              </div>
            </div>
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
    
    // Инициализация при загрузке
    updateSubcategories();
    
    // Обработка клика по всей карточке вопроса
    document.querySelectorAll('.question-content').forEach(link => {
      link.addEventListener('click', function(e) {
        // Если клик был по кнопке "Интересно", отменяем переход
        if (e.target.closest('.btn-interest')) {
          e.preventDefault();
        }
      });
    });
    
    // Обработка кнопок "Интересно"
    document.querySelectorAll('.btn-interest').forEach(button => {
      button.addEventListener('click', async () => {
        const questionId = button.dataset.questionId;
        const likeCountElem = document.querySelector(`.like-count[data-question-id="${questionId}"]`);
        if (!questionId || !likeCountElem) return;

        const isInterested = button.classList.contains('interested');
        const likeIcon = button.querySelector('i');
        
        // Анимация иконки
        likeIcon.classList.remove('fa-thumbs-up');
        likeIcon.classList.add('fa-spinner', 'fa-spin');
        button.disabled = true;

        try {
          const response = await fetch('like_question.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question_id: questionId })
          });
          
          const result = await response.json();

          if (result.success) {
            if (result.action === 'added') {
              button.classList.add('interested');
              button.setAttribute('aria-pressed', 'true');
              likeCountElem.innerHTML = `<i class="fas fa-heart"></i> <span>${result.likes_count}</span>`;
              likeCountElem.classList.add('bounce');
              setTimeout(() => likeCountElem.classList.remove('bounce'), 600);
            } else if (result.action === 'removed') {
              button.classList.remove('interested');
              button.setAttribute('aria-pressed', 'false');
              likeCountElem.innerHTML = `<i class="fas fa-heart"></i> <span>${result.likes_count}</span>`;
            }
          } else {
            alert(result.message || 'Ошибка при обновлении лайка.');
          }
        } catch (err) {
          alert('Ошибка сети. Попробуйте позже.');
          console.error(err);
        } finally {
          likeIcon.classList.remove('fa-spinner', 'fa-spin');
          likeIcon.classList.add('fa-thumbs-up');
          button.disabled = false;
        }
      });
    });
  </script>
</body>
</html>