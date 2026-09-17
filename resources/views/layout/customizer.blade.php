{{--
    Theme customizer — the offcanvas the topbar's sliders button
    (data-bs-target="#customizer" in header.blade.php) opens. Without it that
    button has nothing to open and does nothing, silently.

    Copied VERBATIM from the theme's own built output rather than hand-translated
    from customizer.hbs: every control is bound generically by
    js/layout/customizer.js through data-setting / data-token, so there is
    nothing here to adapt and nothing for Blade to interpolate. Re-extract the
    same block from a fresh `npm run build:static` when the theme adds settings.

    If the app forces an accent (app.blade.php's $accent, which also sets
    data-lock="accent"), the Accent swatches below are inert — the lock is what
    makes the forced colour stick. Wrap that section in @if (empty($accent))
    rather than leaving a row of controls that quietly do nothing.
--}}
<div class="offcanvas offcanvas-end customizer" tabindex="-1" id="customizer" aria-labelledby="customizer-title">
  <div class="offcanvas-header">
    <div>
      <h5 class="offcanvas-title" id="customizer-title">Theme</h5>
      <p class="mb-0 text-size-xs text-body-secondary">Every value is a live CSS variable.</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body customizer-body">
    <!-- Colour scheme -->
    <div class="customizer-section">
      <div class="customizer-label">Colour scheme</div>
      <div class="customizer-options">
          <div class="customizer-option">
            <input type="radio" name="uf-theme" id="theme-light" value="light" data-setting="theme" />
            <label for="theme-light">
              <i data-lucide="sun"></i>
              <span class="text-capitalize">light</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-theme" id="theme-dark" value="dark" data-setting="theme" />
            <label for="theme-dark">
              <i data-lucide="moon"></i>
              <span class="text-capitalize">dark</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-theme" id="theme-auto" value="auto" data-setting="theme" />
            <label for="theme-auto">
              <i data-lucide="monitor"></i>
              <span class="text-capitalize">auto</span>
            </label>
          </div>
      </div>
    </div>

    <!-- Layout -->
    <div class="customizer-section">
      <div class="customizer-label">Layout</div>
      <p class="customizer-hint">Same markup in every case — only <code>data-layout</code> changes.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-layout" id="layout-vertical" value="vertical" data-setting="layout" />
            <label for="layout-vertical">
              <span class="layout-preview " aria-hidden="true"><span style="width:30%"></span><span style="flex:1;opacity:.28"></span></span>
              <span>Vertical</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-layout" id="layout-compact" value="compact" data-setting="layout" />
            <label for="layout-compact">
              <span class="layout-preview " aria-hidden="true"><span style="width:14%"></span><span style="flex:1;opacity:.28"></span></span>
              <span>Compact</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-layout" id="layout-two-column" value="two-column" data-setting="layout" />
            <label for="layout-two-column">
              <span class="layout-preview " aria-hidden="true"><span style="width:13%"></span><span style="width:26%;opacity:.6"></span><span style="flex:1;opacity:.28"></span></span>
              <span>Two column</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-layout" id="layout-horizontal" value="horizontal" data-setting="layout" />
            <label for="layout-horizontal">
              <span class="layout-preview layout-preview-col" aria-hidden="true"><span style="height:30%"></span><span style="flex:1;opacity:.28"></span></span>
              <span>Horizontal</span>
            </label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-layout" id="layout-overlay" value="overlay" data-setting="layout" />
            <label for="layout-overlay">
              <span class="layout-preview " aria-hidden="true"><span style="width:22%;opacity:.55"></span><span style="flex:1;opacity:.28"></span></span>
              <span>Overlay</span>
            </label>
          </div>
      </div>
    </div>

    <!-- Accent -->
    <div class="customizer-section">
      <div class="customizer-label">Accent</div>
      <div class="customizer-swatches">
          <div class="customizer-swatch" style="--uf-swatch: #0284c7;">
            <input type="radio" name="uf-accent" id="accent-sky" value="sky" data-setting="accent" />
            <label for="accent-sky" title="Sky"><span class="visually-hidden">Sky</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #4f46e5;">
            <input type="radio" name="uf-accent" id="accent-indigo" value="indigo" data-setting="accent" />
            <label for="accent-indigo" title="Indigo"><span class="visually-hidden">Indigo</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #7c3aed;">
            <input type="radio" name="uf-accent" id="accent-violet" value="violet" data-setting="accent" />
            <label for="accent-violet" title="Violet"><span class="visually-hidden">Violet</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #2563eb;">
            <input type="radio" name="uf-accent" id="accent-blue" value="blue" data-setting="accent" />
            <label for="accent-blue" title="Blue"><span class="visually-hidden">Blue</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #0891b2;">
            <input type="radio" name="uf-accent" id="accent-cyan" value="cyan" data-setting="accent" />
            <label for="accent-cyan" title="Cyan"><span class="visually-hidden">Cyan</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #0d9488;">
            <input type="radio" name="uf-accent" id="accent-teal" value="teal" data-setting="accent" />
            <label for="accent-teal" title="Teal"><span class="visually-hidden">Teal</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #059669;">
            <input type="radio" name="uf-accent" id="accent-emerald" value="emerald" data-setting="accent" />
            <label for="accent-emerald" title="Emerald"><span class="visually-hidden">Emerald</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #65a30d;">
            <input type="radio" name="uf-accent" id="accent-lime" value="lime" data-setting="accent" />
            <label for="accent-lime" title="Lime"><span class="visually-hidden">Lime</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #d97706;">
            <input type="radio" name="uf-accent" id="accent-amber" value="amber" data-setting="accent" />
            <label for="accent-amber" title="Amber"><span class="visually-hidden">Amber</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #ea580c;">
            <input type="radio" name="uf-accent" id="accent-orange" value="orange" data-setting="accent" />
            <label for="accent-orange" title="Orange"><span class="visually-hidden">Orange</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #e11d48;">
            <input type="radio" name="uf-accent" id="accent-rose" value="rose" data-setting="accent" />
            <label for="accent-rose" title="Rose"><span class="visually-hidden">Rose</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #c026d3;">
            <input type="radio" name="uf-accent" id="accent-fuchsia" value="fuchsia" data-setting="accent" />
            <label for="accent-fuchsia" title="Fuchsia"><span class="visually-hidden">Fuchsia</span></label>
          </div>
          <div class="customizer-swatch" style="--uf-swatch: #1e293b;">
            <input type="radio" name="uf-accent" id="accent-slate" value="slate" data-setting="accent" />
            <label for="accent-slate" title="Slate"><span class="visually-hidden">Slate</span></label>
          </div>
      </div>

      <div class="mt-4">
        <div class="customizer-label">Raw token override</div>
        <p class="customizer-hint">Writes straight onto <code>&lt;html&gt;.style</code>, beating every preset.</p>
        <div class="customizer-token">
          <input type="color" data-token="--uf-accent-600" aria-label="Accent 600" />
          <code>--uf-accent-600</code>
        </div>
        <div class="customizer-token">
          <input type="color" data-token="--uf-accent-500" aria-label="Accent 500" />
          <code>--uf-accent-500</code>
        </div>
      </div>
    </div>

    <!-- Neutrals -->
    <div class="customizer-section">
      <div class="customizer-label">Neutral temperature</div>
      <div class="customizer-options customizer-options-4">
          <div class="customizer-option">
            <input type="radio" name="uf-gray" id="gray-cool" value="cool" data-setting="gray" />
            <label for="gray-cool"><span class="text-capitalize">cool</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-gray" id="gray-neutral" value="neutral" data-setting="gray" />
            <label for="gray-neutral"><span class="text-capitalize">neutral</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-gray" id="gray-warm" value="warm" data-setting="gray" />
            <label for="gray-warm"><span class="text-capitalize">warm</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-gray" id="gray-slate" value="slate" data-setting="gray" />
            <label for="gray-slate"><span class="text-capitalize">slate</span></label>
          </div>
      </div>
    </div>

    <!-- Density -->
    <div class="customizer-section">
      <div class="customizer-label">Density</div>
      <p class="customizer-hint">Moves control heights, padding and base type together.</p>
      <div class="customizer-options customizer-options-4">
          <div class="customizer-option">
            <input type="radio" name="uf-density" id="density-tight" value="tight" data-setting="density" />
            <label for="density-tight"><span class="text-capitalize">tight</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-density" id="density-compact" value="compact" data-setting="density" />
            <label for="density-compact"><span class="text-capitalize">compact</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-density" id="density-comfortable" value="comfortable" data-setting="density" />
            <label for="density-comfortable"><span class="text-capitalize">comfortable</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-density" id="density-spacious" value="spacious" data-setting="density" />
            <label for="density-spacious"><span class="text-capitalize">spacious</span></label>
          </div>
      </div>
    </div>

    <!-- Radius -->
    <div class="customizer-section">
      <div class="customizer-label">Corner radius</div>
      <div class="customizer-options customizer-options-4">
          <div class="customizer-option">
            <input type="radio" name="uf-radius" id="radius-none" value="none" data-setting="radius" />
            <label for="radius-none"><span class="text-capitalize">none</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-radius" id="radius-sm" value="sm" data-setting="radius" />
            <label for="radius-sm"><span class="text-capitalize">sm</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-radius" id="radius-default" value="default" data-setting="radius" />
            <label for="radius-default"><span class="text-capitalize">default</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-radius" id="radius-lg" value="lg" data-setting="radius" />
            <label for="radius-lg"><span class="text-capitalize">lg</span></label>
          </div>
      </div>
    </div>

    <!-- Typeface -->
    <div class="customizer-section">
      <div class="customizer-label">Typeface</div>
      <div class="customizer-options customizer-options-font">
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-inter" value="inter" data-setting="font" />
            <label for="font-inter" style="font-family: 'Inter Variable';">Inter</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-manrope" value="manrope" data-setting="font" />
            <label for="font-manrope" style="font-family: 'Manrope';">Manrope</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-jakarta" value="jakarta" data-setting="font" />
            <label for="font-jakarta" style="font-family: 'Plus Jakarta Sans';">Plus Jakarta Sans</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-sora" value="sora" data-setting="font" />
            <label for="font-sora" style="font-family: 'Sora';">Sora</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-grotesk" value="grotesk" data-setting="font" />
            <label for="font-grotesk" style="font-family: 'Space Grotesk';">Space Grotesk</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-outfit" value="outfit" data-setting="font" />
            <label for="font-outfit" style="font-family: 'Outfit';">Outfit</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-dmsans" value="dmsans" data-setting="font" />
            <label for="font-dmsans" style="font-family: 'DM Sans';">DM Sans</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-system" value="system" data-setting="font" />
            <label for="font-system" style="font-family: 'system-ui';">System</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-font" id="font-mono" value="mono" data-setting="font" />
            <label for="font-mono" style="font-family: 'ui-monospace';">Mono</label>
          </div>
      </div>
    </div>

    <div class="customizer-section">
      <div class="customizer-label">Icon style</div>
      <div class="customizer-options customizer-options-4">
          <div class="customizer-option">
            <input type="radio" name="uf-icons" id="icons-line" value="line" data-setting="icons" />
            <label for="icons-line"><span class="text-capitalize">line</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-icons" id="icons-thin" value="thin" data-setting="icons" />
            <label for="icons-thin"><span class="text-capitalize">thin</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-icons" id="icons-bold" value="bold" data-setting="icons" />
            <label for="icons-bold"><span class="text-capitalize">bold</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-icons" id="icons-duotone" value="duotone" data-setting="icons" />
            <label for="icons-duotone"><span class="text-capitalize">duotone</span></label>
          </div>
      </div>
    </div>

    <!-- Chrome -->
    <div class="customizer-section">
      <div class="customizer-label">Sidebar</div>
      <div class="customizer-options customizer-options-3">
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar" id="sidebar-light" value="light" data-setting="sidebar" />
            <label for="sidebar-light"><span class="text-capitalize">light</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar" id="sidebar-dark" value="dark" data-setting="sidebar" />
            <label for="sidebar-dark"><span class="text-capitalize">dark</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar" id="sidebar-accent" value="accent" data-setting="sidebar" />
            <label for="sidebar-accent"><span class="text-capitalize">accent</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar" id="sidebar-gradient" value="gradient" data-setting="sidebar" />
            <label for="sidebar-gradient"><span class="text-capitalize">gradient</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar" id="sidebar-transparent" value="transparent" data-setting="sidebar" />
            <label for="sidebar-transparent"><span class="text-capitalize">transparent</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Sidebar logo</div>
      <p class="customizer-hint">Icon-only shows just the square brand mark, same as the collapsed rail.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar-logo" id="sidebar-logo-full" value="full" data-setting="sidebarLogo" />
            <label for="sidebar-logo-full"><span class="text-capitalize">full</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar-logo" id="sidebar-logo-icon" value="icon" data-setting="sidebarLogo" />
            <label for="sidebar-logo-icon"><span class="text-capitalize">icon</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Documentation box</div>
      <p class="customizer-hint">Hiding it lets the nav list use the full sidebar height instead of stopping short.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar-docs" id="sidebar-docs-visible" value="visible" data-setting="sidebarDocs" />
            <label for="sidebar-docs-visible"><span class="text-capitalize">visible</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-sidebar-docs" id="sidebar-docs-hidden" value="hidden" data-setting="sidebarDocs" />
            <label for="sidebar-docs-hidden"><span class="text-capitalize">hidden</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Account chip</div>
      <p class="customizer-hint">Moves the profile menu out of the header and into the sidebar, under the brand or pinned to the bottom.</p>
      <div class="customizer-options customizer-options-3">
          <div class="customizer-option">
            <input type="radio" name="uf-user-menu" id="user-menu-header" value="header" data-setting="userMenu" />
            <label for="user-menu-header">Header</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-user-menu" id="user-menu-sidebar-top" value="sidebar-top" data-setting="userMenu" />
            <label for="user-menu-sidebar-top">Top</label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-user-menu" id="user-menu-sidebar-bottom" value="sidebar-bottom" data-setting="userMenu" />
            <label for="user-menu-sidebar-bottom">Bottom</label>
          </div>
      </div>

      <div class="customizer-label mt-4">Topbar</div>
      <div class="customizer-options customizer-options-3">
          <div class="customizer-option">
            <input type="radio" name="uf-topbar" id="topbar-light" value="light" data-setting="topbar" />
            <label for="topbar-light"><span class="text-capitalize">light</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-topbar" id="topbar-dark" value="dark" data-setting="topbar" />
            <label for="topbar-dark"><span class="text-capitalize">dark</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-topbar" id="topbar-accent" value="accent" data-setting="topbar" />
            <label for="topbar-accent"><span class="text-capitalize">accent</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-topbar" id="topbar-gradient" value="gradient" data-setting="topbar" />
            <label for="topbar-gradient"><span class="text-capitalize">gradient</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-topbar" id="topbar-transparent" value="transparent" data-setting="topbar" />
            <label for="topbar-transparent"><span class="text-capitalize">transparent</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Footer</div>
      <p class="customizer-hint">Hidden removes it entirely — the page reclaims its height instead of leaving a gap.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-footer-visible" id="footer-visible-visible" value="visible" data-setting="footerVisible" />
            <label for="footer-visible-visible"><span class="text-capitalize">visible</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer-visible" id="footer-visible-hidden" value="hidden" data-setting="footerVisible" />
            <label for="footer-visible-hidden"><span class="text-capitalize">hidden</span></label>
          </div>
      </div>
      <div class="customizer-options customizer-options-3 mt-2">
          <div class="customizer-option">
            <input type="radio" name="uf-footer" id="footer-light" value="light" data-setting="footer" />
            <label for="footer-light"><span class="text-capitalize">light</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer" id="footer-dark" value="dark" data-setting="footer" />
            <label for="footer-dark"><span class="text-capitalize">dark</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer" id="footer-accent" value="accent" data-setting="footer" />
            <label for="footer-accent"><span class="text-capitalize">accent</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer" id="footer-gradient" value="gradient" data-setting="footer" />
            <label for="footer-gradient"><span class="text-capitalize">gradient</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer" id="footer-transparent" value="transparent" data-setting="footer" />
            <label for="footer-transparent"><span class="text-capitalize">transparent</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Footer position</div>
      <p class="customizer-hint">Static sits at the end of the page; fixed pins it to the viewport bottom in every shell — docked, floating or detached.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-footer-position" id="footer-position-static" value="static" data-setting="footerPosition" />
            <label for="footer-position-static"><span class="text-capitalize">static</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer-position" id="footer-position-fixed" value="fixed" data-setting="footerPosition" />
            <label for="footer-position-fixed"><span class="text-capitalize">fixed</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Footer width</div>
      <p class="customizer-hint">Inset aligns the footer to the content column. Full spans the whole viewport and lifts the sidebar off it — pair it with a fixed footer, since a static one sits at the end of the document where the sidebar cannot see it.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-footer-width" id="footer-width-inset" value="inset" data-setting="footerWidth" />
            <label for="footer-width-inset"><span class="text-capitalize">inset</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-footer-width" id="footer-width-full" value="full" data-setting="footerWidth" />
            <label for="footer-width-full"><span class="text-capitalize">full</span></label>
          </div>
      </div>
    </div>

    <!-- Shell -->
    <div class="customizer-section">
      <div class="customizer-label">Shell</div>
      <p class="customizer-hint">Docked sits flush to the window; floating and detached inset the chrome with a gutter.</p>
      <div class="customizer-options">
          <div class="customizer-option">
            <input type="radio" name="uf-shell" id="shell-docked" value="docked" data-setting="shell" />
            <label for="shell-docked"><span class="text-capitalize">docked</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-shell" id="shell-floating" value="floating" data-setting="shell" />
            <label for="shell-floating"><span class="text-capitalize">floating</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-shell" id="shell-detached" value="detached" data-setting="shell" />
            <label for="shell-detached"><span class="text-capitalize">detached</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Horizontal nav</div>
      <p class="customizer-hint">Only visible with a floating shell and the horizontal layout — attached merges the nav into the topbar's own box.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-menubar-attach" id="menubar-attach-separate" value="separate" data-setting="menubarAttach" />
            <label for="menubar-attach-separate"><span class="text-capitalize">separate</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-menubar-attach" id="menubar-attach-attached" value="attached" data-setting="menubarAttach" />
            <label for="menubar-attach-attached"><span class="text-capitalize">attached</span></label>
          </div>
      </div>
    </div>

    <!-- Background -->
    <div class="customizer-section">
      <div class="customizer-label">Page background</div>
      <div class="customizer-options customizer-options-3">
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-plain" value="plain" data-setting="bg" />
            <label for="bg-plain"><span class="text-capitalize">plain</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-subtle" value="subtle" data-setting="bg" />
            <label for="bg-subtle"><span class="text-capitalize">subtle</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-gradient" value="gradient" data-setting="bg" />
            <label for="bg-gradient"><span class="text-capitalize">gradient</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-mesh" value="mesh" data-setting="bg" />
            <label for="bg-mesh"><span class="text-capitalize">mesh</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-dots" value="dots" data-setting="bg" />
            <label for="bg-dots"><span class="text-capitalize">dots</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-bg" id="bg-grid" value="grid" data-setting="bg" />
            <label for="bg-grid"><span class="text-capitalize">grid</span></label>
          </div>
      </div>
    </div>

    <!-- Elevation & width -->
    <div class="customizer-section">
      <div class="customizer-label">Elevation</div>
      <div class="customizer-options">
          <div class="customizer-option">
            <input type="radio" name="uf-shadow" id="shadow-flat" value="flat" data-setting="shadow" />
            <label for="shadow-flat"><span class="text-capitalize">flat</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-shadow" id="shadow-soft" value="soft" data-setting="shadow" />
            <label for="shadow-soft"><span class="text-capitalize">soft</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-shadow" id="shadow-elevated" value="elevated" data-setting="shadow" />
            <label for="shadow-elevated"><span class="text-capitalize">elevated</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Content width</div>
      <div class="customizer-options customizer-options-3">
          <div class="customizer-option">
            <input type="radio" name="uf-width" id="width-fluid" value="fluid" data-setting="contentWidth" />
            <label for="width-fluid"><span class="text-capitalize">fluid</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-width" id="width-container" value="container" data-setting="contentWidth" />
            <label for="width-container"><span class="text-capitalize">container</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-width" id="width-boxed" value="boxed" data-setting="contentWidth" />
            <label for="width-boxed"><span class="text-capitalize">boxed</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-width" id="width-centered" value="centered" data-setting="contentWidth" />
            <label for="width-centered"><span class="text-capitalize">centered</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-width" id="width-full" value="full" data-setting="contentWidth" />
            <label for="width-full"><span class="text-capitalize">full</span></label>
          </div>
      </div>
    </div>

    <!-- Chrome & scrollbars -->
    <div class="customizer-section">
      <div class="customizer-label">Header width</div>
      <p class="customizer-hint">Caps the topbar and menubar CONTENT, not the bar itself.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-chromewidth" id="chromewidth-fluid" value="fluid" data-setting="chromeWidth" />
            <label for="chromewidth-fluid"><span class="text-capitalize">fluid</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-chromewidth" id="chromewidth-container" value="container" data-setting="chromeWidth" />
            <label for="chromewidth-container"><span class="text-capitalize">container</span></label>
          </div>
      </div>

      <div class="customizer-label mt-4">Scrollbars</div>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-scrollbar" id="scrollbar-overlay" value="overlay" data-setting="scrollbar" />
            <label for="scrollbar-overlay"><span class="text-capitalize">overlay</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-scrollbar" id="scrollbar-stacked" value="stacked" data-setting="scrollbar" />
            <label for="scrollbar-stacked"><span class="text-capitalize">stacked</span></label>
          </div>
      </div>
    </div>

    <!-- Mobile -->
    <div class="customizer-section">
      <div class="customizer-label">Mobile navigation</div>
      <p class="customizer-hint">Resize below 768px to see the difference.</p>
      <div class="customizer-options customizer-options-2">
          <div class="customizer-option">
            <input type="radio" name="uf-mobilenav" id="mobilenav-tabbar" value="tabbar" data-setting="mobileNav" />
            <label for="mobilenav-tabbar"><span class="text-capitalize">tabbar</span></label>
          </div>
          <div class="customizer-option">
            <input type="radio" name="uf-mobilenav" id="mobilenav-drawer" value="drawer" data-setting="mobileNav" />
            <label for="mobilenav-drawer"><span class="text-capitalize">drawer</span></label>
          </div>
      </div>
    </div>
  </div>

  <div class="customizer-footer">
    <button type="button" class="btn btn-secondary btn-sm" data-customizer-reset>
      <i data-lucide="rotate-ccw"></i>Reset
    </button>
    <button type="button" class="btn btn-primary btn-sm" data-customizer-copy>Copy CSS</button>
  </div>
</div>
