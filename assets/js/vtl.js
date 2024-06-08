function openVtlModal(modalId) {
    const body = document.body;
    let pageOverlay = document.getElementById("vtl-overlay");

    if (!pageOverlay) {
        const modalContainer = document.createElement("div");
        modalContainer.id = "vtl-modal-container";
        modalContainer.style.zIndex = 3;
        body.prepend(modalContainer);

        const overlay = document.createElement("div");
        overlay.id = "vtl-overlay";
        overlay.style.zIndex = 2;
        body.prepend(overlay);

        const targetModal = document.getElementById(modalId);
        if (!targetModal) {
            console.error(`Modal with id "${modalId}" not found.`);
            return;
        }

        const targetModalContent = targetModal.innerHTML;
        targetModal.remove();

        const newModal = document.createElement("div");
        newModal.className = "vtl-modal";
        newModal.id = modalId;
        newModal.style.zIndex = 4;
        newModal.innerHTML = targetModalContent;
        modalContainer.appendChild(newModal);

        // Event listener for clicking outside the modal
        overlay.addEventListener('click', closeVtlModal);

        // Event listener for pressing the Escape key
        document.addEventListener('keydown', handleEscapeKey);

        setTimeout(() => {
            newModal.style.opacity = 1;
            newModal.style.marginTop = "12vh";
        }, 0);
    }
}

function closeVtlModal() {
    const modalContainer = document.getElementById("vtl-modal-container");
    if (modalContainer) {
        const openModal = modalContainer.firstChild;

        openModal.style.zIndex = -4;
        openModal.style.opacity = 0;
        openModal.style.marginTop = "12vh";
        openModal.style.display = "none";
        document.body.appendChild(openModal);

        modalContainer.remove();

        const overlay = document.getElementById("vtl-overlay");
        if (overlay) {
            overlay.remove();
        }

        // Remove event listener for clicking outside the modal
        document.removeEventListener('click', closeVtlModal);

        // Remove event listener for pressing the Escape key
        document.removeEventListener('keydown', handleEscapeKey);

        // Dispatch a custom event indicating modal closure
        const event = new Event('vtlModalClosed');
        document.dispatchEvent(event);
    }
}

function handleEscapeKey(event) {
    if (event.key === 'Escape') {
        closeVtlModal();
    }
}

// Adding event listeners to improve UX
document.addEventListener('vtlModalClosed', () => {
    console.log('VTL Modal has been closed.');
});

// Example usage: Open modal and set content
function showResponseModal(response) {
    openVtlModal('responseModal');
    const targetEl = document.getElementById('the-response');
    targetEl.innerHTML = response.message;
}

// Example response object
const response = {
    message: 'This is a sample response message.'
};

// Call showResponseModal to demonstrate opening the modal with content
document.addEventListener('DOMContentLoaded', () => {
    showResponseModal(response);
});


