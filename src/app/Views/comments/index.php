<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Комментарии</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        :root {
            --accent: #d9343a;
            --accent-dark: #b8202b;
            --ink: #263238;
            --muted: #7c858d;
            --line: #e2e6e9;
            --page: #f5f7fa;
        }

        body {
            min-height: 100vh;
            background: var(--page);
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .card { border: 1px solid var(--line); border-radius: 10px; }
        .card-body { padding: 1.25rem; }
        .section-title { color: #172b4d; font-size: 1.15rem; font-weight: 700; }
        .section-caption { color: var(--muted); font-size: .88rem; }
        .count-pill {
            display: inline-block;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: #fff0ef;
            color: var(--accent-dark);
            font-size: .75rem;
            font-weight: 700;
        }

        .list-toolbar { padding-bottom: 1rem; border-bottom: 1px solid var(--line); }
        .sort-label { margin-right: .35rem; color: var(--muted); font-size: .8rem; white-space: nowrap; }
        .sort-controls { gap: .5rem; }
        .sort-controls .form-control-sm { min-width: 130px; }
        .form-control { border-color: #ccd4dc; border-radius: 6px; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(217, 52, 58, .12); }
        #comments-list { min-height: 76px; padding-top: 1rem; }
        .comment-card { border-left: 4px solid var(--accent); border-radius: 7px; }
        .comment-card .card-body { padding: 1rem 1.1rem; }
        .comment-date { color: var(--muted); font-size: .82rem; }
        .comment-text { white-space: pre-wrap; word-break: break-word; }
        .required::after { content: ' *'; color: var(--accent); }
        .btn-primary, .btn-primary:hover, .btn-primary:focus {
            border-color: var(--accent);
            background: var(--accent);
        }
        .btn-primary:hover, .btn-primary:focus { background: var(--accent-dark); }
        .btn-outline-danger { color: var(--accent); border-color: #e9aeb1; }
        .btn-outline-danger:hover { border-color: var(--accent); background: var(--accent); }
        .page-item.active .page-link { border-color: var(--accent); background: var(--accent); }
        .page-link { color: var(--accent); border-radius: 6px; margin: 0 .1rem; }

        .loading-indicator {
            text-align: center;
            padding: 1.5rem 0;
            color: var(--muted);
        }

        @media (max-width: 575.98px) {
            .card-body { padding: 1rem; }
            .sort-controls { flex-direction: column; align-items: stretch !important; }
            .sort-controls .form-control-sm { min-width: 100%; }
        }
    </style>
</head>
<body>
<main class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="list-toolbar d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                        <div class="mb-3 mb-md-0">
                            <h2 class="section-title mb-1">Список комментариев</h2>
                            <div class="section-caption"><span class="count-pill" id="comments-count">0 комментариев</span></div>
                        </div>
                        <div class="d-flex flex-column flex-md-row align-items-md-center sort-controls">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <span class="sort-label">Сортировка:</span>
                                <label class="sr-only" for="sort">Сортировать по</label>
                                <select id="sort" class="form-control form-control-sm">
                                    <option value="id">ID</option>
                                    <option value="date">Дате</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="sort-label">Направление:</span>
                                <label class="sr-only" for="direction">Направление</label>
                                <select id="direction" class="form-control form-control-sm">
                                    <option value="DESC">По убыванию</option>
                                    <option value="ASC">По возрастанию</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="comments-list" aria-live="polite"></div>
                    <nav aria-label="Навигация по страницам">
                        <ul id="pagination" class="pagination justify-content-center mb-0"></ul>
                    </nav>
                </div>
            </div>

            <div class="card shadow-sm" id="comment-form-section">
                <div class="card-body">
                    <h2 class="section-title mb-1">Добавить комментарий</h2>
                    <p class="section-caption mb-4">Заполните форму - сообщение появится в общем списке.</p>
                    <div id="form-message" class="alert d-none" role="alert"></div>

                    <form id="comment-form" novalidate>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="required" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       maxlength="255" required>
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="required" for="comment_date">Дата и время</label>
                                <input type="datetime-local" class="form-control" id="comment_date"
                                       name="comment_date" required>
                                <div class="invalid-feedback" id="comment_date-error"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required" for="text">Комментарий</label>
                            <textarea class="form-control" id="text" name="text" rows="4"
                                      maxlength="5000" required></textarea>
                            <div class="invalid-feedback" id="text-error"></div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submit-button">
                            Добавить комментарий
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(function () {
        const commentsUrl = 'comments';
        let currentPage = 1;

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function setDefaultDate() {
            const now = new Date();
            const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                .toISOString().slice(0, 16);
            $('#comment_date').val(local);
        }

        function showFormMessage(message, type) {
            $('#form-message')
                .removeClass('d-none alert-success alert-danger')
                .addClass('alert-' + type)
                .text(message);
        }

        function clearValidation() {
            $('#comment-form .is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function showValidation(errors) {
            Object.keys(errors || {}).forEach(function (field) {
                $('#' + field).addClass('is-invalid');
                $('#' + field + '-error').text(errors[field]);
            });
        }

        function validateForm() {
            const errors = {};
            const email = $('#email').val().trim();
            const text = $('#text').val().trim();
            const commentDate = $('#comment_date').val().trim();

            if (!email) {
                errors.email = 'Введите email.';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.email = 'Введите корректный email.';
            }

            if (!text) {
                errors.text = 'Введите текст комментария.';
            }

            if (!commentDate) {
                errors.comment_date = 'Укажите дату и время.';
            }

            if (Object.keys(errors).length > 0) {
                showValidation(errors);
                return false;
            }

            return true;
        }

        function renderComments(comments) {
            const $list = $('#comments-list').empty();

            if (!comments.length) {
                $list.append('<p class="text-muted">Комментариев пока нет.</p>');
                return;
            }

            comments.forEach(function (comment) {
                const card = `
                    <article class="comment-card card mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-sm-row justify-content-between mb-2">
                                <strong>${escapeHtml(comment.email)}</strong>
                                <span class="comment-date">${escapeHtml(comment.comment_date)}</span>
                            </div>
                            <p class="comment-text mb-3">${escapeHtml(comment.text)}</p>
                            <button type="button" class="btn btn-outline-danger btn-sm delete-comment"
                                    data-id="${Number(comment.id)}">Удалить</button>
                        </div>
                    </article>`;
                $list.append(card);
            });
        }

        function renderPagination(pagination) {
            const $pagination = $('#pagination').empty();

            if (pagination.totalPages <= 1) {
                return;
            }

            for (let page = 1; page <= pagination.totalPages; page += 1) {
                const active = page === pagination.page ? ' active' : '';
                $pagination.append(`
                    <li class="page-item${active}">
                        <button class="page-link page-button" type="button" data-page="${page}">${page}</button>
                    </li>`);
            }
        }

        function renderCommentsCount(total) {
            const lastDigit = total % 10;
            const lastTwoDigits = total % 100;
            let suffix = 'комментариев';

            if (lastDigit === 1 && lastTwoDigits !== 11) {
                suffix = 'комментарий';
            } else if (
                lastDigit >= 2
                && lastDigit <= 4
                && (lastTwoDigits < 12 || lastTwoDigits > 14)
            ) {
                suffix = 'комментария';
            }

            $('#comments-count').text(total + ' ' + suffix);
        }

        function loadComments(page) {
            const $list = $('#comments-list');
            $list.html('<div class="loading-indicator">Загрузка...</div>');

            $.getJSON(commentsUrl, {
                page: page,
                sort: $('#sort').val(),
                direction: $('#direction').val()
            })
                .done(function (response) {
                    currentPage = response.pagination.page;
                    renderCommentsCount(response.pagination.total);
                    renderComments(response.comments);
                    renderPagination(response.pagination);
                })
                .fail(function () {
                    $list.html('<div class="alert alert-danger">Не удалось загрузить комментарии.</div>');
                });
        }

        $('#sort, #direction').on('change', function () {
            currentPage = 1;
            loadComments(currentPage);
        });

        $(document).on('click', '.page-button', function () {
            loadComments(Number($(this).data('page')));
        });

        $(document).on('click', '.delete-comment', function () {
            const id = Number($(this).data('id'));

            if (! window.confirm('Удалить этот комментарий?')) {
                return;
            }

            $.ajax({
                url: commentsUrl + '/' + id,
                type: 'DELETE',
                dataType: 'json'
            })
                .done(function () {
                    loadComments(currentPage);
                })
                .fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    window.alert(response.message || 'Не удалось удалить комментарий.');
                });
        });

        $('#comment-form').on('submit', function (event) {
            event.preventDefault();
            clearValidation();
            $('#form-message').addClass('d-none');

            if (! validateForm()) {
                return;
            }

            $('#submit-button').prop('disabled', true);

            $.ajax({
                url: commentsUrl,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    email: $('#email').val(),
                    text: $('#text').val(),
                    comment_date: $('#comment_date').val()
                })
            })
                .done(function (response) {
                    showFormMessage(response.message, 'success');
                    $('#comment-form')[0].reset();
                    setDefaultDate();
                    currentPage = 1;
                    loadComments(currentPage);
                })
                .fail(function (xhr) {
                    const response = xhr.responseJSON || {};
                    if (response.errors) {
                        showValidation(response.errors);
                    } else {
                        showFormMessage(response.message || 'Не удалось сохранить комментарий.', 'danger');
                    }
                })
                .always(function () {
                    $('#submit-button').prop('disabled', false);
                });
        });

        setDefaultDate();
        loadComments(currentPage);
    });
</script>
</body>
</html>