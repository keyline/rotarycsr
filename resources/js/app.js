

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function initRichEditors() {
    document.querySelectorAll('[data-rich-editor]').forEach((wrapper) => {
        if (wrapper.dataset.richEditorInit) return;
        wrapper.dataset.richEditorInit = '1';

        const input = wrapper.querySelector('[data-rich-editor-input]');
        const textarea = wrapper.querySelector('textarea');
        if (!input || !textarea) return;

        wrapper.querySelectorAll('[data-cmd]').forEach((btn) => {
            btn.addEventListener('mousedown', (e) => e.preventDefault());
            btn.addEventListener('click', () => {
                document.execCommand(btn.dataset.cmd, false, null);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.focus();
            });
        });

        input.addEventListener('input', () => {
            textarea.value = input.innerHTML;
        });
    });
}

document.addEventListener('DOMContentLoaded', initRichEditors);
