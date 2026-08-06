const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signUp');

if (signUpButton && signInButton && signInForm && signUpForm) {
    signUpButton.addEventListener('click',function(){
        signInForm.style.display="none";
        signUpForm.style.display="block";
    })
    signInButton.addEventListener('click',function(){
        signInForm.style.display="block";
        signUpForm.style.display="none";
    })
}

/* ========================================
   My Business — Add Category functionality
   Works on: dashboard.php, notes.html,
             tests.html, sessional.html
   ======================================== */

document.addEventListener('DOMContentLoaded', function () {

    var page = document.body.dataset.page || 'default';
    var storageKey = 'eschool_business_categories_' + page;

    var overlay   = document.getElementById('categoryModalOverlay');
    var form      = document.getElementById('categoryForm');
    var codeInput = document.getElementById('categoryCode');
    var nameInput = document.getElementById('categoryName');
    var closeBtn  = document.getElementById('categoryModalClose');
    var cancelBtn = document.getElementById('categoryCancelBtn');

    // Dashboard uses a clickable card, other pages use the "Add" button
    var trigger = document.getElementById('addCategoryCard') ||
                  document.getElementById('addBusinessBtn');

    // Dashboard appends into .cards, other pages append into the card-row
    var container = document.getElementById('businessCards') ||
                     document.getElementById('businessCardRow');

    if (!overlay || !form || !trigger || !container) {
        // Required hooks aren't on this page — nothing to wire up.
        return;
    }

    function openModal() {
        overlay.classList.add('active');
        codeInput.focus();
    }

    function closeModal() {
        overlay.classList.remove('active');
        form.reset();
    }

    function buildCard(code, name) {
        var card = document.createElement('div');
        card.className = 'card business-card';

        var image = document.createElement('div');
        image.className = 'image';

        var text = document.createElement('div');
        text.className = 'text';
        text.appendChild(document.createTextNode(code));
        if (name) {
            var small = document.createElement('small');
            small.textContent = name;
            text.appendChild(small);
        }

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-card';
        removeBtn.title = 'Remove category';
        removeBtn.setAttribute('aria-label', 'Remove category');
        removeBtn.innerHTML = '&times;';
        removeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            card.remove();
            saveCategories();
        });

        card.appendChild(removeBtn);
        card.appendChild(image);
        card.appendChild(text);
        return card;
    }

    function insertCard(card) {
        var arrow = container.querySelector('.arrow');
        if (arrow) {
            container.insertBefore(card, arrow);
        } else {
            container.appendChild(card);
        }
    }

    function saveCategories() {
        var cards = container.querySelectorAll('.business-card');
        var categories = [];
        cards.forEach(function (card) {
            var textEl = card.querySelector('.text');
            var smallEl = textEl.querySelector('small');
            var code = textEl.childNodes[0] ? textEl.childNodes[0].textContent.trim() : '';
            var name = smallEl ? smallEl.textContent.trim() : '';
            categories.push({ code: code, name: name });
        });
        localStorage.setItem(storageKey, JSON.stringify(categories));
    }

    function loadCategories() {
        var raw = localStorage.getItem(storageKey);
        if (!raw) return;
        var categories;
        try {
            categories = JSON.parse(raw);
        } catch (e) {
            categories = [];
        }
        categories.forEach(function (cat) {
            insertCard(buildCard(cat.code, cat.name));
        });
    }

    trigger.addEventListener('click', function (e) {
        e.preventDefault();
        openModal();
    });

    [closeBtn, cancelBtn].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) closeModal();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var code = codeInput.value.trim().toUpperCase();
        var name = nameInput.value.trim();
        if (!code) return;

        insertCard(buildCard(code, name));
        saveCategories();
        closeModal();
    });

    loadCategories();
});
