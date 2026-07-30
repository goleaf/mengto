const registerPrintControls = () => {
    document.querySelectorAll('[data-print-page]').forEach((control) => {
        if (control.dataset.printReady === 'true') {
            return;
        }

        control.dataset.printReady = 'true';
        control.addEventListener('click', () => window.print());
    });
};

document.addEventListener('DOMContentLoaded', registerPrintControls);
document.addEventListener('livewire:navigated', registerPrintControls);
