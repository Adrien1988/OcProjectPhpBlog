/*!
 * Start Bootstrap - Freelancer v7.0.7 (https://startbootstrap.com/theme/freelancer)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-freelancer/blob/master/LICENSE)
 */

/* global bootstrap */

const handleDOMContentLoaded = () => {
    // Navbar shrink function.
    const navbarShrink = () => {
        const navbarCollapsible = document.body.querySelector('#mainNav');
        if (!navbarCollapsible) {
            return;
        }
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink');
        } else {
            navbarCollapsible.classList.add('navbar-shrink');
        }
    };

    // Shrink the navbar.
    navbarShrink();

    // Shrink the navbar when page is scrolled.
    document.addEventListener('scroll', navbarShrink);

    // Activate Bootstrap scrollspy on the main nav element.
    const mainNav = document.body.querySelector('#mainNav');
    if (mainNav) {
        // Initialise ScrollSpy.
        // eslint-disable-next-line no-new
        new bootstrap.ScrollSpy(document.body, {
            target: '#mainNav',
            rootMargin: '0px 0px -40%',
        });
    }

    // Collapse responsive navbar when toggler is visible.
    const navbarToggler = document.body.querySelector('.navbar-toggler');
    const responsiveNavItems = [].slice.call(
        document.querySelectorAll('#navbarResponsive .nav-link'),
    );

    responsiveNavItems.forEach((responsiveNavItem) => {
        responsiveNavItem.addEventListener('click', () => {
            if (window.getComputedStyle(navbarToggler).display !== 'none') {
                navbarToggler.click();
            }
        });
    });

    // Function to reset the form.
    const resetForm = () => {
        const form = document.getElementById('contactForm');
        if (form) {
            form.reset();
        }
    };

    // Handle contact form submission with AJAX.
    const submitButton = document.getElementById('submitButton');
    if (submitButton) {
        submitButton.addEventListener('click', function (event) {
            event.preventDefault();

            const form = document.getElementById('contactForm');
            if (form) {
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => { throw new Error(text); });
                        }
                        return response.text();
                    })
                    .then(data => {
                        document.getElementById('modalMessage').innerHTML = data;
                        const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
                        feedbackModal.show();

                        // Reset the form after submission.
                        resetForm();

                        // Add an event listener to reset the form when the modal is closed.
                        const feedbackModalElement = document.getElementById('feedbackModal');
                        feedbackModalElement.addEventListener('hidden.bs.modal', resetForm);
                    })
                    .catch(error => {
                        document.getElementById('modalMessage').innerHTML = 'Erreur lors de l\'envoi du message: ' + error.message;
                        const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
                        feedbackModal.show();
                    });
            }
        });
    }
};

window.addEventListener('DOMContentLoaded', handleDOMContentLoaded);
