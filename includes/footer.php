</main>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
<script>
document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', e => {
  if (!confirm(el.dataset.confirm)) e.preventDefault();
}));
</script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
</body></html>
