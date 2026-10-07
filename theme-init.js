(function () {
  'use strict';
  try {
    var savedTheme = localStorage.getItem('readpilot-theme');
    if (savedTheme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.remove('light-theme');
    } else {
      document.documentElement.removeAttribute('data-theme');
      document.documentElement.classList.add('light-theme');
    }
    if (localStorage.getItem('readpilot-sidebar') === 'collapsed') {
      document.documentElement.setAttribute('data-sidebar', 'collapsed');
    }
  } catch (error) {
  }
}());
