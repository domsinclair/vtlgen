document.addEventListener("DOMContentLoaded", function() {
    const questionModal = document.getElementById('vtlQuestionModal');
    const questionOverlay = document.getElementById('vtlQuestionOverlay');
    const questionModalHeader = document.getElementById('vtlQuestionModalHeader');
    const questionModalTitle = document.getElementById('vtlQuestionModalTitle');
    const questionContent = document.getElementById('vtlQuestionContent');
    const questionIconPicture = document.getElementById('vtlQuestionIconPicture');
    const questionIconDark = document.getElementById('vtlQuestionIconDark');
    const questionIconLight = document.getElementById('vtlQuestionIconLight');
    const acceptQuestionButton = document.getElementById('vtlAcceptQuestion');
    const cancelQuestionButton = document.getElementById('vtlCancelQuestion');

    function showQuestionModal(title, content, iconBaseName = '', type = '') {
        questionModalTitle.textContent = title;
        questionContent.textContent = content;

        // Set the icon sources or clear them if no icon is provided
        if (iconBaseName) {
            questionIconDark.srcset = `vtlgen_module/help/images/${iconBaseName}Dark.svg`;
            questionIconLight.src = `vtlgen_module/help/images/${iconBaseName}.svg`;
            questionIconPicture.style.display = 'inline';
        } else {
            questionIconDark.srcset = '';
            questionIconLight.src = '';
            questionIconPicture.style.display = 'none';
        }

        // Remove any existing type classes
        questionModalHeader.classList.remove('info', 'warning', 'error');
        questionModal.classList.remove('info', 'warning', 'error');

        // Add the appropriate class based on the type
        if (type) {
            questionModalHeader.classList.add(type);
            questionModal.classList.add(type);
        }

        // Override display property to make the modal visible
        questionOverlay.style.display = 'block';
        questionModal.style.display = 'block';
        questionModal.open = true;
        questionModal.dispatchEvent(new Event('vtlQuestionModalOpened'));
    }

    function closeQuestionModal() {
        questionModal.open = false;
        questionModal.style.display = 'none';
        questionOverlay.style.display = 'none';
        questionModal.dispatchEvent(new Event('vtlQuestionModalClosed'));
    }

    function acceptQuestion() {
        questionModal.dispatchEvent(new Event('vtlQuestionAccepted'));
        closeQuestionModal();
    }

    function cancelQuestion() {
        questionModal.dispatchEvent(new Event('vtlQuestionCancelled'));
        closeQuestionModal();
    }

    function handleKeydown(event) {
        if (event.key === 'Enter' && questionModal.open) {
            acceptQuestion();
        } else if (event.key === 'Escape' && questionModal.open) {
            cancelQuestion();
        }
    }

    acceptQuestionButton.addEventListener('click', acceptQuestion);
    cancelQuestionButton.addEventListener('click', cancelQuestion);

    // Close modal on overlay click
    questionOverlay.addEventListener('click', cancelQuestion);

    // Close the modal when pressing the Escape key or accepting with Enter key
    document.addEventListener('keydown', handleKeydown);

    // Attach functions to the global scope for external calls
    window.openVtlQuestionModal = function(title, content, iconBaseName = '', type = '') {
        showQuestionModal(title, content, iconBaseName, type);
    }

    window.closeVtlQuestionModal = closeQuestionModal;
});
