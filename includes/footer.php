
</main>
</div>

<!-- THU VIEN ICON -->
<script src="https://unpkg.com/lucide@latest"></script>

<script>
if (window.lucide) {
    lucide.createIcons();
}
</script>

<!-- XAC NHAN THAO TAC -->
<script>
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) {
            e.preventDefault();
        }
    });
});
</script>

<!-- =========================================
     DONG BO GIAO DIEN TOAN WEBSITE
========================================= -->

<script>
(function () {
    'use strict';

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

        const selectedColor =
            colors[background] || colors.default;

        // DONG BO CHE DO SANG TOI

        document.documentElement.classList.toggle(
            'dark-mode',
            isDark
        );

        document.body.classList.toggle(
            'dark-mode',
            isDark
        );

        document.documentElement.style.colorScheme =
            isDark ? 'dark' : 'light';

        // DONG BO MAU NEN

        document.documentElement.style.setProperty(
            '--selected-background',
            selectedColor
        );

        // XOA ANH NEN CU

        const targets = [
            document.body,
            mainArea
        ].filter(Boolean);

        targets.forEach(element => {
            element.style.removeProperty('background-image');
            element.style.removeProperty('background-size');
            element.style.removeProperty('background-position');
            element.style.removeProperty('background-repeat');
            element.style.removeProperty('background-attachment');
            element.style.removeProperty('background-color');
        });

        // AP DUNG ANH NEN TUY CHINH

        if (
            background === 'custom' &&
            customImage &&
            customImage.startsWith('data:image/')
        ) {
            const imageUrl = 'url("' + customImage + '")';

            const finalImage = isDark
                ? 'linear-gradient(rgba(15,20,35,0.48), rgba(15,20,35,0.48)), ' + imageUrl
                : imageUrl;

            targets.forEach(element => {
                element.style.setProperty(
                    'background-image',
                    finalImage
                );

                element.style.setProperty(
                    'background-size',
                    'cover'
                );

                element.style.setProperty(
                    'background-position',
                    'center'
                );

                element.style.setProperty(
                    'background-repeat',
                    'no-repeat'
                );
            });

            document.body.style.setProperty(
                'background-attachment',
                'fixed'
            );

        } else {
            // AP DUNG MAU NEN

            const finalColor = isDark
                ? '#171c2b'
                : selectedColor;

            targets.forEach(element => {
                element.style.setProperty(
                    'background-color',
                    finalColor
                );
            });
        }
    }

    // CHO CAC TRANG GOI LAI HAM

    window.applySharedTheme = applySharedTheme;

    // KHOI PHUC GIAO DIEN KHI TAI TRANG

    applySharedTheme();

})();
</script>

</body>
</html>
