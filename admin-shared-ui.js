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
     Shared floating-panel manager (identical contract to the teacher
     dashboard's shared-ui.js): only one dropdown open at a time.
     ===================================================================== */
  var currentPanel = null;
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
     Date-pill calendar (unchanged from the teacher dashboard)
     ===================================================================== */
  function setupDatePills() {
    var MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var WEEKDAYS = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    function formatLabel(date){ return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); }
    function isSameDay(a, b){ return !!a && !!b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); }

    document.querySelectorAll('.date-pill').forEach(function (pill, index) {
      if (pill.dataset.dpReady) return;
      pill.dataset.dpReady = 'true';

      var currentDate = new Date();
      var label = document.createElement('span');
      label.className = 'date-pill-label';
      label.textContent = formatLabel(currentDate);
      pill.childNodes.forEach(function (node) { if (node.nodeType === Node.TEXT_NODE) node.remove(); });
      pill.appendChild(label);

      var chevron = document.createElement('i');
      chevron.className = 'bx bx-chevron-down dp-chevron';
      pill.appendChild(chevron);

      pill.classList.add('date-pill-interactive');
      pill.setAttribute('role', 'button');
      pill.setAttribute('tabindex', '0');
      pill.title = 'Choose date';

      var selectedDate = currentDate, hasSelection = true;
      var viewYear = selectedDate.getFullYear(), viewMonth = selectedDate.getMonth();

      var calendar = document.createElement('div');
      calendar.className = 'dp-calendar';
      document.body.appendChild(calendar);
      calendar.addEventListener('click', function (e) { e.stopPropagation(); });

      function positionCalendar(){
        var rect = pill.getBoundingClientRect();
        var calWidth = calendar.offsetWidth || 290, calHeight = calendar.offsetHeight || 360;
        var left = Math.max(12, Math.min(rect.right - calWidth, window.innerWidth - calWidth - 12));
        var top = rect.bottom + 10;
        if (top + calHeight > window.innerHeight - 12 && rect.top - calHeight - 10 > 12) top = rect.top - calHeight - 10;
        calendar.style.left = left + 'px';
        calendar.style.top = top + 'px';
      }

      var panelId = 'date-pill-' + index;
      function onScroll(e){ if (e && calendar.contains(e.target)) return; closeCurrentPanel(); }
      function closeCalendarUI(){ calendar.classList.remove('open'); window.removeEventListener('scroll', onScroll, true); window.removeEventListener('resize', positionCalendar); }
      function toggleCalendar(){
        var opened = openPanel(panelId, closeCalendarUI);
        if (!opened) return;
        viewYear = selectedDate.getFullYear(); viewMonth = selectedDate.getMonth();
        render(); calendar.classList.add('open'); positionCalendar();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionCalendar);
      }
      function choose(date){
        selectedDate = date; hasSelection = true; label.textContent = formatLabel(date);
        closeCurrentPanel(); showToast('Viewing ' + formatLabel(date));
      }

      function render(){
        var today = new Date();
        calendar.innerHTML = '';
        var header = document.createElement('div'); header.className = 'dp-cal-header';
        var prevBtn = document.createElement('button'); prevBtn.type='button'; prevBtn.className='dp-cal-nav'; prevBtn.innerHTML='<i class="bx bx-chevron-left"></i>'; prevBtn.setAttribute('aria-label','Previous month');
        var title = document.createElement('div'); title.className='dp-cal-title'; title.textContent = MONTH_NAMES[viewMonth] + ' ' + viewYear;
        var nextBtn = document.createElement('button'); nextBtn.type='button'; nextBtn.className='dp-cal-nav'; nextBtn.innerHTML='<i class="bx bx-chevron-right"></i>'; nextBtn.setAttribute('aria-label','Next month');
        header.appendChild(prevBtn); header.appendChild(title); header.appendChild(nextBtn); calendar.appendChild(header);
        prevBtn.addEventListener('click', function (e) { e.stopPropagation(); viewMonth -= 1; if (viewMonth < 0) { viewMonth = 11; viewYear -= 1; } render(); });
        nextBtn.addEventListener('click', function (e) { e.stopPropagation(); viewMonth += 1; if (viewMonth > 11) { viewMonth = 0; viewYear += 1; } render(); });

        var weekdaysRow = document.createElement('div'); weekdaysRow.className='dp-cal-weekdays';
        WEEKDAYS.forEach(function (day) { var cell = document.createElement('span'); cell.textContent = day; weekdaysRow.appendChild(cell); });
        calendar.appendChild(weekdaysRow);

        var grid = document.createElement('div'); grid.className = 'dp-cal-grid';
        var firstWeekday = new Date(viewYear, viewMonth, 1).getDay();
        var daysInThisMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        var daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();
        var cells = [];
        for (var i = firstWeekday - 1; i >= 0; i--) cells.push({ day: daysInPrevMonth - i, muted: true, date: new Date(viewYear, viewMonth - 1, daysInPrevMonth - i) });
        for (var d = 1; d <= daysInThisMonth; d++) cells.push({ day: d, muted: false, date: new Date(viewYear, viewMonth, d) });
        var remaining = 42 - cells.length;
        for (var n = 1; n <= remaining; n++) cells.push({ day: n, muted: true, date: new Date(viewYear, viewMonth + 1, n) });

        cells.forEach(function (cell) {
          var btn = document.createElement('button'); btn.type='button'; btn.className='dp-cal-day';
          if (cell.muted) btn.classList.add('muted');
          if (isSameDay(cell.date, today)) btn.classList.add('today');
          if (hasSelection && isSameDay(cell.date, selectedDate)) btn.classList.add('selected');
          btn.textContent = cell.day;
          btn.addEventListener('click', function (e) { e.stopPropagation(); choose(cell.date); });
          grid.appendChild(btn);
        });
        calendar.appendChild(grid);

        var footer = document.createElement('div'); footer.className='dp-cal-footer';
        var clearBtn = document.createElement('button'); clearBtn.type='button'; clearBtn.className='dp-cal-link'; clearBtn.textContent='Clear';
        var todayBtn = document.createElement('button'); todayBtn.type='button'; todayBtn.className='dp-cal-link dp-cal-link-accent'; todayBtn.textContent='Today';
        footer.appendChild(clearBtn); footer.appendChild(todayBtn); calendar.appendChild(footer);
        clearBtn.addEventListener('click', function (e) { e.stopPropagation(); hasSelection=false; label.textContent='Choose date'; closeCurrentPanel(); showToast('Date cleared'); });
        todayBtn.addEventListener('click', function (e) { e.stopPropagation(); choose(new Date()); });
      }

      pill.addEventListener('click', function (e) { e.stopPropagation(); toggleCalendar(); });
      pill.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleCalendar(); }
        else if (e.key === 'Escape') closeCurrentPanel();
      });
    });
  }

  /* =====================================================================
     Custom <select> dropdowns (unchanged pattern)
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
      trigger.className = (originalClasses + ' cs-trigger').trim();
      trigger.setAttribute('aria-haspopup', 'listbox');
      trigger.setAttribute('aria-expanded', 'false');
      var label = document.createElement('span'); label.className='cs-trigger-label'; trigger.appendChild(label);
      var chevron = document.createElement('i'); chevron.className='bx bx-chevron-down cs-chevron'; trigger.appendChild(chevron);
      select.parentNode.insertBefore(trigger, select.nextSibling);

      var panel = document.createElement('div'); panel.className='cs-panel'; panel.setAttribute('role','listbox');
      document.body.appendChild(panel);
      panel.addEventListener('click', function (e) { e.stopPropagation(); });

      function currentLabel(){ var opt = select.options[select.selectedIndex]; return opt ? opt.textContent : ''; }
      function renderOptions(){
        panel.innerHTML = '';
        Array.prototype.forEach.call(select.options, function (opt, i) {
          var item = document.createElement('button'); item.type='button';
          item.className = 'cs-option' + (i === select.selectedIndex ? ' selected' : '');
          item.setAttribute('role','option'); item.setAttribute('aria-selected', i === select.selectedIndex ? 'true' : 'false');
          var text = document.createElement('span'); text.textContent = opt.textContent;
          var check = document.createElement('i'); check.className='bx bx-check';
          item.appendChild(text); item.appendChild(check);
          item.addEventListener('click', function (e) {
            e.stopPropagation();
            if (select.selectedIndex !== i) { select.selectedIndex = i; select.dispatchEvent(new Event('change', { bubbles: true })); }
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
        var left = Math.max(12, Math.min(rect.right - panelWidth, window.innerWidth - panelWidth - 12));
        var top = rect.bottom + 8;
        if (top + panelHeight > window.innerHeight - 12 && rect.top - panelHeight - 8 > 12) top = rect.top - panelHeight - 8;
        panel.style.left = left + 'px'; panel.style.top = top + 'px';
      }
      var panelId = 'cs-select-' + index;
      function onScroll(e){ if (e && panel.contains(e.target)) return; closeCurrentPanel(); }
      function closePanelUI(){ panel.classList.remove('open'); trigger.classList.remove('open'); trigger.setAttribute('aria-expanded','false'); window.removeEventListener('scroll', onScroll, true); window.removeEventListener('resize', positionPanel); }
      function togglePanel(){
        var opened = openPanel(panelId, closePanelUI);
        if (!opened) return;
        renderOptions(); panel.classList.add('open'); trigger.classList.add('open'); trigger.setAttribute('aria-expanded','true');
        positionPanel();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionPanel);
      }
      label.textContent = currentLabel();
      select.addEventListener('change', function () { label.textContent = currentLabel(); });
      trigger.addEventListener('click', function (e) { e.stopPropagation(); togglePanel(); });
      trigger.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePanel(); }
        else if (e.key === 'Escape') closeCurrentPanel();
        else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          var dir = e.key === 'ArrowDown' ? 1 : -1;
          var next = select.selectedIndex + dir;
          if (next < 0) next = 0;
          if (next > select.options.length - 1) next = select.options.length - 1;
          if (next !== select.selectedIndex) { select.selectedIndex = next; select.dispatchEvent(new Event('change', { bubbles: true })); label.textContent = currentLabel(); }
        }
      });
    });
  }

  /* =====================================================================
     Notification bell (unchanged pattern)
     ===================================================================== */
  function setupNotifications() {
    document.querySelectorAll('.bell').forEach(function (bell, index) {
      if (bell.dataset.dpReady || bell.dataset.notificationsManaged === 'true') return;
      bell.dataset.dpReady = 'true';
      bell.setAttribute('role', 'button'); bell.setAttribute('tabindex', '0'); bell.title = 'Notifications';

      var items = [];
      if (bell.dataset.notifications) {
        try {
          var parsedItems = JSON.parse(bell.dataset.notifications);
          if (Array.isArray(parsedItems)) items = parsedItems;
        } catch (error) {
          console.error('Unable to read notification data:', error);
        }
      }
      var badge = bell.querySelector('.badge');
      if (badge) {
        var notificationCount = Number(bell.dataset.notificationCount);
        if (!Number.isFinite(notificationCount) || notificationCount < 0) notificationCount = items.length;
        if (notificationCount > 0) {
          badge.textContent = notificationCount > 9 ? '9+' : String(notificationCount);
          badge.style.display = '';
        } else {
          badge.style.display = 'none';
        }
      }

      var panel = document.createElement('div'); panel.className='bell-panel';
      document.body.appendChild(panel);
      panel.addEventListener('click', function (e) { e.stopPropagation(); });

      function renderPanel(){
        if (!items.length) {
          panel.innerHTML =
            '<div class="bell-panel-head"><span class="bell-panel-title">Notifications</span></div>' +
            '<div class="bell-item"><span class="bell-item-icon"><i class="bx bx-check-double"></i></span>' +
            '<div><div>You are all caught up</div><div class="sub">No new notifications</div></div></div>';
          return;
        }
        var html = '<div class="bell-panel-head"><span class="bell-panel-title">Notifications</span></div>';
        panel.innerHTML = html;
        items.forEach(function (item) {
          var row = document.createElement('div');
          row.className = 'bell-item';
          row.textContent = item.title || '';
          var sub = document.createElement('div');
          sub.className = 'sub';
          sub.textContent = item.sub || '';
          row.appendChild(sub);
          panel.appendChild(row);
        });
      }
      function positionPanel(){
        var rect = bell.getBoundingClientRect();
        var panelWidth = panel.offsetWidth || 280, panelHeight = panel.offsetHeight || 140;
        var left = Math.max(12, Math.min(rect.right - panelWidth, window.innerWidth - panelWidth - 12));
        var top = rect.bottom + 10;
        if (top + panelHeight > window.innerHeight - 12 && rect.top - panelHeight - 10 > 12) top = rect.top - panelHeight - 10;
        panel.style.left = left + 'px'; panel.style.top = top + 'px';
      }
      var panelId = 'bell-' + index;
      function onScroll(){ closeCurrentPanel(); }
      function closePanelUI(){ panel.classList.remove('open'); window.removeEventListener('scroll', onScroll, true); window.removeEventListener('resize', positionPanel); }
      function togglePanel(){
        var opened = openPanel(panelId, closePanelUI);
        if (!opened) return;
        renderPanel(); panel.classList.add('open'); positionPanel();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', positionPanel);
        if (badge) badge.style.display = 'none';
      }
      bell.addEventListener('click', function (e) { e.stopPropagation(); togglePanel(); });
      bell.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePanel(); }
        else if (e.key === 'Escape') closeCurrentPanel();
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

  function setupSearch() {
    // Turns the decorative topbar search bar into a real input. Page-specific
    // filtering (User Management's table, Audit Log's list) is wired up by
    // that page's own setup function once this input exists; there's no
    // shared destination page to jump to on Enter in the admin console, so
    // unlike the teacher dashboard this never navigates away on its own.
    document.querySelectorAll('.main .search').forEach(function (search) {
      if (search.querySelector('input')) return;
      var text = search.textContent.trim();
      var input = document.createElement('input');
      input.type = 'search'; input.placeholder = text || 'Search'; input.setAttribute('aria-label', input.placeholder);
      Array.prototype.slice.call(search.childNodes).forEach(function (node) { if (node.nodeType === Node.TEXT_NODE) node.remove(); });
      search.appendChild(input);
    });
  }

  /* =====================================================================
     Sidebar logout (shared across every admin page)
     ===================================================================== */
  function setupLogout() {
    var logoutModal = document.getElementById('logoutModal');
    var openBtn = document.getElementById('sidebarLogoutBtn');
    if (!logoutModal || !openBtn) return;
    function open(){ logoutModal.classList.add('show'); }
    function close(){ logoutModal.classList.remove('show'); }
    openBtn.addEventListener('click', open);
    var cancelBtn = document.getElementById('logoutCancelBtn');
    if (cancelBtn) cancelBtn.addEventListener('click', close);
    logoutModal.addEventListener('click', function (e) { if (e.target === logoutModal) close(); });
    var confirmBtn = document.getElementById('logoutConfirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', function () {
      try {
        sessionStorage.clear();
        localStorage.removeItem('readpilot-admin-auth-token');
        localStorage.removeItem('readpilot-admin-user');
      } catch (e) {}
      close();
      setTimeout(function(){ window.location.href = 'logout.php'; }, 700);
    });
  }

  function setupLogoutLinks() {
    document.querySelectorAll('a[href="logout.php"]').forEach(function (link) {
      if (link.dataset.logoutConfirmReady) return;
      link.dataset.logoutConfirmReady = 'true';
    });
  }

  /* =====================================================================
     Dashboard recent activity
     ===================================================================== */
  function setupDashboardActions() {
    var loadMore = document.querySelector('.main .load-more');
    if (loadMore && document.title.indexOf('Dashboard') !== -1) {
      var extraActivity = Array.prototype.slice.call(document.querySelectorAll('.dashboard-extra-activity'));
      loadMore.addEventListener('click', function () {
        extraActivity.forEach(function (row) { row.style.display = ''; });
        loadMore.style.display = 'none';
      });
    }
    var viewAll = document.querySelector('.view-all');
    if (viewAll && document.title.indexOf('Dashboard') !== -1) {
      viewAll.href = 'audit-trail-admin.php';
      viewAll.addEventListener('click', function () { window.location.href = 'audit-trail-admin.php'; });
    }
  }

  /* =====================================================================
     User Management page
     ===================================================================== */
  function setupUserManagement() {
    var page = document.getElementById('userMgmtPage');
    if (!page) return;

    var rows = Array.prototype.slice.call(page.querySelectorAll('tbody tr'));
    var tabs = Array.prototype.slice.call(page.querySelectorAll('.tabs .tab'));
    var searchInput = null;
    var resultsCount = document.getElementById('resultsCount');
    var activeFilter = 'all';

    function applyFilters() {
      var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
      var visible = 0;
      rows.forEach(function (row) {
        var role = row.dataset.role;
        var status = row.dataset.status;
        var matchesTab =
          activeFilter === 'all' ? true :
          activeFilter === 'teachers' ? role === 'teacher' :
          activeFilter === 'admins' ? role === 'admin' :
          activeFilter === 'deactivated' ? status === 'inactive' : true;
        var matchesQuery = !query || row.textContent.toLowerCase().indexOf(query) !== -1;
        var show = matchesTab && matchesQuery;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      if (resultsCount) resultsCount.textContent = visible + (visible === 1 ? ' user' : ' users');
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        activeFilter = tab.dataset.filter || 'all';
        applyFilters();
      });
    });

    // Hook the generic search box built by setupSearch()
    setTimeout(function () {
      searchInput = page.querySelector('.search input');
      if (searchInput) searchInput.addEventListener('input', applyFilters);
      applyFilters();
    }, 0);

    // ---- Add / Edit user modal ----
    var overlay = document.getElementById('userModalOverlay');
    var form = document.getElementById('userForm');
    var modalTitle = document.getElementById('userModalTitle');
    var modalSub = document.getElementById('userModalSub');
    var fName = document.getElementById('userName');
    var fEmail = document.getElementById('userEmail');
    var fPassword = document.getElementById('userPassword');
    var fRole = document.getElementById('userRole');
    var fGrade = document.getElementById('userGrade');
    var fGradeRow = document.getElementById('userGradeRow');
    var fStatusToggle = document.getElementById('userStatusToggle');
    var deleteBtn = document.getElementById('userDeleteBtn');
    var editingRow = null;

    function toggleGradeRowVisibility() {
      if (!fGradeRow || !fRole) return;
      fGradeRow.style.display = fRole.value === 'Teacher' ? '' : 'none';
    }
    if (fRole) fRole.addEventListener('change', toggleGradeRowVisibility);

    function openModal(row) {
      editingRow = row || null;
      if (row) {
        modalTitle.textContent = 'Edit user';
        modalSub.textContent = 'Update account details for this user.';
        fName.value = row.dataset.name;
        fEmail.value = row.dataset.email;
        if (fPassword) fPassword.value = '';
        fRole.value = row.dataset.role === 'admin' ? 'Admin' : 'Teacher';
        if (fGrade) fGrade.value = row.dataset.grade || 'Grade 3';
        if (fStatusToggle) fStatusToggle.checked = row.dataset.status === 'active';
        if (deleteBtn) deleteBtn.style.display = '';
      } else {
        modalTitle.textContent = 'Add user';
        modalSub.textContent = 'Create a new teacher or admin account.';
        form.reset();
        fRole.value = 'Teacher';
        if (fStatusToggle) fStatusToggle.checked = true;
        if (deleteBtn) deleteBtn.style.display = 'none';
        if (fPassword) fPassword.required = true;
      }
      if (row && fPassword) fPassword.required = false;
      toggleGradeRowVisibility();
      overlay.classList.add('open');
    }
    function closeModal() { overlay.classList.remove('open'); editingRow = null; }

    var addBtn = document.getElementById('addUserBtn');
    if (addBtn) addBtn.addEventListener('click', function () { openModal(null); });
    document.querySelectorAll('#userModalOverlay .modal-close, #userCancelBtn').forEach(function (btn) {
      btn.addEventListener('click', closeModal);
    });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });

    page.querySelectorAll('.icon-btn.edit').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        openModal(btn.closest('tr'));
      });
    });

    if (form) form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = fName.value.trim();
      var email = fEmail.value.trim();
      var role = fRole.value;
      var grade = fGrade ? fGrade.value : '';
      var active = fStatusToggle ? fStatusToggle.checked : true;
      if (!name || !email) return;

      if (editingRow) {
        editingRow.dataset.name = name;
        editingRow.dataset.email = email;
        editingRow.dataset.role = role.toLowerCase();
        editingRow.dataset.grade = grade;
        editingRow.dataset.status = active ? 'active' : 'inactive';
        renderRow(editingRow);
        attachRowActions(editingRow);
        showToast('User updated');
      } else {
        if (!fPassword || fPassword.value.length < 8) {
          showToast('Use a password with at least 8 characters');
          return;
        }
        var formData = new URLSearchParams({
          name: name,
          email: email,
          password: fPassword.value,
          role: role,
          grade_level: grade,
          status: active ? 'active' : 'inactive'
        });
        fetch('admin-api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData })
          .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok) throw new Error(result.data.error || 'Unable to create account');
            var row = document.createElement('tr');
            row.dataset.name = name;
            row.dataset.email = email;
            row.dataset.role = role.toLowerCase();
            row.dataset.grade = grade;
            row.dataset.status = active ? 'active' : 'inactive';
            row.dataset.lastLogin = 'Never';
            page.querySelector('tbody').prepend(row);
            rows.unshift(row);
            renderRow(row);
            attachRowActions(row);
            applyFilters();
            closeModal();
            showToast('User account created');
          })
          .catch(function (error) { showToast(error.message); });
        return;
      }
      applyFilters();
      closeModal();
    });

    function initials(name) {
      return name.split(' ').filter(Boolean).map(function (p) { return p[0]; }).slice(0, 2).join('').toUpperCase();
    }
    var avatarColors = ['#4c63d2', '#8b6bd1', '#dd9636', '#3f95ac', '#d1495b'];
    function colorFor(name) {
      var sum = 0;
      for (var i = 0; i < name.length; i++) sum += name.charCodeAt(i);
      return avatarColors[sum % avatarColors.length];
    }

    function renderRow(row) {
      var name = row.dataset.name, email = row.dataset.email, role = row.dataset.role;
      var grade = row.dataset.grade, status = row.dataset.status, lastLogin = row.dataset.lastLogin || 'Never';
      var roleLabel = role === 'admin' ? 'Admin' : 'Teacher';
      var statusLabel = status === 'active' ? 'Active' : 'Deactivated';
      var gradeCell = role === 'teacher' ? (grade || '—') : '<span class="cell-muted">—</span>';
      row.innerHTML =
        '<td><div class="cell-user">' +
          '<div class="u-avatar" style="background:' + colorFor(name) + '">' + initials(name) + '</div>' +
          '<div><div class="u-name">' + name + '</div><div class="u-email">' + email + '</div></div>' +
        '</div></td>' +
        '<td><span class="role-badge ' + role + '">' + roleLabel + '</span></td>' +
        '<td>' + gradeCell + '</td>' +
        '<td><span class="status-pill ' + (status === 'active' ? 'active' : 'inactive') + '"><span class="dot"></span>' + statusLabel + '</span></td>' +
        '<td class="cell-muted">' + lastLogin + '</td>' +
        '<td><div class="row-actions">' +
          '<button class="icon-btn edit" title="Edit user" aria-label="Edit user"><i class="bx bx-edit-alt"></i></button>' +
          '<button class="icon-btn ' + (status === 'active' ? 'danger' : 'success') + ' toggle-status" title="' + (status === 'active' ? 'Deactivate user' : 'Activate user') + '" aria-label="' + (status === 'active' ? 'Deactivate user' : 'Activate user') + '"><i class="bx ' + (status === 'active' ? 'bx-block' : 'bx-check-circle') + '"></i></button>' +
        '</div></td>';
    }

    // ---- Deactivate / activate confirm ----
    var confirmOverlay = document.getElementById('statusConfirmOverlay');
    var confirmText = document.getElementById('statusConfirmText');
    var confirmBtn = document.getElementById('statusConfirmBtn');
    var confirmCancel = document.getElementById('statusConfirmCancel');
    var pendingRow = null;

    function openStatusConfirm(row) {
      pendingRow = row;
      var name = row.dataset.name;
      var willDeactivate = row.dataset.status === 'active';
      confirmText.textContent = willDeactivate
        ? name + ' will lose access to ReadPilot immediately. You can reactivate this account at any time.'
        : name + ' will regain access to ReadPilot with their existing login.';
      confirmBtn.textContent = willDeactivate ? 'Deactivate' : 'Activate';
      confirmBtn.className = willDeactivate ? 'btn-solid btn-danger-solid' : 'btn-solid';
      confirmOverlay.classList.add('show');
    }
    function closeStatusConfirm() { confirmOverlay.classList.remove('show'); pendingRow = null; }
    if (confirmCancel) confirmCancel.addEventListener('click', closeStatusConfirm);
    if (confirmOverlay) confirmOverlay.addEventListener('click', function (e) { if (e.target === confirmOverlay) closeStatusConfirm(); });
    if (confirmBtn) confirmBtn.addEventListener('click', function () {
      if (!pendingRow) return;
      var willDeactivate = pendingRow.dataset.status === 'active';
      pendingRow.dataset.status = willDeactivate ? 'inactive' : 'active';
      renderRow(pendingRow);
      attachRowActions(pendingRow);
      applyFilters();
      showToast(willDeactivate ? 'User deactivated' : 'User activated');
      closeStatusConfirm();
    });

    if (deleteBtn) deleteBtn.addEventListener('click', function () {
      if (!editingRow) return;
      closeModal();
      openStatusConfirm(editingRow);
    });

    function attachRowActions(row) {
      var editBtn = row.querySelector('.icon-btn.edit');
      if (editBtn) editBtn.addEventListener('click', function (e) { e.stopPropagation(); openModal(row); });
      var toggleBtn = row.querySelector('.toggle-status');
      if (toggleBtn) toggleBtn.addEventListener('click', function (e) { e.stopPropagation(); openStatusConfirm(row); });
    }
    rows.forEach(function (row) {
      var toggleBtn = row.querySelector('.toggle-status');
      if (toggleBtn) toggleBtn.addEventListener('click', function (e) { e.stopPropagation(); openStatusConfirm(row); });
    });
  }

  /* =====================================================================
     Grades & Sections page
     ===================================================================== */
  function setupGradesPage() {
    var page = document.getElementById('gradesPage');
    if (!page) return;

    var gradeCards = Array.prototype.slice.call(page.querySelectorAll('.grade-card'));
    var sectionTabsWrap = document.getElementById('sectionTabs');
    var rosterBody = document.getElementById('rosterBody');
    var rosterWrap = document.getElementById('rosterWrap');
    var lockedWrap = document.getElementById('rosterLocked');
    var rosterGradeLabel = document.getElementById('rosterGradeLabel');

    function selectGrade(card) {
      gradeCards.forEach(function (c) { c.classList.remove('active'); });
      card.classList.add('active');
      var grade = card.dataset.grade;
      var hasRoster = card.dataset.hasRoster === 'true';
      rosterGradeLabel.textContent = grade;

      if (!hasRoster) {
        rosterWrap.style.display = 'none';
        sectionTabsWrap.style.display = 'none';
        lockedWrap.style.display = '';
        return;
      }
      lockedWrap.style.display = 'none';
      rosterWrap.style.display = '';
      sectionTabsWrap.style.display = '';
      var sections = JSON.parse(card.dataset.sections || '[]');
      renderSectionTabs(sections);
    }

    function renderSectionTabs(sections) {
      sectionTabsWrap.innerHTML = '';
      var allTab = document.createElement('button');
      allTab.type = 'button';
      allTab.className = 'section-tab active';
      allTab.textContent = 'All sections';
      allTab.addEventListener('click', function () { setActiveTab(allTab); filterRoster('all'); });
      sectionTabsWrap.appendChild(allTab);
      sections.forEach(function (sec) {
        var tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'section-tab';
        tab.textContent = 'Section ' + sec;
        tab.addEventListener('click', function () { setActiveTab(tab); filterRoster(sec); });
        sectionTabsWrap.appendChild(tab);
      });
    }
    function setActiveTab(tab) {
      Array.prototype.forEach.call(sectionTabsWrap.children, function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
    }
    function filterRoster(section) {
      Array.prototype.slice.call(rosterBody.querySelectorAll('tr')).forEach(function (row) {
        row.style.display = (section === 'all' || row.dataset.section === section) ? '' : 'none';
      });
    }

    gradeCards.forEach(function (card) {
      card.addEventListener('click', function () { selectGrade(card); });
      card.setAttribute('role', 'button');
      card.setAttribute('tabindex', '0');
      card.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectGrade(card); } });
    });

    var defaultCard = page.querySelector('.grade-card[data-has-roster="true"]') || gradeCards[0];
    if (defaultCard) selectGrade(defaultCard);
  }

  /* =====================================================================
     Audit Log page
     ===================================================================== */
  function setupAuditLog() {
    var page = document.getElementById('auditPage');
    if (!page) return;

    var tabs = Array.prototype.slice.call(page.querySelectorAll('.tabs .tab'));
    var entries = Array.prototype.slice.call(page.querySelectorAll('.audit-row'));
    var resultsCount = document.getElementById('auditResultsCount');
    var loadMoreBtn = page.querySelector('.load-more');
    var VISIBLE_STEP = 6;
    var visibleCount = VISIBLE_STEP;
    var activeFilter = 'all';

    function apply() {
      var shown = 0;
      entries.forEach(function (row, i) {
        var matchesFilter = activeFilter === 'all' || row.dataset.type === activeFilter;
        var withinLimit = shown < visibleCount;
        var show = matchesFilter && withinLimit;
        if (matchesFilter) shown++;
        row.style.display = show ? '' : 'none';
      });
      var totalMatching = entries.filter(function (row) { return activeFilter === 'all' || row.dataset.type === activeFilter; }).length;
      if (resultsCount) resultsCount.textContent = Math.min(visibleCount, totalMatching) + ' of ' + totalMatching + ' events';
      if (loadMoreBtn) loadMoreBtn.style.display = visibleCount >= totalMatching ? 'none' : 'flex';
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        activeFilter = tab.dataset.filter || 'all';
        visibleCount = VISIBLE_STEP;
        apply();
      });
    });
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', function () { visibleCount += VISIBLE_STEP; apply(); });

    apply();
  }

  document.addEventListener('DOMContentLoaded', function () {
    setupSidebarPlanes();
    setupDatePills();
    setupCustomSelects();
    setupNotifications();
    setupSearch();
    setupLogout();
    setupLogoutLinks();
    setupDashboardActions();
    setupUserManagement();
    setupGradesPage();
    setupAuditLog();
  });
}());