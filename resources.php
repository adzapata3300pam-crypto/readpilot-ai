<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Resources</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
(function () {
  try {
    var savedTheme = localStorage.getItem('readpilot-theme');
    if (savedTheme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  } catch (e) {
    /* localStorage unavailable (e.g. private browsing) — default to light */
  }
 
  try {
    var savedSidebar = localStorage.getItem('readpilot-sidebar');
    if (savedSidebar === 'collapsed') {
      document.documentElement.setAttribute('data-sidebar', 'collapsed');
    }
  } catch (e) {
    /* localStorage unavailable — default to expanded */
  }
})();
</script>
<style>
  /* ================= Resources — page-specific styles ================= */
  /* Reuses shared tokens + components from style.css: .tabs/.tab, .sort-select,
     .overlay/.modal/.modal-stats/.m-stat, .btn-primary/.btn-secondary, .toast */

  .res-controls{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
  .res-search{
    display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--border);
    border-radius:12px;padding:10px 16px;width:280px;box-shadow:var(--shadow);
  }
  .res-search svg{width:15px;height:15px;color:var(--muted);flex-shrink:0;}
  .res-search input{border:none;outline:none;background:transparent;font-family:inherit;font-size:13.5px;color:var(--ink);width:100%;}
  .res-search input::placeholder{color:var(--muted);}
  .res-filters{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}

  /* ---------- Book grid ---------- */
  .book-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));gap:18px;}
  .book-card{
    min-height:220px;height:auto;
    background:var(--card);border-radius:16px;box-shadow:var(--shadow);overflow:hidden;
    cursor:pointer;display:flex;flex-direction:column;
    transition:transform .12s steps(2), box-shadow .12s ease;
  }
  .book-card:hover{transform:translate(-2px,-3px);box-shadow:0 12px 28px rgba(30,60,40,0.14);}
  .book-spine{height:64px;flex-shrink:0;position:relative;display:flex;align-items:flex-start;justify-content:space-between;padding:12px 14px;}
  .book-spine .bx{font-size:22px;color:rgba(255,255,255,0.9);}
  .book-level{
    font-size:10px;font-weight:800;color:#fff;background:rgba(0,0,0,0.18);
    padding:3px 8px;border-radius:20px;white-space:nowrap;
  }
  .book-body{padding:14px 16px 16px;display:flex;flex-direction:column;gap:8px;flex:1 0 auto;min-height:0;}
  .book-title{font-family:'Poppins',sans-serif;font-size:14.5px;font-weight:700;color:#16281d;line-height:1.3;min-height:2.6em;overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;}
  .book-author{font-size:12px;color:var(--muted);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .book-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:2px;}
  .book-genre{font-size:10.5px;font-weight:800;padding:4px 9px;border-radius:20px;}
  .book-lexile{font-size:10.5px;font-weight:700;color:var(--muted);}
  .difficulty-pill{display:inline-flex;align-items:center;font-size:9.5px;font-weight:800;padding:3px 8px;border-radius:20px;white-space:nowrap;}
  .difficulty-pill.beginner{background:var(--green-light);color:var(--green-dark);}
  .difficulty-pill.intermediate{background:var(--orange-light);color:var(--orange);}
  .difficulty-pill.advanced{background:var(--red-light);color:var(--red);}
  .book-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;border-top:1px solid var(--border);padding-top:10px;margin-top:auto;}
  .book-words{font-size:11px;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:4px;}
  .book-words .bx{font-size:13px;}
  .book-foot-actions{display:flex;align-items:center;gap:6px;flex-shrink:0;}
  .book-quiz-btn{
    width:28px;height:28px;border-radius:50%;border:none;background:var(--bg);
    color:var(--green-dark);display:flex;align-items:center;justify-content:center;
    cursor:pointer;flex-shrink:0;font-family:inherit;position:relative;
  }
  .book-quiz-btn .bx{font-size:15px;}
  .book-quiz-btn:hover{background:var(--green-light);}
  .book-quiz-btn.has-custom::after{
    content:'';position:absolute;top:-1px;right:-1px;width:8px;height:8px;border-radius:50%;
    background:var(--orange);border:2px solid var(--card);
  }
  .book-assign-btn{
    font-size:11.5px;font-weight:800;color:var(--green-dark);background:var(--green-light);
    border:none;border-radius:20px;padding:6px 12px;cursor:pointer;font-family:inherit;
    display:flex;align-items:center;gap:4px;
  }
  .book-assign-btn:hover{background:var(--sidebar-active);}
  .book-assign-btn.assigned{background:var(--green);color:#fff;}
  .resource-delete-btn{color:var(--red);}
  .resource-delete-btn:hover{background:var(--red-light);color:var(--red);}
  .resource-delete-dialog{
    width:min(420px,calc(100% - 32px));padding:22px;border:1px solid var(--border);
    border-radius:16px;background:var(--card);color:var(--ink);box-shadow:var(--shadow);
  }
  .resource-delete-dialog::backdrop{background:rgba(0,0,0,.55);}
  .resource-delete-dialog h2{margin:0 0 8px;font-family:'Poppins',sans-serif;font-size:17px;}
  .resource-delete-dialog p{margin:0;color:var(--muted);font-size:13px;line-height:1.5;}
  .resource-delete-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px;}
  .resource-delete-actions button{padding:9px 13px;}
  .resource-delete-confirm{background:var(--red);color:#fff;border:0;border-radius:12px;font:700 13px 'Nunito',sans-serif;cursor:pointer;}
  .resource-delete-confirm:hover{filter:brightness(.95);}
  .resource-delete-confirm:disabled{opacity:.6;cursor:wait;}

  /* ---------- "No quiz created yet" pop-up ---------- */
  .no-quiz-dialog{
    width:min(420px,calc(100% - 32px));padding:28px 24px 22px;border:1px solid var(--border);
    border-radius:20px;background:var(--card);color:var(--ink);box-shadow:var(--shadow);text-align:center;
  }
  .no-quiz-dialog::backdrop{background:rgba(0,0,0,.55);}
  .no-quiz-dialog[open]{animation:resModalIn .28s cubic-bezier(.2,.9,.3,1.1) both;}
  .no-quiz-dialog-icon{
    width:58px;height:58px;border-radius:50%;margin:0 auto 14px;
    background:var(--orange-light);color:var(--orange);
    display:flex;align-items:center;justify-content:center;
  }
  .no-quiz-dialog-icon .bx{font-size:28px;}
  .no-quiz-dialog h2{margin:0 0 8px;font-family:'Poppins',sans-serif;font-size:18px;}
  .no-quiz-dialog p{margin:0;color:var(--muted);font-size:13.5px;font-weight:600;line-height:1.6;}
  .no-quiz-dialog-actions{display:flex;justify-content:center;gap:10px;margin-top:20px;}
  .no-quiz-dialog-actions button{padding:10px 18px;font-size:13px;}
  .no-quiz-dialog-actions .btn-primary{flex:none;}

  .empty-grid{grid-column:1/-1;background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:50px 20px;text-align:center;color:var(--muted);}
  .empty-grid .bx{font-size:34px;color:var(--green);margin-bottom:10px;display:block;}
  .empty-grid b{color:#16281d;display:block;font-size:15px;margin-bottom:4px;}

  /* ---------- Upload tab ---------- */
  .dropzone{
    border:2px dashed var(--border);border-radius:18px;background:#fff;
    padding:46px 20px;text-align:center;cursor:pointer;
    transition:border-color .15s ease, background .15s ease;
  }
  .dropzone.dragover{border-color:var(--green);background:var(--green-light);}
  .dropzone-icon{
    width:56px;height:56px;border-radius:50%;background:var(--green-light);color:var(--green-dark);
    display:flex;align-items:center;justify-content:center;margin:0 auto 14px auto;
  }
  .dropzone-icon .bx{font-size:26px;}
  .dropzone-title{font-family:'Poppins',sans-serif;font-size:15.5px;font-weight:700;color:#16281d;margin-bottom:4px;}
  .dropzone-sub{font-size:12.5px;color:var(--muted);font-weight:600;}
  .dropzone-sub b{color:var(--green-dark);text-decoration:underline;cursor:pointer;}
  .dropzone-types{font-size:11px;color:var(--muted);font-weight:600;margin-top:12px;}

  .file-list{margin-top:20px;display:flex;flex-direction:column;gap:10px;}
  .file-row{
    display:flex;align-items:center;gap:14px;background:var(--card);border-radius:14px;
    box-shadow:var(--shadow);padding:13px 16px;
  }
  .file-icon{
    width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    flex-shrink:0;color:#fff;
  }
  .file-icon .bx{font-size:18px;}
  .file-info{flex-grow:1;min-width:0;}
  .file-name{font-size:13.5px;font-weight:700;color:#16281d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .file-sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:1px;}
  .file-status{
    font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;white-space:nowrap;flex-shrink:0;
  }
  .file-status.processing{background:var(--orange-light);color:var(--orange);}
  .file-status.ready{background:var(--green-light);color:var(--green-dark);}
  .file-status.error{background:var(--red-light);color:var(--red);}
  .file-add-btn{
    font-size:11.5px;font-weight:800;color:#fff;background:var(--green);border:none;border-radius:20px;
    padding:7px 13px;cursor:pointer;font-family:inherit;flex-shrink:0;
  }
  .file-add-btn:disabled{background:var(--border);color:var(--muted);cursor:not-allowed;}
  .file-remove{
    width:26px;height:26px;border-radius:8px;background:var(--bg);border:none;color:var(--muted);
    display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;
  }
  .file-remove:hover{background:var(--red-light);color:var(--red);}

  /* ---------- URL tab ---------- */
  .url-form,.url-added-list{display:none!important;}
  .url-form{background:var(--card);border-radius:16px;box-shadow:var(--shadow);padding:22px;}
  .url-form-row{display:flex;gap:10px;flex-wrap:wrap;}
  .url-input-wrap{
    flex-grow:1;min-width:220px;display:flex;align-items:center;gap:8px;
    border:1px solid var(--border);border-radius:12px;padding:12px 16px;background:var(--bg);
  }
  .url-input-wrap svg{width:15px;height:15px;color:var(--muted);flex-shrink:0;}
  .url-input-wrap input{border:none;outline:none;background:transparent;font-family:inherit;font-size:13.5px;color:var(--ink);width:100%;}
  .url-fetch-btn{
    background:var(--green);color:#fff;border:none;border-radius:12px;padding:12px 22px;
    font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap;
    display:flex;align-items:center;gap:8px;
  }
  .url-fetch-btn:disabled{background:var(--border);color:var(--muted);cursor:not-allowed;}
  .url-hint{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:10px;}

  .url-preview{
    margin-top:18px;background:var(--green-light);border-radius:16px;padding:18px 20px;
    display:none;gap:16px;
  }
  .url-preview.show{display:flex;}
  .url-preview-icon{
    width:46px;height:46px;border-radius:12px;background:#fff;color:var(--green-dark);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .url-preview-icon .bx{font-size:22px;}
  .url-preview-body{flex-grow:1;min-width:0;}
  .url-preview-title{font-family:'Poppins',sans-serif;font-size:14.5px;font-weight:700;color:#16281d;}
  .url-preview-source{font-size:11.5px;color:var(--green-dark);font-weight:700;margin-top:1px;word-break:break-all;}
  .url-preview-meta{font-size:12px;color:#3d5a48;font-weight:600;margin-top:8px;}
  .url-preview-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;}

  .url-added-list{margin-top:20px;display:flex;flex-direction:column;gap:10px;}

  .spinner{
    width:14px;height:14px;border-radius:50%;border:2px solid rgba(255,255,255,0.4);
    border-top-color:#fff;animation:spin .7s linear infinite;
  }
  @keyframes spin{to{transform:rotate(360deg);}}

  .tab-panel{display:none;}
  .tab-panel.active{display:block;}

  /* ---------- Resource detail modal (child-friendly) ---------- */
  .res-modal{
    text-align:center;
    overflow:visible;
    padding:0 26px 26px 26px;
  }
  .res-modal .modal-close{
    background:rgba(255,255,255,0.85);
    backdrop-filter:blur(2px);
    z-index:2;
  }
  html[data-theme="dark"] .res-modal .modal-close{background:rgba(22,34,27,0.8);}

  /* Illustrated cover banner — a small themed scene generated per story,
     sits flush with the modal's rounded top corners since the modal now
     has zero top padding (padding lives on .res-modal instead). */
  .res-cover-banner{
    position:relative;
    width:calc(100% + 52px);
    margin:0 -26px 0 -26px;
    height:150px;
    border-radius:20px 20px 0 0;
    overflow:hidden;
    display:block;
  }
  .res-cover-banner svg{width:100%;height:100%;display:block;}

  .res-modal .modal-head{
    flex-direction:column;
    text-align:center;
    padding-right:0;
    gap:6px;
    margin-top:-30px;
  }
  .res-modal-icon{
    width:64px;height:64px;border-radius:20px;color:#fff;display:flex;align-items:center;justify-content:center;
    flex-shrink:0;margin:0 auto 10px auto;
    border:4px solid var(--card);
    box-shadow:0 8px 18px rgba(30,60,40,0.22);
    animation:resIconPop .5s cubic-bezier(.34,1.56,.64,1) both;
  }
  .res-modal-icon .bx{font-size:28px;}
  .res-modal .modal-head h2{font-size:21px;margin-top:10px;}
  .res-modal .modal-head .sub{font-size:13px;}

  @keyframes resIconPop{
    0%{transform:scale(0) rotate(-15deg);opacity:0;}
    60%{transform:scale(1.12) rotate(6deg);opacity:1;}
    100%{transform:scale(1) rotate(0deg);}
  }
  @keyframes resModalIn{
    0%{transform:translateY(18px) scale(.96);opacity:0;}
    100%{transform:translateY(0) scale(1);opacity:1;}
  }
  .res-modal{animation:resModalIn .32s cubic-bezier(.2,.9,.3,1.1) both;}

  .res-tag-row{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:16px;justify-content:center;}
  .res-tag{font-size:11px;font-weight:700;padding:5px 11px;border-radius:20px;background:var(--bg);color:var(--ink);}
  .res-desc{font-size:13.5px;color:#3d5a48;font-weight:600;line-height:1.7;margin-bottom:4px;text-align:center;}
  html[data-theme="dark"] .res-desc{color:#bcd6c5;}

  .res-modal .modal-stats{grid-template-columns:repeat(4,1fr);}
  .res-modal .m-stat{border-radius:16px;}

  /* Playful "Start Reading" button */
  .start-reading-btn{
    position:relative;overflow:hidden;
    font-size:14.5px !important;
    padding:13px 18px !important;
    border-radius:16px !important;
    background:linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%) !important;
    box-shadow:0 8px 18px rgba(111,191,90,0.38);
  }
  .start-reading-btn svg{width:16px;height:16px;}
  .start-reading-btn .bx{font-size:17px;}
  .start-reading-btn:hover{
    transform:translateY(-2px) scale(1.02);
    box-shadow:0 12px 22px rgba(111,191,90,0.46);
  }
  .start-reading-btn:active{transform:translateY(0) scale(.98);}
  .start-reading-btn .sr-icon{
    display:inline-flex;
    animation:srWiggle 1.8s ease-in-out infinite;
  }
  @keyframes srWiggle{
    0%,100%{transform:rotate(0deg) translateX(0);}
    50%{transform:rotate(-8deg) translateX(-1px);}
  }

  /* Little celebratory sparkles that pop when the modal opens */
  .res-sparkle{
    position:absolute;pointer-events:none;
    font-size:16px;opacity:0;
    animation:resSparkle 1.1s ease-out forwards;
  }
  @keyframes resSparkle{
    0%{opacity:0;transform:scale(.2) translateY(0) rotate(0deg);}
    25%{opacity:1;}
    100%{opacity:0;transform:scale(1.1) translateY(-26px) rotate(50deg);}
  }

  .empty-grid .bx{font-size:34px;color:var(--green);margin-bottom:10px;display:block;}
  .empty-grid b{color:#16281d;display:block;font-size:15px;margin-bottom:4px;}

  /* Dark mode overrides for local light-mode defaults. */
  html[data-theme="dark"] .res-search,
  html[data-theme="dark"] .dropzone,
  html[data-theme="dark"] .url-preview-icon{background:var(--card);color:var(--ink);}
  html[data-theme="dark"] .book-title,
  html[data-theme="dark"] .empty-grid b,
  html[data-theme="dark"] .dropzone-title,
  html[data-theme="dark"] .file-name,
  html[data-theme="dark"] .url-preview-title,
  html[data-theme="dark"] .res-desc{color:var(--ink);}
  html[data-theme="dark"] .url-preview-meta{color:#bcd6c5;}

  @media (max-width:700px){
    .res-search{width:100%;}
    .res-controls{flex-direction:column;align-items:stretch;}
  }

  /* ---------- Student picker modal ---------- */
  .student-picker-modal{max-width:420px;}
  .sp-search{
    display:flex;align-items:center;gap:8px;background:var(--bg);border:1px solid var(--border);
    border-radius:12px;padding:10px 14px;margin-bottom:14px;
  }
  .sp-section-filter{margin-bottom:10px;}
  .sp-section-filter label{display:block;margin-bottom:5px;font-size:11px;font-weight:800;color:var(--muted);}
  .sp-section-filter select{width:100%;}
  .sp-search svg{width:15px;height:15px;color:var(--muted);flex-shrink:0;}
  .sp-search input{border:none;outline:none;background:transparent;font-family:inherit;font-size:13.5px;color:var(--ink);width:100%;}
  .sp-list{display:flex;flex-direction:column;gap:8px;max-height:340px;overflow-y:auto;margin-bottom:6px;}
  .sp-row{
    display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:12px;
    border:1.5px solid var(--border);cursor:pointer;transition:border-color .12s ease, background .12s ease;
  }
  .sp-row:hover{border-color:var(--green);}
  .sp-row.selected{border-color:var(--green);background:var(--green-light);}
  .sp-row.too-challenging{border-color:var(--orange);background:var(--orange-light);}
  .sp-avatar{
    width:36px;height:36px;border-radius:50%;color:#fff;font-size:12.5px;font-weight:800;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Poppins',sans-serif;
  }
  .sp-info{flex-grow:1;min-width:0;}
  .sp-name{font-size:13.5px;font-weight:700;color:#16281d;}
  .sp-grade{font-size:11.5px;color:var(--muted);font-weight:600;}
  .sp-level-row{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:3px;}
  .sp-level-row small{font-size:10.5px;color:var(--muted);font-weight:700;}
  html[data-theme="dark"] .sp-name{color:var(--ink);}
  .sp-check{
    width:22px;height:22px;border-radius:7px;border:2px solid var(--border);flex-shrink:0;
    display:flex;align-items:center;justify-content:center;color:#fff;transition:all .12s ease;
  }
  .sp-row.selected .sp-check{background:var(--green);border-color:var(--green);}
  .sp-check svg{width:13px;height:13px;opacity:0;transition:opacity .12s ease;}
  .sp-row.selected .sp-check svg{opacity:1;}
  .sp-empty{text-align:center;padding:26px 10px;color:var(--muted);font-size:13px;font-weight:600;}
  .sp-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:4px;}
  .sp-count{font-size:12px;color:var(--muted);font-weight:700;}
  .sp-book-context{
    display:flex;align-items:center;gap:10px;background:var(--bg);border-radius:12px;padding:10px 12px;margin-bottom:16px;
  }
  .sp-book-context .bx{font-size:18px;color:var(--green-dark);flex-shrink:0;}
  .sp-book-context .t{font-size:12.5px;font-weight:700;color:#16281d;}
  html[data-theme="dark"] .sp-book-context .t{color:var(--ink);}

  /* ---------- Create Quiz tab ---------- */
  .cq-layout{display:grid;grid-template-columns:1.1fr 1fr;gap:20px;align-items:flex-start;}
  @media (max-width:900px){ .cq-layout{grid-template-columns:1fr;} }
  .cq-card{background:var(--card);border-radius:16px;box-shadow:var(--shadow);padding:22px;}
  .cq-field{margin-bottom:16px;}
  .cq-field label{display:block;font-size:12px;font-weight:800;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.02em;}
  .cq-field input[type="text"],
  .cq-field select,
  .cq-field textarea{
    width:100%;border:1px solid var(--border);border-radius:10px;padding:10px 12px;
    font-family:inherit;font-size:13.5px;color:var(--ink);background:var(--bg);outline:none;
    transition:border-color .12s ease;
  }
  .cq-field input[type="text"]:focus,
  .cq-field select:focus,
  .cq-field textarea:focus{border-color:var(--green);}
  .cq-field textarea{resize:vertical;min-height:56px;}
  .cq-opts{display:flex;flex-direction:column;gap:8px;margin-bottom:12px;}
  .cq-opt-row{display:flex;align-items:center;gap:10px;}
  .cq-opt-row input[type="radio"]{width:17px;height:17px;accent-color:var(--green);flex-shrink:0;}
  .cq-opt-row input[type="text"]{flex-grow:1;}
  .cq-add-q-btn{
    width:100%;background:var(--green-light);color:var(--green-dark);border:1.5px dashed var(--green);
    border-radius:10px;padding:11px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit;
    display:flex;align-items:center;justify-content:center;gap:6px;
  }
  .cq-add-q-btn:hover{background:var(--sidebar-active);}
  .cq-qlist{display:flex;flex-direction:column;gap:10px;max-height:520px;overflow-y:auto;}
  .cq-qcard{background:var(--bg);border-radius:12px;padding:13px 15px;border:1px solid var(--border);}
  .cq-qcard-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
  .cq-qcard-num{font-size:10.5px;font-weight:800;color:var(--green-dark);background:var(--green-light);border-radius:20px;padding:2px 9px;flex-shrink:0;}
  .cq-qcard-prompt{font-size:13px;font-weight:700;color:#16281d;margin:6px 0 8px 0;}
  html[data-theme="dark"] .cq-qcard-prompt{color:var(--ink);}
  .cq-qcard-remove{width:24px;height:24px;border-radius:7px;border:none;background:var(--card);color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;}
  .cq-qcard-remove:hover{background:var(--red-light);color:var(--red);}
  .cq-qcard-opts{display:flex;flex-direction:column;gap:4px;}
  .cq-qcard-opt{font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:6px;}
  .cq-qcard-opt.correct{color:var(--green-dark);font-weight:800;}
  .cq-qcard-opt .bx{font-size:14px;flex-shrink:0;}
  .cq-empty-q{text-align:center;padding:30px 10px;color:var(--muted);font-size:12.5px;font-weight:600;}
  .cq-empty-q .bx{font-size:26px;color:var(--green);display:block;margin-bottom:8px;}
  .cq-save-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);}
  .cq-count-pill{font-size:12px;font-weight:700;color:var(--muted);}
  .cq-saved-list{margin-top:20px;}
  .cq-saved-title{font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.02em;margin-bottom:10px;}
  .cq-saved-row{
    display:flex;align-items:center;gap:14px;background:var(--card);border-radius:14px;box-shadow:var(--shadow);
    padding:13px 16px;margin-bottom:10px;
  }
  .cq-saved-row.editing{border:1.5px solid var(--orange);}
  .cq-saved-icon{width:38px;height:38px;border-radius:10px;background:var(--orange-light);color:var(--orange);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .cq-saved-icon .bx{font-size:18px;}
  .cq-saved-info{flex-grow:1;min-width:0;}
  .cq-saved-name{font-size:13.5px;font-weight:700;color:#16281d;}
  html[data-theme="dark"] .cq-saved-name{color:var(--ink);}
  .cq-saved-sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:1px;}
  .cq-saved-actions{display:flex;gap:6px;flex-shrink:0;}
  .cq-saved-actions button{
    font-size:11.5px;font-weight:800;border:none;border-radius:20px;padding:7px 13px;cursor:pointer;font-family:inherit;
    display:flex;align-items:center;gap:4px;
  }
  .cq-saved-launch{background:var(--green);color:#fff;}
  .cq-saved-edit{background:var(--bg);color:var(--ink);}
  .cq-saved-edit:hover{background:var(--sidebar-active);}
  .cq-saved-delete{background:var(--bg);color:var(--muted);}
  .cq-saved-delete:hover{background:var(--red-light);color:var(--red);}
  .cq-saved-badge{
    display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:800;
    color:var(--green-dark);background:var(--green-light);border-radius:20px;padding:2px 9px;margin-left:6px;
  }
  .cq-saved-badge.standalone{color:var(--muted);background:var(--bg);}
  .cq-saved-badge.editing{color:var(--orange);background:var(--orange-light);}

  /* ---------- Quizzes tab ---------- */
  .quiz-upload-card{margin-bottom:24px;}
  .cq-empty-quizzes{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:50px 20px;text-align:center;color:var(--muted);}
  .cq-empty-quizzes .bx{font-size:34px;color:var(--green);margin-bottom:10px;display:block;}
  .cq-empty-quizzes b{color:#16281d;display:block;font-size:15px;margin-bottom:4px;}
  html[data-theme="dark"] .cq-empty-quizzes b{color:var(--ink);}
</style>
</head>
<body>

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2"  height="1" fill="#cbe98f"/>
        <rect x="0" y="1" width="4"  height="1" fill="#cbe98f"/>
        <rect x="0" y="2" width="6"  height="1" fill="#cbe98f"/>
        <rect x="0" y="3" width="9"  height="1" fill="#cbe98f"/>
        <rect x="0" y="4" width="13" height="1" fill="#b1db65"/>
        <rect x="0" y="5" width="9"  height="1" fill="#7fae55"/>
        <rect x="0" y="6" width="6"  height="1" fill="#7fae55"/>
        <rect x="0" y="7" width="4"  height="1" fill="#7fae55"/>
        <rect x="0" y="8" width="2"  height="1" fill="#7fae55"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2"  height="1" fill="#cbe98f"/>
        <rect x="0" y="1" width="4"  height="1" fill="#cbe98f"/>
        <rect x="0" y="2" width="6"  height="1" fill="#cbe98f"/>
        <rect x="0" y="3" width="9"  height="1" fill="#cbe98f"/>
        <rect x="0" y="4" width="13" height="1" fill="#b1db65"/>
        <rect x="0" y="5" width="9"  height="1" fill="#7fae55"/>
        <rect x="0" y="6" width="6"  height="1" fill="#7fae55"/>
        <rect x="0" y="7" width="4"  height="1" fill="#7fae55"/>
        <rect x="0" y="8" width="2"  height="1" fill="#7fae55"/>
      </svg>
    </div>

    <div>
      <div class="logo-row">
        <div class="logo-left">
          <div class="logo-icon">
            <i class='bx bxs-paper-plane'></i>
          </div>
          <div class="logo-text">
            <span class="brand">ReadPilot</span>
            <span class="tagline">Guide. Read. Grow.</span>
          </div>
        </div>
        <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation">
          <span class="bar"></span>
        </button>
      </div>

      <nav>
        <a class="nav-item" href="index.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
        <a class="nav-item" href="sessions.php"><i class="bx bx-calendar"></i><span class="label">Sessions</span></a>
        <a class="nav-item" href="reports.php"><i class="bx bx-file"></i><span class="label">Reports</span></a>
        <a class="nav-item" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
        <a class="nav-item" href="recommendations.php"><i class="bx bx-bulb"></i><span class="label">Recommendations</span></a>
        <a class="nav-item active" href="resources.php"><i class="bx bx-book-open"></i><span class="label">Resources</span></a>
        <a class="nav-item" href="recordings.php"><i class="bx bx-cloud-upload"></i><span class="label">Cloud Recordings</span></a>
        <a class="nav-item" href="settings.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
      </nav>
    </div>

    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
          <div>
            <div class="teacher-name"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="teacher-role">Grade 3 Teacher</div>
          </div>
        </div>
        <a class="teacher-logout-btn" href="logout.php" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></a>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Resources</h1>
        <div class="greet">Browse the library, upload your own material, or pull a story in from the web.</div>
      </div>
      <div class="topbar-actions">
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
        <button class="btn-new" id="addResourceBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Add Resource
        </button>
      </div>
    </div>

    <div class="panel">
      <div class="res-controls">
        <div class="tabs" id="resTabs">
          <div class="tab active" data-tab="library">Library</div>
          <div class="tab" data-tab="upload">Upload</div>
          <div class="tab" data-tab="createquiz">Create Quiz</div>
          <div class="tab" data-tab="quizzes">Quizzes</div>
        </div>
        <div class="res-filters" id="libraryFilters">
          <div class="res-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="librarySearch" placeholder="Search title, author, or tag...">
          </div>
          <select class="sort-select" id="genreFilter"></select>
          <select class="sort-select" id="difficultyFilter" aria-label="Filter by difficulty">
            <option value="all">All Levels</option>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
          </select>
        </div>
      </div>

      <!-- ============ LIBRARY TAB ============ -->
      <div class="tab-panel active" id="panel-library">
        <div class="book-grid" id="bookGrid"><!-- filled by JS --></div>
      </div>

      <!-- ============ UPLOAD TAB ============ -->
      <div class="tab-panel" id="panel-upload">
        <div class="dropzone" id="dropzone">
          <div class="dropzone-icon"><i class='bx bx-upload'></i></div>
          <div class="dropzone-title">Drag &amp; drop a file here</div>
          <div class="dropzone-sub">or <b>browse from your computer</b></div>
          <div class="dropzone-types">Supports text (.txt), PDF (.pdf), and Word (.docx) — up to 20MB. Scanned PDFs need OCR and are not supported.</div>
          <input type="file" id="fileInput" multiple accept=".pdf,.docx,.txt" hidden>
        </div>
        <div class="file-list" id="fileList"><!-- filled by JS --></div>
      </div>

        <div class="url-form">
          <div class="url-form-row">
            <div class="url-input-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1.5 1.5"/><path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1.5-1.5"/></svg>
              <input type="url" id="urlInput" placeholder="Paste a link to an article, story, or PDF...">
            </div>
            <button class="url-fetch-btn" id="urlFetchBtn">
              <i class='bx bx-download'></i> Fetch
            </button>
          </div>
          <div class="url-hint">We'll pull the readable text from the page so it's ready for a reading session — works best on a single article or story page.</div>

          <div class="url-preview" id="urlPreview">
            <div class="url-preview-icon"><i class='bx bx-file-blank'></i></div>
            <div class="url-preview-body">
              <div class="url-preview-title" id="urlPreviewTitle">--</div>
              <div class="url-preview-source" id="urlPreviewSource">--</div>
              <div class="url-preview-meta" id="urlPreviewMeta">--</div>
              <div class="url-preview-actions">
                <button class="btn-primary" id="urlAddBtn" style="flex:none;padding:9px 16px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                  Add to Library
                </button>
                <button class="btn-secondary" id="urlDiscardBtn" style="padding:9px 16px;">Discard</button>
              </div>
            </div>
          </div>
        </div>

        <div class="url-added-list" id="urlAddedList"><!-- filled by JS --></div>
      </div>

      <!-- ============ CREATE QUIZ TAB ============ -->
      <div class="tab-panel" id="panel-createquiz">
        <div class="cq-layout">
          <div class="cq-card">
            <div class="cq-field">
              <label for="cqTitle">Quiz Title</label>
              <input type="text" id="cqTitle" placeholder="e.g. Chapter 1 Check-In">
            </div>
            <div class="cq-field">
              <label for="cqBookSelect">Attach to a Resource</label>
              <select id="cqBookSelect"></select>
            </div>

            <div class="cq-field">
              <label>Question</label>
              <textarea id="cqQPrompt" placeholder="Type the question..."></textarea>
            </div>
            <div class="cq-field">
              <label>Answer Options — select the correct one</label>
              <div class="cq-opts" id="cqOptsWrap">
                <div class="cq-opt-row"><input type="radio" name="cqCorrect" value="0" checked><input type="text" class="cq-opt-input" placeholder="Option A"></div>
                <div class="cq-opt-row"><input type="radio" name="cqCorrect" value="1"><input type="text" class="cq-opt-input" placeholder="Option B"></div>
                <div class="cq-opt-row"><input type="radio" name="cqCorrect" value="2"><input type="text" class="cq-opt-input" placeholder="Option C (optional)"></div>
                <div class="cq-opt-row"><input type="radio" name="cqCorrect" value="3"><input type="text" class="cq-opt-input" placeholder="Option D (optional)"></div>
              </div>
            </div>
            <div class="cq-field">
              <label>Explanation (shown after answering)</label>
              <textarea id="cqExplain" placeholder="Why is that the right answer?"></textarea>
            </div>
            <button type="button" class="cq-add-q-btn" id="cqAddQuestionBtn">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              Add Question to Quiz
            </button>

            <div class="cq-save-row">
              <span class="cq-count-pill" id="cqCountPill">0 questions added</span>
              <button type="button" class="btn-primary" id="cqSaveQuizBtn" style="flex:none;padding:10px 18px;" disabled>Save Quiz</button>
            </div>
          </div>

          <div class="cq-card">
            <div class="cq-field" style="margin-bottom:12px;">
              <label style="margin-bottom:0;">Questions in this quiz</label>
            </div>
            <div class="cq-qlist" id="cqQList"><!-- filled by JS --></div>
          </div>
        </div>

      </div>

      <!-- ============ QUIZZES TAB ============ -->
      <div class="tab-panel" id="panel-quizzes">
        <div class="quiz-upload-card">
          <div class="dropzone" id="quizDropzone">
            <div class="dropzone-icon"><i class='bx bx-upload'></i></div>
            <div class="dropzone-title">Drag &amp; drop a quiz file here</div>
            <div class="dropzone-sub">or <b>browse from your computer</b></div>
            <div class="dropzone-types">Accepts a single .json quiz file</div>
            <input type="file" id="quizFileInput" accept=".json,application/json" hidden>
          </div>

          <div class="url-preview" id="quizUploadPreview">
            <div class="url-preview-icon"><i class='bx bx-help-circle'></i></div>
            <div class="url-preview-body">
              <div class="url-preview-title" id="quizUploadTitle">--</div>
              <div class="url-preview-source" id="quizUploadMeta">--</div>
              <div class="cq-field" style="margin-top:10px;max-width:320px;">
                <label for="quizUploadBookSelect" style="margin-bottom:4px;">Attach to a Resource (optional)</label>
                <select id="quizUploadBookSelect"></select>
              </div>
              <div class="url-preview-actions">
                <button class="btn-primary" id="quizUploadAddBtn" style="flex:none;padding:9px 16px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                  Add This Quiz
                </button>
                <button class="btn-secondary" id="quizUploadDiscardBtn" style="padding:9px 16px;">Discard</button>
              </div>
            </div>
          </div>
        </div>

        <div class="cq-saved-list">
          <div class="cq-saved-title">All Quizzes</div>
          <div id="allQuizzesList"><!-- filled by JS --></div>
        </div>
      </div>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
      </div>
      <div>
        <div class="tip-title">Tip of the day</div>
        <div class="tip-text">Mix it up! Pairing a fiction pick with a short nonfiction piece each week keeps Grade 3 readers building vocabulary across topics.</div>
      </div>
      <div class="tip-close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ============ RESOURCE DETAIL MODAL ============ -->
  <div class="overlay" id="resOverlay">
    <div class="modal res-modal">
      <button class="modal-close" id="resModalClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="res-cover-banner" id="resCoverBanner"><!-- filled by JS: themed story illustration --></div>
      <div class="modal-head">
        <div class="res-modal-icon" id="resModalIcon"><i class='bx bx-book-open'></i></div>
        <div>
          <h2 id="resModalTitle">Title</h2>
          <div class="sub" id="resModalAuthor">Author</div>
        </div>
      </div>
      <div class="modal-stats">
        <div class="m-stat"><div class="v" id="resModalLevel">--</div><div class="l">Level</div></div>
        <div class="m-stat"><div class="v" id="resModalLexile">--</div><div class="l">Lexile</div></div>
        <div class="m-stat"><div class="v" id="resModalWords">--</div><div class="l">Words</div></div>
        <div class="m-stat"><div class="v" id="resModalAssigned">--</div><div class="l">Assigned</div></div>
      </div>
      <div class="res-tag-row" id="resModalTags"><!-- filled by JS --></div>
      <p class="res-desc" id="resModalDesc">Description goes here.</p>
      <div class="modal-actions">
        <button class="btn-primary start-reading-btn" id="resModalAssignBtn">
          <span class="sr-icon"><i class='bx bxs-rocket'></i></span>
          Start Reading
        </button>
        <button class="btn-secondary" id="resModalQuizBtn"><i class='bx bx-help-circle'></i> Take Quiz</button>
        <button class="btn-secondary resource-delete-btn" id="resModalDeleteBtn" hidden><i class='bx bx-trash'></i> Remove Resource</button>
      </div>
    </div>
  </div>

  <dialog class="resource-delete-dialog" id="resourceDeleteDialog" aria-labelledby="resourceDeleteTitle" aria-describedby="resourceDeleteMessage">
    <h2 id="resourceDeleteTitle">Remove reading material?</h2>
    <p id="resourceDeleteMessage"></p>
    <div class="resource-delete-actions">
      <button class="btn-secondary" id="cancelResourceDelete" type="button">Keep material</button>
      <button class="resource-delete-confirm" id="confirmResourceDelete" type="button"><i class='bx bx-trash'></i> Remove material</button>
    </div>
  </dialog>

  <!-- ============ NO QUIZ YET POP-UP ============ -->
  <dialog class="no-quiz-dialog" id="noQuizDialog" aria-labelledby="noQuizTitle" aria-describedby="noQuizMessage">
    <div class="no-quiz-dialog-icon"><i class='bx bx-help-circle'></i></div>
    <h2 id="noQuizTitle">No quiz created yet</h2>
    <p id="noQuizMessage"></p>
    <div class="no-quiz-dialog-actions">
      <button class="btn-secondary" id="noQuizCancelBtn" type="button">Not now</button>
      <button class="btn-primary" id="noQuizCreateBtn" type="button"><i class='bx bx-plus'></i> Create Quiz</button>
    </div>
  </dialog>

  <!-- ============ STUDENT PICKER MODAL ============ -->
  <div class="overlay" id="spOverlay">
    <div class="modal student-picker-modal">
      <button class="modal-close" id="spCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="modal-head" style="padding-right:0;">
        <div>
          <h2 id="spTitle">Assign to Students</h2>
          <div class="sub" id="spSubtitle">Pick who should read this next</div>
        </div>
      </div>
      <div class="sp-book-context" id="spBookContext">
        <i class='bx bx-book-open'></i>
        <span class="t" id="spBookTitle">--</span>
      </div>
      <div class="sp-section-filter">
        <label for="spSectionFilter">View students by section</label>
        <select class="sort-select" id="spSectionFilter">
          <option value="all">All Sections</option>
        </select>
      </div>
      <div class="sp-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="spSearch" placeholder="Search students...">
      </div>
      <div class="sp-list" id="spList"><!-- filled by JS --></div>
      <div class="sp-footer">
        <span class="sp-count" id="spCount">No one selected</span>
        <div style="display:flex;gap:8px;">
          <button class="btn-secondary" id="spCancelBtn" style="padding:9px 16px;">Cancel</button>
          <button class="btn-primary" id="spConfirmBtn" style="flex:none;padding:9px 18px;" disabled>Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ TOAST ============ -->
  <div class="toast" id="toast">
    <i class='bx bxs-check-circle'></i>
    <span id="toastMsg">Done</span>
  </div>

  <script>
    // ---- Hamburger: collapse sidebar ----
    // Toggles the same data-sidebar="collapsed" attribute on <html> that the
    // shared stylesheet's collapsed rules key off of, and persists the choice
    // to localStorage so it stays collapsed/expanded across every page (the
    // inline <head> script above reads it back before paint on load).
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      const html = document.documentElement;
      const isCollapsed = html.getAttribute('data-sidebar') === 'collapsed';

      if (isCollapsed) {
        html.removeAttribute('data-sidebar');
      } else {
        html.setAttribute('data-sidebar', 'collapsed');
      }

      try {
        localStorage.setItem('readpilot-sidebar', isCollapsed ? 'expanded' : 'collapsed');
      } catch (e) {
        /* localStorage unavailable (e.g. private browsing) — toggle still
           works for this page load, it just won't persist across pages */
      }
    });
    // ---- Tip dismiss ----
    document.querySelector('.tip-close').addEventListener('click', function(){
      document.querySelector('.tip').style.display = 'none';
    });

    // ---- Toast helper ----
    let toastTimer;
    function showToast(message){
      const toast = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = message;
      toast.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
    }

    /* =====================================================================
       DATA — swap this for a real catalog / API response when wiring up
       a backend. Everything on the page renders from these arrays.
    ===================================================================== */
    const GENRE_COLORS = {
      "Fable": "#3f7d4a", "Sci-Fi": "#8b6bd1", "Folktale": "#f2a13a",
      "Realistic Fiction": "#4fa3b8", "Nonfiction": "#ea5d5d",
      "Fantasy": "#c9924d", "default": "#6fbf5a"
    };

    const DIFFICULTY_ORDER = {Beginner:0, Intermediate:1, Advanced:2};
    function resourceDifficulty(book){
      const lexile = Number.parseInt(String(book.lexile || '').match(/\d+/)?.[0] || '', 10);
      if(!Number.isFinite(lexile)) return 'Beginner';
      if(lexile <= 450) return 'Beginner';
      if(lexile <= 550) return 'Intermediate';
      return 'Advanced';
    }
    function studentDifficulty(student){
      const sessions = Number(student.sessions) || 0;
      const wpm = Number(student.wpm) || 0;
      const accuracy = Number(student.accuracy) || 0;
      if(sessions < 3 || accuracy < 85 || wpm < 60) return 'Beginner';
      if(sessions >= 6 && accuracy >= 95 && wpm >= 120) return 'Advanced';
      return 'Intermediate';
    }
    function difficultyClass(level){ return level.toLowerCase(); }

    let LIBRARY = [
      { id: 1, title: "The Lion and the Mouse", author: "Aesop (retold)", genre: "Fable", level: "Grade 3", lexile: "430L", words: 620, desc: "A classic fable about kindness and an unexpected friendship between a mighty lion and a small mouse.", tags: ["Fiction","Read Aloud"], assigned: 14 },
      { id: 2, title: "Journey to the Stars", author: "Priya Anand", genre: "Sci-Fi", level: "Grade 3", lexile: "510L", words: 980, desc: "A young astronaut-in-training imagines a voyage past the moon and back before bedtime.", tags: ["Fiction","Space","Adventure"], assigned: 9 },
      { id: 3, title: "The Three Little Pigs", author: "Traditional", genre: "Folktale", level: "Grade 2–3", lexile: "390L", words: 540, desc: "The timeless tale of three pigs, three houses, and one very determined wolf.", tags: ["Fiction","Folktale"], assigned: 11 },
      { id: 4, title: "A Rainy Day Surprise", author: "Maribel Cruz", genre: "Realistic Fiction", level: "Grade 3", lexile: "460L", words: 710, desc: "When recess gets rained out, a class discovers an unexpected indoor adventure.", tags: ["Fiction","Friendship"], assigned: 7 },
      { id: 5, title: "How Butterflies Are Born", author: "Dr. Elena Ford", genre: "Nonfiction", level: "Grade 3", lexile: "540L", words: 860, desc: "A step-by-step look at metamorphosis, from egg to caterpillar to butterfly.", tags: ["Nonfiction","Science"], assigned: 16 },
      { id: 6, title: "The Kind Knight", author: "Owen Park", genre: "Fantasy", level: "Grade 3", lexile: "470L", words: 690, desc: "A knight who'd rather solve problems with kindness than a sword.", tags: ["Fiction","Fantasy"], assigned: 5 },
      { id: 7, title: "Our Solar System", author: "NASA Kids (adapted)", genre: "Nonfiction", level: "Grade 3–4", lexile: "600L", words: 1100, desc: "An overview of the eight planets and what makes each one unique.", tags: ["Nonfiction","Space"], assigned: 8 },
      { id: 8, title: "The Grumpy Garden Gnome", author: "Nora Bell", genre: "Fantasy", level: "Grade 3", lexile: "440L", words: 640, desc: "A gnome who dislikes visitors learns the value of good company.", tags: ["Fiction","Fantasy","Humor"], assigned: 4 }
    ];

    fetch('student-api.php?view=resources').then(response => response.json()).then(result => {
      if (Array.isArray(result.resources)) {
        LIBRARY = result.resources;
        buildGenreFilter();
        renderLibrary();
        openRequestedResource();
      }
    }).catch(() => {});

    let UPLOADED_FILES = []; // { id, name, sizeLabel, ext, status }
    let URL_RESOURCES = [];  // { id, title, source, words }
    let nextId = 100;

    // ---- Genre filter options ----
    const genreFilterEl = document.getElementById('genreFilter');
    function buildGenreFilter(){
      const genres = [...new Set(LIBRARY.map(b => b.genre))].sort();
      genreFilterEl.innerHTML = `<option value="all">All Genres</option>` + genres.map(g => `<option value="${g}">${g}</option>`).join('');
    }

    // ---- Tabs ----
    document.querySelectorAll('#resTabs .tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('#resTabs .tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
        document.getElementById('libraryFilters').style.display = tab.dataset.tab === 'library' ? 'flex' : 'none';
        if (tab.dataset.tab === 'createquiz') buildCqBookSelect();
        if (tab.dataset.tab === 'quizzes') renderAllQuizzes();
      });
    });
    document.getElementById('addResourceBtn').addEventListener('click', () => {
      document.querySelector('#resTabs .tab[data-tab="upload"]').click();
    });

    // ---- Library rendering ----
    let librarySearch = '';
    let genreFilterValue = 'all';
    let difficultyFilterValue = 'all'; // 'all' | 'Beginner' | 'Intermediate' | 'Advanced'

    function lexileNumber(book){
      return Number.parseInt(String(book.lexile || '').match(/\d+/)?.[0] || '0', 10);
    }

    function filteredLibrary(){
      const list = LIBRARY.filter(b => {
        if (genreFilterValue !== 'all' && b.genre !== genreFilterValue) return false;
        if (difficultyFilterValue !== 'all' && resourceDifficulty(b) !== difficultyFilterValue) return false;
        if (librarySearch){
          const hay = (b.title + ' ' + b.author + ' ' + b.tags.join(' ')).toLowerCase();
          if (!hay.includes(librarySearch)) return false;
        }
        return true;
      });

      // Always list Beginner → Advanced, then by Lexile, then by title.
      return list.slice().sort((a, b) =>
        (DIFFICULTY_ORDER[resourceDifficulty(a)] - DIFFICULTY_ORDER[resourceDifficulty(b)]) ||
        (lexileNumber(a) - lexileNumber(b)) ||
        String(a.title).localeCompare(String(b.title))
      );
    }

    const bookGridEl = document.getElementById('bookGrid');
    function renderLibrary(){
      const list = filteredLibrary();
      bookGridEl.innerHTML = '';
      if (!list.length){
        bookGridEl.innerHTML = `
          <div class="empty-grid">
            <i class='bx bx-book bx-tada'></i>
            <b>No matching resources</b>
            Try a different search term or clear the genre filter.
          </div>`;
        return;
      }
      list.forEach(b => {
        const color = GENRE_COLORS[b.genre] || GENRE_COLORS.default;
        const card = document.createElement('div');
        card.className = 'book-card';
        card.innerHTML = `
          <div class="book-spine" style="background:${color}">
            <i class='bx bx-book-open'></i>
            <span class="book-level">${escapeHtml(b.level)}</span>
          </div>
          <div class="book-body">
            <div class="book-title" title="${escapeHtml(b.title)}">${escapeHtml(b.title)}</div>
            <div class="book-author">${escapeHtml(b.author)}</div>
            <div class="book-meta">
              <span class="book-genre" style="background:${color}22;color:${color}">${escapeHtml(b.genre)}</span>
              <span class="difficulty-pill ${difficultyClass(resourceDifficulty(b))}">${resourceDifficulty(b)}</span>
              <span class="book-lexile">${escapeHtml(b.lexile)}</span>
            </div>
            <div class="book-foot">
              <span class="book-words"><i class='bx bx-file-blank'></i>${Number(b.words) || 0} words</span>
              <div class="book-foot-actions">
                <button class="book-quiz-btn ${CUSTOM_QUIZZES[b.id] ? 'has-custom' : ''}" data-id="${b.id}" title="${CUSTOM_QUIZZES[b.id] ? 'Take Quiz' : 'No quiz created yet'}"><i class='bx bx-help-circle'></i></button>
                <button class="book-assign-btn ${b.justAssigned ? 'assigned' : ''}" data-id="${b.id}">
                  ${b.justAssigned ? '<i class="bx bx-check"></i> Assigned' : 'Assign'}
                </button>
              </div>
            </div>
          </div>
        `;
        card.addEventListener('click', (e) => {
          if (e.target.closest('.book-assign-btn') || e.target.closest('.book-quiz-btn')) return;
          openResourceModal(b);
        });
        card.querySelector('.book-assign-btn').addEventListener('click', (e) => {
          e.stopPropagation();
          openStudentPicker(b, 'multi');
        });
        card.querySelector('.book-quiz-btn').addEventListener('click', (e) => {
          e.stopPropagation();
          openStudentPicker(b, 'quiz');
        });
        bookGridEl.appendChild(card);
      });
    }

    document.getElementById('librarySearch').addEventListener('input', (e) => {
      librarySearch = e.target.value.trim().toLowerCase();
      renderLibrary();
    });
    genreFilterEl.addEventListener('change', (e) => {
      genreFilterValue = e.target.value;
      renderLibrary();
    });
    document.getElementById('difficultyFilter').addEventListener('change', (e) => {
      difficultyFilterValue = e.target.value;
      renderLibrary();
    });

    /* =====================================================================
       COVER ILLUSTRATIONS
       Small themed SVG scenes for the resource modal's cover banner.
       These are original flat-style illustrations drawn to match each
       story (not real book cover art / photos) — that keeps things safe
       to ship (no licensing on someone else's cover art or hotlinked
       photos that could break) while still giving every title its own
       distinct, kid-friendly look. Keyed by book id, with genre-based
       fallbacks for uploads / web-fetched resources that don't have a
       hand-drawn scene yet.
    ===================================================================== */
    function svgWrap(bg, inner){
      return `<svg viewBox="0 0 400 150" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
        <rect width="400" height="150" fill="${bg}"/>${inner}</svg>`;
    }

    const BOOK_COVERS = {
      // 1 — The Lion and the Mouse (Fable): sun, savanna hill, lion + mouse
      1: svgWrap('#3f7d4a', `
        <circle cx="345" cy="35" r="26" fill="#f6d878"/>
        <path d="M0 115 Q100 85 200 108 T400 100 V150 H0 Z" fill="#4a8f56"/>
        <ellipse cx="150" cy="118" rx="42" ry="30" fill="#e0a95c"/>
        <circle cx="118" cy="98" r="24" fill="#e0a95c"/>
        <circle cx="98" cy="82" r="8" fill="#c88f47"/><circle cx="140" cy="80" r="8" fill="#c88f47"/>
        <circle cx="110" cy="98" r="3" fill="#2b2b2b"/><circle cx="126" cy="98" r="3" fill="#2b2b2b"/>
        <path d="M110 108 Q118 114 126 108" stroke="#7a4a1e" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        <ellipse cx="245" cy="128" rx="15" ry="10" fill="#c9c9c9"/>
        <circle cx="262" cy="120" r="9" fill="#c9c9c9"/>
        <circle cx="266" cy="115" r="2" fill="#2b2b2b"/>
        <path d="M266 112 L272 106" stroke="#c9c9c9" stroke-width="3" stroke-linecap="round"/>`),
      // 2 — Journey to the Stars (Sci-Fi): night sky, moon, rocket
      2: svgWrap('#4a3a86', `
        <circle cx="60" cy="30" r="2" fill="#fff"/><circle cx="120" cy="55" r="1.6" fill="#fff"/>
        <circle cx="200" cy="20" r="2" fill="#fff"/><circle cx="330" cy="45" r="1.6" fill="#fff"/>
        <circle cx="360" cy="90" r="2" fill="#fff"/><circle cx="30" cy="90" r="1.6" fill="#fff"/>
        <circle cx="290" cy="20" r="24" fill="#f2e6b8"/>
        <circle cx="282" cy="14" r="4" fill="#e0d29a"/><circle cx="298" cy="26" r="3" fill="#e0d29a"/>
        <g transform="translate(150,55) rotate(18)">
          <path d="M20 0 C34 10 34 55 20 75 C6 55 6 10 20 0 Z" fill="#eef1ee"/>
          <circle cx="20" cy="26" r="8" fill="#4fa3b8"/>
          <path d="M4 55 L-10 78 L14 68 Z" fill="#f2a13a"/>
          <path d="M36 55 L50 78 L26 68 Z" fill="#f2a13a"/>
          <path d="M12 72 L20 95 L28 72 Z" fill="#f2a13a"/>
        </g>`),
      // 3 — The Three Little Pigs (Folktale): three houses
      3: svgWrap('#f2a13a', `
        <circle cx="345" cy="30" r="20" fill="#ffe1a8"/>
        <path d="M0 150 V120 H400 V150 Z" fill="#d98a2b"/>
        <g transform="translate(30,80)"><path d="M0 40 L30 10 L60 40 Z" fill="#c9924d"/><rect x="8" y="40" width="44" height="30" fill="#f2d9ad"/></g>
        <g transform="translate(150,65)"><path d="M0 50 L45 10 L90 50 Z" fill="#a86b3c"/><rect x="10" y="50" width="70" height="35" fill="#f7e6c4"/><rect x="35" y="65" width="20" height="20" fill="#7a4a1e"/></g>
        <g transform="translate(280,88)"><path d="M0 32 L25 8 L50 32 Z" fill="#8f5a2f"/><rect x="6" y="32" width="38" height="24" fill="#eecf9d"/></g>`),
      // 4 — A Rainy Day Surprise (Realistic Fiction): clouds, rain, umbrella
      4: svgWrap('#4fa3b8', `
        <ellipse cx="90" cy="40" rx="46" ry="22" fill="#e7f3f5"/>
        <ellipse cx="300" cy="30" rx="36" ry="18" fill="#e7f3f5"/>
        <g stroke="#dff2f6" stroke-width="3" stroke-linecap="round">
          <path d="M60 70 L52 88"/><path d="M90 74 L82 94"/><path d="M120 70 L112 88"/>
          <path d="M270 62 L262 80"/><path d="M300 66 L292 86"/>
        </g>
        <path d="M150 130 A50 50 0 0 1 250 130 Z" fill="#ea5d5d"/>
        <rect x="196" y="128" width="8" height="22" rx="3" fill="#7a4a1e"/>
        <path d="M150 130 L166 122 L182 130 L198 122 L214 130 L230 122 L250 130" stroke="#c94a4a" stroke-width="2" fill="none"/>`),
      // 5 — How Butterflies Are Born (Nonfiction): leaf + butterfly
      5: svgWrap('#ea5d5d', `
        <path d="M0 150 Q140 90 400 150 Z" fill="#f28080"/>
        <path d="M140 150 Q150 90 200 70 Q170 100 165 150 Z" fill="#4a8f56"/>
        <g transform="translate(255,60)">
          <ellipse cx="-14" cy="0" rx="20" ry="26" fill="#f6d878" transform="rotate(-18 -14 0)"/>
          <ellipse cx="14" cy="0" rx="20" ry="26" fill="#f2a13a" transform="rotate(18 14 0)"/>
          <ellipse cx="0" cy="2" rx="4" ry="16" fill="#5a3a26"/>
          <circle cx="0" cy="-14" r="4" fill="#5a3a26"/>
        </g>`),
      // 6 — The Kind Knight (Fantasy): shield + castle silhouette
      6: svgWrap('#c9924d', `
        <path d="M0 150 L40 95 L80 150 Z" fill="#b57c3d"/>
        <path d="M310 150 L350 90 L390 150 Z" fill="#b57c3d"/>
        <circle cx="200" cy="35" r="18" fill="#ffe9c2"/>
        <g transform="translate(160,55)">
          <path d="M40 0 L80 14 V52 C80 82 60 100 40 108 C20 100 0 82 0 52 V14 Z" fill="#e7ecef"/>
          <path d="M40 0 L80 14 V52 C80 82 60 100 40 108 Z" fill="#c7d0d6"/>
          <path d="M40 24 V88 M18 44 H62" stroke="#f2a13a" stroke-width="7" stroke-linecap="round"/>
        </g>`),
      // 7 — Our Solar System (Nonfiction): planets orbiting a sun
      7: svgWrap('#2c2454', `
        <circle cx="30" cy="120" r="1.6" fill="#fff"/><circle cx="90" cy="30" r="2" fill="#fff"/>
        <circle cx="360" cy="110" r="1.6" fill="#fff"/><circle cx="330" cy="25" r="2" fill="#fff"/>
        <circle cx="60" cy="75" r="28" fill="#f6d878"/>
        <ellipse cx="200" cy="75" rx="130" ry="46" fill="none" stroke="#4a4478" stroke-width="1.5"/>
        <circle cx="150" cy="42" r="9" fill="#4fa3b8"/>
        <circle cx="250" cy="108" r="13" fill="#ea5d5d"/>
        <g transform="translate(320,70)"><circle cx="0" cy="0" r="10" fill="#c9924d"/><ellipse cx="0" cy="0" rx="20" ry="6" fill="none" stroke="#e0c193" stroke-width="2.5"/></g>`),
      // 8 — The Grumpy Garden Gnome (Fantasy): gnome hat among flowers
      8: svgWrap('#7fae55', `
        <path d="M0 150 Q100 120 200 145 T400 130 V150 Z" fill="#6fa04a"/>
        <g transform="translate(70,110)"><circle r="8" fill="#f2a13a"/><circle cx="14" cy="0" r="8" fill="#ea5d5d"/><circle cx="-14" cy="0" r="8" fill="#f6d878"/><rect x="-2" y="4" width="4" height="16" fill="#4a8f56"/></g>
        <g transform="translate(330,118)"><circle r="7" fill="#f6d878"/><circle cx="12" cy="2" r="7" fill="#4fa3b8"/><rect x="-1" y="4" width="3" height="14" fill="#4a8f56"/></g>
        <g transform="translate(200,95)">
          <path d="M-26 40 C-26 10 26 10 26 40 Z" fill="#3a6b46"/>
          <circle cx="0" cy="30" r="16" fill="#eecfa3"/>
          <path d="M-18 24 Q0 -30 18 24 Q0 34 -18 24 Z" fill="#ea5d5d"/>
          <circle cx="0" cy="8" r="6" fill="#f6f0e6"/>
        </g>`)
    };

    const GENRE_COVER_FALLBACK = {
      'Sci-Fi': svgWrap('#4a3a86', `<circle cx="200" cy="75" r="34" fill="#f2e6b8"/><circle cx="192" cy="66" r="6" fill="#e0d29a"/>`),
      'Nonfiction': svgWrap('#ea5d5d', `<path d="M0 150 Q200 100 400 150 Z" fill="#f28080"/>`),
      'Fantasy': svgWrap('#c9924d', `<circle cx="200" cy="75" r="30" fill="#f2d9ad"/>`),
      'Folktale': svgWrap('#f2a13a', `<path d="M150 100 L200 60 L250 100 Z" fill="#c9924d"/><rect x="165" y="100" width="70" height="35" fill="#f2d9ad"/>`),
      'Fable': svgWrap('#3f7d4a', `<circle cx="200" cy="60" r="20" fill="#f6d878"/>`),
      'Realistic Fiction': svgWrap('#4fa3b8', `<ellipse cx="200" cy="60" rx="50" ry="24" fill="#e7f3f5"/>`),
      'default': svgWrap('#6fbf5a', `<circle cx="200" cy="75" r="30" fill="#e6f4e1"/>`)
    };

    function coverFor(b){
      return BOOK_COVERS[b.id] || GENRE_COVER_FALLBACK[b.genre] || GENRE_COVER_FALLBACK.default;
    }

    /* =====================================================================
       STORY TEXT — full passages for the follow-along reading page,
       pre-split into short lines/sentences (matching how reading.php
       paces one sentence at a time). Swap for real fetched/uploaded
       content when wiring up a backend.
    ===================================================================== */
    const STORY_TEXT = {
      1: [
        "A lion was asleep in the forest one quiet afternoon.",
        "A tiny mouse scurried across his paw and woke him up.",
        "The lion opened one eye and let out an angry roar.",
        "He grabbed the mouse in his big paw, ready to eat him.",
        "\u201CPlease let me go,\u201D squeaked the mouse. \u201COne day I might help you too.\u201D",
        "The lion laughed at the idea, but he let the little mouse go free.",
        "A few days later, the lion got tangled in a hunter's net.",
        "He roared and roared, but the ropes only pulled tighter.",
        "The tiny mouse heard him and came running as fast as he could.",
        "With his sharp little teeth, the mouse chewed through the ropes and set the lion free.",
        "\u201CYou laughed when I said I could help you,\u201D said the mouse with a smile.",
        "\u201CNow you know that even the smallest friend can make the biggest difference.\u201D"
      ],
      2: [
        "Every night before bed, Mira looked up at the twinkling stars.",
        "She dreamed of flying past the moon in her very own rocket ship.",
        "One night, her toy rocket began to glow with a soft blue light.",
        "\u201CClimb aboard,\u201D it whispered, \u201Cand I will take you on a journey.\u201D",
        "Mira hopped in, and the little rocket zoomed straight through her window.",
        "They flew past clouds shaped like cotton candy and stars that sparkled like diamonds.",
        "\u201CLook!\u201D said Mira. \u201CThat glowing circle must be the moon!\u201D",
        "The rocket landed softly on the moon's silver, bumpy ground.",
        "Mira bounced in giant leaps, laughing as she floated with each step.",
        "Before the sun came up, the rocket carried her gently back home.",
        "Mira climbed into bed, smiling, already dreaming of her next journey to the stars."
      ],
      3: [
        "Once there were three little pigs who each built a home of their own.",
        "The first pig built his house out of straw because it was quick and easy.",
        "The second pig built his house out of sticks, a little stronger than straw.",
        "The third pig worked hard and built his house out of bricks.",
        "One day, a hungry wolf came knocking at the door of the straw house.",
        "\u201CLittle pig, little pig, let me come in!\u201D the wolf called out.",
        "\u201CNot by the hair on my chinny chin chin!\u201D the pig answered.",
        "So the wolf huffed and puffed and blew the straw house down.",
        "The pig ran to his brother's stick house, but the wolf blew that down too.",
        "Both pigs ran to their brother's brick house and slammed the door shut.",
        "The wolf huffed and puffed with all his might, but the brick house did not budge.",
        "The three little pigs lived safely together, proud that hard work had kept them safe."
      ],
      4: [
        "Dark clouds rolled in just as Ms. Ortiz's class lined up for recess.",
        "\u201CNo outside time today,\u201D she announced, and everyone groaned in disappointment.",
        "But then Ms. Ortiz smiled and pulled a big box of blankets from the closet.",
        "\u201CLet's build the biggest indoor fort this classroom has ever seen,\u201D she said.",
        "The students pushed desks together and draped blankets over the tops like a roof.",
        "They grabbed flashlights and crawled inside, giggling at their cozy new hideout.",
        "Rain tapped softly on the windows while the class read stories by flashlight.",
        "Someone started a game of shadow puppets on the blanket wall.",
        "By the time the rain stopped, nobody even remembered they had missed recess.",
        "\u201CBest rainy day ever,\u201D said one student, and everyone else agreed."
      ],
      5: [
        "A butterfly's life begins as a tiny egg laid on the leaf of a plant.",
        "After a few days, a small caterpillar hatches and starts eating right away.",
        "The caterpillar eats leaves nonstop, growing bigger and bigger every day.",
        "When it is big enough, the caterpillar forms a hard shell called a chrysalis.",
        "Inside the chrysalis, something amazing is happening that we cannot see.",
        "The caterpillar's body slowly changes shape over one to two weeks.",
        "Finally, the chrysalis splits open and a butterfly climbs out.",
        "At first its wings are wet and folded, so it waits patiently for them to dry.",
        "Once its wings are dry and strong, the butterfly opens them wide.",
        "It flutters into the air, ready to visit flowers and start the cycle again."
      ],
      6: [
        "In a faraway kingdom lived a knight named Sir Cedric who carried no sword.",
        "Instead of a sword, Cedric carried a satchel filled with bread and bandages.",
        "Other knights laughed and asked how he planned to face dragons without a blade.",
        "\u201CI would rather make a friend than make an enemy,\u201D Cedric always replied.",
        "One day, a dragon was spotted circling the village, and everyone panicked.",
        "While other knights sharpened their swords, Cedric walked calmly toward the dragon's cave.",
        "Inside, he found the dragon curled up, whimpering with a thorn stuck in its paw.",
        "Cedric gently pulled the thorn free and wrapped the paw with a soft bandage.",
        "The grateful dragon nuzzled Cedric and never bothered the village again.",
        "From that day on, the kingdom knew that kindness could be the strongest armor of all."
      ],
      7: [
        "Our solar system is made up of the sun and everything that orbits around it.",
        "The sun is a giant star that gives us light and warmth every day.",
        "Closest to the sun is Mercury, a small and extremely hot planet.",
        "Next comes Venus, wrapped in thick clouds that trap in heat.",
        "Earth is the third planet, and the only one we know that has life.",
        "Mars, the red planet, is covered in rusty-colored dust and rocky canyons.",
        "Beyond Mars is Jupiter, the largest planet, with a giant storm called the Great Red Spot.",
        "Saturn is famous for the beautiful rings of ice and rock that circle around it.",
        "Uranus and Neptune are icy blue planets far out at the edge of the solar system.",
        "Together, these planets travel around the sun in paths called orbits, again and again."
      ],
      8: [
        "In a small garden behind an old cottage lived a gnome named Pip.",
        "Pip did not like visitors, and he definitely did not like noise.",
        "\u201CShoo! Go away!\u201D he would grumble whenever birds landed near his flower beds.",
        "One rainy morning, Pip found a shivering baby rabbit hiding under his favorite mushroom.",
        "He crossed his arms and grumbled, but the rabbit looked up with big, scared eyes.",
        "With a heavy sigh, Pip made a tiny blanket out of a fallen leaf.",
        "He set out a thimble of water and a few crumbs of bread.",
        "Slowly, the rabbit stopped shivering and hopped closer to say thank you.",
        "To his surprise, Pip found himself smiling for the first time in years.",
        "From then on, Pip's garden was always open to any creature who needed a little kindness."
      ]
    };

    function escapeHtml(value){
      return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      })[char]);
    }

    // Splits uploaded passages into sentence-sized lines for the reading view.
    function linesFor(b){
      if (typeof b.content === 'string' && b.content.trim()){
        const text = b.content.replace(/\s+/g, ' ').trim();
        const sentences = text.match(/[^.!?]+[.!?]+(?:["'’”)]*)|[^.!?]+$/g);
        return (sentences || [text]).map(sentence => sentence.trim()).filter(Boolean);
      }
      if (Array.isArray(b.story) && b.story.length) return b.story.map(line => String(line).trim()).filter(Boolean);
      if (STORY_TEXT[b.id]) return STORY_TEXT[b.id];
      const bySentence = (b.desc || '').match(/[^.!?]+[.!?]+/g);
      return (bySentence && bySentence.length ? bySentence.map(s => s.trim()) : [b.desc || b.title]);
    }

    /* =====================================================================
       CUSTOM QUIZZES — teacher-authored quizzes built on the "Create Quiz"
       tab, or imported as JSON on the "Quizzes" tab. Keyed by book id when
       attached to a resource so the grid/modal quiz buttons pick them up
       automatically instead of the auto-generated quiz.php questions.
       Every quiz — attached or standalone — is browsable, launchable,
       editable, and deletable from the "Quizzes" tab, so a finished quiz
       always has somewhere to live even before it's assigned to anyone.
    ===================================================================== */
    let CUSTOM_QUIZZES = {};       // bookId -> quiz object, for resource-attached quizzes
    let STANDALONE_QUIZZES = [];   // quizzes with no resource attached
    let cqDraftQuestions = [];     // questions being built for the quiz currently in progress
    let cqEditingBookId = null;    // bookId currently being edited, if any (kept in CUSTOM_QUIZZES until the edit is saved)
    let cqEditingStandaloneIdx = null; // index into STANDALONE_QUIZZES currently being edited, if any
    const QUIZ_STORAGE_KEY = 'readpilot-custom-quizzes';

    function loadSavedQuizzes(){
      try {
        const saved = JSON.parse(localStorage.getItem(QUIZ_STORAGE_KEY) || '{}');
        if (saved && saved.attached && typeof saved.attached === 'object') CUSTOM_QUIZZES = saved.attached;
        if (saved && Array.isArray(saved.standalone)) STANDALONE_QUIZZES = saved.standalone;
      } catch (error) {
        CUSTOM_QUIZZES = {};
        STANDALONE_QUIZZES = [];
      }
    }

    function saveQuizzes(){
      try {
        localStorage.setItem(QUIZ_STORAGE_KEY, JSON.stringify({
          attached: CUSTOM_QUIZZES,
          standalone: STANDALONE_QUIZZES
        }));
      } catch (error) {
        showToast('Quiz saved for this visit only');
      }
      Object.keys(CUSTOM_QUIZZES).forEach(bookId => persistQuiz(CUSTOM_QUIZZES[bookId], bookId));
      STANDALONE_QUIZZES.forEach(quiz => persistQuiz(quiz, ''));
    }

    function persistQuiz(quiz, resourceId){
      fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'save_quiz', title:quiz.title, questions:JSON.stringify(quiz.questions), resource_id:resourceId || ''})}).catch(()=>{});
    }

    loadSavedQuizzes();
    fetch('student-api.php?view=quizzes').then(response=>response.json()).then(result => {
      if (!Array.isArray(result.quizzes)) return;
      CUSTOM_QUIZZES = {};
      STANDALONE_QUIZZES = [];
      result.quizzes.forEach(quiz => {
        if (quiz.resource_id) CUSTOM_QUIZZES[quiz.resource_id] = {title:quiz.title, questions:quiz.questions};
        else STANDALONE_QUIZZES.push({title:quiz.title, questions:quiz.questions});
      });
      renderLibrary();
      renderAllQuizzes();
    }).catch(()=>{});

    function buildBookSelectOptions(selectEl, { includeCustomHint = false } = {}){
      const current = selectEl.value;
      selectEl.innerHTML = `<option value="">No resource — standalone quiz</option>` +
        LIBRARY.map(b => `<option value="${b.id}">${b.title}${includeCustomHint && CUSTOM_QUIZZES[b.id] ? ' (has a custom quiz — saving will replace it)' : ''}</option>`).join('');
      if (current) selectEl.value = current;
    }

    function buildCqBookSelect(){
      buildBookSelectOptions(document.getElementById('cqBookSelect'), { includeCustomHint: true });
    }

    function renderCqDraftList(){
      const wrap = document.getElementById('cqQList');
      if (!cqDraftQuestions.length){
        wrap.innerHTML = `
          <div class="cq-empty-q">
            <i class='bx bx-list-plus'></i>
            No questions yet — fill out the form on the left and add your first one.
          </div>`;
      } else {
        wrap.innerHTML = cqDraftQuestions.map((q, i) => `
          <div class="cq-qcard">
            <div class="cq-qcard-head">
              <div>
                <span class="cq-qcard-num">Q${i + 1}</span>
                <div class="cq-qcard-prompt">${q.prompt}</div>
              </div>
              <button class="cq-qcard-remove" data-idx="${i}" title="Remove question"><i class='bx bx-x'></i></button>
            </div>
            <div class="cq-qcard-opts">
              ${q.options.map((opt, oi) => `
                <div class="cq-qcard-opt ${oi === q.correctIndex ? 'correct' : ''}">
                  <i class='bx ${oi === q.correctIndex ? 'bxs-check-circle' : 'bx-circle'}'></i>${opt}
                </div>`).join('')}
            </div>
          </div>
        `).join('');
        wrap.querySelectorAll('.cq-qcard-remove').forEach(btn => {
          btn.addEventListener('click', () => {
            cqDraftQuestions.splice(Number(btn.dataset.idx), 1);
            renderCqDraftList();
            updateCqSaveState();
          });
        });
      }
    }

    function updateCqSaveState(){
      document.getElementById('cqCountPill').textContent =
        cqDraftQuestions.length + (cqDraftQuestions.length === 1 ? ' question added' : ' questions added');
      const titleFilled = document.getElementById('cqTitle').value.trim().length > 0;
      document.getElementById('cqSaveQuizBtn').disabled = !(cqDraftQuestions.length > 0 && titleFilled);
    }

    document.getElementById('cqTitle').addEventListener('input', updateCqSaveState);

    document.getElementById('cqAddQuestionBtn').addEventListener('click', () => {
      const prompt = document.getElementById('cqQPrompt').value.trim();
      const optionInputs = [...document.querySelectorAll('.cq-opt-input')];
      const options = optionInputs.map(i => i.value.trim());
      const correctRadio = document.querySelector('input[name="cqCorrect"]:checked');
      const correctIndex = correctRadio ? Number(correctRadio.value) : 0;
      const explanation = document.getElementById('cqExplain').value.trim();

      if (!prompt){ showToast('Add a question first'); return; }
      const filledOptions = options.filter(o => o.length);
      if (filledOptions.length < 2){ showToast('Add at least two answer options'); return; }
      if (!options[correctIndex] || !options[correctIndex].length){ showToast('The selected correct answer needs text'); return; }

      // Compress out any empty trailing option slots but keep the correct index valid.
      const keptOptions = [];
      let newCorrectIndex = 0;
      options.forEach((opt, i) => {
        if (!opt.length) return;
        if (i === correctIndex) newCorrectIndex = keptOptions.length;
        keptOptions.push(opt);
      });

      cqDraftQuestions.push({
        prompt,
        options: keptOptions,
        correctIndex: newCorrectIndex,
        explanation: explanation || 'Great job working through that one!'
      });

      // Reset the question form for the next question, keep title/book selection.
      document.getElementById('cqQPrompt').value = '';
      optionInputs.forEach(i => i.value = '');
      document.querySelector('input[name="cqCorrect"][value="0"]').checked = true;
      document.getElementById('cqExplain').value = '';
      document.getElementById('cqQPrompt').focus();

      renderCqDraftList();
      updateCqSaveState();
      showToast('Question added');
    });

    // Builds one unified, browsable list combining every resource-attached
    // quiz (CUSTOM_QUIZZES) and every standalone quiz (STANDALONE_QUIZZES) —
    // this is the "somewhere for a finished quiz to go" that the Quizzes tab
    // provides, regardless of how the quiz was created (built here, or
    // uploaded as JSON). A quiz currently open in the builder (mid-edit) is
    // flagged with an "Editing…" badge instead of being hidden or removed.
    function renderAllQuizzes(){
      const listEl = document.getElementById('allQuizzesList');
      const attachedRows = Object.keys(CUSTOM_QUIZZES).map(bookId => {
        const book = LIBRARY.find(b => String(b.id) === String(bookId));
        return { source: 'book', bookId, quiz: CUSTOM_QUIZZES[bookId], book };
      }).filter(row => row.book); // drop orphaned entries if a resource was ever removed
      const standaloneRows = STANDALONE_QUIZZES.map((quiz, idx) => ({ source: 'standalone', idx, quiz }));
      const allRows = [...attachedRows, ...standaloneRows];

      if (!allRows.length){
        listEl.innerHTML = `
          <div class="cq-empty-quizzes">
            <i class='bx bx-help-circle bx-tada'></i>
            <b>No quizzes yet</b>
            Build one in the Create Quiz tab, or upload a quiz file above.
          </div>`;
        return;
      }

      listEl.innerHTML = allRows.map((row, i) => {
        const q = row.quiz;
        const isEditing = (row.source === 'book' && cqEditingBookId != null && String(row.bookId) === String(cqEditingBookId)) ||
                           (row.source === 'standalone' && cqEditingStandaloneIdx === row.idx);
        const badge = row.source === 'book'
          ? `<span class="cq-saved-badge"><i class='bx bx-link'></i> ${row.book.title}</span>`
          : `<span class="cq-saved-badge standalone">Standalone</span>`;
        const editingBadge = isEditing ? `<span class="cq-saved-badge editing"><i class='bx bx-edit-alt'></i> Editing…</span>` : '';
        return `
          <div class="cq-saved-row ${isEditing ? 'editing' : ''}">
            <div class="cq-saved-icon"><i class='bx bx-help-circle'></i></div>
            <div class="cq-saved-info">
              <div class="cq-saved-name">${q.title}${badge}${editingBadge}</div>
              <div class="cq-saved-sub">${q.questions.length} question${q.questions.length === 1 ? '' : 's'}</div>
            </div>
            <div class="cq-saved-actions">
              <button class="cq-saved-launch" data-row="${i}"><i class='bx bx-play'></i> Take Quiz</button>
              <button class="cq-saved-edit" data-row="${i}" title="Edit"><i class='bx bx-edit-alt'></i></button>
              <button class="cq-saved-delete" data-row="${i}" title="Delete"><i class='bx bx-trash'></i></button>
            </div>
          </div>`;
      }).join('');

      listEl.querySelectorAll('.cq-saved-launch').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = allRows[Number(btn.dataset.row)];
          if (row.source === 'book'){
            openStudentPicker(row.book, 'quiz');
          } else {
            openStudentPicker({ id: 'standalone-' + row.idx, title: row.quiz.title, customQuiz: row.quiz }, 'quiz');
          }
        });
      });
      listEl.querySelectorAll('.cq-saved-edit').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = allRows[Number(btn.dataset.row)];
          loadQuizIntoBuilder(row);
        });
      });
      listEl.querySelectorAll('.cq-saved-delete').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = allRows[Number(btn.dataset.row)];
          if (row.source === 'book'){
            delete CUSTOM_QUIZZES[row.bookId];
            if (cqEditingBookId != null && String(cqEditingBookId) === String(row.bookId)) cqEditingBookId = null;
            renderLibrary(); // clear the custom-quiz dot on the book card
          } else {
            STANDALONE_QUIZZES.splice(row.idx, 1);
            if (cqEditingStandaloneIdx === row.idx) cqEditingStandaloneIdx = null;
          }
          saveQuizzes();
          renderAllQuizzes();
          showToast('Quiz deleted');
        });
      });
    }

    // Pulls a saved quiz into the builder form on the Create Quiz tab so the
    // teacher can tweak it. The quiz is deliberately left in place in
    // CUSTOM_QUIZZES / STANDALONE_QUIZZES while it's being edited — it only
    // ever gets removed from its old slot once the edited version is
    // actually saved (see the Save Quiz handler below). That way switching
    // tabs, navigating away, or opening a different quiz mid-edit never
    // silently deletes anything; at worst an in-progress edit is discarded,
    // and the original quiz is exactly as it was before Edit was clicked.
    function loadQuizIntoBuilder(row){
      if (row.source === 'book'){
        cqEditingBookId = row.bookId;
        cqEditingStandaloneIdx = null;
      } else {
        cqEditingStandaloneIdx = row.idx;
        cqEditingBookId = null;
      }
      cqDraftQuestions = row.quiz.questions.slice();
      document.getElementById('cqTitle').value = row.quiz.title;
      buildCqBookSelect();
      document.getElementById('cqBookSelect').value = row.source === 'book' ? row.bookId : '';
      renderCqDraftList();
      updateCqSaveState();
      renderAllQuizzes();
      document.querySelector('#resTabs .tab[data-tab="createquiz"]').click();
      showToast(`Editing "${row.quiz.title}" — your changes save when you click "Save Quiz"`);
    }

    document.getElementById('cqSaveQuizBtn').addEventListener('click', () => {
      const title = document.getElementById('cqTitle').value.trim();
      if (!title || !cqDraftQuestions.length) return;
      const bookId = document.getElementById('cqBookSelect').value;
      const quiz = { title, questions: cqDraftQuestions.slice() };

      // Only now — with the replacement ready — clear the quiz's old slot.
      // Covers moving an edited quiz to a different resource, or between
      // "attached" and "standalone", without ever leaving a stale
      // duplicate behind or losing data if the edit had been abandoned.
      if (cqEditingBookId != null && cqEditingBookId !== bookId){
        delete CUSTOM_QUIZZES[cqEditingBookId];
      }
      if (cqEditingStandaloneIdx != null && bookId){
        STANDALONE_QUIZZES.splice(cqEditingStandaloneIdx, 1);
      }

      if (bookId){
        const book = LIBRARY.find(b => String(b.id) === String(bookId));
        CUSTOM_QUIZZES[bookId] = quiz;
        saveQuizzes();
        showToast(`"${title}" saved — attached to "${book ? book.title : 'that resource'}". Find it in the Quizzes tab anytime.`);
        renderLibrary(); // refresh the little custom-quiz dot on the book card
      } else if (cqEditingStandaloneIdx != null){
        // Editing an existing standalone quiz — update it in place instead
        // of appending a duplicate at the end of the list.
        STANDALONE_QUIZZES[cqEditingStandaloneIdx] = quiz;
        saveQuizzes();
        showToast(`"${title}" saved — find it in the Quizzes tab anytime.`);
      } else {
        STANDALONE_QUIZZES.push(quiz);
        saveQuizzes();
        showToast(`"${title}" saved as a standalone quiz — find it in the Quizzes tab anytime.`);
      }

      // Reset the builder for the next quiz.
      cqDraftQuestions = [];
      cqEditingBookId = null;
      cqEditingStandaloneIdx = null;
      document.getElementById('cqTitle').value = '';
      document.getElementById('cqQPrompt').value = '';
      document.querySelectorAll('.cq-opt-input').forEach(i => i.value = '');
      document.querySelector('input[name="cqCorrect"][value="0"]').checked = true;
      document.getElementById('cqExplain').value = '';
      buildCqBookSelect();
      renderCqDraftList();
      updateCqSaveState();
      renderAllQuizzes();
    });

    /* =====================================================================
       QUIZ UPLOAD — lets a teacher import a quiz written elsewhere as a
       JSON file: { title, questions: [{ prompt, options, correctIndex,
       explanation }] }. Mirrors the resource Upload tab's dropzone pattern.
    ===================================================================== */
    const quizDropzone = document.getElementById('quizDropzone');
    const quizFileInput = document.getElementById('quizFileInput');
    const quizUploadPreview = document.getElementById('quizUploadPreview');
    let pendingUploadedQuiz = null;

    function validateQuizJson(data){
      if (!data || typeof data !== 'object') return 'That file is not a valid quiz.';
      if (!data.title || typeof data.title !== 'string') return 'The quiz needs a "title".';
      if (!Array.isArray(data.questions) || !data.questions.length) return 'The quiz needs a non-empty "questions" array.';
      for (const q of data.questions){
        if (!q || typeof q.prompt !== 'string' || !q.prompt.trim()) return 'Every question needs a "prompt".';
        if (!Array.isArray(q.options) || q.options.length < 2) return 'Every question needs at least two "options".';
        if (typeof q.correctIndex !== 'number' || q.correctIndex < 0 || q.correctIndex >= q.options.length) return 'Every question needs a valid "correctIndex".';
      }
      return null;
    }

    function handleQuizFile(file){
      if (!file){ return; }
      if (!/\.json$/i.test(file.name)){
        showToast('Please upload a .json quiz file');
        return;
      }
      const reader = new FileReader();
      reader.onload = () => {
        let data;
        try {
          data = JSON.parse(reader.result);
        } catch (e) {
          showToast("That file isn't valid JSON");
          return;
        }
        const error = validateQuizJson(data);
        if (error){ showToast(error); return; }
        pendingUploadedQuiz = {
          title: data.title,
          questions: data.questions.map(q => ({
            prompt: q.prompt,
            options: q.options,
            correctIndex: q.correctIndex,
            explanation: (typeof q.explanation === 'string' && q.explanation.trim()) ? q.explanation : 'Great job working through that one!'
          }))
        };
        document.getElementById('quizUploadTitle').textContent = pendingUploadedQuiz.title;
        document.getElementById('quizUploadMeta').textContent = `${pendingUploadedQuiz.questions.length} question${pendingUploadedQuiz.questions.length === 1 ? '' : 's'} · from ${file.name}`;
        buildBookSelectOptions(document.getElementById('quizUploadBookSelect'));
        quizUploadPreview.classList.add('show');
      };
      reader.onerror = () => showToast('Could not read that file');
      reader.readAsText(file);
    }

    quizDropzone.addEventListener('click', () => quizFileInput.click());
    quizFileInput.addEventListener('change', (e) => { handleQuizFile(e.target.files[0]); quizFileInput.value = ''; });
    ['dragenter','dragover'].forEach(evt => quizDropzone.addEventListener(evt, (e) => {
      e.preventDefault(); quizDropzone.classList.add('dragover');
    }));
    ['dragleave','drop'].forEach(evt => quizDropzone.addEventListener(evt, (e) => {
      e.preventDefault(); quizDropzone.classList.remove('dragover');
    }));
    quizDropzone.addEventListener('drop', (e) => {
      if (e.dataTransfer.files.length) handleQuizFile(e.dataTransfer.files[0]);
    });

    document.getElementById('quizUploadDiscardBtn').addEventListener('click', () => {
      pendingUploadedQuiz = null;
      quizUploadPreview.classList.remove('show');
    });

    document.getElementById('quizUploadAddBtn').addEventListener('click', () => {
      if (!pendingUploadedQuiz) return;
      const bookId = document.getElementById('quizUploadBookSelect').value;
      if (bookId){
        const book = LIBRARY.find(b => String(b.id) === String(bookId));
        CUSTOM_QUIZZES[bookId] = pendingUploadedQuiz;
        saveQuizzes();
        showToast(`"${pendingUploadedQuiz.title}" attached to "${book ? book.title : 'that resource'}"`);
        renderLibrary();
      } else {
        STANDALONE_QUIZZES.push(pendingUploadedQuiz);
        saveQuizzes();
        showToast(`"${pendingUploadedQuiz.title}" added as a standalone quiz`);
      }
      pendingUploadedQuiz = null;
      quizUploadPreview.classList.remove('show');
      renderAllQuizzes();
    });

    // Bundles a book's data and its teacher-authored quiz into the shape
    // quiz.php expects and jumps straight to the quiz for that story.
    // If no quiz has been created for the book, the teacher is told so
    // instead of navigating (no auto-generated fallback exists).
    // There are NO auto-generated quizzes. A quiz exists only if a teacher
    // created one (attached to this resource, or a standalone quiz passed in
    // as b.customQuiz).
    function quizFor(b){
      return b.customQuiz || CUSTOM_QUIZZES[b.id] || null;
    }
    function hasQuizFor(b){
      const quiz = quizFor(b);
      return Boolean(quiz && Array.isArray(quiz.questions) && quiz.questions.length);
    }
    // Pop-up shown when "Take Quiz" is clicked on a resource that has no quiz.
    // Offers to create one; "Create Quiz" opens the Create Quiz tab with this
    // resource already selected.
    let noQuizBook = null;
    function notifyNoQuiz(b){
      noQuizBook = b;
      document.getElementById('noQuizMessage').textContent =
        `There is no quiz created for "${b.title}" yet. Would you like to create one now?`;
      document.getElementById('noQuizDialog').showModal();
    }
    document.getElementById('noQuizCancelBtn').addEventListener('click', () => {
      document.getElementById('noQuizDialog').close();
      noQuizBook = null;
    });
    document.getElementById('noQuizCreateBtn').addEventListener('click', () => {
      const book = noQuizBook;
      noQuizBook = null;
      document.getElementById('noQuizDialog').close();
      resOverlay.classList.remove('open');
      document.querySelector('#resTabs .tab[data-tab="createquiz"]').click();
      if (book && LIBRARY.some(item => String(item.id) === String(book.id))){
        document.getElementById('cqBookSelect').value = String(book.id);
      }
      document.getElementById('cqTitle').focus();
    });

    function startQuizFor(b, student){
      if (!hasQuizFor(b)){ notifyNoQuiz(b); return; }
      const custom = quizFor(b);
      const quizStory = {
        id: b.id,
        title: custom ? custom.title : b.title,
        author: b.author,
        level: b.level,
        difficulty: resourceDifficulty(b),
        genre: b.genre,
        lines: b.title ? linesFor(b) : [],
        customQuestions: custom ? custom.questions : null,
        studentId: student ? student.id : null,
        studentName: student ? student.name : null
      };
      try {
        sessionStorage.setItem('readpilot-quiz-story', JSON.stringify(quizStory));
      } catch (e) {
        /* sessionStorage unavailable (e.g. private browsing) — quiz.php
           falls back to its own demo story */
      }
      window.location.href = 'quiz.php';
    }

    // ---- Resource detail modal (library books) ----
    const resOverlay = document.getElementById('resOverlay');
    const resModalEl = resOverlay.querySelector('.res-modal');

    // Small burst of sparkle emoji around the icon when the modal opens —
    // purely decorative, removes itself after the animation finishes.
    function spawnResSparkles(){
      resModalEl.querySelectorAll('.res-sparkle').forEach(el => el.remove());
      const glyphs = ['✨','⭐','🌟'];
      const spots = [
        { top: '6px',  left: '30%' },
        { top: '2px',  left: '68%' },
        { top: '30px', left: '80%' },
        { top: '34px', left: '18%' }
      ];
      spots.forEach((pos, i) => {
        const s = document.createElement('span');
        s.className = 'res-sparkle';
        s.textContent = glyphs[i % glyphs.length];
        s.style.top = pos.top;
        s.style.left = pos.left;
        s.style.animationDelay = (i * 0.08) + 's';
        resModalEl.appendChild(s);
      });
    }

    function openResourceModal(b){
      const color = GENRE_COLORS[b.genre] || GENRE_COLORS.default;
      document.getElementById('resCoverBanner').innerHTML = coverFor(b);
      document.getElementById('resModalIcon').style.background = color;
      document.getElementById('resModalTitle').textContent = b.title;
      document.getElementById('resModalAuthor').textContent = b.author;
      document.getElementById('resModalLevel').textContent = b.level;
      document.getElementById('resModalLexile').textContent = b.lexile;
      document.getElementById('resModalWords').textContent = b.words;
      document.getElementById('resModalAssigned').textContent = b.assigned + '×';
      document.getElementById('resModalDesc').textContent = b.desc;
      document.getElementById('resModalTags').innerHTML = `<span class="difficulty-pill ${difficultyClass(resourceDifficulty(b))}">${resourceDifficulty(b)} reading</span>` + b.tags.map(t => `<span class="res-tag">${escapeHtml(t)}</span>`).join('');
      const deleteBtn = document.getElementById('resModalDeleteBtn');
      deleteBtn.hidden = !b.canDelete;
      deleteBtn.onclick = () => confirmRemoveResource(b);
      const assignBtn = document.getElementById('resModalAssignBtn');
      assignBtn.onclick = () => {
        resOverlay.classList.remove('open');
        openStudentPicker(b, 'single');
      };
      document.getElementById('resModalQuizBtn').onclick = () => {
        resOverlay.classList.remove('open');
        openStudentPicker(b, 'quiz');
      };

      // Restart the entrance + icon-pop animations every time the modal opens
      resModalEl.style.animation = 'none';
      document.getElementById('resModalIcon').style.animation = 'none';
      // Force reflow so the animation restarts cleanly
      void resModalEl.offsetWidth;
      resModalEl.style.animation = '';
      document.getElementById('resModalIcon').style.animation = '';
      spawnResSparkles();

      resOverlay.classList.add('open');
    }
    document.getElementById('resModalClose').addEventListener('click', () => resOverlay.classList.remove('open'));
    resOverlay.addEventListener('click', (e) => { if (e.target === resOverlay) resOverlay.classList.remove('open'); });

    function openRequestedResource(){
      const resourceId = new URLSearchParams(window.location.search).get('resource_id');
      if (!resourceId) return;
      const resource = LIBRARY.find(item => String(item.id) === resourceId);
      if (!resource) return;
      document.querySelector('#resTabs .tab[data-tab="library"]').click();
      openResourceModal(resource);
    }

    const resourceDeleteDialog = document.getElementById('resourceDeleteDialog');
    const resourceDeleteMessage = document.getElementById('resourceDeleteMessage');
    const confirmResourceDelete = document.getElementById('confirmResourceDelete');
    document.getElementById('cancelResourceDelete').addEventListener('click', () => resourceDeleteDialog.close());

    function confirmRemoveResource(book){
      if (!book.canDelete) return;
      resourceDeleteMessage.textContent = `Permanently remove "${book.title}" for all teachers? This also removes its student assignments, uploaded text, and attached quizzes.`;
      confirmResourceDelete.onclick = () => removeResource(book);
      resourceDeleteDialog.showModal();
    }

    async function removeResource(book){
      if (!book.canDelete) return;
      const deleteBtn = document.getElementById('resModalDeleteBtn');
      deleteBtn.disabled = true;
      confirmResourceDelete.disabled = true;
      try {
        const response = await fetch('student-api.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: new URLSearchParams({action: 'delete_resource', resource_id: book.id})
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Unable to remove resource');

        LIBRARY = LIBRARY.filter(resource => String(resource.id) !== String(book.id));
        delete CUSTOM_QUIZZES[book.id];
        if (String(cqEditingBookId) === String(book.id)){
          cqEditingBookId = null;
          cqDraftQuestions = [];
        }
        saveQuizzes();
        resourceDeleteDialog.close();
        resOverlay.classList.remove('open');
        buildGenreFilter();
        buildCqBookSelect();
        renderLibrary();
        renderAllQuizzes();
        showToast(`"${book.title}" removed from your library`);
      } catch(error) {
        showToast(error.message || 'Unable to remove resource');
      } finally {
        deleteBtn.disabled = false;
        confirmResourceDelete.disabled = false;
      }
    }

    /* =====================================================================
       STUDENT PICKER
       Lets the teacher choose which student(s) a resource — or quiz — is
       meant for.
       - 'multi' mode (grid "Assign" button): pick any number of students,
         no navigation — just logs the assignment.
       - 'single' mode ("Start Reading" in the detail modal): pick exactly
         one student, then jumps straight into a reading session for them.
       - 'quiz' mode ("Take Quiz" buttons, and launching a saved custom
         quiz): pick exactly one student, then jumps into quiz.php for
         them, using that resource's custom quiz if one has been created.
       Swap STUDENTS for a shared roster / API response when wiring up a
       backend so this list always matches the Students page.
    ===================================================================== */
    let STUDENTS = [];
    let studentRosterLoading = true;

    fetch('student-api.php').then(response => response.json().then(result => {
      if(!response.ok) throw new Error(result.error || 'Unable to load students');
      return result;
    })).then(result => {
      if (Array.isArray(result.students)) {
        STUDENTS = result.students.map(student => ({
          id: Number(student.id),
          name: student.name,
          grade: student.section,
          color: student.color,
          wpm: Number(student.wpm) || 0,
          accuracy: Number(student.accuracy) || 0,
          sessions: Number(student.sessions) || 0
        }));
      }
      studentRosterLoading = false;
      if(spPendingBook){
        populateStudentSectionFilter();
        renderStudentPicker();
      }
    }).catch(error => {
      studentRosterLoading = false;
      if(spPendingBook) renderStudentPicker();
      showToast(error.message || 'Unable to load students');
    });

    function spInitials(name){
      return name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
    }

    const spOverlay = document.getElementById('spOverlay');
    const spListEl = document.getElementById('spList');
    const spConfirmBtn = document.getElementById('spConfirmBtn');
    const spCountEl = document.getElementById('spCount');
    let spSelected = new Set();
    let spMode = 'multi';
    let spPendingBook = null;
    let spSearchTerm = '';
    let spSectionFilter = 'all';

    function populateStudentSectionFilter(){
      const select = document.getElementById('spSectionFilter');
      const sections = [...new Set(STUDENTS.map(student => student.grade).filter(Boolean))]
        .sort((a,b) => a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'}));
      select.innerHTML = '<option value="all">All Sections</option>' +
        sections.map(section => `<option value="${escapeHtml(section)}">${escapeHtml(section)}</option>`).join('');
      if(spSectionFilter !== 'all' && !sections.includes(spSectionFilter)) spSectionFilter = 'all';
      select.value = spSectionFilter;
    }

    function renderStudentPicker(){
      const list = STUDENTS.filter(s =>
        (spSectionFilter === 'all' || s.grade === spSectionFilter) &&
        (!spSearchTerm || s.name.toLowerCase().includes(spSearchTerm))
      );
      if(studentRosterLoading){
        spListEl.innerHTML = `<div class="sp-empty">Loading students...</div>`;
      } else if (!list.length){
        spListEl.innerHTML = `<div class="sp-empty">${STUDENTS.length ? 'No students match that search.' : 'No students are available for this account.'}</div>`;
      } else {
        spListEl.innerHTML = list.map(s => {
          const recommendedLevel = studentDifficulty(s);
          const selectedLevel = spPendingBook ? resourceDifficulty(spPendingBook) : 'Beginner';
          const tooChallenging = DIFFICULTY_ORDER[selectedLevel] > DIFFICULTY_ORDER[recommendedLevel];
          return `
          <div class="sp-row ${spSelected.has(s.id) ? 'selected' : ''} ${tooChallenging ? 'too-challenging' : ''}" data-id="${s.id}">
            <div class="sp-avatar" style="background:${s.color}">${spInitials(s.name)}</div>
            <div class="sp-info">
              <div class="sp-name">${s.name}</div>
              <div class="sp-grade">${s.grade}</div>
              <div class="sp-level-row"><span class="difficulty-pill ${difficultyClass(recommendedLevel)}">Suggested: ${recommendedLevel}</span><small>${s.sessions} sessions · ${s.wpm} WPM · ${s.accuracy}% accuracy</small></div>
              ${tooChallenging ? `<div class="sp-grade">This ${selectedLevel} title is above the suggested level.</div>` : ''}
            </div>
            <div class="sp-check">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
          </div>`;
        }).join('');
        spListEl.querySelectorAll('.sp-row').forEach(row => {
          row.addEventListener('click', () => {
            const student = STUDENTS.find(item => item.id === Number(row.dataset.id));
            if(student && spPendingBook && DIFFICULTY_ORDER[resourceDifficulty(spPendingBook)] > DIFFICULTY_ORDER[studentDifficulty(student)]){
              showToast(`${student.name} is currently recommended for ${studentDifficulty(student)} reading. Choose a ${studentDifficulty(student)} resource first.`);
              return;
            }
            toggleStudent(Number(row.dataset.id));
          });
        });
      }
      spCountEl.textContent = spSelected.size === 0
        ? 'No one selected'
        : spSelected.size + (spSelected.size === 1 ? ' student selected' : ' students selected');
      spConfirmBtn.disabled = spSelected.size === 0;
    }

    function toggleStudent(id){
      if (spMode === 'single' || spMode === 'quiz'){
        spSelected = spSelected.has(id) ? new Set() : new Set([id]);
      } else {
        if (spSelected.has(id)) spSelected.delete(id); else spSelected.add(id);
      }
      renderStudentPicker();
    }

    function openStudentPicker(book, mode){
      if (mode === 'quiz' && !hasQuizFor(book)){
        notifyNoQuiz(book);
        return;
      }
      spPendingBook = book;
      spMode = mode;
      spSelected = new Set();
      spSearchTerm = '';
      spSectionFilter = 'all';
      document.getElementById('spSearch').value = '';
      populateStudentSectionFilter();
      document.getElementById('spBookTitle').textContent = book.title;
      document.getElementById('spTitle').textContent =
        mode === 'single' ? 'Who is reading this?' :
        mode === 'quiz' ? 'Who is taking this quiz?' : 'Assign to Students';
      document.getElementById('spSubtitle').textContent =
        mode === 'single' ? `Choose one student · this book is ${resourceDifficulty(book)}` :
        mode === 'quiz' ? 'Choose one student to take the quiz' :
        'Select one or more students for this resource';
      renderStudentPicker();
      spOverlay.classList.add('open');
    }

    function closeStudentPicker(){
      spOverlay.classList.remove('open');
      spPendingBook = null;
    }

    document.getElementById('spSearch').addEventListener('input', (e) => {
      spSearchTerm = e.target.value.trim().toLowerCase();
      renderStudentPicker();
    });
    document.getElementById('spSectionFilter').addEventListener('change', (e) => {
      spSectionFilter = e.target.value;
      renderStudentPicker();
    });
    document.getElementById('spCloseBtn').addEventListener('click', closeStudentPicker);
    document.getElementById('spCancelBtn').addEventListener('click', closeStudentPicker);
    spOverlay.addEventListener('click', (e) => { if (e.target === spOverlay) closeStudentPicker(); });

    spConfirmBtn.addEventListener('click', () => {
      if (!spPendingBook || spSelected.size === 0) return;
      const chosen = STUDENTS.filter(s => spSelected.has(s.id));
      const book = spPendingBook;
      const mode = spMode;
      closeStudentPicker();

      if (mode === 'single'){
        // "Start Reading" flow — assign to the one chosen student, then
        // jump straight into a reading session for them.
        const student = chosen[0];
        book.assigned += 1;
        fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'assign_resource', resource_id:book.id, student_id:student.id})}).catch(()=>{});
        book.justAssigned = true;
        renderLibrary();
        setTimeout(() => { book.justAssigned = false; renderLibrary(); }, 2200);

        const color = GENRE_COLORS[book.genre] || GENRE_COLORS.default;
        const session = {
          id: book.id,
          title: book.title,
          author: book.author,
          level: book.level,
          difficulty: resourceDifficulty(book),
          genre: book.genre,
          color: color,
          lines: linesFor(book),
          studentId: student.id,
          studentName: student.name
        };
        try {
          sessionStorage.setItem('readpilot-reading-session', JSON.stringify(session));
        } catch (e) {
          /* sessionStorage unavailable — reading.php falls back to its own demo story */
        }
        showToast(`Starting "${book.title}" with ${student.name}`);
        window.location.href = 'reading.php';
      } else if (mode === 'quiz'){
        // "Take Quiz" flow — pick one student, then launch the quiz for
        // them, using a teacher-authored custom quiz when one exists.
        const student = chosen[0];
        showToast(`Starting the "${book.title}" quiz with ${student.name}`);
        startQuizFor(book, student);
      } else {
        // "Assign" flow — can target several students at once, no navigation.
        book.assigned += chosen.length;
        chosen.forEach(student => fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'assign_resource', resource_id:book.id, student_id:student.id})}).catch(()=>{}));
        book.justAssigned = true;
        renderLibrary();
        setTimeout(() => { book.justAssigned = false; renderLibrary(); }, 2200);

        const names = chosen.map(s => s.name);
        const label = names.length === 1 ? names[0] : names.length + ' students';
        showToast(`"${book.title}" assigned to ${label}`);
      }
    });

    /* =====================================================================
       UPLOAD TAB
    ===================================================================== */
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileListEl = document.getElementById('fileList');

    const EXT_STYLE = {
      pdf:  { icon: 'bxs-file-pdf',  color: '#ea5d5d' },
      doc:  { icon: 'bxs-file-doc',  color: '#4fa3b8' },
      docx: { icon: 'bxs-file-doc',  color: '#4fa3b8' },
      txt:  { icon: 'bxs-file-txt',  color: '#7c8d82' },
      png:  { icon: 'bxs-file-image', color: '#8b6bd1' },
      jpg:  { icon: 'bxs-file-image', color: '#8b6bd1' },
      jpeg: { icon: 'bxs-file-image', color: '#8b6bd1' },
      default: { icon: 'bxs-file', color: '#f2a13a' }
    };

    function formatSize(bytes){
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    const READER_SCRIPTS = {};
    function loadReaderScript(src, isReady){
      if (isReady()) return Promise.resolve();
      if (!READER_SCRIPTS[src]){
        READER_SCRIPTS[src] = new Promise((resolve, reject) => {
          const script = document.createElement('script');
          script.src = src;
          script.onload = resolve;
          script.onerror = () => {
            delete READER_SCRIPTS[src];
            reject(new Error('Document reader could not load. Check your connection and try again.'));
          };
          document.head.appendChild(script);
        });
      }
      return READER_SCRIPTS[src];
    }

    async function extractFileText(file, ext){
      let text = '';
      if (ext === 'txt'){
        text = await file.text();
      } else if (ext === 'docx'){
        await loadReaderScript('https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.8.0/mammoth.browser.min.js', () => Boolean(window.mammoth));
        if (!window.mammoth) throw new Error('Word document reader did not load. Refresh the page and try again.');
        const result = await window.mammoth.extractRawText({arrayBuffer: await file.arrayBuffer()});
        text = result.value;
      } else if (ext === 'pdf'){
        await loadReaderScript('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js', () => Boolean(window.pdfjsLib));
        if (!window.pdfjsLib) throw new Error('PDF reader did not load. Refresh the page and try again.');
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        const pdf = await window.pdfjsLib.getDocument({data: new Uint8Array(await file.arrayBuffer())}).promise;
        const pages = [];
        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++){
          const page = await pdf.getPage(pageNumber);
          const pageText = await page.getTextContent();
          pages.push(pageText.items.map(item => item.str || '').join(' '));
        }
        text = pages.join('\n');
      } else {
        throw new Error('Choose a .txt, .pdf, or .docx file.');
      }

      text = text
        .replace(/\u0000/g, '')
        .replace(/(^|\s)[\u2022\u00B7\u25AA\u25AB\u25CF\u25E6\u2043\u2219](?=\s)/g, '$1')
        .replace(/(^|\s)\.(?=\s+[A-Za-z])/g, '$1')
        .replace(/[ \t]{2,}/g, ' ')
        .replace(/[ \t]+\n/g, '\n')
        .trim();
      if (!text) throw new Error(ext === 'pdf'
        ? 'No selectable text was found. This PDF may be scanned; scanned PDFs need OCR.'
        : 'No readable text was found in this file.');
      if (text.length > 1000000) throw new Error('This document contains too much text. Please upload a smaller document.');
      return text;
    }

    function handleFiles(fileArr){
      [...fileArr].forEach(f => {
        const ext = (f.name.split('.').pop() || '').toLowerCase();
        const entry = {
          id: nextId++,
          name: f.name,
          sizeLabel: formatSize(f.size),
          ext,
          file: f,
          status: 'processing',
          error: ''
        };
        if (!['txt', 'pdf', 'docx'].includes(ext)){
          showToast(`"${f.name}" is not supported. Choose a .txt, .pdf, or .docx file.`);
          return;
        }
        if (f.size > 20 * 1024 * 1024){
          showToast(`"${f.name}" is larger than the 20MB upload limit.`);
          return;
        }
        UPLOADED_FILES.push(entry);
        renderFileList();
        extractFileText(f, ext).then(content => {
          entry.content = content;
          entry.words = content.split(/\s+/).filter(Boolean).length;
          entry.status = 'ready';
        }).catch(error => {
          entry.status = 'error';
          entry.error = error.message || 'Unable to extract text from this file.';
        }).finally(() => {
          renderFileList();
        });
      });
    }

    function renderFileList(){
      fileListEl.innerHTML = '';
      UPLOADED_FILES.forEach(f => {
        const style = EXT_STYLE[f.ext] || EXT_STYLE.default;
        const row = document.createElement('div');
        row.className = 'file-row';
        row.innerHTML = `
          <div class="file-icon" style="background:${style.color}"><i class='bx ${style.icon}'></i></div>
          <div class="file-info">
            <div class="file-name"></div>
            <div class="file-sub"></div>
          </div>
          <span class="file-status ${f.status}">${f.status === 'ready' ? 'Ready' : f.status === 'error' ? 'Could not read' : f.status === 'saving' ? 'Saving…' : 'Reading…'}</span>
          <button class="file-add-btn" ${f.status !== 'ready' ? 'disabled' : ''}>Add to Library</button>
          <button class="file-remove" title="Remove"><i class='bx bx-x'></i></button>
        `;
        row.querySelector('.file-name').textContent = f.name;
        row.querySelector('.file-sub').textContent = f.error || (f.status === 'ready'
          ? `${f.sizeLabel} · ${f.words} words extracted`
          : `${f.sizeLabel} · .${f.ext || 'file'}`);
        row.querySelector('.file-add-btn').addEventListener('click', () => addUploadToLibrary(f));
        row.querySelector('.file-remove').addEventListener('click', () => {
          UPLOADED_FILES = UPLOADED_FILES.filter(x => x.id !== f.id);
          renderFileList();
        });
        fileListEl.appendChild(row);
      });
    }

    async function addUploadToLibrary(f){
      const title = f.name.replace(/\.[^/.]+$/, '');
      const mimeTypes = {txt: 'text/plain', pdf: 'application/pdf', docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'};
      f.status = 'saving';
      f.error = '';
      renderFileList();
      try {
        const uploadData = new FormData();
        uploadData.append('action', 'save_resource');
        uploadData.append('title', title);
        uploadData.append('author', 'Uploaded material');
        uploadData.append('genre', 'Uploaded');
        uploadData.append('level', 'Grade 3');
        uploadData.append('lexile', '');
        uploadData.append('words', String(f.words));
        uploadData.append('description', `Uploaded from your computer (${f.sizeLabel}).`);
        uploadData.append('file_name', f.name);
        uploadData.append('mime_type', mimeTypes[f.ext]);
        uploadData.append('content', f.content);
        const savedResponse = await fetch('student-api.php', {method: 'POST', body: uploadData});
        const result = await savedResponse.json();
        if (!savedResponse.ok) throw new Error(result.error || 'Unable to save material');
        UPLOADED_FILES = UPLOADED_FILES.filter(x => x.id !== f.id);
        const libraryResponse = await fetch('student-api.php?view=resources');
        const libraryResult = await libraryResponse.json();
        if (!libraryResponse.ok) throw new Error(libraryResult.error || 'Resource was saved, but the library could not be refreshed.');
        LIBRARY = libraryResult.resources || LIBRARY;
        buildGenreFilter();
        renderFileList();
        renderLibrary();
        showToast(`"${result.title || title}" added to your library`);
        document.querySelector('#resTabs .tab[data-tab="library"]').click();
      } catch(error) {
        f.status = 'ready';
        f.error = error.message || 'Unable to save material.';
        renderFileList();
        showToast(f.error);
      }
    }

    dropzone.addEventListener('click', (event) => { if (event.target !== fileInput) fileInput.click(); });
    fileInput.addEventListener('change', (e) => { handleFiles(e.target.files); fileInput.value = ''; });
    ['dragenter','dragover'].forEach(evt => dropzone.addEventListener(evt, (e) => {
      e.preventDefault(); dropzone.classList.add('dragover');
    }));
    ['dragleave','drop'].forEach(evt => dropzone.addEventListener(evt, (e) => {
      e.preventDefault(); dropzone.classList.remove('dragover');
    }));
    dropzone.addEventListener('drop', (e) => {
      if (e.dataTransfer.files.length) handleFiles(e.dataTransfer.files);
    });

    /* =====================================================================
       FROM URL TAB
    ===================================================================== */
    const urlInput = document.getElementById('urlInput');
    const urlFetchBtn = document.getElementById('urlFetchBtn');
    const urlPreview = document.getElementById('urlPreview');
    let pendingUrlResource = null;

    function titleCaseFromSlug(str){
      return str.replace(/[-_]/g, ' ').replace(/\.(html?|php|aspx?)$/i, '')
        .split(' ').filter(Boolean)
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ') || 'Untitled Article';
    }

    urlFetchBtn.addEventListener('click', () => {
      const raw = urlInput.value.trim();
      if (!raw) { showToast('Paste a URL first'); return; }
      let parsed;
      try {
        parsed = new URL(raw.match(/^https?:\/\//i) ? raw : 'https://' + raw);
      } catch (err) {
        showToast("That doesn't look like a valid URL");
        return;
      }
      urlFetchBtn.disabled = true;
      urlFetchBtn.innerHTML = `<span class="spinner"></span> Fetching...`;
      urlPreview.classList.remove('show');

      // NOTE: fetching and extracting readable text from an arbitrary page requires
      // a server-side step (to avoid CORS issues and to strip nav/ads/scripts).
      // This simulates that step for the prototype — wire it to your backend endpoint
      // (e.g. POST /api/resources/fetch-url) to make it real.
      setTimeout(() => {
        const lastSegment = parsed.pathname.split('/').filter(Boolean).pop() || parsed.hostname;
        const title = titleCaseFromSlug(lastSegment);
        const words = 400 + Math.floor(Math.random() * 900);
        pendingUrlResource = { id: nextId++, title, source: parsed.hostname, url: parsed.href, words };
        document.getElementById('urlPreviewTitle').textContent = title;
        document.getElementById('urlPreviewSource').textContent = parsed.href;
        document.getElementById('urlPreviewMeta').textContent = `~${words} words · from ${parsed.hostname}`;
        urlPreview.classList.add('show');
        urlFetchBtn.disabled = false;
        urlFetchBtn.innerHTML = `<i class='bx bx-download'></i> Fetch`;
      }, 1100);
    });

    document.getElementById('urlDiscardBtn').addEventListener('click', () => {
      urlPreview.classList.remove('show');
      pendingUrlResource = null;
      urlInput.value = '';
    });

    document.getElementById('urlAddBtn').addEventListener('click', () => {
      if (!pendingUrlResource) return;
      const r = pendingUrlResource;
      URL_RESOURCES.push(r);
      LIBRARY.push({
        id: r.id, title: r.title, author: r.source, genre: 'Nonfiction', level: 'Grade 3',
        lexile: '—', words: r.words, desc: `Fetched from ${r.source}. Ready to assign to your Grade 3 class.`,
        tags: ['From the Web'], assigned: 0
      });
      buildGenreFilter();
      renderUrlAddedList();
      renderLibrary();
      showToast(`"${r.title}" added to your library`);
      urlPreview.classList.remove('show');
      urlInput.value = '';
      pendingUrlResource = null;
      document.querySelector('#resTabs .tab[data-tab="library"]').click();
    });

    function renderUrlAddedList(){
      const listEl = document.getElementById('urlAddedList');
      listEl.innerHTML = '';
      URL_RESOURCES.forEach(r => {
        const row = document.createElement('div');
        row.className = 'file-row';
        row.innerHTML = `
          <div class="file-icon" style="background:#4fa3b8"><i class='bx bx-link-external'></i></div>
          <div class="file-info">
            <div class="file-name">${r.title}</div>
            <div class="file-sub">${r.source} · ~${r.words} words</div>
          </div>
          <span class="file-status ready">Added</span>
        `;
        listEl.appendChild(row);
      });
    }

    // ---- Init ----
    buildGenreFilter();
    renderLibrary();
    renderCqDraftList();
    updateCqSaveState();
    renderAllQuizzes();
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>