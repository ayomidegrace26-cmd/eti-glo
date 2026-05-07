document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-ajax-submit]');

    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]') || (form.id ? document.querySelector(`button[form="${form.id}"]`) : null);
            if (!submitBtn) return; // Prevent error if no button found
            
            const originalBtnText = submitBtn.innerHTML;
            
            // Add loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;

            const formData = new FormData(form);

            try {
                const response = await fetch('process_form.php', {
                    method: 'POST',
                    body: formData
                });

                const rawText = await response.text();
                let result;
                try {
                    result = JSON.parse(rawText);
                } catch (parseError) {
                    console.error('Raw Server Response:', rawText);
                    const errorSnippet = rawText ? rawText.substring(0, 200) : '[Empty Response]';
                    throw new Error('Server returned invalid data: ' + errorSnippet);
                }

                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message || 'Your form has been submitted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#113521' // brand-green
                    });
                    form.reset(); // Clear the form
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: result.message || 'An error occurred while submitting the form. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#D2A143' // brand-gold
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Detailed Error',
                    text: error.message || 'A network error occurred.',
                    icon: 'error',
                    confirmButtonColor: '#D2A143'
                });
            } finally {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    });
});
