<?php
class CFB_Builder
{
    public function render_page()
    {
?>
        <div class="wrap cfb-wrapper">
            <h1>Custom Form Builder - Contact Us</h1>

            <!-- Top navigation like Formidable -->
            <div class="cfb-header">
                <button id="cfb-preview" class="button">Preview</button>
                <button id="cfb-download" class="button">Download</button>
                <button class="button">Settings</button>
                <button class="button">Entries</button>
                <button id="cfb-save" class="button button-primary">Save</button>
            </div>

            <div class="cfb-title-wrap">
                <label for="cfb-form-title" class="screen-reader-text">Form Title</label>
                <input id="cfb-form-title" type="text" class="cfb-title-input" placeholder="Form Title" />
            </div>
            <!-- MAIN BUILDER LAYOUT -->
            <div id="cfb-builder" class="cfb-grid">

                <!-- LEFT PANEL (Tabs: Add Fields / Field Options) -->
                <aside id="cfb-fields" class="cfb-side cfb-side--left">
                    <div class="cfb-side-header">
                        <div class="cfb-tab-switch">
                            <button class="cfb-tab-btn is-active" data-tab="fields">Add Fields</button>
                            <button class="cfb-tab-btn" data-tab="options">Field Options</button>
                        </div>
                    </div>
                    <div id="cfb-tab-options" class="cfb-tab-pane">
                        <div id="cfb-field-options">
                            <p class="cfb-muted">Select a field in the form to edit its options</p>
                            <!-- dynamic options go here -->
                        </div>
                    </div>


                    <div class="cfb-side-body">
                        <!-- Tab: Add Fields -->
                        <div class="cfb-tab-pane is-active" id="cfb-tab-fields">
                            <div class="cfb-search-wrap">
                                <span class="dashicons dashicons-search"></span>
                                <input type="text" class="cfb-search" placeholder="Search Fields..." />
                            </div>
                            <ul class="cfb-field-list">
                                <!-- Existing items but styled like Formidable -->
                                <li role="button" tabindex="0"
                                    data-type="text"
                                    data-template='<div class="cfb-field" data-type="text"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Text <input type="text" name="field_text[]" placeholder="Enter text"></label></div>'>
                                    <span class="dashicons dashicons-edit"></span>
                                    <span>Text</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="textarea"
                                    data-template='<div class="cfb-field" data-type="textarea"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Paragraph <textarea name="field_message[]" placeholder="Type here..."></textarea></label></div>'>
                                    <span class="dashicons dashicons-feedback"></span>
                                    <span>Paragraph</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="checkbox"
                                    data-template='<div class="cfb-field" data-type="checkbox"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label><input type="checkbox" name="field_checkbox[]"> Option 1</label></div>'>
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Checkboxes</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="radio"
                                    data-template='<div class="cfb-field" data-type="radio"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label><input type="radio" name="field_radio[]"> Option A</label></div>'>
                                    <span class="dashicons dashicons-marker"></span>
                                    <span>Radio Buttons</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="select"
                                    data-template='<div class="cfb-field" data-type="select"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Dropdown <select name="field_select[]"><option>Option 1</option><option>Option 2</option></select></label></div>'>
                                    <span class="dashicons dashicons-menu"></span>
                                    <span>Dropdown</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="email"
                                    data-template='<div class="cfb-field" data-type="email"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Email <input type="email" name="field_email[]" placeholder="name@example.com"></label></div>'>
                                    <span class="dashicons dashicons-email"></span>
                                    <span>Email</span>
                                </li>

                                <!-- Extra items (for richer left menu like screenshot) -->
                                <li role="button" tabindex="0"
                                    data-type="url"
                                    data-template='<div class="cfb-field" data-type="url"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Website/URL <input type="url" name="field_url[]" placeholder="https://"></label></div>'>
                                    <span class="dashicons dashicons-admin-links"></span>
                                    <span>Website/URL</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="number"
                                    data-template='<div class="cfb-field" data-type="number"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Number <input type="number" name="field_number[]" placeholder="0"></label></div>'>
                                    <span class="dashicons dashicons-editor-ol"></span>
                                    <span>Number</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="name"
                                    data-template='<div class="cfb-field" data-type="name"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><div class="cfb-row"><div class="cfb-col"><label>First <input type="text" name="field_first[]" placeholder="First"></label></div><div class="cfb-col"><label>Last <input type="text" name="field_last[]" placeholder="Last"></label></div></div></div>'>
                                    <span class="dashicons dashicons-id"></span>
                                    <span>Name</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="phone"
                                    data-template='<div class="cfb-field" data-type="phone"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Phone <input type="tel" name="field_phone[]" placeholder="+1 555 555 5555"></label></div>'>
                                    <span class="dashicons dashicons-phone"></span>
                                    <span>Phone</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="html"
                                    data-template='<div class="cfb-field" data-type="html"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><div class="cfb-html">This is custom HTML block.</div></div>'>
                                    <span class="dashicons dashicons-editor-code"></span>
                                    <span>HTML</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="hidden"
                                    data-template='<div class="cfb-field" data-type="hidden"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>Hidden <input type="hidden" name="field_hidden[]" value="hidden-value"></label><em class="cfb-muted">Hidden field</em></div>'>
                                    <span class="dashicons dashicons-hidden"></span>
                                    <span>Hidden</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="user_id"
                                    data-template='<div class="cfb-field" data-type="user_id"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><label>User ID <input type="text" name="field_user_id[]" placeholder="Auto" readonly></label></div>'>
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <span>User ID</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="captcha"
                                    data-template='<div class="cfb-field" data-type="captcha"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><div class="cfb-captcha-placeholder">Captcha Placeholder</div></div>'>
                                    <span class="dashicons dashicons-shield"></span>
                                    <span>Captcha</span>
                                </li>

                                <li role="button" tabindex="0"
                                    data-type="payment"
                                    data-template='<div class="cfb-field" data-type="payment"><div class="field-actions"><button class="cfb-field-delete" type="button" title="Delete Field">⋮</button></div><div class="cfb-payment-placeholder">Payment (demo)</div></div>'>
                                    <span class="dashicons dashicons-cart"></span>
                                    <span>Payment</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab: Field Options (will move the same options panel here) -->
                        <div class="cfb-tab-pane" id="cfb-tab-options">
                            <div id="cfb-left-options-placeholder"></div>
                        </div>

                        <!-- HTML Code Box at bottom of left panel -->
                        <div class="cfb-html-code-box">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <h3 style="margin:0;">HTML Code</h3>
                                <button id="cfb-copy-html" type="button" class="button">Copy</button>
                            </div>
                            <textarea id="cfb-html-code" rows="12" readonly></textarea>
                        </div>
                    </div>
                </aside>

                <!-- CENTER PANEL (Form Preview/Drag & Drop) -->
                <main id="cfb-form">
                    <div class="cfb-canvas">
                        <div class="cfb-canvas-header">
                            <h2>Form Layout</h2>
                        </div>
                        <form id="cfb-dropzone" class="cfb-dropzone">
                            <!-- PRE-POPULATED DEFAULT FIELDS -->
                            <div class="cfb-field" data-type="first_name">
                                <div class="field-actions">
                                    <button class="cfb-field-delete" type="button" title="Delete Field">⋮</button>
                                </div>
                                <label>First Name <input type="text" name="field_first_name[]" placeholder="First Name"></label>
                            </div>

                            <div class="cfb-field" data-type="last_name">
                                <div class="field-actions">
                                    <button class="cfb-field-delete" type="button" title="Delete Field">⋮</button>
                                </div>
                                <label>Last Name <input type="text" name="field_last_name[]" placeholder="Last Name"></label>
                            </div>


                            <div class="cfb-field" data-type="email">
                                <div class="field-actions">
                                    <button class="cfb-field-delete" type="button" title="Delete Field">⋮</button>
                                </div>
                                <label>Email <input type="email" name="field_email[]" placeholder="Enter your email"></label>
                            </div>

                            <div class="cfb-field" data-type="text">
                                <div class="field-actions">
                                    <button class="cfb-field-delete" type="button" title="Delete Field">⋮</button>
                                </div>
                                <label>Subject <input type="text" name="field_text[]" placeholder="Subject"></label>
                            </div>

                            <div class="cfb-field" data-type="textarea">
                                <div class="field-actions">
                                    <button class="cfb-field-delete" type="button" title="Delete Field">⋮</button>
                                </div>
                                <label>Message <textarea name="field_message[]" placeholder="Your message here..."></textarea></label>
                            </div>
                            <div class="">
                                <button type="submit" class="cfb-submit-button">Submit</button>
                            </div>
                        </form>
                    </div>
                </main>

            </div>

            <!-- STYLES -->
            <style>
                :root {
                    --cfb-bg: #f5f7fb;
                    --cfb-panel: #ffffff;
                    --cfb-border: #e6e9ef;
                    --cfb-text: #2b2f36;
                    --cfb-muted: #6b7280;
                    --cfb-primary: #2f80ed;
                }

                /* Title input at top */
                .cfb-title-wrap {
                    margin: 12px 0 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .cfb-title-input {
                    width: 100%;
                    max-width: 420px;
                    height: 36px;
                    border: 1px solid var(--cfb-border);
                    border-radius: 8px;
                    padding: 0 12px;
                    outline: none;
                    background: #fff;
                }

                .cfb-title-input:focus {
                    border-color: var(--cfb-primary);
                    box-shadow: 0 0 0 3px rgba(47, 128, 237, .15);
                }

                .cfb-grid {
                    display: grid;
                    grid-template-columns: 1fr 2fr;
                    gap: 20px;
                    margin-top: 20px;
                }

                #cfb-fields {
                    border: 1px solid #ddd;
                    padding: 15px;
                    background: #fff;
                    min-height: 400px;
                    border-radius: 6px;
                }

                #cfb-fields h2 {
                    font-size: 16px;
                    margin-top: 0;
                }

                #cfb-fields .cfb-search {
                    width: 100%;
                    margin-bottom: 10px;
                    padding: 6px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }

                /* Side panels */
                .cfb-side {
                    background: var(--cfb-panel);
                    border: 1px solid var(--cfb-border);
                    border-radius: 12px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
                    display: flex;
                    flex-direction: column;
                    position: sticky;
                    top: 80px;
                    overflow: hidden;
                }

                .cfb-side-header {
                    padding: 12px 12px;
                    border-bottom: 1px solid var(--cfb-border);
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .cfb-side-body,
                .cfb-options-body {
                    padding: 12px;
                }

                /* Tabs (left) */
                .cfb-tab-switch {
                    background: #eef2f7;
                    border-radius: 10px;
                    padding: 4px;
                    display: inline-flex;
                    gap: 4px;
                }

                .cfb-tab-btn {
                    background: transparent;
                    border: none;
                    padding: 8px 12px;
                    border-radius: 8px;
                    font-weight: 600;
                    color: var(--cfb-muted);
                    cursor: pointer;
                }

                .cfb-tab-btn.is-active {
                    background: var(--cfb-panel);
                    color: var(--cfb-text);
                    box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
                }

                .cfb-tab-pane {
                    display: none;
                }

                .cfb-tab-pane.is-active {
                    display: block;
                }

                /* Search */
                .cfb-search-wrap {
                    position: relative;
                    margin-bottom: 12px;
                }

                .cfb-search-wrap .dashicons {
                    position: absolute;
                    left: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: var(--cfb-muted);
                }

                .cfb-search {
                    width: 100%;
                    height: 36px;
                    border: 1px solid var(--cfb-border);
                    border-radius: 10px;
                    padding: 0 12px 40px 34px;
                    outline: none;
                    background: #fff;
                }

                .cfb-search:focus {
                    border-color: var(--cfb-primary);
                    box-shadow: 0 0 0 3px rgba(47, 128, 237, .15);
                }

                /* Field list (left) */
                .cfb-field-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 8px;
                }

                .cfb-field-list li {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    border: 1px solid var(--cfb-border);
                    border-radius: 10px;
                    padding: 10px 12px;
                    cursor: grab;
                    transition: .15s ease;
                    background: #fff;
                }

                .cfb-field-list li:hover {
                    border-color: var(--cfb-primary);
                    box-shadow: 0 2px 6px rgba(47, 128, 237, .12);
                    transform: translateY(-1px);
                }

                .cfb-field-list .dashicons {
                    color: #64748b;
                }

                /* Canvas */
                #cfb-form {
                    min-height: 70vh;
                }

                .cfb-canvas {
                    background: var(--cfb-panel);
                    border: 1px solid var(--cfb-border);
                    border-radius: 12px;
                    padding: 16px;
                }

                .cfb-canvas-header {
                    border-bottom: 1px solid var(--cfb-border);
                    margin: -16px -16px 12px;
                    padding: 12px 16px;
                }

                .cfb-dropzone {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }

                /* Field cards */
                .cfb-field {
                    position: relative;
                    padding: 16px;
                    background: #fff;
                    border: 1px solid var(--cfb-border);
                    border-radius: 12px;
                }

                .field-actions {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                }

                .cfb-field-delete {
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    font-size: 18px;
                    color: #868e96;
                }

                .cfb-field-delete:hover {
                    color: #d00;
                }

                /* Field action menu (three-dots dropdown) */
                .cfb-field-menu {
                    position: absolute;
                    top: 28px;
                    right: 0;
                    background: #fff;
                    border: 1px solid var(--cfb-border);
                    border-radius: 8px;
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
                    min-width: 140px;
                    z-index: 10;
                    padding: 6px 0;
                }

                .cfb-field-menu .cfb-menu-item {
                    width: 100%;
                    text-align: left;
                    background: transparent;
                    border: none;
                    padding: 8px 12px;
                    cursor: pointer;
                    font-size: 13px;
                    color: var(--cfb-text);
                }

                .cfb-field-menu .cfb-menu-item:hover {
                    background: #f3f6fb;
                }

                .cfb-field-menu .cfb-action-delete {
                    color: #d00;
                }

                .cfb-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 12px;
                }

                .cfb-col {
                    display: block;
                }

                /* Inputs look */
                .cfb-field label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 6px;
                    color: var(--cfb-text);
                }

                .cfb-field input[type="text"],
                .cfb-field input[type="email"],
                .cfb-field input[type="url"],
                .cfb-field input[type="number"],
                .cfb-field input[type="tel"],
                .cfb-field select,
                .cfb-field textarea {
                    width: 100%;
                    background: #fff;
                    border: 1px solid var(--cfb-border);
                    border-radius: 10px;
                    padding: 10px 12px;
                    outline: none;
                }

                .cfb-field textarea {
                    min-height: 110px;
                }

                .cfb-field input:focus,
                .cfb-field select:focus,
                .cfb-field textarea:focus {
                    border-color: var(--cfb-primary);
                    box-shadow: 0 0 0 3px rgba(47, 128, 237, .15);
                }

                /* Right options panel text */
                .cfb-muted {
                    color: var(--cfb-muted);
                }

                /* HTML Code Box */
                .cfb-html-code-box {
                    margin-top: 16px;
                    padding-top: 16px;
                    border-top: 1px solid var(--cfb-border);
                }

                #cfb-html-code {
                    width: 100%;
                    height: 150px;
                    font-family: monospace;
                    background: #f8f9fa;
                    border: 1px solid var(--cfb-border);
                    border-radius: 8px;
                    padding: 8px;
                    resize: vertical;
                }

                /* Responsive */
                @media (max-width:1200px) {
                    .cfb-grid {
                        grid-template-columns: 300px 1fr;
                    }

                    .cfb-side--right {
                        display: none;
                    }
                }

                @media (max-width:782px) {
                    .cfb-grid {
                        grid-template-columns: 1fr;
                    }

                    .cfb-side {
                        position: relative;
                        top: auto;
                        height: auto;
                    }

                    .cfb-row {
                        grid-template-columns: 1fr;
                    }
                }

                .cfb-submit-button {
                    background-color: var(--cfb-primary);
                    color: white;
                    border: none;
                    border-radius: 12px;
                    padding: 12px 24px;
                    font-weight: 700;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    box-shadow: 0 4px 8px rgba(47, 128, 237, 0.3);
                }

                .cfb-submit-button:hover,
                .cfb-submit-button:focus {
                    background-color: #1161d8;
                    /* Slightly darker blue for hover/focus */
                    outline: none;
                }

                /* Preview & Download Button Style */
                #cfb-preview,
                #cfb-download {
                    background-color: #2f80ed !important;
                    color: white !important;
                    border: none !important;
                    margin-right: 8px;
                }

                #cfb-download {
                    background-color: #28a745 !important;
                    /* Green for download */
                }

                #cfb-download:hover {
                    background-color: #218838 !important;
                }
            </style>

            <!-- SCRIPTS -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dropzone = document.getElementById('cfb-dropzone');
                    const htmlCodeBox = document.getElementById('cfb-html-code');
                    const previewBtn = document.getElementById('cfb-preview');
                    const downloadBtn = document.getElementById('cfb-download');
                    const fieldOptionsPanel = document.getElementById('cfb-field-options');

                    // Clean and format HTML for display/export
                    window.getCleanFormHTML = function() {
                        const clone = dropzone.cloneNode(true);
                        // Remove builder-only elements
                        clone.querySelectorAll('.field-actions, .cfb-field-menu').forEach(el => el.remove());
                        // Remove builder-only attributes
                        clone.querySelectorAll('[data-type]').forEach(el => el.removeAttribute('data-type'));
                        // Remove selection classes
                        clone.querySelectorAll('.cfb-selected').forEach(el => el.classList.remove('cfb-selected'));
                        return clone.outerHTML;
                    }

                    function htmlPrettyPrint(html) {
                        const voidTags = /^(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)$/i;
                        html = html.replace(/></g, '><\n<');
                        const lines = html.split('\n');
                        let indent = 0;
                        const out = [];
                        for (let i = 0; i < lines.length; i++) {
                            let line = lines[i].trim();
                            if (!line) continue;
                            if (/^<\//.test(line)) {
                                indent = Math.max(indent - 1, 0);
                            }
                            const isOpenTag = /^<([a-zA-Z0-9-]+)(\s|>|$)/.test(line) && !/^<\//.test(line) && !/\/?>$/.test(line) && !/^<!|^<\?/.test(line);
                            const tagNameMatch = line.match(/^<([a-zA-Z0-9-]+)/);
                            const isVoid = tagNameMatch ? voidTags.test(tagNameMatch[1]) : false;
                            out.push('  '.repeat(indent) + line);
                            if (isOpenTag && !isVoid) {
                                indent++;
                            }
                        }
                        return out.join('\n');
                    }
                    // Update HTML code display
                    function updateHtmlCode() {
                        if (htmlCodeBox) {
                            const clean = getCleanFormHTML();
                            htmlCodeBox.value = htmlPrettyPrint(clean);
                        }
                    }

                    // Highlight selected field
                    function clearFieldSelection() {
                        dropzone.querySelectorAll('.cfb-field').forEach(f => f.classList.remove('cfb-selected'));
                    }

                    function showFieldOptions(field) {
                        if (!fieldOptionsPanel) return;
                        // Try to get label and input name
                        let label = '';
                        let name = '';
                        // For composite fields (first/last), handle differently
                        if (field.dataset.type === 'name') {
                            // Name field (first/last)
                            const firstLabel = field.querySelector('label[for], label')?.innerText || 'First';
                            const firstInput = field.querySelector('input[name^="field_first"]');
                            const lastLabel = field.querySelector('input[name^="field_last"]')?.closest('label')?.innerText || 'Last';
                            const lastInput = field.querySelector('input[name^="field_last"]');
                            fieldOptionsPanel.innerHTML = `<div><strong>First Name</strong><br>Label: <input type="text" value="${firstLabel}"><br>Name: <input type="text" value="${firstInput ? firstInput.name : ''}"></div><div style="margin-top:10px"><strong>Last Name</strong><br>Label: <input type="text" value="${lastLabel}"><br>Name: <input type="text" value="${lastInput ? lastInput.name : ''}"></div>`;
                            return;
                        }
                        // For first/last fields (if rendered separately)
                        if (field.dataset.type === 'first' || field.dataset.type === 'last') {
                            label = field.querySelector('label')?.innerText || '';
                            name = field.querySelector('input')?.name || '';
                        } else {
                            // For other fields
                            label = field.querySelector('label')?.innerText || '';
                            name = field.querySelector('input, textarea, select')?.name || '';
                        }
                        fieldOptionsPanel.innerHTML = `<div>Label: <input type="text" value="${label}"><br>Name: <input type="text" value="${name}"></div>`;
                        fieldOptionsPanel.innerHTML = `<div style="display:flex;flex-direction:column;gap:16px;"><div><label style="font-weight:600;">Label:</label><br><input type="text" value="${label}" style="margin-bottom:8px;width:100%;"></div><div><label style="font-weight:600;">Name:</label><br><input type="text" value="${name}" style="width:100%;"></div></div>`;
                        // Get input/textarea/select for placeholder/required
                        const inputElem = field.querySelector('input, textarea, select');
                        let placeholder = '';
                        let required = false;
                        if (inputElem) {
                            placeholder = inputElem.getAttribute('placeholder') || '';
                            required = inputElem.hasAttribute('required');
                        }
                        fieldOptionsPanel.innerHTML = `<div style="display:flex;flex-direction:column;gap:16px;">
                            <div><label style="font-weight:600;">Label:</label><br><input id="cfb-label-input" type="text" value="${label}" style="margin-bottom:8px;width:100%;"></div>
                            <div><label style="font-weight:600;">Name:</label><br><input id="cfb-name-input" type="text" value="${name}" style="width:100%;"></div>
                            <div><label style="font-weight:600;">Placeholder:</label><br><input id="cfb-placeholder-input" type="text" value="${placeholder}" style="width:100%;"></div>
                            <div><label style="font-weight:600;">Required:</label> <input id="cfb-required-input" type="checkbox" ${required ? 'checked' : ''}></div>
                            <div style="display:flex;gap:8px;">
                                <button id="cfb-move-up" style="background:#2f80ed;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-weight:600;">Move Up</button>
                                <button id="cfb-move-down" style="background:#2f80ed;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-weight:600;">Move Down</button>
                            </div>
                            <button id="cfb-delete-selected" style="margin-top:16px;background:#d00;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-weight:600;">Delete Field</button>
                        </div>`;
                        // Live label update
                        const labelInput = document.getElementById('cfb-label-input');
                        if (labelInput) {
                            labelInput.addEventListener('input', function() {
                                const labelElem = field.querySelector('label');
                                if (labelElem) {
                                    if (labelElem.childNodes.length > 0 && labelElem.childNodes[0].nodeType === Node.TEXT_NODE) {
                                        labelElem.childNodes[0].nodeValue = this.value + ' ';
                                    } else {
                                        labelElem.innerHTML = this.value + ' ' + labelElem.innerHTML.replace(/^[^<]*/, '');
                                    }
                                }
                                updateHtmlCode();
                            });
                        }
                        // Live placeholder update
                        const placeholderInput = document.getElementById('cfb-placeholder-input');
                        if (placeholderInput && inputElem) {
                            placeholderInput.addEventListener('input', function() {
                                inputElem.setAttribute('placeholder', this.value);
                                updateHtmlCode();
                            });
                        }
                        // Live required update
                        const requiredInput = document.getElementById('cfb-required-input');
                        if (requiredInput && inputElem) {
                            requiredInput.addEventListener('change', function() {
                                if (this.checked) {
                                    inputElem.setAttribute('required', 'required');
                                } else {
                                    inputElem.removeAttribute('required');
                                }
                                updateHtmlCode();
                            });
                        }
                        // Move Up button handler
                        const moveUpBtn = document.getElementById('cfb-move-up');
                        if (moveUpBtn) {
                            moveUpBtn.onclick = function() {
                                const prev = field.previousElementSibling;
                                if (prev && prev.classList.contains('cfb-field')) {
                                    field.parentNode.insertBefore(field, prev);
                                    updateHtmlCode();
                                }
                            };
                        }
                        // Move Down button handler
                        const moveDownBtn = document.getElementById('cfb-move-down');
                        if (moveDownBtn) {
                            moveDownBtn.onclick = function() {
                                const next = field.nextElementSibling;
                                if (next && next.classList.contains('cfb-field')) {
                                    field.parentNode.insertBefore(next, field);
                                    updateHtmlCode();
                                }
                            };
                        }
                        // Add delete button handler
                        const deleteBtn = document.getElementById('cfb-delete-selected');
                        if (deleteBtn) {
                            deleteBtn.onclick = function() {
                                if (confirm('Are you sure you want to delete this field?')) {
                                    field.remove();
                                    fieldOptionsPanel.innerHTML = '<p class="cfb-muted">Select a field in the form to edit its options</p>';
                                    updateHtmlCode();
                                }
                            };
                        }
                    }

                    // Initialize HTML code
                    updateHtmlCode();
                    // Copy HTML button
                    const copyBtn = document.getElementById('cfb-copy-html');
                    if (copyBtn && htmlCodeBox) {
                        copyBtn.addEventListener('click', function() {
                            // Try new Clipboard API first
                            const text = htmlCodeBox.value;
                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(text).then(() => {
                                    copyBtn.textContent = 'Copied';
                                    setTimeout(() => copyBtn.textContent = 'Copy', 1200);
                                }).catch(() => {
                                    htmlCodeBox.focus();
                                    htmlCodeBox.select();
                                    document.execCommand('copy');
                                    copyBtn.textContent = 'Copied';
                                    setTimeout(() => copyBtn.textContent = 'Copy', 1200);
                                });
                            } else {
                                htmlCodeBox.focus();
                                htmlCodeBox.select();
                                document.execCommand('copy');
                                copyBtn.textContent = 'Copied';
                                setTimeout(() => copyBtn.textContent = 'Copy', 1200);
                            }
                        });
                    }

                    // 1) Drag-and-drop from left panel to form layout
                    document.querySelectorAll('.cfb-field-list li').forEach(li => {
                        li.setAttribute('draggable', 'true');
                        li.addEventListener('dragstart', function(e) {
                            e.dataTransfer.setData('text/plain', li.getAttribute('data-template'));
                            e.dataTransfer.effectAllowed = 'copy';
                        });
                        // Still allow click to add at bottom for accessibility
                        const addToForm = () => {
                            const tpl = li.getAttribute('data-template');
                            if (!tpl) return;
                            const wrapper = document.createElement('div');
                            wrapper.innerHTML = tpl.trim();
                            const node = wrapper.firstElementChild;
                            dropzone.appendChild(node);
                            updateHtmlCode();
                            node.addEventListener('click', function(e) {
                                clearFieldSelection();
                                node.classList.add('cfb-selected');
                                showFieldOptions(node);
                            });
                        };
                        li.addEventListener('click', addToForm);
                        li.addEventListener('keypress', e => {
                            if (e.key === 'Enter') addToForm();
                        });
                    });

                    // Drag-over indicator
                    let dragOverElem = null;

                    function showDragOverIndicator(target) {
                        if (dragOverElem) dragOverElem.remove();
                        dragOverElem = document.createElement('div');
                        dragOverElem.style.height = '0';
                        dragOverElem.style.borderTop = '2px dashed #2f80ed';
                        dragOverElem.style.margin = '4px 0';
                        target.parentNode.insertBefore(dragOverElem, target);
                    }

                    function removeDragOverIndicator() {
                        if (dragOverElem) dragOverElem.remove();
                        dragOverElem = null;
                    }

                    // Make dropzone accept drops at any position
                    dropzone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'copy';
                        // Find closest .cfb-field under mouse
                        let afterElem = null;
                        const mouseY = e.clientY;
                        Array.from(dropzone.querySelectorAll('.cfb-field')).forEach(field => {
                            const rect = field.getBoundingClientRect();
                            if (mouseY < rect.top + rect.height / 2) {
                                if (!afterElem || rect.top < afterElem.getBoundingClientRect().top) {
                                    afterElem = field;
                                }
                            }
                        });
                        if (afterElem) {
                            showDragOverIndicator(afterElem);
                        } else {
                            removeDragOverIndicator();
                        }
                    });

                    dropzone.addEventListener('dragleave', function(e) {
                        removeDragOverIndicator();
                    });

                    dropzone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        const tpl = e.dataTransfer.getData('text/plain');
                        if (!tpl) return;
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = tpl.trim();
                        const node = wrapper.firstElementChild;
                        // Find where to insert
                        if (dragOverElem && dragOverElem.nextSibling) {
                            dropzone.insertBefore(node, dragOverElem.nextSibling);
                        } else {
                            dropzone.appendChild(node);
                        }
                        removeDragOverIndicator();
                        // Add click handler for field options
                        node.addEventListener('click', function(e) {
                            clearFieldSelection();
                            node.classList.add('cfb-selected');
                            showFieldOptions(node);
                        });
                        // Ensure HTML code updates immediately after drop
                        updateHtmlCode();
                    });

                    // 1b) Add click handler to all initial fields
                    dropzone.querySelectorAll('.cfb-field').forEach(field => {
                        field.addEventListener('click', function(e) {
                            clearFieldSelection();
                            field.classList.add('cfb-selected');
                            showFieldOptions(field);
                        });
                    });

                    // 2) Field actions menu (Delete / Duplicate)
                    function cfbCloseAllMenus() {
                        document.querySelectorAll('.cfb-field-menu').forEach(menu => menu.remove());
                    }
                    dropzone.addEventListener('click', function(e) {
                        const menuBtn = e.target.closest('.cfb-field-delete');
                        const isMenuItem = e.target.closest('.cfb-menu-item');
                        // Toggle menu on three-dots button
                        if (menuBtn) {
                            e.stopPropagation();
                            const actions = menuBtn.parentElement;
                            // Close other menus first
                            cfbCloseAllMenus();
                            // Toggle this menu
                            let menu = actions.querySelector('.cfb-field-menu');
                            if (menu) {
                                menu.remove();
                            } else {
                                menu = document.createElement('div');
                                menu.className = 'cfb-field-menu';
                                menu.innerHTML = '<button type="button" class="cfb-menu-item cfb-action-duplicate">Duplicate</button><button type="button" class="cfb-menu-item cfb-action-delete">Delete</button>';
                                actions.appendChild(menu);
                            }
                            return;
                        }
                        // Handle Delete action
                        if (e.target.closest('.cfb-action-delete')) {
                            e.stopPropagation();
                            const field = e.target.closest('.cfb-field');
                            if (field && confirm('Are you sure you want to delete this field?')) {
                                field.remove();
                                cfbCloseAllMenus();
                                updateHtmlCode();
                                fieldOptionsPanel.innerHTML = '<p class="cfb-muted">Select a field in the form to edit its options</p>';
                            } else {
                                cfbCloseAllMenus();
                            }
                            return;
                        }
                        // Handle Duplicate action
                        if (e.target.closest('.cfb-action-duplicate')) {
                            e.stopPropagation();
                            const field = e.target.closest('.cfb-field');
                            if (field) {
                                const clone = field.cloneNode(true);
                                clone.classList.remove('cfb-selected');
                                const menuInClone = clone.querySelector('.cfb-field-menu');
                                if (menuInClone) menuInClone.remove();
                                field.parentNode.insertBefore(clone, field.nextElementSibling);
                                // Attach click handler for options on the clone
                                clone.addEventListener('click', function(ev) {
                                    clearFieldSelection();
                                    clone.classList.add('cfb-selected');
                                    showFieldOptions(clone);
                                });
                                clearFieldSelection();
                                clone.classList.add('cfb-selected');
                                showFieldOptions(clone);
                                cfbCloseAllMenus();
                                updateHtmlCode();
                            }
                            return;
                        }
                        // Click outside menu closes it
                        if (!isMenuItem) {
                            cfbCloseAllMenus();
                        }
                    });
                    // Global click to close menus when clicking outside of fields
                    document.addEventListener('click', function(e) {
                        if (!e.target.closest('.cfb-field-menu') && !e.target.closest('.cfb-field-delete')) {
                            document.querySelectorAll('.cfb-field-menu').forEach(menu => menu.remove());
                        }
                    });

                    // 3) Search filter
                    const search = document.querySelector('.cfb-search');
                    if (search) {
                        search.addEventListener('input', function() {
                            const q = this.value.toLowerCase();
                            document.querySelectorAll('.cfb-field-list li').forEach(li => {
                                const text = li.innerText.toLowerCase();
                                li.style.display = text.includes(q) ? '' : 'none';
                            });
                        });
                    }

                    // 4) Tabs toggle (only left side now)
                    const leftTabs = document.querySelectorAll('.cfb-tab-btn');
                    const panes = {
                        fields: document.getElementById('cfb-tab-fields'),
                        options: document.getElementById('cfb-tab-options'),
                    };

                    leftTabs.forEach(btn => {
                        btn.addEventListener('click', () => {
                            leftTabs.forEach(b => b.classList.remove('is-active'));
                            btn.classList.add('is-active');

                            // Show/Hide panes
                            Object.values(panes).forEach(p => p.classList.remove('is-active'));
                            panes[btn.dataset.tab].classList.add('is-active');
                        });
                    });

                    // 5) Preview Button - Opens form in new tab
                    if (previewBtn) {
                        previewBtn.addEventListener('click', function() {
                            const formHTML = getCleanFormHTML();
                            const styleElements = Array.from(document.querySelectorAll('style')).map(el => el.outerHTML).join('');

                            const previewHTML = `
                                <!DOCTYPE html>
                                <html lang="en">
                                <head>
                                    <meta charset="UTF-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <title>Form Preview</title>
                                    ${styleElements}
                                   <style>
                                       body { margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
                                      .cfb-wrapper { max-width: 800px; margin: 0 auto; }
                                       .cfb-header { display: none; }
                                       .cfb-grid { grid-template-columns: 1fr; }
                                      .cfb-side { display: none; }
                                                                  .cfb-canvas { border: none; box-shadow: none; }
                                        .cfb-field { margin-bottom: 16px; }
                                        .field-actions { display: none; }
                                      .cfb-submit-button { width: auto; }
                                  </style>
                                </head>
                                <body>
                                    <div class="cfb-wrapper">
                                        ${formHTML}
                                    </div>
                                </body>
                                </html>
                            `.trim();

                            const previewWindow = window.open('', '_blank');
                            if (previewWindow) {
                                previewWindow.document.write(previewHTML);
                                previewWindow.document.close();
                            } else {
                                alert('Popup blocked. Please allow popups for this site.');
                            }
                        });
                    }

                    // 6) Download Button - Saves form as .html file
                    if (downloadBtn) {
                        downloadBtn.addEventListener('click', function() {
                            const formHTML = getCleanFormHTML();
                            const styleElements = Array.from(document.querySelectorAll('style')).map(el => el.outerHTML).join('');

                            const downloadHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Form</title>
    ${styleElements}
    <style>
        /* Reset for clean download */
        body { margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        .cfb-wrapper { max-width: 800px; margin: 0 auto; }
        .cfb-header { display: none; }
        .cfb-grid { grid-template-columns: 1fr; }
        .cfb-side { display: none; }
        .cfb-canvas { border: none; box-shadow: none; }
        .cfb-field { margin-bottom: 16px; }
        .field-actions { display: none; }
        .cfb-submit-button { width: auto; }
    </style>
</head>
<body>
    <div class="cfb-wrapper">
        ${formHTML}
    </div>
</body>
</html>
                            `.trim();

                            // Create blob and trigger download
                            const blob = new Blob([downloadHTML], {
                                type: 'text/html'
                            });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'my-form.html'; // Default filename
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);

                            // Optional: Show feedback
                            alert('Form downloaded as my-form.html!');
                        });
                    }
                });
                document.addEventListener('DOMContentLoaded', function() {
                    const saveBtn = document.getElementById('cfb-save');
                    if (!saveBtn) return;

                    saveBtn.addEventListener('click', function() {
                        // Clean HTML le lo
                        const formHTML = getCleanFormHTML();

                        // Title input field se le lo (agar hai)
                        const titleInput = document.getElementById('cfb-form-title');
                        const title = titleInput ? titleInput.value : 'Untitled Form';

                        // AJAX bhejna
                        jQuery.ajax({
                            url: cfb_vars.ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'cfb_save_form',
                                nonce: cfb_vars.nonce,
                                title: title,
                                fields: formHTML
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('✅ ' + response.data.message + '\nShortcode: ' + response.data.shortcode);
                                } else {
                                    alert('❌ Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                                }
                            },
                            error: function(xhr) {
                                alert('❌ AJAX error: ' + xhr.statusText);
                            }
                        });
                    });
                });
            </script>
        </div>
<?php
    }
}
