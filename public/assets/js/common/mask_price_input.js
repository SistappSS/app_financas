(() => {
    const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    document.querySelectorAll('.moeda-brl').forEach(initMask);

    function initMask(input) {
        const hidden = input.nextElementSibling?.classList?.contains('moeda-brl-valor')
            ? input.nextElementSibling
            : null;

        setMasked(input, hidden, 0);

        input.addEventListener('input', () => {
            const cents = getCentsFromText(input.value);
            setMasked(input, hidden, cents);
            moveCaretToEnd(input);
        });

        input.addEventListener('keydown', (e) => {
            const allow = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
            if (allow.includes(e.key)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });

        input.addEventListener('focus', () => {
            setTimeout(() => input.select(), 0);
        });
    }

    function getCentsFromText(txt) {
        const digits = (txt || '').replace(/\D/g, '');
        if (!digits) return 0;
        return parseInt(digits.slice(0, 15), 10);
    }

    function setMasked(input, hidden, cents) {
        const valor = cents / 100;
        input.value = fmt.format(valor);
        if (hidden) hidden.value = valor.toFixed(2);
    }

    function moveCaretToEnd(el) {
        const len = el.value.length;
        el.setSelectionRange(len, len);
    }
})();
