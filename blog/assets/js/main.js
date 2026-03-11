// Burger menu
document.addEventListener('DOMContentLoaded', function () {
    const burger = document.getElementById('burger');
    const nav = document.getElementById('nav');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            nav.classList.toggle('open');
            burger.setAttribute('aria-expanded', nav.classList.contains('open'));
        });
        document.addEventListener('click', function (e) {
            if (!burger.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
            }
        });
    }

    // Comment form AJAX
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = commentForm.querySelector('button[type="submit"]');
            const textarea = commentForm.querySelector('textarea');
            const postId = commentForm.querySelector('[name="post_id"]').value;
            const content = textarea.value.trim();
            if (!content) return;

            btn.disabled = true;
            btn.textContent = 'Отправка...';

            try {
                const formData = new FormData();
                formData.append('post_id', postId);
                formData.append('content', content);

                const res = await fetch('add_comment.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    const list = document.getElementById('comments-list');
                    const noComments = document.getElementById('no-comments');
                    if (noComments) noComments.remove();

                    const count = document.querySelector('.comments-count');
                    if (count) count.textContent = parseInt(count.textContent || '0') + 1;

                    list.insertAdjacentHTML('afterbegin', buildCommentHTML(data.comment));
                    textarea.value = '';
                } else {
                    showToast(data.error || 'Ошибка. Попробуйте снова.', 'error');
                }
            } catch (err) {
                showToast('Ошибка сети. Попробуйте снова.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Отправить';
            }
        });
    }

    // Post like button
    document.addEventListener('click', async function (e) {
        const likeBtn = e.target.closest('.like-btn[data-type="post"]');
        if (!likeBtn) return;
        await handleLike(likeBtn, 'like_post.php');
    });

    // Comment like buttons
    document.addEventListener('click', async function (e) {
        const likeBtn = e.target.closest('.comment-like-btn');
        if (!likeBtn) return;
        await handleLike(likeBtn, 'like_comment.php');
    });

    // File upload label
    const fileInput = document.getElementById('image-upload');
    const fileLabel = document.getElementById('file-label-text');
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                fileLabel.textContent = fileInput.files[0].name;
            } else {
                fileLabel.textContent = 'Выберите файл';
            }
        });
    }
});

async function handleLike(btn, endpoint) {
    if (btn.dataset.loading) return;
    btn.dataset.loading = true;

    const id = btn.dataset.id;
    const countEl = btn.querySelector('.like-count');

    try {
        const formData = new FormData();
        formData.append('id', id);
        const res = await fetch(endpoint, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            if (countEl) countEl.textContent = data.likes;
            btn.classList.toggle('liked', data.liked);
        } else if (data.error === 'not_logged_in') {
            showToast('Войдите, чтобы поставить лайк.', 'error');
        }
    } catch (err) {
        console.error(err);
    } finally {
        delete btn.dataset.loading;
    }
}

function buildCommentHTML(c) {
    const initials = (c.name || '?')[0].toUpperCase();
    return `
    <div class="comment-item" id="comment-${c.id}">
        <div class="comment-header">
            <div class="comment-avatar">${initials}</div>
            <span class="comment-author">${escapeHtml(c.name)}</span>
            <span class="comment-date">${c.date}</span>
        </div>
        <div class="comment-text">${escapeHtml(c.content)}</div>
        <div class="comment-footer">
            <button class="comment-like-btn" data-id="${c.id}">
                ♥ <span class="like-count">0</span>
            </button>
        </div>
    </div>`;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function showToast(msg, type = 'info') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = msg;
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        padding:14px 22px; border-radius:8px; font-size:0.93rem; font-weight:600;
        box-shadow:0 4px 20px rgba(0,0,0,0.2); animation:fadeIn 0.3s ease;
        background:${type === 'error' ? '#b04a2f' : '#5a7a5f'}; color:#fff;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
