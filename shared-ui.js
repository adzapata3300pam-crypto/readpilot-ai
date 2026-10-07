(function () {
  'use strict';

  function showToast(message) {
    var toast = document.getElementById('toast');
    var toastMessage = document.getElementById('toastMsg');
    if (!toast || !toastMessage) return;
    toastMessage.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(window.readPilotToastTimer);
    window.readPilotToastTimer = window.setTimeout(function () {
      toast.classList.remove('show');
    }, 2400);
  }

  /* =====================================================================
     Shared floating-panel manager
     Every dropdown (date calendar, notifications, custom selects, and any
     future ones) registers itself here instead of managing its own
     document-level click/Escape listeners. This guarantees only one
     dropdown is ever open at a time — opening the bell while the calendar
     is open closes the calendar automatically, the way a polished app
     behaves — instead of every dropdown quietly duplicating the same
     open/close logic.
     ===================================================================== */
  var currentPanel = null; // { id, close }
  function closeCurrentPanel() {
    if (currentPanel) {
      var panel = currentPanel;
      currentPanel = null;
      panel.close();
    }
  }
  function openPanel(id, close) {
    var reopening = currentPanel && currentPanel.id === id;
    closeCurrentPanel();
    if (reopening) return false;
    currentPanel = { id: id, close: close };
    return true;
  }
  document.addEventListener('click', closeCurrentPanel);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeCurrentPanel();
  });

  /* =====================================================================
     Custom date-pill calendar
     Replaces the old hidden <input type="date"> + native showPicker()
     approach. The native picker can't be restyled, so it always looked
     like a plain browser control instead of a ReadPilot component, and
     didn't reliably close itself after a date was picked. This builds a
     small themed dropdown calendar per .date-pill instead, driven by the
     same CSS variables (--card, --green, --border, etc.) as everything
     else, so it follows light/dark mode automatically.
     ===================================================================== */
  function setupDatePills() {
    var MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var WEEKDAYS = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function formatLabel(date){
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    function isSameDay(a, b){
      return !!a && !!b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    }

    document.querySelectorAll('.date-pill').forEach(function (pill, index) {
      if (pill.dataset.dpReady) return;
      pill.dataset.dpReady = 'true';

      var currentDate = new Date();

      var label = document.createElement('span');
      label.className = 'date-pill-label';
      label.textContent = formatLabel(currentDate);
      pill.childNodes.forEach(function (node) {
        if (node.nodeType === Node.TEXT_NODE) node.remove();
      });
      pill.appendChild(label);

      var chevron = document.createElement('i');
      chevron.className = 'bx bx-chevron-down dp-chevron';
      pill.appendChild(chevron);

      pill.classList.add('date-pill-interactive');
      pill.setAttribute('role', 'button');
      pill.setAttribute('tabindex', '0');
      pill.title = 'Choose date';

      var selectedDate = currentDate;
      var hasSelection = true;
      var viewYear = selectedDate.getFullYear();
      var viewMonth = selectedDate.getMonth();

      // Appended directly to <body> (not inside the pill) and positioned with
      // `fixed` coordinates computed from the pill's on-screen location. This
      // is deliberate: cards elsewhere on the page (e.g. the sticky "Students"
      // panel on Struggle Map) create their own stacking context, which lets
      // them paint over a same-page z-indexed dropdown regardless of how high
      // that z-index is. Living at the top level of <body> sidesteps the
      // problem entirely instead of chasing z-index numbers.
      var calendar = document.createElement('div');
      calendar.className = 'dp-calendar';
      document.body.appendChild(calendar);
      calendar.addEventListener('click', function (e) { e.stopPropagation(); });

      function positionCalendar(){
        var rect = pill.getBoundingClientRect();
        var calWidth = calendar.offsetWidth || 290;
        var calHeight = calendar.offsetHeight || 360;

        var left = rect.right - calWidth;
        left = Math.max(12, Math.min(left, window.innerWidth - calWidth - 12));

        var top = rect.bottom + 10;
        if (top + calHeight > window.innerHeight - 12 && rect.top - calHeight - 10 > 12) {
          top = rect.top - calHeight - 10; // flip above the pill if it won't fit below
        }
        calendar.style.left = left + 'px';
        calendar.style.top = top + 'px';
      }

      var panelId = 'date-pill-' + index;

      function onScroll(e){
        if (e && calendar.contains(e.target)) return;
        closeCurrentPanel();
      }
      function closeCalendarUI(){
        calendar.classList.remove('open');
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', positionCalendar);
      }
      function toggleCalendar(){
        var opened = openPanel(panelId, closeCalendarUI);
        if (!opened) return;
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth();
        render();
        calendar.classList.add('open');
        positionCalendar();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionCalendar);
      }
      function choose(date){
        selectedDate = date;
        hasSelection = true;
        label.textContent = formatLabel(date);
        closeCurrentPanel();
        showToast('Viewing ' + formatLabel(date));
      }

      function render(){
        var today = new Date();
        calendar.innerHTML = '';

        var header = document.createElement('div');
        header.className = 'dp-cal-header';

        var prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'dp-cal-nav';
        prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
        prevBtn.setAttribute('aria-label', 'Previous month');

        var title = document.createElement('div');
        title.className = 'dp-cal-title';
        title.textContent = MONTH_NAMES[viewMonth] + ' ' + viewYear;

        var nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'dp-cal-nav';
        nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
        nextBtn.setAttribute('aria-label', 'Next month');

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        calendar.appendChild(header);

        prevBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          viewMonth -= 1;
          if (viewMonth < 0) { viewMonth = 11; viewYear -= 1; }
          render();
        });
        nextBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          viewMonth += 1;
          if (viewMonth > 11) { viewMonth = 0; viewYear += 1; }
          render();
        });

        var weekdaysRow = document.createElement('div');
        weekdaysRow.className = 'dp-cal-weekdays';
        WEEKDAYS.forEach(function (day) {
          var cell = document.createElement('span');
          cell.textContent = day;
          weekdaysRow.appendChild(cell);
        });
        calendar.appendChild(weekdaysRow);

        var grid = document.createElement('div');
        grid.className = 'dp-cal-grid';

        var firstWeekday = new Date(viewYear, viewMonth, 1).getDay();
        var daysInThisMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        var daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

        var cells = [];
        for (var i = firstWeekday - 1; i >= 0; i--) {
          cells.push({ day: daysInPrevMonth - i, muted: true, date: new Date(viewYear, viewMonth - 1, daysInPrevMonth - i) });
        }
        for (var d = 1; d <= daysInThisMonth; d++) {
          cells.push({ day: d, muted: false, date: new Date(viewYear, viewMonth, d) });
        }
        var remaining = 42 - cells.length;
        for (var n = 1; n <= remaining; n++) {
          cells.push({ day: n, muted: true, date: new Date(viewYear, viewMonth + 1, n) });
        }

        cells.forEach(function (cell) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'dp-cal-day';
          if (cell.muted) btn.classList.add('muted');
          if (isSameDay(cell.date, today)) btn.classList.add('today');
          if (hasSelection && isSameDay(cell.date, selectedDate)) btn.classList.add('selected');
          btn.textContent = cell.day;
          btn.addEventListener('click', function (e) {
            e.stopPropagation();
            choose(cell.date);
          });
          grid.appendChild(btn);
        });
        calendar.appendChild(grid);

        var footer = document.createElement('div');
        footer.className = 'dp-cal-footer';

        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'dp-cal-link';
        clearBtn.textContent = 'Clear';

        var todayBtn = document.createElement('button');
        todayBtn.type = 'button';
        todayBtn.className = 'dp-cal-link dp-cal-link-accent';
        todayBtn.textContent = 'Today';

        footer.appendChild(clearBtn);
        footer.appendChild(todayBtn);
        calendar.appendChild(footer);

        clearBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          hasSelection = false;
          label.textContent = 'Choose date';
          closeCurrentPanel();
          showToast('Date cleared');
        });
        todayBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          choose(new Date());
        });
      }

      pill.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleCalendar();
      });
      pill.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggleCalendar();
        } else if (e.key === 'Escape') {
          closeCurrentPanel();
        }
      });
    });
  }

  /* =====================================================================
     Custom <select> dropdowns
     Replaces native <select> controls (the "All Students" and "Sort"
     filters on Students/Sessions) with a themed trigger + dropdown panel,
     built on the exact same pattern as the date-pill calendar and the
     notification bell: appended to <body>, positioned with fixed
     coordinates, and registered with the shared panel manager so only
     one dropdown is ever open at once.

     The original <select> is kept in the DOM (visually hidden, not
     removed) rather than replaced outright. That's deliberate: every
     page's inline script reads `select.value` and listens for `change`
     on the select's own id (e.g. #sortSelect, #studentFilter), so this
     component drives that real select under the hood — flipping its
     selectedIndex and dispatching a `change` event — instead of
     duplicating that state management here. Existing page logic keeps
     working unmodified.

     Scope is intentionally narrow: only .sort-select and .sel, which are
     the topbar filter/sort controls. Modal form <select>s (grade,
     student picker) are left native since they weren't part of this pass.
     ===================================================================== */
  function setupCustomSelects() {
    document.querySelectorAll('select.sort-select, select.sel').forEach(function (select, index) {
      if (select.dataset.csReady) return;
      select.dataset.csReady = 'true';

      var originalClasses = select.className;
      select.classList.add('cs-native');
      select.tabIndex = -1;

      var trigger = document.createElement('button');
      trigger.type = 'button';
      // Reuse the select's own classes (.sort-select / .sel) on the trigger
      // so it automatically inherits the same box/padding/shadow styling
      // already defined for that control, instead of redefining it here.
      trigger.className = (originalClasses + ' cs-trigger').trim();
      trigger.setAttribute('aria-haspopup', 'listbox');
      trigger.setAttribute('aria-expanded', 'false');

      var label = document.createElement('span');
      label.className = 'cs-trigger-label';
      trigger.appendChild(label);

      var chevron = document.createElement('i');
      chevron.className = 'bx bx-chevron-down cs-chevron';
      trigger.appendChild(chevron);

      select.parentNode.insertBefore(trigger, select.nextSibling);

      var panel = document.createElement('div');
      panel.className = 'cs-panel';
      panel.setAttribute('role', 'listbox');
      document.body.appendChild(panel);
      panel.addEventListener('click', function (e) { e.stopPropagation(); });

      function currentLabel(){
        var opt = select.options[select.selectedIndex];
        return opt ? opt.textContent : '';
      }

      function renderOptions(){
        panel.innerHTML = '';
        Array.prototype.forEach.call(select.options, function (opt, i) {
          var item = document.createElement('button');
          item.type = 'button';
          item.className = 'cs-option' + (i === select.selectedIndex ? ' selected' : '');
          item.setAttribute('role', 'option');
          item.setAttribute('aria-selected', i === select.selectedIndex ? 'true' : 'false');

          var text = document.createElement('span');
          text.textContent = opt.textContent;
          var check = document.createElement('i');
          check.className = 'bx bx-check';
          item.appendChild(text);
          item.appendChild(check);

          item.addEventListener('click', function (e) {
            e.stopPropagation();
            if (select.selectedIndex !== i) {
              select.selectedIndex = i;
              select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            label.textContent = currentLabel();
            closeCurrentPanel();
          });
          panel.appendChild(item);
        });
      }

      function positionPanel(){
        var rect = trigger.getBoundingClientRect();
        var panelWidth = Math.max(panel.scrollWidth, rect.width);
        panel.style.width = panelWidth + 'px';
        var panelHeight = panel.offsetHeight || 220;

        var left = rect.right - panelWidth;
        left = Math.max(12, Math.min(left, window.innerWidth - panelWidth - 12));

        var top = rect.bottom + 8;
        if (top + panelHeight > window.innerHeight - 12 && rect.top - panelHeight - 8 > 12) {
          top = rect.top - panelHeight - 8; // flip above the trigger if it won't fit below
        }
        panel.style.left = left + 'px';
        panel.style.top = top + 'px';
      }

      var panelId = 'cs-select-' + index;

      function onScroll(e){
        if (e && panel.contains(e.target)) return;
        closeCurrentPanel();
      }
      function closePanelUI(){
        panel.classList.remove('open');
        trigger.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', positionPanel);
      }
      function togglePanel(){
        var opened = openPanel(panelId, closePanelUI);
        if (!opened) return;
        renderOptions();
        panel.classList.add('open');
        trigger.classList.add('open');
        trigger.setAttribute('aria-expanded', 'true');
        positionPanel();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionPanel);
      }

      label.textContent = currentLabel();
      select.addEventListener('change', function () {
        label.textContent = currentLabel();
      });

      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel();
      });
      trigger.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          togglePanel();
        } else if (e.key === 'Escape') {
          closeCurrentPanel();
        } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          // Quick keyboard cycling without opening the panel, matching
          // how a native <select> responds to arrow keys.
          e.preventDefault();
          var dir = e.key === 'ArrowDown' ? 1 : -1;
          var next = select.selectedIndex + dir;
          if (next < 0) next = 0;
          if (next > select.options.length - 1) next = select.options.length - 1;
          if (next !== select.selectedIndex) {
            select.selectedIndex = next;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            label.textContent = currentLabel();
          }
        }
      });
    });
  }

  /* =====================================================================
     Notification bell dropdown
     Rebuilt on the same pattern as the date-pill calendar: appended to
     <body> and positioned with `fixed` coordinates from the bell's own
     rect, so it can't be painted over by a sticky/positioned card
     elsewhere on the page, and it shares the calendar's visual language
     (rounded card, icon bubbles, hover rows) instead of being a plain
     box of text.
     ===================================================================== */
  function setupNotifications() {
    document.querySelectorAll('.bell').forEach(function (bell, index) {
      if (bell.dataset.dpReady) return;
      bell.dataset.dpReady = 'true';
      bell.setAttribute('role', 'button');
      bell.setAttribute('tabindex', '0');
      bell.title = 'Notifications';

      var panel = document.createElement('div');
      panel.className = 'bell-panel';
      document.body.appendChild(panel);
      panel.addEventListener('click', function (e) { e.stopPropagation(); });

      function renderPanel(){
        panel.innerHTML =
          '<div class="bell-panel-head">' +
            '<span class="bell-panel-title">Notifications</span>' +
          '</div>' +
          '<div class="bell-item">' +
            '<span class="bell-item-icon"><i class="bx bx-check-double"></i></span>' +
            '<div>' +
              '<div>You are all caught up</div>' +
              '<div class="sub">No new notifications</div>' +
            '</div>' +
          '</div>';
      }

      function positionPanel(){
        var rect = bell.getBoundingClientRect();
        var panelWidth = panel.offsetWidth || 280;
        var panelHeight = panel.offsetHeight || 140;

        var left = rect.right - panelWidth;
        left = Math.max(12, Math.min(left, window.innerWidth - panelWidth - 12));

        var top = rect.bottom + 10;
        if (top + panelHeight > window.innerHeight - 12 && rect.top - panelHeight - 10 > 12) {
          top = rect.top - panelHeight - 10;
        }
        panel.style.left = left + 'px';
        panel.style.top = top + 'px';
      }

      var panelId = 'bell-' + index;
      function onScroll(){ closeCurrentPanel(); }
      function closePanelUI(){
        panel.classList.remove('open');
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', positionPanel);
      }
      function togglePanel(){
        var opened = openPanel(panelId, closePanelUI);
        if (!opened) return;
        renderPanel();
        panel.classList.add('open');
        positionPanel();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionPanel);
        var badge = bell.querySelector('.badge');
        if (badge) badge.textContent = '0';
      }

      bell.addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel();
      });
      bell.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          togglePanel();
        } else if (e.key === 'Escape') {
          closeCurrentPanel();
        }
      });
    });
  }

  function setupDashboardActions() {
    var newSession = document.querySelector('.main .btn-new:not(#openRecModal):not(#addResourceBtn)');
    if (newSession && document.title.indexOf('Dashboard') !== -1) {
      newSession.addEventListener('click', function () { window.location.href = 'sessions.php'; });
    }
    var viewAll = document.querySelector('.view-all');
    if (viewAll) {
      viewAll.href = 'sessions.php';
      viewAll.addEventListener('click', function () { window.location.href = 'sessions.php'; });
    }
    if (document.title.indexOf('Dashboard') !== -1) {
      document.querySelectorAll('.session-row').forEach(function (row) {
        row.addEventListener('click', function () { window.location.href = 'sessions.php'; });
      });
    }
    var loadMore = document.querySelector('.main .load-more');
    if (loadMore && document.title.indexOf('Dashboard') !== -1) {
      loadMore.addEventListener('click', function () {
        window.location.href = 'sessions.php';
      });
    }
  }

  function setupSearch() {
    document.querySelectorAll('.main .search').forEach(function (search) {
      if (search.querySelector('input')) return;
      var text = search.textContent.trim();
      var icon = search.querySelector('svg');
      var input = document.createElement('input');
      input.type = 'search';
      input.placeholder = text || 'Search';
      input.setAttribute('aria-label', input.placeholder);
      Array.prototype.slice.call(search.childNodes).forEach(function (node) {
        if (node.nodeType === Node.TEXT_NODE) node.remove();
      });
      search.appendChild(input);

      var cards = document.querySelectorAll('.rec-card');
      if (cards.length) {
        input.addEventListener('input', function () {
          var query = this.value.trim().toLowerCase();
          var visible = 0;
          cards.forEach(function (card) {
            var match = !query || card.textContent.toLowerCase().indexOf(query) !== -1;
            card.classList.toggle('is-hidden', !match);
            if (match) visible++;
          });
          var count = document.getElementById('resultsCount');
          if (count) count.textContent = visible + (visible === 1 ? ' recommendation' : ' recommendations');
        });
      } else {
        input.addEventListener('keydown', function (event) {
          if (event.key !== 'Enter' || !this.value.trim()) return;
          window.location.href = 'students.php?search=' + encodeURIComponent(this.value.trim());
        });
      }
    });
  }

  function setupRecommendationActions() {
    document.querySelectorAll('.rec-card').forEach(function (card) {
      var dismiss = Array.prototype.slice.call(card.querySelectorAll('.rec-actions button'))
        .find(function (button) { return button.textContent.trim() === 'Dismiss'; });
      var edit = Array.prototype.slice.call(card.querySelectorAll('.rec-actions button'))
        .find(function (button) { return button.textContent.trim() === 'Edit'; });
      if (dismiss) dismiss.addEventListener('click', function () {
        card.remove();
        showToast('Recommendation dismissed');
      });
      if (edit) edit.addEventListener('click', function () {
        var open = document.getElementById('openRecModal');
        if (open) open.click();
        showToast('Edit the recommendation details, then save');
      });
    });
  }

  function setupSupport() {
    document.querySelectorAll('button').forEach(function (button) {
      if (button.textContent.trim() !== 'Contact support') return;
      button.addEventListener('click', function () {
        window.location.href = 'mailto:support@readpilot.app?subject=ReadPilot%20support';
      });
    });
  }

  function setupSidebarPlanes() {
    document.querySelectorAll('.pixel-plane').forEach(function (plane) {
      var duration = parseFloat(getComputedStyle(plane).getPropertyValue('--dur')) || 14;
      var initialDelay = parseFloat(getComputedStyle(plane).getPropertyValue('--delay')) || 0;
      var elapsed = Date.now() / 1000;
      var phase = ((elapsed - initialDelay) % duration + duration) % duration;
      plane.style.setProperty('--plane-phase', '-' + phase + 's');
      plane.classList.add('is-synced');
    });
  }

  function setupSettingsPersistence() {
    if (!document.getElementById('saveSettingsBtn')) return;
    var fields = Array.prototype.slice.call(document.querySelectorAll('.settings-card input, .settings-card textarea'))
      .filter(function (field) { return field.type !== 'file' && field.type !== 'password'; });
    var storageKey = 'readpilot-settings';

    function restore() {
      var saved;
      try { saved = JSON.parse(localStorage.getItem(storageKey) || '{}'); } catch (error) { saved = {}; }
      fields.forEach(function (field, index) {
        if (saved[index] === undefined) return;
        if (field.type === 'checkbox') field.checked = saved[index];
        else field.value = saved[index];
      });
      var name = document.getElementById('inputName');
      var sidebarName = document.getElementById('sidebarName');
      if (name && sidebarName) sidebarName.textContent = name.value || 'Ms. Hernandez';
    }

    function save() {
      var values = fields.map(function (field) {
        return field.type === 'checkbox' ? field.checked : field.value;
      });
      try { localStorage.setItem(storageKey, JSON.stringify(values)); } catch (error) {}
    }

    restore();
    document.getElementById('saveSettingsBtn').addEventListener('click', save);
    document.getElementById('discardBtn').addEventListener('click', restore);
  }

  function setupLogoutLinks() {
    document.querySelectorAll('a[href="logout.php"]').forEach(function (link) {
      if (link.dataset.logoutConfirmReady) return;
      link.dataset.logoutConfirmReady = 'true';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    setupSidebarPlanes();
    setupDatePills();
    setupCustomSelects();
    setupNotifications();
    setupDashboardActions();
    setupSearch();
    setupRecommendationActions();
    setupSupport();
    setupSettingsPersistence();
    setupLogoutLinks();
  });
}());

// page loader//
(function () {
  var loader = document.getElementById('pageLoader');
  if (!loader) return;
  var MIN_MS = 1800; // how long the loader stays visible, in milliseconds
  var start = Date.now();

  function hideLoader() {
    var wait = Math.max(0, MIN_MS - (Date.now() - start));
    setTimeout(function () {
      loader.classList.add('loader-hide');
      setTimeout(function () { loader.remove(); }, 500);
    }, wait);
  }

  if (document.readyState === 'complete') hideLoader();
  else window.addEventListener('load', hideLoader);
})();