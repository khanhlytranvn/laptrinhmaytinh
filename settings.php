
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
                class="wallpaper-option wallpaper-default selected"
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
    <label for="backgroundUpload" class="upload-button"
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
            <h2> Cài đặt mặc định</h2>
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

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(showToast.timer);

        showToast.timer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2200);
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
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = document.body.classList.contains('dark-mode');

            applyTheme(isDark ? 'light' : 'dark');
        });
    }

    // MÀU NỀN
  
// MÀU NỀN
function applyBackground(background) {

    const backgroundColors = {
        default: '#f4f6fa',
        blue: '#dceafa',
        green: '#dcebd8',
        pink: '#f8dfe5',
        lavender: '#e9e0f8',
        cream: '#f2e7cf'
    };

    const selectedColor = backgroundColors[background] || backgroundColors.default;

    // XÓA ẢNH NỀN CŨ
    document.body.style.backgroundImage = '';

    if (mainArea) {
        mainArea.style.backgroundImage = '';
    }

    // ÁP DỤNG MÀU NỀN
    document.body.style.setProperty(
        'background-color',
        selectedColor,
        'important'
    );

    if (mainArea) {
        mainArea.style.setProperty(
            'background-color',
            selectedColor,
            'important'
        );
    }

    document.body.style.backgroundSize = '';
    document.body.style.backgroundPosition = '';
    document.body.style.backgroundAttachment = '';

    // BỎ CHỌN ẢNH TÙY CHỈNH
    localStorage.removeItem('meMoneyCustomImage');

    // LƯU MÀU ĐÃ CHỌN
    localStorage.setItem('meMoneyBackground', background);

    // ĐÁNH DẤU Ô MÀU ĐƯỢC CHỌN
    wallpaperButtons.forEach(button => {
        button.classList.toggle(
            'selected',
            button.dataset.bg === background
        );
    });

    if (imagePreview) {
        imagePreview.style.display = 'none';
        imagePreview.src = '';
    }
    // NHẤN CHỌN MÀU HÌNH NỀN
wallpaperButtons.forEach(button => {
    button.addEventListener('click', function () {
        const selectedBackground = this.dataset.bg;

        applyBackground(selectedBackground);
    });
});
}

    
function applyTheme(theme) {
    const isDark = theme === 'dark';

    document.body.classList.toggle('dark-mode', isDark);
    document.documentElement.classList.toggle('dark-mode', isDark);

    const darkElements = [
    document.body,
    document.querySelector('.app-shell'),
    document.querySelector('.main-area'),
    ...document.querySelectorAll('.settings-card')
].filter(Boolean);

    darkElements.forEach(element => {
        if (isDark) {
            element.style.setProperty(
                'background-color',
                element.classList.contains('settings-card')
                    ? '#252d40'
                    : '#171c2b',
                'important'
            );

            element.style.setProperty('color', '#e8edf5', 'important');
            element.style.setProperty('border-color', '#39445b', 'important');
        } else {
            element.style.removeProperty('background-color');
            element.style.removeProperty('color');
            element.style.removeProperty('border-color');
        }
    });

    document.querySelectorAll(
        '.settings-card h2, .settings-card p, .settings-card label'
    ).forEach(element => {
        element.style.setProperty(
            'color',
            isDark ? '#e8edf5' : '#26344a',
            'important'
        );
    });

    document.querySelectorAll('.settings-icon').forEach(element => {
        element.style.setProperty(
            'background-color',
            isDark ? '#34425b' : '#edf2fb',
            'important'
        );
        element.style.setProperty(
            'color',
            isDark ? '#b8d0ff' : '#3568d4',
            'important'
        );
    });

    if (themeToggle) {
    themeToggle.classList.toggle('night', isDark);

    const label = themeToggle.querySelector('.toggle-label');

    if (label) {
        label.textContent = isDark ? 'NIGHT MODE' : 'DAY MODE';
    }
}

    document.documentElement.style.colorScheme = theme;
    localStorage.setItem('meMoneyTheme', theme);
}
    // TẢI ẢNH
    // TẢI ẢNH VÀ ĐẶT LÀM HÌNH NỀN NGAY
if (uploadInput) {
    uploadInput.addEventListener('change', function () {
        const file = this.files[0];

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

                // LƯU ẢNH
                localStorage.setItem('meMoneyCustomImage', imageData);
                localStorage.setItem('meMoneyBackground', 'custom');

                // ÁP DỤNG ẢNH LÀM HÌNH NỀN NGAY
                document.body.style.backgroundImage =
                    `url("${imageData}")`;

                document.body.style.backgroundSize = 'cover';
                document.body.style.backgroundPosition = 'center';
                document.body.style.backgroundRepeat = 'no-repeat';
                document.body.style.backgroundAttachment = 'fixed';

                if (mainArea) {
                    mainArea.style.backgroundImage =
                        `url("${imageData}")`;

                    mainArea.style.backgroundSize = 'cover';
                    mainArea.style.backgroundPosition = 'center';
                    mainArea.style.backgroundRepeat = 'no-repeat';
                }

                // BỎ CHỌN CÁC MÀU NỀN CŨ
                wallpaperButtons.forEach(button => {
                    button.classList.remove('selected');
                });

                // HIỂN THỊ ẢNH XEM TRƯỚC NẾU CÓ
                if (imagePreview) {
                    imagePreview.src = imageData;
                    imagePreview.style.display = 'block';
                }

                showToast('Đã cập nhật ảnh nền.');

            } catch (error) {
                showToast('Không thể lưu ảnh.');
            }
        };

        reader.readAsDataURL(file);
    });
}

    // KHÔI PHỤC MẶC ĐỊNH
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (!confirm('Bạn có chắc muốn khôi phục giao diện mặc định không?')) {
                return;
            }

            localStorage.removeItem('meMoneyTheme');
            localStorage.removeItem('meMoneyBackground');
            localStorage.removeItem('meMoneyCustomImage');

            applyTheme('light');
            applyBackground('default');

            if (uploadInput) uploadInput.value = '';

            showToast('Đã khôi phục cài đặt mặc định.');
        });
    }

    // KHÔI PHỤC CÀI ĐẶT ĐÃ LƯU
    const savedTheme = localStorage.getItem('meMoneyTheme') || 'light';
    const savedBackground = localStorage.getItem('meMoneyBackground') || 'default';
    const savedImage = localStorage.getItem('meMoneyCustomImage');

    applyTheme(savedTheme);

    if (savedBackground === 'custom' && savedImage) {
    document.body.style.backgroundImage = `url("${savedImage}")`;
    document.body.style.backgroundSize = 'cover';
    document.body.style.backgroundPosition = 'center';
    document.body.style.backgroundRepeat = 'no-repeat';
    document.body.style.backgroundAttachment = 'fixed';

    if (mainArea) {
        mainArea.style.backgroundImage = `url("${savedImage}")`;
        mainArea.style.backgroundSize = 'cover';
        mainArea.style.backgroundPosition = 'center';
        mainArea.style.backgroundRepeat = 'no-repeat';
    }

    if (imagePreview) {
        imagePreview.src = savedImage;
        imagePreview.style.display = 'block';
    }
} else {
    applyBackground(savedBackground);
}
})();
</script>
<?php require 'includes/footer.php'; ?>
