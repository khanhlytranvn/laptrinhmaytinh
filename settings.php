<?php
require 'config.php';
require 'includes/functions.php';
require_login();

$pageTitle = 'Cài đặt';
require 'includes/header.php';
?>

<div class="settings-intro">
    <h2>Be yourself</h2>
</div>

<!-- CHẾ ĐỘ SÁNG TỐI -->
<section class="card settings-card">
    <div class="settings-heading">
        <div class="settings-icon">
            <i data-lucide="sun-moon"></i>
        </div>

        <div>
            <h2>Chế độ hiển thị</h2>
        </div>
    </div>

    <div class="theme-options">
        <button type="button" id="themeToggle" class="theme-toggle">
            <span class="toggle-circle">
                <i data-lucide="sun" class="day-icon"></i>
                <i data-lucide="moon-star" class="night-icon"></i>
            </span>
            <span class="toggle-label">DAYMODE</span>
        </button>
    </div>
</section>

<!-- HÌNH NỀN -->
<section class="card settings-card">
    <div class="settings-heading">
        <div class="settings-icon">
            <i data-lucide="image"></i>
        </div>

        <div>
            <h2>Giao diện</h2>
        </div>
    </div>

    <div class="wallpaper-grid" id="wallpaperGrid">
        <button type="button"
                class="wallpaper-option wallpaper-default"
                data-bg="default"
                aria-label="Mặc định"
                title="Mặc định">
            <span></span>
        </button>

        <button type="button"
                class="wallpaper-option wallpaper-blue"
                data-bg="blue"
                aria-label="Xanh dịu"
                title="Xanh dịu">
            <span></span>
        </button>

        <button type="button"
                class="wallpaper-option wallpaper-green"
                data-bg="green"
                aria-label="Xanh lá"
                title="Xanh lá">
            <span></span>
        </button>

        <button type="button"
                class="wallpaper-option wallpaper-pink"
                data-bg="pink"
                aria-label="Hồng pastel"
                title="Hồng pastel">
            <span></span>
        </button>

        <button type="button"
                class="wallpaper-option wallpaper-lavender"
                data-bg="lavender"
                aria-label="Tím nhạt"
                title="Tím nhạt">
            <span></span>
        </button>

        <button type="button"
                class="wallpaper-option wallpaper-cream"
                data-bg="cream"
                aria-label="Màu kem"
                title="Màu kem">
            <span></span>
        </button>
    </div>

    <div class="upload-area">
        <label for="backgroundUpload"
               class="upload-button"
               title="Thêm ảnh từ thư viện">
            <i data-lucide="plus"></i>
        </label>

        <input type="file"
               id="backgroundUpload"
               accept="image/*">

        <img id="imagePreview"
             class="image-preview"
             alt="Ảnh nền đã chọn">
    </div>
</section>

<!-- KHÔI PHỤC CÀI ĐẶT -->
<section class="card settings-card settings-reset-card">
    <div class="settings-heading">
        <div class="settings-icon">
            <i data-lucide="rotate-ccw"></i>
        </div>

        <div>
            <h2>Cài đặt mặc định</h2>
        </div>
    </div>

    <button type="button"
            class="btn btn-danger"
            id="resetSettings">
        <i data-lucide="refresh-ccw"></i>
        Khôi phục
    </button>
</section>

<div class="settings-toast"
     id="settingsToast"
     role="status">
</div>

<script>
(function () {
    const themeToggle = document.getElementById('themeToggle');
    const wallpaperButtons = document.querySelectorAll('.wallpaper-option');
    const uploadInput = document.getElementById('backgroundUpload');
    const imagePreview = document.getElementById('imagePreview');
    const resetButton = document.getElementById('resetSettings');
    const toast = document.getElementById('settingsToast');
    const mainArea = document.querySelector('.main-area');

    const backgroundColors = {
        default: '#f4f6fa',
        blue: '#dceafa',
        green: '#dcebd8',
        pink: '#f8dfe5',
        lavender: '#e9e0f8',
        cream: '#f2e7cf'
    };

    function showToast(message) {
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 2200);
    }

    // ÁP DỤNG HÌNH NỀN THEO CHẾ ĐỘ ĐANG CHỌN
    function renderBackground() {
        const theme = localStorage.getItem('meMoneyTheme') || 'light';
        const background = localStorage.getItem('meMoneyBackground') || 'default';
        const customImage = localStorage.getItem('meMoneyCustomImage');
        const isDark = theme === 'dark';

        let backgroundImage = 'none';

        if (background === 'custom' && customImage) {
            const imageUrl = 'url("' + customImage + '")';

            backgroundImage = isDark
                ? 'linear-gradient(rgba(15, 20, 35, 0.48), rgba(15, 20, 35, 0.48)), ' + imageUrl
                : imageUrl;
        }

        const selectedColor = isDark
            ? '#171c2b'
            : (backgroundColors[background] || backgroundColors.default);

        [document.body, mainArea].filter(Boolean).forEach(function (element) {
            element.style.setProperty('background-color', selectedColor, 'important');

            if (backgroundImage !== 'none') {
                element.style.setProperty('background-image', backgroundImage, 'important');
                element.style.setProperty('background-size', 'cover', 'important');
                element.style.setProperty('background-position', 'center', 'important');
                element.style.setProperty('background-repeat', 'no-repeat', 'important');
            } else {
                element.style.removeProperty('background-image');
                element.style.removeProperty('background-size');
                element.style.removeProperty('background-position');
                element.style.removeProperty('background-repeat');
            }

            if (element === document.body) {
                if (backgroundImage !== 'none') {
                    element.style.setProperty('background-attachment', 'fixed', 'important');
                } else {
                    element.style.removeProperty('background-attachment');
                }
            }
        });
    }

    // CHẾ ĐỘ SÁNG TỐI
    function applyTheme(theme) {
        const isDark = theme === 'dark';

        document.body.classList.toggle('dark-mode', isDark);
        document.documentElement.classList.toggle('dark-mode', isDark);
        document.documentElement.style.colorScheme = theme;

        if (themeToggle) {
            themeToggle.classList.toggle('night', isDark);

            const label = themeToggle.querySelector('.toggle-label');
            if (label) {
                label.textContent = isDark ? 'NIGHTMODE' : 'DAYMODE';
            }
        }

        localStorage.setItem('meMoneyTheme', theme);
        renderBackground();
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = localStorage.getItem('meMoneyTheme') || 'light';
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }

    // CHỌN MÀU HÌNH NỀN
    function applyBackground(background) {
        if (!backgroundColors[background]) {
            background = 'default';
        }

        localStorage.setItem('meMoneyBackground', background);
        localStorage.removeItem('meMoneyCustomImage');

        wallpaperButtons.forEach(function (button) {
            button.classList.toggle('selected', button.dataset.bg === background);
        });

        if (imagePreview) {
            imagePreview.style.display = 'none';
            imagePreview.removeAttribute('src');
        }

        renderBackground();
    }

    wallpaperButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            applyBackground(this.dataset.bg);
            showToast('Đã cập nhật hình nền.');
        });
    });

    // TẢI ẢNH VÀ ĐẶT LÀM HÌNH NỀN
    if (uploadInput) {
        uploadInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showToast('Vui lòng chọn file hình ảnh.');
                this.value = '';
                return;
            }

            if (file.size > 3 * 1024 * 1024) {
                showToast('Ảnh phải nhỏ hơn 3MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                try {
                    const imageData = event.target.result;

                    localStorage.setItem('meMoneyCustomImage', imageData);
                    localStorage.setItem('meMoneyBackground', 'custom');

                    wallpaperButtons.forEach(function (button) {
                        button.classList.remove('selected');
                    });

                    if (imagePreview) {
                        imagePreview.src = imageData;
                        imagePreview.style.display = 'block';
                    }

                    renderBackground();
                    showToast('Đã cập nhật ảnh nền.');
                } catch (error) {
                    showToast('Không thể lưu ảnh. Có thể dung lượng lưu trữ đã đầy.');
                }
            };

            reader.onerror = function () {
                showToast('Không thể đọc ảnh đã chọn.');
            };

            reader.readAsDataURL(file);
        });
    }

    // KHÔI PHỤC CÀI ĐẶT MẶC ĐỊNH
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            const confirmed = confirm('Bạn có chắc muốn khôi phục giao diện mặc định không?');

            if (!confirmed) return;

            localStorage.removeItem('meMoneyTheme');
            localStorage.removeItem('meMoneyBackground');
            localStorage.removeItem('meMoneyCustomImage');

            if (uploadInput) {
                uploadInput.value = '';
            }

            applyTheme('light');
            applyBackground('default');

            showToast('Đã khôi phục cài đặt mặc định.');
        });
    }

    // KHÔI PHỤC CÀI ĐẶT ĐÃ LƯU KHI MỞ TRANG
    const savedTheme = localStorage.getItem('meMoneyTheme') || 'light';
    const savedBackground = localStorage.getItem('meMoneyBackground') || 'default';
    const savedImage = localStorage.getItem('meMoneyCustomImage');

    applyTheme(savedTheme);

    if (savedBackground === 'custom' && savedImage) {
        wallpaperButtons.forEach(function (button) {
            button.classList.remove('selected');
        });

        if (imagePreview) {
            imagePreview.src = savedImage;
            imagePreview.style.display = 'block';
        }
    } else {
        const validBackground = backgroundColors[savedBackground]
            ? savedBackground
            : 'default';

        wallpaperButtons.forEach(function (button) {
            button.classList.toggle('selected', button.dataset.bg === validBackground);
        });

        if (validBackground !== savedBackground) {
            localStorage.setItem('meMoneyBackground', validBackground);
        }
    }

    renderBackground();
})();
</script>

<?php require 'includes/footer.php'; ?>