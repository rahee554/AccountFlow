{{--
    Command palette (Ctrl/Cmd+K) — port of ui-flow-admin/partials/shell/palette.hbs.

    core/js/palette.js binds the shortcut unconditionally, so WITHOUT this
    markup in the DOM the shortcut fires and finds nothing to open: no error,
    no warning, the feature simply does nothing. Same for the customizer and
    the menubar below.

    It needs no navigation data of its own — palette.js reads the result list
    straight out of the rendered .sidebar-nav, so it always matches menu.blade.php.
--}}
<div class="modal fade" id="command-palette" tabindex="-1" aria-labelledby="palette-label" aria-hidden="true">
  <div class="modal-dialog palette-dialog">
    <div class="modal-content palette-content">
      <h2 class="visually-hidden" id="palette-label">Search</h2>

      <div class="palette-input-wrap">
        <i data-lucide="search"></i>
        <input type="text" class="palette-input" placeholder="Jump to a page…" aria-label="Search pages" autocomplete="off" />
        <kbd>Esc</kbd>
      </div>

      <div class="palette-results" role="listbox" aria-label="Results"></div>

      <div class="palette-footer">
        <span><kbd>&uarr;</kbd><kbd>&darr;</kbd> navigate</span>
        <span><kbd>&crarr;</kbd> open</span>
        <span class="ms-auto"><kbd>&#8984;</kbd><kbd>K</kbd> anywhere</span>
      </div>
    </div>
  </div>
</div>
