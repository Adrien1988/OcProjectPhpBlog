/*!
 * Start Bootstrap - Freelancer v7.0.7 (https://startbootstrap.com/theme/freelancer)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-freelancer/blob/master/LICENSE)
 */

/* global bootstrap */

(function() {
    // Vérifier que le DOM est chargé
    document.addEventListener('DOMContentLoaded', function() {
      // Navbar shrink function.
      var navbarShrink = function() {
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
  
      responsiveNavItems.forEach(function(responsiveNavItem) {
        responsiveNavItem.addEventListener('click', function() {
          if (window.getComputedStyle(navbarToggler).display !== 'none') {
            navbarToggler.click();
          }
        });
      });
  
      // Function to reset the form.
      var resetForm = function() {
        var form = document.getElementById('contactForm');
        if (form) {
          form.reset();
        }
      };
  
      // Named function for handling the form submission.
      var handleFormSubmit = function(event) {
        event.preventDefault();
  
        var form = document.getElementById('contactForm');
        if (form) {
          var formData = new FormData(form);
  
          fetch(form.action, {
            method: 'POST',
            body: formData,
          })
            .then(function(response) {
              if (!response.ok) {
                return response.text().then(function(text) {
                  throw new Error(text);
                });
              }
              return response.text();
            })
            .then(function(data) {
              document.getElementById('modalMessage').textContent = data;
              var feedbackModal = new bootstrap.Modal(
                document.getElementById('feedbackModal'),
              );
              feedbackModal.show();
  
              // Reset the form after submission.
              resetForm();
  
              // Add an event listener to reset the form when the modal is closed.
              var feedbackModalElement = document.getElementById('feedbackModal');
              feedbackModalElement.addEventListener('hidden.bs.modal', resetForm);
            })
            .catch(function(error) {
              document.getElementById('modalMessage').textContent =
                "Erreur lors de l'envoi du message: " + error.message;
              var feedbackModal = new bootstrap.Modal(
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
      var handleBackToTopButton = function() {
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
  