export function initWorkspace() {
    document.querySelectorAll('[data-payment-tabs]').forEach((group) => {
        const tabs = [...group.querySelectorAll('[data-payment-tab]')];
        const select = (tab) => {
            tabs.forEach((item) => {
                const selected = item === tab;
                item.setAttribute('aria-selected', String(selected));
                item.tabIndex = selected ? 0 : -1;
            });
            group.querySelectorAll('[data-payment-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.paymentPanel !== tab.dataset.paymentTab;
            });
        };
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => select(tab));
            tab.addEventListener('keydown', (event) => {
                let next;
                if (event.key === 'ArrowRight') next = tabs[(index + 1) % tabs.length];
                if (event.key === 'ArrowLeft') next = tabs[(index + tabs.length - 1) % tabs.length];
                if (event.key === 'Home') next = tabs[0];
                if (event.key === 'End') next = tabs.at(-1);
                if (next) {
                    event.preventDefault();
                    select(next);
                    next.focus();
                }
            });
        });
    });

    const preview = document.querySelector('[data-shipping-preview]');
    const input = document.getElementById('o-fee');
    if (preview && input) {
        const rupiah = (value) => 'Rp' + value.toLocaleString('id-ID');
        const update = () => {
            const fee = input.valueAsNumber;
            const valid = input.value !== '' && input.validity.valid && Number.isSafeInteger(fee);
            preview.querySelector('[data-preview-fee]').textContent = valid ? rupiah(fee) : '—';
            preview.querySelector('[data-preview-total]').textContent = valid ? rupiah(Number(preview.dataset.subtotal) + fee) : '—';
        };
        input.addEventListener('input', update);
        update();
    }
}
