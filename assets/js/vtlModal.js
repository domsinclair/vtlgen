document.addEventListener("DOMContentLoaded", function() {
    const vtlModal = document.getElementById('vtlModal');
    const vtlOverlay = document.getElementById('vtlOverlay');
    const vtlModalHeader = document.getElementById('vtlModalHeader');
    const vtlModalTitle = document.getElementById('vtlModalTitle');
    const vtlResponse = document.getElementById('vtlResponse');
    const vtlCloseModal = document.getElementById('vtlCloseModal');

    function showModal(title, content, isSuccess) {
        vtlModalTitle.textContent = title;
        vtlResponse.textContent = content;

        // Remove previous success/error classes
        vtlModalHeader.classList.remove('modalTitleSuccess', 'modalTitleError');

        // Add the appropriate class based on success/error
        if (isSuccess) {
            vtlModalHeader.classList.add('modalTitleSuccess');
        } else {
            vtlModalHeader.classList.add('modalTitleError');
        }

        // Override display property to make the modal visible
        vtlOverlay.style.display = 'block';
        vtlModal.style.display = 'block'; // Override the CSS rule
        vtlModal.open = true;
        vtlModal.dispatchEvent(new Event('vtlModalOpened'));
    }

    function closeModal() {
        vtlModal.open = false; // Set open attribute to false
        vtlModal.style.display = 'none'; // Hide the modal
        vtlOverlay.style.display = 'none'; // Hide the overlay
        vtlModal.dispatchEvent(new Event('vtlModalClosed'));
    }

    vtlCloseModal.addEventListener('click', closeModal);

    // Close modal on overlay click
    vtlOverlay.addEventListener('click', closeModal);

    // Close the modal when pressing the Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && vtlModal.open) {
            closeModal();
        }
    });

    // Attach functions to the global scope for external calls
    // Function to open the modal with a custom title, success status, and message
    window.openVtlModal = function(title, isSuccess, responseMessage) {
        showModal(title, responseMessage, isSuccess);
    }

    window.closeVtlModal = closeModal;


});


