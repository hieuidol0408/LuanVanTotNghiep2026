/**
 * STU ENTERPRISE DASHBOARD JAVASCRIPT
 * Trường Đại học Công nghệ Sài Gòn (STU)
 * Quản lý tương tác: Theme Switcher, Collapsible Sidebar, Tabs, Modals, Dropdowns
 */

(function () {
  'use strict';

  // --- 1. THEME SWITCHER (Light / Dark Mode with localStorage) ---
  const THEME_KEY = 'stu_enterprise_theme';

  function initTheme() {
    const savedTheme = localStorage.getItem(THEME_KEY) || 
      (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);
  }

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    localStorage.setItem(THEME_KEY, theme);
    updateThemeIcons(theme);
  }

  function toggleTheme() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    applyTheme(isDark ? 'light' : 'dark');
  }

  function updateThemeIcons(theme) {
    const themeButtons = document.querySelectorAll('[data-stu-theme-toggle]');
    themeButtons.forEach(btn => {
      btn.setAttribute('aria-label', theme === 'dark' ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối');
      const sunIcon = btn.querySelector('.stu-icon-sun');
      const moonIcon = btn.querySelector('.stu-icon-moon');
      if (sunIcon && moonIcon) {
        if (theme === 'dark') {
          sunIcon.style.display = 'block';
          moonIcon.style.display = 'none';
        } else {
          sunIcon.style.display = 'none';
          moonIcon.style.display = 'block';
        }
      }
    });
  }

  // --- 2. SIDEBAR COLLAPSE & MOBILE OVERLAY ---
  function initSidebar() {
    const sidebar = document.querySelector('.stu-sidebar');
    const app = document.querySelector('.stu-app');
    const toggleBtns = document.querySelectorAll('[data-stu-sidebar-toggle]');

    if (!sidebar || !app) return;

    toggleBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        if (window.innerWidth <= 1024) {
          sidebar.classList.toggle('mobile-open');
        } else {
          sidebar.classList.toggle('collapsed');
          app.classList.toggle('sidebar-collapsed');
        }
      });
    });

    // Close mobile sidebar on window resize if > 1024px
    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024) {
        sidebar.classList.remove('mobile-open');
      }
    });
  }

  // --- 3. TABS CONTROLLER ---
  function initTabs() {
    document.addEventListener('click', function (e) {
      const tabBtn = e.target.closest('.stu-tab-btn');
      if (!tabBtn) return;

      const targetId = tabBtn.getAttribute('data-tab-target');
      if (!targetId) return;

      const tabContainer = tabBtn.closest('.stu-tabs-wrapper');
      if (!tabContainer) return;

      // Deactivate all sibling buttons
      tabContainer.querySelectorAll('.stu-tab-btn').forEach(btn => btn.classList.remove('active'));
      tabBtn.classList.add('active');

      // Deactivate all sibling panes
      tabContainer.querySelectorAll('.stu-tab-pane').forEach(pane => pane.classList.remove('active'));
      const targetPane = tabContainer.querySelector(targetId);
      if (targetPane) {
        targetPane.classList.add('active');
      }
    });
  }

  // --- 4. MODAL CONTROLLER ---
  window.STUModal = {
    open: function (modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
    },
    close: function (modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
      }
    }
  };

  function initModals() {
    // Open trigger
    document.addEventListener('click', function (e) {
      const openTrigger = e.target.closest('[data-modal-open]');
      if (openTrigger) {
        const modalId = openTrigger.getAttribute('data-modal-open');
        window.STUModal.open(modalId);
      }

      // Close trigger
      const closeTrigger = e.target.closest('[data-modal-close]');
      if (closeTrigger) {
        const modal = closeTrigger.closest('.stu-modal-backdrop');
        if (modal) {
          window.STUModal.close(modal.id);
        }
      }

      // Backdrop click
      if (e.target.classList.contains('stu-modal-backdrop')) {
        window.STUModal.close(e.target.id);
      }
    });

    // Escape key listener
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        const openModal = document.querySelector('.stu-modal-backdrop.show');
        if (openModal) {
          window.STUModal.close(openModal.id);
        }
      }
    });
  }

  // --- 5. DROPDOWN CONTROLLER ---
  function initDropdowns() {
    document.addEventListener('click', function (e) {
      const toggle = e.target.closest('[data-stu-dropdown-toggle]');
      const openDropdowns = document.querySelectorAll('.stu-dropdown.open');

      if (toggle) {
        const dropdown = toggle.closest('.stu-dropdown');
        if (dropdown) {
          const isOpen = dropdown.classList.contains('open');
          // Close other open dropdowns
          openDropdowns.forEach(d => {
            if (d !== dropdown) d.classList.remove('open');
          });
          dropdown.classList.toggle('open', !isOpen);
        }
      } else {
        // Click outside closes all dropdowns
        if (!e.target.closest('.stu-dropdown-menu')) {
          openDropdowns.forEach(d => d.classList.remove('open'));
        }
      }
    });
  }

  // --- INITIALIZATION ---
  document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initSidebar();
    initTabs();
    initModals();
    initDropdowns();

    // Attach theme toggle buttons
    document.querySelectorAll('[data-stu-theme-toggle]').forEach(btn => {
      btn.addEventListener('click', toggleTheme);
    });
  });

  // Global helper on window
  window.STUTheme = {
    toggle: toggleTheme,
    apply: applyTheme
  };

})();
