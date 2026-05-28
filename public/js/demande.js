function show(id) {
    const el = document.getElementById(id);
    el.style.display = 'block';
    setTimeout(() => el.classList.add('form-slide-in'), 10);
}
function hide(id) {
    const el = document.getElementById(id);
    el.classList.remove('form-slide-in');
    setTimeout(() => el.style.display = 'none', 200);
}
function toggleAutre(sel) {
    const input = document.getElementById('dem-autre');
    if (sel.value === 'AUTRES') {
        show('autre-field');
        input.required = true;
    } else {
        hide('autre-field');
        input.required = false;
    }
}
