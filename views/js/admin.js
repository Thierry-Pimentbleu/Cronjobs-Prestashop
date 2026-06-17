/* pb_cronjobs — Admin JS */
$(document).ready(function () {

    /* ── Copy cron URL ──────────────────────────────────────────────────── */
    $('#pb-copy-url').on('click', function () {
        var $input = $('#pb-cron-url');
        $input.select();
        try {
            document.execCommand('copy');
            var $btn = $(this);
            $btn.html('<i class="icon-check"></i>');
            setTimeout(function () {
                $btn.html('<i class="icon-copy"></i> Copy');
            }, 1500);
        } catch (e) {}
    });

    /* ── Delete confirmation modal ──────────────────────────────────────── */
    $(document).on('click', '.pb-delete-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var url  = $(this).attr('href');
        var desc = $(this).data('desc') || '';

        $('#pb-delete-desc').text(desc);
        $('#pb-delete-confirm').attr('href', url);
        $('#pb-delete-modal').modal('show');
    });

    /* ── Required fields → enable/disable submit button ────────────────── */
    var form = document.getElementById('pb-cron-form');
    if (form) {
        var descInput = form.querySelector('[name="description"]');
        var urlInput  = form.querySelector('[name="task"]');
        var submitBtn = form.querySelector('.pb-btn-submit');

        function checkSubmit() {
            if (!descInput || !urlInput || !submitBtn) { return; }
            var ok = descInput.value.trim() !== '' && urlInput.value.trim() !== '';
            submitBtn.disabled = !ok;
        }

        if (descInput) { descInput.addEventListener('input', checkSubmit); }
        if (urlInput)  { urlInput.addEventListener('input', checkSubmit); }
        checkSubmit();
    }

    /* ── Tab persistence ────────────────────────────────────────────────── */
    var tabKey = 'pb_cronjobs_tab';
    var savedTab = localStorage.getItem(tabKey);
    if (savedTab && $('#pb-main-tabs a[href="' + savedTab + '"]').length) {
        $('#pb-main-tabs a[href="' + savedTab + '"]').tab('show');
    }
    $('#pb-main-tabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem(tabKey, $(e.target).attr('href'));
    });

    /* ── Log : filter + pagination ──────────────────────────────────────── */
    var logPage    = 1;
    var logPerPage = 25;

    function getFilteredRows() {
        var id = $('#pb-log-filter').val();
        return $('.pb-logs-table tbody tr').filter(function () {
            return !id || String($(this).data('cron-id')) === String(id);
        });
    }

    function renderLog() {
        var $all      = getFilteredRows();
        var total     = $all.length;
        var totalPages = Math.max(1, Math.ceil(total / logPerPage));

        if (logPage > totalPages) { logPage = totalPages; }

        var from = (logPage - 1) * logPerPage;
        var to   = Math.min(from + logPerPage, total);

        $('.pb-logs-table tbody tr').hide();
        $all.each(function (i) {
            if (i >= from && i < to) { $(this).show(); }
        });

        /* info text */
        $('#pb-log-info').text(total > 0 ? (from + 1) + '–' + to + ' / ' + total : '');

        /* pagination */
        var $nav = $('#pb-log-pagination').empty();
        if (totalPages <= 1) { return; }

        var $ul = $('<ul class="pagination pagination-sm" style="margin:0;"></ul>');

        function addBtn(label, page, disabled, active) {
            var $li = $('<li>');
            if (disabled) { $li.addClass('disabled'); }
            if (active)   { $li.addClass('active'); }
            var $a = $('<a href="#">').html(label);
            if (!disabled && !active) {
                $a.on('click', function (e) {
                    e.preventDefault();
                    logPage = page;
                    renderLog();
                });
            } else {
                $a.on('click', function (e) { e.preventDefault(); });
            }
            $ul.append($li.append($a));
        }

        addBtn('&lsaquo;', logPage - 1, logPage === 1, false);

        var start = Math.max(1, logPage - 2);
        var end   = Math.min(totalPages, start + 4);
        start = Math.max(1, end - 4);

        for (var p = start; p <= end; p++) {
            addBtn(p, p, false, p === logPage);
        }

        addBtn('&rsaquo;', logPage + 1, logPage === totalPages, false);
        $nav.append($ul);
    }

    if ($('.pb-logs-table').length) {
        renderLog();
    }

    $('#pb-log-filter').on('change', function () {
        logPage = 1;
        renderLog();
    });

    $('#pb-per-page').on('change', function () {
        logPerPage = parseInt($(this).val(), 10);
        logPage    = 1;
        renderLog();
    });

    /* ── Copy purge URL ─────────────────────────────────────────────────── */
    $('#pb-copy-purge-url').on('click', function () {
        var $input = $('#pb-purge-url');
        $input.select();
        try { document.execCommand('copy'); } catch (e) {}
    });

    /* ── Drag & drop sort ───────────────────────────────────────────────── */
    var tbody = document.getElementById('pb-crons-tbody');
    if (tbody && typeof Sortable !== 'undefined') {
        Sortable.create(tbody, {
            handle:    '.pb-sort-handle',
            animation: 150,
            ghostClass: 'pb-row-dragging',
            onEnd: function () {
                var ids = [];
                tbody.querySelectorAll('tr[data-id]').forEach(function (tr) {
                    ids.push(tr.getAttribute('data-id'));
                });
                var url = tbody.getAttribute('data-sort-url');
                fetch(url, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    'order=' + encodeURIComponent(JSON.stringify(ids))
                });
            }
        });
    }

});
