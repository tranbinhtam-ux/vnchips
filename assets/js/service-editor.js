/**
 * Shared editor for Design Service / IP Service item bodies.
 *
 * The database keeps interactive keywords as [[keyword]] (or [[label|keyword]]) markers.
 * Inside CKEditor an editor never sees those brackets: every marker is shown as an orange
 * underlined <span class="tip-marker" data-tip="keyword"> exactly like on the public page,
 * and is turned back into a marker when the body is read out for saving.
 *
 * Used by chip-admin/service-edit.php and by the frontend Live Edit modal (admin_bar.php).
 */
(function (window, document) {
  'use strict';

  var MARKER_RE = /\[\[\s*([^\[\]|]+?)\s*(?:\|\s*([^\[\]|]+?)\s*)?\]\]/g;
  var CK_URL = 'https://cdn.ckeditor.com/4.22.1/full/ckeditor.js';

  function escAttr(s) {
    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function normalise(k) {
    return String(k || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  /** [[label|key]] text markers  ->  <span class="tip-marker" data-tip="key">label</span> */
  function markersToSpans(html) {
    return String(html || '').replace(MARKER_RE, function (m, label, key) {
      label = label.trim();
      key = (key || label).trim();
      // label/key are already HTML text (entities intact); only quotes need care in the attribute
      return '<span class="tip-marker" data-tip="' + key.replace(/"/g, '&quot;') + '">' + label + '</span>';
    });
  }

  /** <span class="tip-marker">  ->  [[label]] / [[label|key]] */
  function spansToMarkers(html) {
    var doc = new DOMParser().parseFromString('<body>' + (html || '') + '</body>', 'text/html');
    var spans = doc.body.querySelectorAll('span.tip-marker');
    for (var i = 0; i < spans.length; i++) {
      var span = spans[i];
      var label = (span.textContent || '').replace(/\s+/g, ' ').trim();
      var key = (span.getAttribute('data-tip') || label).replace(/\s+/g, ' ').trim();
      if (!label) { span.parentNode.removeChild(span); continue; }
      var marker = normalise(label) === normalise(key) ? '[[' + label + ']]' : '[[' + label + '|' + key + ']]';
      span.parentNode.replaceChild(doc.createTextNode(marker), span);
    }
    return doc.body.innerHTML;
  }

  /** Distinct keywords referenced by an editor body (span form) in document order */
  function keywordsInSpans(html) {
    var doc = new DOMParser().parseFromString('<body>' + (html || '') + '</body>', 'text/html');
    var out = [], seen = {};
    var spans = doc.body.querySelectorAll('span.tip-marker');
    for (var i = 0; i < spans.length; i++) {
      var key = (spans[i].getAttribute('data-tip') || spans[i].textContent || '').replace(/\s+/g, ' ').trim();
      var n = normalise(key);
      if (!key || seen[n]) continue;
      seen[n] = true;
      out.push(key);
    }
    return out;
  }

  /** Load CKEditor 4 from the CDN once, then call cb */
  function loadCKEditor(cb) {
    if (window.CKEDITOR) { cb(); return; }
    if (loadCKEditor._waiting) { loadCKEditor._waiting.push(cb); return; }
    loadCKEditor._waiting = [cb];
    var s = document.createElement('script');
    s.src = CK_URL;
    s.onload = function () {
      var list = loadCKEditor._waiting || [];
      loadCKEditor._waiting = null;
      list.forEach(function (fn) { fn(); });
    };
    s.onerror = function () { alert('Không tải được trình soạn thảo (CKEditor). Kiểm tra kết nối mạng.'); };
    document.head.appendChild(s);
  }

  /**
   * Create an editor on a textarea.
   * opts: { height, baseHref, contentsCss: [], baseFloatZIndex, onChange(keywords), onCaret(keyword|null) }
   * Returns a controller; call .ready(fn) before using the editor.
   */
  function create(textareaId, opts) {
    opts = opts || {};
    var ctl = {
      editor: null,
      _readyCbs: [],
      ready: function (fn) { if (ctl.editor && ctl._isReady) fn(ctl); else ctl._readyCbs.push(fn); },
      /** body with [[markers]] (what gets saved) */
      getBody: function () { return ctl.editor ? spansToMarkers(ctl.editor.getData()) : ''; },
      /** load a body with [[markers]] */
      setBody: function (bodyWithMarkers) { if (ctl.editor) ctl.editor.setData(markersToSpans(bodyWithMarkers || '')); },
      /** keywords referenced in the current body */
      getKeywords: function () { return ctl.editor ? keywordsInSpans(ctl.editor.getData()) : []; },
      /** selected text (plain) */
      getSelectedText: function () {
        if (!ctl.editor) return '';
        var sel = ctl.editor.getSelection();
        return sel ? String(sel.getSelectedText() || '').replace(/\s+/g, ' ').trim() : '';
      },
      /** tip-marker span around the caret, if any */
      currentMarker: function () {
        if (!ctl.editor) return null;
        var sel = ctl.editor.getSelection();
        var el = sel && sel.getStartElement();
        if (!el) return null;
        var span = el.getAscendant(function (n) {
          return n.type === CKEDITOR.NODE_ELEMENT && n.is && n.is('span') && n.hasClass('tip-marker');
        }, true);
        return span || null;
      },
      /** wrap the selection as a keyword; returns the keyword or '' */
      wrapSelection: function (keyword) {
        if (!ctl.editor) return '';
        var text = ctl.getSelectedText();
        if (!text) return '';
        keyword = (keyword || text).replace(/\s+/g, ' ').trim();
        var style = new CKEDITOR.style({ element: 'span', attributes: { 'class': 'tip-marker', 'data-tip': keyword } });
        ctl.editor.focus();
        ctl.editor.applyStyle(style);
        ctl.editor.fire('change');
        return keyword;
      },
      /** insert a keyword as a new span at the caret (or wrap the selection) */
      insertKeyword: function (keyword) {
        if (!ctl.editor) return;
        if (ctl.getSelectedText()) { ctl.wrapSelection(keyword); return; }
        ctl.editor.focus();
        ctl.editor.insertHtml('<span class="tip-marker" data-tip="' + escAttr(keyword) + '">' + escHtml(keyword) + '</span>&nbsp;');
        ctl.editor.fire('change');
      },
      /** remove the keyword wrapper around the caret, keeping its text */
      unwrapCurrent: function () {
        var span = ctl.currentMarker();
        if (!span) return false;
        span.remove(true);
        ctl.editor.fire('change');
        return true;
      },
      destroy: function () { if (ctl.editor) { ctl.editor.destroy(true); ctl.editor = null; } }
    };

    loadCKEditor(function () {
      CKEDITOR.config.versionCheck = false;
      if (opts.baseHref) CKEDITOR.config.baseHref = opts.baseHref;
      var cfg = {
        height: opts.height || 380,
        allowedContent: true,            // keep <p class="spec-paragraph"> and our spans untouched
        removeButtons: 'Save,NewPage,ExportPdf,Preview,Print,Flash,Iframe,Smiley,PageBreak,Language,BidiLtr,BidiRtl,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,About,Scayt',
        extraPlugins: opts.extraPlugins || 'justify',
        baseHref: opts.baseHref || undefined,
        contentsCss: opts.contentsCss || [],
        baseFloatZIndex: opts.baseFloatZIndex || 10000,
        disableNativeSpellChecker: false,
        startupFocus: false
      };
      if (opts.toolbar) cfg.toolbar = opts.toolbar;
      if (opts.inline) {
        // tooltip panels must stay inline HTML (no <p>): Enter inserts <br>, no auto paragraphs
        cfg.enterMode = CKEDITOR.ENTER_BR;
        cfg.shiftEnterMode = CKEDITOR.ENTER_BR;
        cfg.autoParagraph = false;
      }
      var ed = CKEDITOR.replace(textareaId, cfg);
      ctl.editor = ed;
      ed.on('instanceReady', function () {
        ctl._isReady = true;
        var cbs = ctl._readyCbs; ctl._readyCbs = [];
        cbs.forEach(function (fn) { fn(ctl); });
      });
      if (typeof opts.onChange === 'function') {
        var t = null;
        ed.on('change', function () { clearTimeout(t); t = setTimeout(function () { opts.onChange(ctl.getKeywords()); }, 250); });
      }
      if (typeof opts.onCaret === 'function') {
        ed.on('selectionChange', function () {
          var span = ctl.currentMarker();
          opts.onCaret(span ? (span.getAttribute('data-tip') || span.getText()) : null, ctl.getSelectedText());
        });
      }
    });

    return ctl;
  }

  /**
   * Draw the "keywords in this item" panel.
   * keywords: ['IP integration', ...]; dict: { normalisedKeyword: {id,keyword,title} }
   * onEdit(keyword, row|null) is called when a chip's button is pressed.
   */
  function renderKeywordList(container, keywords, dict, onEdit) {
    if (!container) return;
    if (!keywords.length) {
      container.innerHTML = '<span class="svc-kw-empty">Chưa có từ khoá nào trong mục này. Bôi đen một cụm từ trong nội dung rồi bấm <b>Gắn tooltip</b>.</span>';
      return;
    }
    container.innerHTML = '';
    keywords.forEach(function (kw) {
      var row = dict[normalise(kw)] || null;
      var chip = document.createElement('div');
      chip.className = 'svc-kw-chip' + (row ? ' has' : ' missing');
      chip.innerHTML =
        '<span class="svc-kw-dot"></span>' +
        '<span class="svc-kw-name">' + escHtml(kw) + '</span>' +
        '<span class="svc-kw-state">' + (row ? 'có giải thích' : 'chưa có giải thích') + '</span>' +
        '<button type="button" class="svc-kw-btn">' + (row ? '<i class="fa-solid fa-pen"></i> Sửa' : '<i class="fa-solid fa-plus"></i> Viết') + '</button>';
      chip.querySelector('.svc-kw-btn').addEventListener('click', function (e) {
        e.preventDefault();
        onEdit(kw, row);
      });
      container.appendChild(chip);
    });
  }

  function dictToMap(list) {
    var map = {};
    (list || []).forEach(function (r) { map[normalise(r.keyword)] = r; });
    return map;
  }

  window.VNServiceEditor = {
    markersToSpans: markersToSpans,
    spansToMarkers: spansToMarkers,
    keywordsInSpans: keywordsInSpans,
    loadCKEditor: loadCKEditor,
    create: create,
    renderKeywordList: renderKeywordList,
    dictToMap: dictToMap,
    normalise: normalise,
    escHtml: escHtml
  };
})(window, document);
