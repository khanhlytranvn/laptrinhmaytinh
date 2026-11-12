</main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>

<script>
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) {
            e.preventDefault();
        }
    });
});



     GIAO DIỆN DÙNG CHUNG TOÀN WEBSITE

<style>

THANH TIÊU ĐỀ 

.topbar {
    background: transparent ;
    box-shadow: none ;
}

NIGHT MODE - NỀN CHUNG 

body.dark-mode {
    background-color: #171c2b ;
    color: #e8edf5 ;
}

body.dark-mode .app-shell {
    background-color: #171c2b ;
}

body.dark-mode .main-area {
    background-color: #171c2b ;
    color: #e8edf5 ;
}

body.dark-mode .topbar {
    background: transparent ;
    color: #e8edf5 ;
}

 SIDEBAR 

body.dark-mode .sidebar {
    background-color: #20283a ;
    color: #e8edf5 ;
    border-color: #39445b ;
}

body.dark-mode .sidebar a,
body.dark-mode .sidebar span,
body.dark-mode .sidebar strong {
    color: #e8edf5;
}

body.dark-mode .side-caption,
body.dark-mode .sidebar small {
    color: #aab6ca ;
}

TIÊU ĐỀ VÀ NỘI DUNG 

body.dark-mode .main-area h1,
body.dark-mode .main-area h2,
body.dark-mode .main-area h3,
body.dark-mode .main-area h4,
body.dark-mode .main-area p,
body.dark-mode .main-area label,
body.dark-mode .main-area th,
body.dark-mode .main-area td,
body.dark-mode .main-area .eyebrow {
    color: #e8edf5;
}

body.dark-mode .topbar h1,
body.dark-mode .topbar .eyebrow {
    color: #e8edf5 ;
}

 CÁC KHUNG NỘI DUNG 

body.dark-mode .card,
body.dark-mode .panel,
body.dark-mode .settings-card,
body.dark-mode .stat-card,
body.dark-mode .summary-card,
body.dark-mode .content-card,
body.dark-mode .table-card {
    background-color: #252d40 ;
    color: #e8edf5 ;
    border-color: #39445b ;
}

 Ô NHẬP LIỆU 

body.dark-mode input,
body.dark-mode select,
body.dark-mode textarea {
    background-color: #20283a ;
    color: #e8edf5 ;
    border-color: #46536d ;
}

body.dark-mode input::placeholder,
body.dark-mode textarea::placeholder {
    color: #9ba9bf ;
}

 NGÀY THÁNG 

body.dark-mode .top-date {
    background-color: #252d40 ;
    color: #e8edf5 ;
    border-color: #39445b ;
}

 BẢNG 

body.dark-mode table {
    color: #e8edf5 ;
}

body.dark-mode th,
body.dark-mode td {
    border-color: #39445b ;
}

</style>

<script>
(function () {

    const colors = {
        default: '#f4f6fa',
        blue: '#dceafa',
        green: '#dcebd8',
        pink: '#f8dfe5',
        lavender: '#e9e0f8',
        cream: '#f2e7cf'
    };

    function applySharedTheme() {

        const theme =
            localStorage.getItem('meMoneyTheme') || 'light';

        const isDark = theme === 'dark';

        const background =
            localStorage.getItem('meMoneyBackground') || 'default';

        const customImage =
            localStorage.getItem('meMoneyCustomImage');

        const mainArea =
            document.querySelector('.main-area');

        // ÁP DỤNG CHẾ ĐỘ SÁNG / TỐI

        document.documentElement.classList.toggle(
            'dark-mode',
            isDark
        );

        document.body.classList.toggle(
            'dark-mode',
            isDark
        );

        document.documentElement.style.colorScheme = theme;

        // NIGHT MODE LUÔN ƯU TIÊN

if (isDark) {

    document.body.style.setProperty(
        'background-color',
        '#171c2b',
        'important'
    );

    if (background === 'custom' && customImage) {

        const imageUrl = 'url("' + customImage + '")';

        const darkOverlay =
            'linear-gradient(rgba(15, 20, 35, 0.48), rgba(15, 20, 35, 0.48)), ' + imageUrl;

        document.body.style.setProperty(
            'background-image',
            darkOverlay,
            'important'
        );

        document.body.style.setProperty(
            'background-size',
            'cover',
            'important'
        );

        document.body.style.setProperty(
            'background-position',
            'center',
            'important'
        );

        document.body.style.setProperty(
            'background-attachment',
            'fixed',
            'important'
        );

        if (mainArea) {
            mainArea.style.setProperty(
                'background-image',
                darkOverlay,
                'important'
            );

            mainArea.style.setProperty(
                'background-size',
                'cover',
                'important'
            );

            mainArea.style.setProperty(
                'background-position',
                'center',
                'important'
            );
        }

    } else {

        document.body.style.removeProperty('background-image');

        if (mainArea) {
            mainArea.style.removeProperty('background-image');
        }
    }

    return;
}
        // KHÔI PHỤC GIAO DIỆN SÁNG

        const selectedColor =
            colors[background] || colors.default;

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

        // ÁP DỤNG ẢNH NỀN ĐÃ CHỌN

        if (background === 'custom' && customImage) {

            const imageUrl = 'url("' + customImage + '")';

            document.body.style.setProperty(
                'background-image',
                imageUrl,
                'important'
            );

            document.body.style.setProperty(
                'background-size',
                'cover',
                'important'
            );

            document.body.style.setProperty(
                'background-position',
                'center',
                'important'
            );

            document.body.style.setProperty(
                'background-repeat',
                'no-repeat',
                'important'
            );

            document.body.style.setProperty(
                'background-attachment',
                'fixed',
                'important'
            );

            if (mainArea) {

                mainArea.style.setProperty(
                    'background-image',
                    imageUrl,
                    'important'
                );

                mainArea.style.setProperty(
                    'background-size',
                    'cover',
                    'important'
                );

                mainArea.style.setProperty(
                    'background-position',
                    'center',
                    'important'
                );

                mainArea.style.setProperty(
                    'background-repeat',
                    'no-repeat',
                    'important'
                );
            }

        } else {

            document.body.style.removeProperty('background-image');

            if (mainArea) {
                mainArea.style.removeProperty('background-image');
            }
        }
    }

    applySharedTheme();

})();
</script>

</body>
</html>