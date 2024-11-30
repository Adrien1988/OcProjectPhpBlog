/*!
 * Start Bootstrap - Freelancer v7.0.7 (https://startbootstrap.com/theme/freelancer)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-freelancer/blob/master/LICENSE)
 */

/* global bootstrap */

(function () {
  // Vérifier que le DOM est chargé
  document.addEventListener('DOMContentLoaded', function () {
    // Navbar shrink function.
    var navbarShrink = function () {
      var navbarCollapsible = document.body.querySelector('#mainNav');
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
    var mainNav = document.body.querySelector('#mainNav');
    if (mainNav) {
      new bootstrap.ScrollSpy(document.body, {
        target: '#mainNav',
        rootMargin: '0px 0px -40%',
      });
    }

    // Collapse responsive navbar when toggler is visible.
    var navbarToggler = document.body.querySelector('.navbar-toggler');
    var responsiveNavItems = [].slice.call(
      document.querySelectorAll('#navbarResponsive .nav-link'),
    );

    responsiveNavItems.forEach(function (responsiveNavItem) {
      responsiveNavItem.addEventListener('click', function () {
        if (window.getComputedStyle(navbarToggler).display !== 'none') {
          navbarToggler.click();
        }
      });
    });

    // Function to reset the form.
    var resetForm = function () {
      var form = document.getElementById('contactForm');
      if (form) {
        form.reset();
      }
    };

    // Fonction pour gérer la soumission du formulaire.
    const handleFormSubmit = function (event) {
      event.preventDefault();

      const form = document.getElementById('contactForm');
      if (form) {
        const formData = new FormData(form);

        fetch(form.action, {
          method: 'POST',
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              return response.text().then((text) => {
                throw new Error(text);
              });
            }
            return response.text();
          })
          .then((data) => {
            document.getElementById('modalMessage').textContent = data;
            const feedbackModal = new bootstrap.Modal(
              document.getElementById('feedbackModal'),
            );
            feedbackModal.show();

            // Réinitialiser le formulaire après soumission.
            resetForm();

            // Ajouter un écouteur pour réinitialiser le formulaire lorsque le modal est fermé.
            const feedbackModalElement =
              document.getElementById('feedbackModal');
            feedbackModalElement.addEventListener('hidden.bs.modal', resetForm);
          })
          .catch((error) => {
            document.getElementById(
              'modalMessage',
            ).textContent = `Erreur lors de l'envoi du message: ${error.message}`;
            const feedbackModal = new bootstrap.Modal(
              document.getElementById('feedbackModal'),
            );
            feedbackModal.show();
          });
      }
    };

    var submitButton = document.getElementById('submitButton');
    if (submitButton) {
      submitButton.addEventListener('click', handleFormSubmit);
    }

    // Back to top button visibility logic
    var handleBackToTopButton = function () {
      var backToTop = document.getElementById('backToTop');
      if (backToTop) {
        if (window.scrollY > 200) {
          backToTop.removeAttribute('hidden');
        } else {
          backToTop.setAttribute('hidden', '');
        }
      }
    };

    // Obtenir le bouton "Retour en haut"
    var backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
      // Gérer la visibilité du bouton au chargement initial
      handleBackToTopButton();

      // Ajouter un écouteur d'événement pour le défilement
      window.addEventListener('scroll', handleBackToTopButton);
    }
  });
})();
