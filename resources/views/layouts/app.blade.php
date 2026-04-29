<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="{{ session('theme','light')==='dark' ? 'theme-dark' : 'theme-light' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', __('messages.site_name'))</title>
<meta name="description" content="@yield('meta_desc', __('messages.site_description'))">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{--primary:#1a9e4a;--primary-dark:#157a39;--primary-light:#e8f5e9;--danger:#e53935;--success:#1a9e4a;}
.theme-dark{--bg:#111827;--surface:#1f2937;--surface-2:#374151;--text:#f9fafb;--muted:#9ca3af;--border:#4b5563;--card:#1f2937;--light:#374151;--sb-bg:linear-gradient(160deg,#0f2027 0%,#203a43 50%,#2c5364 100%);--qty-bg:#4b5563;--qty-color:#f9fafb;--qty-border:#6b7280;}
.theme-light{--bg:#f0f4f8;--surface:#ffffff;--surface-2:#f8fafc;--text:#1e293b;--muted:#64748b;--border:#e2e8f0;--card:#ffffff;--light:#f8fafc;--sb-bg:linear-gradient(160deg,#0f2027 0%,#203a43 50%,#2c5364 100%);--qty-bg:#ffffff;--qty-color:#1e293b;--qty-border:#cbd5e1;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{width:100%;overflow-x:hidden;}
body{background:var(--bg);color:var(--text);font-family:'Inter','Hind Siliguri',sans-serif;}
.fw-wrap{width:100%;padding-left:clamp(12px,2vw,32px);padding-right:clamp(12px,2vw,32px);}

/* HEADER */
.header{background:var(--surface);box-shadow:0 2px 12px rgba(0,0,0,.08);position:fixed;top:0;left:0;right:0;z-index:1000;width:100%;}
.main-header{padding:12px 0;display:flex;align-items:center;gap:12px;}
.logo-img{height:50px;width:auto;}
.search-wrap{flex:1;min-width:0;}
.search-input{width:100%;height:46px;border:2px solid var(--border);border-radius:23px;padding:0 52px 0 20px;font-size:14px;background:var(--bg);color:var(--text);transition:border-color .25s,box-shadow .25s;}
.search-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,158,74,.12);}
.search-btn{position:absolute;right:5px;top:5px;width:36px;height:36px;background:var(--primary);border:none;border-radius:50%;color:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s,transform .2s;}
.search-btn:hover{background:var(--primary-dark);transform:scale(1.06);}
.header-controls{display:flex;align-items:center;gap:8px;flex-shrink:0;overflow:visible;max-width:100%;}
.lang-switcher{display:flex;background:var(--surface-2);border-radius:20px;border:1px solid var(--border);padding:2px;gap:2px;}
.lang-btn{padding:4px 9px;background:transparent;border:none;color:var(--muted);cursor:pointer;text-decoration:none;font-size:11px;font-weight:700;border-radius:16px;transition:all .18s;line-height:1;white-space:nowrap;}
.lang-btn:hover{color:var(--primary);}
.lang-btn.active{background:var(--primary);color:#fff;box-shadow:0 1px 5px rgba(26,158,74,.35);}
.theme-switcher{display:none;}
/* Single theme toggle icon button */
.theme-toggle-btn{width:34px;height:34px;border-radius:50%;background:var(--surface-2);border:1px solid var(--border);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;transition:background .18s,color .18s,transform .18s;}
.theme-toggle-btn:hover{background:var(--primary);color:#fff;transform:rotate(20deg);}
.cart-btn{display:flex;align-items:center;gap:7px;background:var(--light);border:1px solid var(--border);color:var(--text);padding:8px 14px;border-radius:8px;cursor:pointer;white-space:nowrap;font-size:14px;transition:background .2s,color .2s,transform .2s;}
.cart-btn:hover{background:var(--primary);color:#fff;transform:translateY(-2px);}
.cart-icon-wrap{position:relative;font-size:17px;}
.cart-count{position:absolute;top:-7px;right:-7px;background:var(--danger);color:#fff;border-radius:50%;min-width:16px;height:16px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:700;}

/* NAV */
.main-nav{background:#f5f7f5;border-bottom:1px solid #dde8dd;width:100%;position:fixed;z-index:999;}
.nav-bar-inner{display:flex;align-items:stretch;position:relative;}
.departments-btn{background:rgba(26,158,74,.10);color:var(--primary);border:none;padding:12px 20px;font-weight:600;font-size:13.5px;display:flex;align-items:center;gap:8px;cursor:pointer;flex-shrink:0;border-right:1px solid rgba(26,158,74,.15);transition:background .2s;}
.departments-btn:hover,.departments-btn.active{background:rgba(26,158,74,.18);}
.nav-links{display:flex;align-items:center;list-style:none;margin:0;padding:0;overflow-x:auto;scrollbar-width:none;}
.nav-links::-webkit-scrollbar{display:none;}
.nav-links a{color:#4a5e4a;text-decoration:none;font-weight:500;font-size:13px;padding:12px 15px;display:block;white-space:nowrap;border-right:1px solid rgba(26,158,74,.1);transition:background .2s,color .2s;}
.nav-links a:hover{background:rgba(26,158,74,.1);color:var(--primary);}

/* CATEGORY SIDEBAR */
.category-sidebar{position:absolute;top:100%;left:0;width:290px;background:var(--sb-bg);box-shadow:4px 6px 24px rgba(0,0,0,.35);border-radius:0 0 14px 14px;z-index:998;max-height:0;overflow:hidden;transition:max-height .4s cubic-bezier(.4,0,.2,1),opacity .3s;opacity:0;}
.category-sidebar.open{max-height:520px;overflow-y:auto;opacity:1;}
.category-sidebar::-webkit-scrollbar{width:4px;}
.category-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:4px;}
.cat-item{display:flex;align-items:center;gap:12px;padding:10px 18px;color:rgba(255,255,255,.88);text-decoration:none;font-size:13px;font-weight:500;border-bottom:1px solid rgba(255,255,255,.06);transition:background .2s,padding-left .2s,color .2s;}
.cat-item:last-child{border-bottom:none;}
.cat-item:hover{background:rgba(255,255,255,.1);color:#fff;padding-left:26px;}
.cat-icon-wrap{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;}
.cat-item .fa-chevron-right{font-size:10px;color:rgba(255,255,255,.35);margin-left:auto;transition:transform .2s;}
.cat-item:hover .fa-chevron-right{transform:translateX(3px);color:rgba(255,255,255,.7);}

/* PRODUCT GRID */
.product-col{width:25%;padding:0 8px 16px;}
@media(min-width:1400px){.product-col{width:16.666%;}}
@media(max-width:991px){.product-col{width:33.333%;}}
@media(max-width:767px){.product-col{width:50% !important;padding:0 5px 10px;}}
/* Never go to 1 col on mobile — always 2 cols */
.product-grid{display:flex;flex-wrap:wrap;margin:0 -8px;}
.product-card{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:transform .32s cubic-bezier(.4,0,.2,1),box-shadow .32s,border-color .32s;height:100%;display:flex;flex-direction:column;position:relative;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.product-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.13);border-color:rgba(26,158,74,.35);}
.product-img-wrap{position:relative;height:auto;min-height:0;aspect-ratio:1/1;background:var(--surface-2);overflow:hidden;}
.product-img-wrap img{width:100%;height:100%;object-fit:contain;background:var(--surface-2);transition:transform .42s cubic-bezier(.4,0,.2,1);}
.product-card:hover .product-img-wrap img{transform:scale(1.05);}
.product-overlay-btn{width:40px;height:40px;border-radius:50%;background:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;opacity:0;transform:translateY(12px);transition:opacity .25s,transform .25s,background .2s,color .2s;box-shadow:0 3px 12px rgba(0,0,0,.2);}
.product-overlay-btn:hover{background:var(--primary);color:#fff;}
.product-card:hover .product-overlay-btn{opacity:1;transform:none;}
.product-card:hover .product-overlay-btn:nth-child(2){transition-delay:.06s;}
.product-image{position:relative;height:auto;min-height:0;aspect-ratio:1/1;background:var(--surface-2);display:flex;align-items:center;justify-content:center;overflow:hidden;}
.product-image img{width:100%;height:100%;object-fit:contain;background:var(--surface-2);transition:transform .42s cubic-bezier(.4,0,.2,1);}
.product-card:hover .product-image img{transform:scale(1.05);}
.product-badge{position:absolute;top:9px;right:9px;background:linear-gradient(135deg,#e53935,#b71c1c);color:#fff;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:800;box-shadow:0 2px 8px rgba(229,57,53,.45);letter-spacing:.2px;}
.product-info{padding:14px 13px 13px;flex:1;display:flex;flex-direction:column;}
.product-name{font-size:14px;font-weight:700;margin-bottom:4px;line-height:1.4;min-height:40px;color:var(--text);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.product-details-text{font-size:12px;color:var(--muted);margin-bottom:8px;line-height:1.4;min-height:34px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
.product-price{font-size:18px;font-weight:800;color:var(--success);margin-bottom:10px;display:flex;align-items:baseline;gap:5px;flex-wrap:wrap;}
.original-price-text{text-decoration:line-through;color:var(--muted);font-size:14px;margin-right:6px;}
.product-actions{display:flex;gap:7px;margin-top:auto;}
.product-actions .btn-success{background:linear-gradient(135deg,var(--primary),#0d7a38);border:none;}
.product-actions .btn-success:hover{background:linear-gradient(135deg,#0d7a38,#0a5a2a);box-shadow:0 4px 14px rgba(26,158,74,.4);}
.product-actions .btn-outline-success{border-color:var(--primary);color:var(--primary);}
.product-actions .btn-outline-success:hover{background:var(--primary);color:#fff;}
.product-actions .btn{flex:1;font-size:11.5px;padding:6px 8px;}

/* DISCOUNT CAROUSEL */
.discount-carousel{position:relative;overflow:hidden;background:var(--surface);border-radius:14px;padding:16px;margin:24px 0;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.carousel-container{display:flex;transition:transform .5s ease;}
.carousel-item-custom{min-width:50%;padding:0 8px;}
.carousel-controls{position:absolute;top:50%;transform:translateY(-50%);width:100%;display:flex;justify-content:space-between;padding:0 6px;pointer-events:none;}
.carousel-btn{background:var(--primary);color:#fff;border:none;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;pointer-events:all;transition:background .2s,transform .2s;box-shadow:0 3px 10px rgba(0,0,0,.2);}
.carousel-btn:hover{background:var(--primary-dark);transform:scale(1.1);}
.discount-card{background:linear-gradient(135deg,#1a9e4a 0%,#0d7a38 60%,#0a5a2a 100%);border-radius:12px;color:#fff;padding:26px;position:relative;overflow:hidden;height:230px;display:flex;align-items:center;}
.discount-card::before{content:'';position:absolute;top:-40px;right:-40px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;}
.discount-content{flex:1;z-index:2;}
.discount-badge-pill{background:rgba(255,255,255,.22);padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;margin-bottom:9px;letter-spacing:.5px;}
.discount-image{width:130px;height:130px;object-fit:contain;margin-left:14px;}

/* CART SIDEBAR */
.cart-sidebar{position:fixed;top:0;right:-420px;width:400px;height:100vh;background:var(--surface);box-shadow:-3px 0 18px rgba(0,0,0,.18);transition:right .32s cubic-bezier(.4,0,.2,1);z-index:1050;display:flex;flex-direction:column;}
.cart-sidebar.open{right:0;}
.cart-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.45);z-index:1040;opacity:0;visibility:hidden;transition:opacity .32s,visibility .32s;}
.cart-overlay.show{opacity:1;visibility:visible;}
.cart-header{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--surface);flex-shrink:0;position:sticky;top:0;z-index:2;}
.cart-close{background:none;border:none;font-size:22px;cursor:pointer;color:var(--text);line-height:1;width:32px;height:32px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s;}
.cart-close:hover{background:rgba(229,57,53,.1);color:var(--danger);}
.cart-body{flex:1;overflow-y:auto;padding:14px 16px;}
.cart-footer{padding:14px 16px;border-top:1px solid var(--border);background:var(--surface);flex-shrink:0;}

/* CART ITEM */
.cart-item{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--border);position:relative;}
.cart-item-img-wrap{width:72px;height:72px;flex-shrink:0;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);overflow:hidden;display:flex;align-items:center;justify-content:center;}
.cart-item-img-wrap img{width:100%;height:100%;object-fit:contain;}
.cart-item-info{flex:1;min-width:0;padding-right:28px;}
.cart-item-name{font-weight:500;font-size:13.5px;margin-bottom:3px;line-height:1.35;color:var(--text);}
.cart-item-price{color:var(--success);font-weight:700;font-size:14px;margin-bottom:6px;}
.qty-controls{display:flex;align-items:center;gap:6px;}
.qty-btn{width:28px;height:28px;background:var(--qty-bg);border:1.5px solid var(--qty-border);color:var(--qty-color);border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:15px;font-weight:600;transition:background .15s,color .15s,border-color .15s;user-select:none;}
.qty-btn:hover{background:var(--primary);color:#fff;border-color:var(--primary);}
.qty-num{font-size:14px;font-weight:600;min-width:28px;text-align:center;color:var(--text);}
.remove-btn{position:absolute;top:14px;right:0;background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;width:26px;height:26px;border-radius:5px;display:flex;align-items:center;justify-content:center;transition:color .15s,background .15s;}
.remove-btn:hover{color:var(--danger);background:rgba(229,57,53,.1);}
.cart-total-display{font-size:18px;font-weight:700;color:var(--success);margin-bottom:12px;text-align:center;}

/* MODAL */
.modal-content{background:var(--surface);color:var(--text);border:1px solid var(--border);}
.modal-header{border-bottom:1px solid var(--border);background:var(--surface-2);}
.modal-main-img{width:100%;aspect-ratio:1/1;object-fit:contain;background:var(--surface-2);border-radius:10px;border:1px solid var(--border);}
.modal-thumbs{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;}
.modal-thumb{width:60px;height:60px;border-radius:7px;border:2px solid var(--border);overflow:hidden;cursor:pointer;background:var(--surface-2);transition:border-color .2s;}
.modal-thumb.active,.modal-thumb:hover{border-color:var(--primary);}
.modal-thumb img{width:100%;height:100%;object-fit:contain;}

/* INFO BOTTOM SHEETS */
.info-panel{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.5);align-items:flex-end;animation:fadeInBg .25s ease;}
.info-panel.show{display:flex;}
@keyframes fadeInBg{from{opacity:0}to{opacity:1}}
.info-sheet{width:100%;max-height:85vh;background:var(--surface);border-radius:20px 20px 0 0;overflow-y:auto;padding:0 0 28px;animation:slideUp .3s cubic-bezier(.4,0,.2,1);}
@keyframes slideUp{from{transform:translateY(40px);opacity:0}to{transform:none;opacity:1}}
.info-sheet-handle{width:40px;height:4px;background:var(--border);border-radius:2px;margin:12px auto 0;}
.info-sheet-header{padding:16px 22px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--surface);z-index:2;}
.info-sheet-title{font-size:16px;font-weight:600;color:var(--text);}
.info-sheet-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);padding:4px 8px;border-radius:6px;transition:color .15s,background .15s;}
.info-sheet-close:hover{color:var(--danger);background:rgba(229,57,53,.08);}
.info-sheet-body{padding:20px 22px;font-size:14px;line-height:1.8;color:var(--text);}
.info-sheet-body h4{font-size:15px;font-weight:600;margin:18px 0 6px;color:var(--primary);}
.info-sheet-body h4:first-child{margin-top:0;}
.info-sheet-body p{color:var(--muted);margin-bottom:10px;}
.info-sheet-body ul{padding-left:18px;margin-bottom:10px;}
.info-sheet-body ul li{color:var(--muted);margin-bottom:4px;}
.info-badge{display:inline-block;background:var(--primary-light);color:var(--primary);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;margin:2px 2px 8px 0;}
.info-company-card{background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:12px;}
.info-company-card h5{font-size:14px;font-weight:600;margin-bottom:4px;color:var(--text);}
.info-company-card p{font-size:12.5px;color:var(--muted);margin:0;}

/* EMPTY STATE */
.empty-state{text-align:center;padding:52px 20px;background:var(--surface);border-radius:14px;margin:24px 0;}
.empty-state i{font-size:58px;color:var(--muted);margin-bottom:16px;display:block;}

/* FOOTER */
.footer{background:var(--surface);color:var(--text);margin-top:52px;border-top:3px solid var(--primary);}
.footer-top{padding:48px 0 32px;}
.footer-brand-name{font-size:22px;font-weight:800;color:var(--primary);letter-spacing:-.5px;}
.footer-brand-tagline{font-size:12.5px;color:var(--muted);margin-top:4px;}
.footer-contact-item{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px;}
.footer-contact-item:last-child{border-bottom:none;}
.footer-contact-item a{color:var(--text);text-decoration:none;}
.footer-contact-item a:hover{color:var(--primary);}
.footer-contact-icon{width:30px;height:30px;border-radius:8px;background:rgba(26,158,74,.1);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:12px;flex-shrink:0;}
.footer-col-title{font-size:13px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px;position:relative;padding-bottom:10px;}
.footer-col-title::after{content:'';position:absolute;bottom:0;left:0;width:22px;height:2px;background:var(--primary);border-radius:2px;}
.footer-links{list-style:none;padding:0;margin:0;}
.footer-links li{margin-bottom:8px;}
.footer-links a{cursor:pointer;font-size:13px;color:var(--muted);text-decoration:none;transition:color .18s,padding-left .18s;display:flex;align-items:center;gap:6px;}
.footer-links a::before{content:'›';color:var(--primary);font-size:15px;line-height:1;}
.footer-links a:hover{color:var(--primary);padding-left:3px;}
.footer-quick-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;margin-bottom:8px;}
.footer-badge-green{background:rgba(26,158,74,.1);color:#1a9e4a;}
.footer-badge-blue{background:rgba(59,130,246,.1);color:#3b82f6;}
.footer-bottom{background:rgba(0,0,0,.04);padding:14px 0;}
.footer-bottom-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.footer-bottom-links{display:flex;gap:16px;}
.footer-bottom-links a{font-size:12px;color:var(--muted);text-decoration:none;cursor:pointer;}
.footer-bottom-links a:hover{color:var(--primary);}
/* TOAST */
.toast-container{position:fixed;bottom:28px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;align-items:center;gap:10px;pointer-events:none;}
.toast-notify{min-width:260px;max-width:380px;padding:14px 20px;border-radius:14px;font-size:14px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,.22);display:flex;align-items:center;gap:12px;pointer-events:all;animation:toastIn .35s cubic-bezier(.34,1.56,.64,1),toastOut .4s ease 2.5s forwards;white-space:nowrap;}
.toast-success{background:linear-gradient(135deg,#1a9e4a,#0d7a38);color:#fff;}
.toast-error{background:linear-gradient(135deg,#e53935,#b71c1c);color:#fff;}
.toast-notify .toast-icon{width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;}
.toast-notify .toast-text{flex:1;}
.toast-notify .toast-close{background:rgba(255,255,255,.2);border:none;color:#fff;cursor:pointer;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;transition:background .15s;flex-shrink:0;padding:0;line-height:1;}
.toast-notify .toast-close:hover{background:rgba(255,255,255,.35);}
.toast-progress{height:3px;background:rgba(255,255,255,.4);border-radius:0 0 14px 14px;position:absolute;bottom:0;left:0;animation:toastProgress 3s linear forwards;}
@keyframes toastIn{from{transform:translateY(30px) scale(.92);opacity:0}to{transform:translateY(0) scale(1);opacity:1}}
@keyframes toastOut{from{opacity:1;transform:scale(1)}to{opacity:0;transform:scale(.88) translateY(10px)}}
@keyframes toastProgress{from{width:100%}to{width:0%}}
@media(max-width:480px){.toast-notify{min-width:220px;max-width:calc(100vw - 32px);white-space:normal;}}

.bengali-text{font-family:'Hind Siliguri',sans-serif;}
/* ── Desktop nav bar ── */
@media(min-width:992px){.nav-links{display:flex;}}

/* ══════════════════════════════════════════
   MOBILE RESPONSIVE OVERHAUL
   ══════════════════════════════════════════ */
@media(max-width:991px){
  /* Show nav links as horizontal scrollable strip on tablet/mobile */
  .nav-links{
    display:flex !important;
    overflow-x:auto;
    scrollbar-width:none;
    -ms-overflow-style:none;
  }
  .nav-links::-webkit-scrollbar{display:none;}
  .nav-links a{font-size:12px;padding:10px 11px;white-space:nowrap;flex-shrink:0;}
  .departments-btn{padding:10px 13px;font-size:12.5px;}
}
@media(max-width:767px){
  /* Header layout: logo+controls row1, search row2 */
  .main-header{flex-wrap:wrap;padding:9px 0;gap:7px;}
  .main-header > a{order:1;flex-shrink:0;}
  .header-controls{order:2;margin-left:auto;flex-shrink:0;gap:5px;}
  .search-wrap{order:3;width:100%;margin:5px 0 0;}
  .search-input{height:40px;font-size:10px;}
  .search-btn{right:4px;top:4px;width:32px;height:32px;}

   .main-nav{top:108px !important;}
  /* Cart sidebar full-width */
  .cart-sidebar{width:100%;right:-100%;}

  /* Category sidebar full width */
  .category-sidebar{width:100%;border-radius:0 0 14px 14px;}

  /* ── Product grid: EXACTLY 2 columns on mobile ── */
  .product-col{width:50% !important;padding:0 5px 12px;}
  .product-grid{margin:0 -5px;}

  /* ── Product card image: auto height, object-fit:contain, no crop ── */
  .product-img-wrap,
  .product-image{
    height:auto !important;
    min-height:130px;
    aspect-ratio:1/1;
  }
  .product-img-wrap img,
  .product-image img{
    width:100%;
    height:100%;
    object-fit:contain !important;
    background:var(--surface-2);
  }
  .product-name{font-size:12.5px;min-height:30px;}
  .product-price{font-size:14px;margin-bottom:7px;}
  .product-info{padding:10px 9px 9px;}
  .product-actions .btn{font-size:10px;padding:5px 5px;}
  .product-actions{gap:5px;}

  /* Carousel */
  .carousel-item-custom{min-width:100%;}
  .discount-card{flex-direction:column;height:auto;text-align:center;padding:16px;}
  .discount-image{margin:12px 0 0;}

  /* Lang btn */
  .lang-btn{padding:4px 7px;font-size:10.5px;}
}
@media(max-width:479px){
  .logo-img{height:36px;}
  .main-header{gap:5px;}
  .header-controls{gap:4px;}
  .theme-toggle-btn{width:32px;height:32px;font-size:13px;}
  .cart-btn{padding:6px 8px;}
  .product-col{width:50% !important;}
  .product-info{padding:9px 7px 8px;}
  .product-actions .btn{font-size:9.5px;padding:5px 4px;}
}
</style>
@stack('styles')
</head>

<body>
<!-- ══ HEADER ══ -->
<header class="header" id="siteHeader">
    <div class="fw-wrap main-header">
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;" title="{{ __('messages.home') }}">
            @php $logoFile = app()->getLocale()==='bn' ? 'logo_bng.png' : 'logo_eng.png'; @endphp
            <img src="{{ asset($logoFile) }}" alt="{{ __('messages.site_name') }}" class="logo-img"
                 onerror="this.src='{{ asset('logo.png') }}';this.onerror=null;">
        </a>

        <div class="search-wrap">
            <div style="position:relative;">
                <form action="{{ route('search') }}" method="GET">
                    <input type="text" name="q" class="search-input"
                           placeholder="{{ __('messages.search_placeholder') }}"
                           value="{{ request('q') }}">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="header-controls">
            {{-- BUG 2 FIX: POST forms prevent CDN/proxy caching language state-change requests --}}
            <div class="lang-switcher">
                {{-- AJAX language switch — page reload ছাড়া (dark mode-এর মতো) --}}
                <button type="button" onclick="switchLang('bn')"
                    class="lang-btn {{ app()->getLocale()==='bn' ? 'active' : '' }}" id="langBtnBn">বাং</button>
                <button type="button" onclick="switchLang('en')"
                    class="lang-btn {{ app()->getLocale()==='en' ? 'active' : '' }}" id="langBtnEn">EN</button>
            </div>
            <button class="theme-toggle-btn" id="themeToggleBtn"
                onclick="toggleTheme()"
                title="{{ session('theme','light')==='dark' ? (app()->getLocale()==='bn' ? 'লাইট মোড' : 'Light Mode') : (app()->getLocale()==='bn' ? 'ডার্ক মোড' : 'Dark Mode') }}">
                <i class="fas {{ session('theme','light')==='dark' ? 'fa-sun' : 'fa-moon' }}"></i>
            </button>

            {{-- ── AUTH BUTTON ── --}}
            <div id="authBtnArea">
            @auth
                {{-- Logged in user: show avatar dropdown --}}
                <div class="position-relative" id="userMenuWrap">
                    <button onclick="toggleUserMenu(event)" style="display:flex;align-items:center;gap:7px;background:var(--primary-light);border:1.5px solid var(--primary);color:var(--primary);padding:7px 13px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        <i class="fas fa-user-circle" style="font-size:16px;"></i>
                        <span class="d-none d-sm-inline">{{ Str::limit(auth()->user()->name, 12) }}</span>
                        <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                    </button>
                    <div id="userMenuDropdown" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:var(--surface);border:1px solid var(--border);border-radius:10px;min-width:180px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:2000;overflow:hidden;">
                        <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:var(--text);text-decoration:none;font-size:13px;border-bottom:1px solid var(--border);">
                            <i class="fas fa-th-large" style="color:var(--primary);width:16px;"></i>
                            {{ app()->getLocale()==='bn' ? 'ড্যাশবোর্ড' : 'Dashboard' }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit" style="display:flex;align-items:center;gap:10px;padding:12px 16px;width:100%;background:none;border:none;color:#ef4444;font-size:13px;cursor:pointer;text-align:left;">
                                <i class="fas fa-sign-out-alt" style="width:16px;"></i>
                                {{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- Not logged in: Sign In icon button --}}
                <button onclick="openLoginModal()" title="{{ app()->getLocale()==='bn' ? 'লগইন' : 'Sign In' }}" style="display:flex;align-items:center;justify-content:center;width:38px;height:38px;background:var(--surface-2);color:var(--primary);border:1.5px solid var(--primary);border-radius:50%;cursor:pointer;font-size:16px;transition:background .2s,transform .2s;" onmouseover="this.style.background='var(--primary)';this.style.color='#fff';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='var(--surface-2)';this.style.color='var(--primary)';this.style.transform=''">
                    <i class="fas fa-user"></i>
                </button>
            @endauth
            </div>

            <button class="cart-btn" onclick="toggleCart()">
                <div class="cart-icon-wrap">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartBadge">0</span>
                </div>
                <span class="d-none d-sm-inline">{{ __('messages.cart') }}</span>
            </button>
        </div>
    </div>

    <!-- NAV -->
    <div class="main-nav">
        <div class="fw-wrap">
            <div class="nav-bar-inner">
                <button class="departments-btn" id="deptBtn" onclick="toggleDept()">
                    <i class="fas fa-th-large"></i>
                    {{ __('messages.all_departments') }}
                    <i class="fas fa-chevron-down" id="deptArrow" style="font-size:10px;"></i>
                </button>

                {{-- ══ CATEGORY SIDEBAR — DB থেকে আসছে, hardcoded নয় ══ --}}
                <div class="category-sidebar" id="catSidebar">
                    @php
                        $sidebarCategories = \App\Models\Category::where('status', true)
                            ->orderBy('sort_order')
                            ->get();
                    @endphp

                    @forelse($sidebarCategories as $cat)
                    <a href="{{ route('search', ['category' => $cat->id]) }}" class="cat-item" onclick="closeDept()">
                        <div class="cat-icon-wrap"
                             style="background:{{ $cat->color ?? '#1a9e4a' }}20;color:{{ $cat->color ?? '#1a9e4a' }};border:1px solid {{ $cat->color ?? '#1a9e4a' }}40;">
                            <i class="{{ $cat->icon ?? 'fas fa-tag' }}"></i>
                        </div>
                        <span class="bengali-text">{{ app()->getLocale()==='bn' ? $cat->name_bn : $cat->name_en }}</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    @empty
                    <div style="padding:16px 18px;color:rgba(255,255,255,.55);font-size:13px;" class="bengali-text">
                        কোনো ক্যাটাগরি নেই
                    </div>
                    @endforelse
                </div>

                <ul class="nav-links">
                    <li><a href="{{ route('home') }}" {{ request()->routeIs('home') ? 'style=background:rgba(26,158,74,.15);color:var(--primary);font-weight:600;' : '' }}>
                        {{ app()->getLocale()==='bn' ? 'হোম' : 'Home' }}</a></li>
                    <li><a href="{{ route('new-arrivals') }}" {{ request()->routeIs('new-arrivals') ? 'style=background:rgba(26,158,74,.15);color:var(--primary);font-weight:600;' : '' }}>
                        {{ app()->getLocale()==='bn' ? 'নতুন আগমন' : 'New Arrivals' }}</a></li>
                    <li><a href="{{ route('offers') }}" {{ request()->routeIs('offers') ? 'style=background:rgba(26,158,74,.15);color:var(--primary);font-weight:600;' : '' }}>
                        {{ app()->getLocale()==='bn' ? 'অফার' : 'Offers' }}</a></li>
                    <li><a href="{{ request()->routeIs('home') ? '#section-flash-sale' : route('home').'#section-flash-sale' }}">
                        ⚡ {{ app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল' : 'Flash Sale' }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CART SIDEBAR -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h5 class="mb-0" style="font-size:15px;">
                <i class="fas fa-shopping-cart me-2" style="color:var(--primary);"></i>
                {{ __('messages.your_cart') }}
            </h5>
            <button class="cart-close" onclick="toggleCart()">×</button>
        </div>
        <div class="cart-body" id="cartBody"></div>
        <div class="cart-footer">
            <div class="cart-total-display" id="cartTotal">{{ __('messages.total') }}: ৳0</div>
            <button class="btn btn-outline-success w-100 mb-2" onclick="toggleCart(); window.location.href='{{ route('home') }}'">
                {{ __('messages.continue_shopping') }}
            </button>
            <button class="btn btn-success w-100" onclick="window.location.href='{{ route('checkout') }}'">
                {{ __('messages.checkout') }}
            </button>
        </div>
    </div>
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
</header>

<!-- MAIN -->
<main id="siteMain">
    @yield('content')
</main>

<!-- INFO PANELS -->
@include('layouts.partials.info-panels')

<!-- PRODUCT MODAL -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title bengali-text" id="modalTitle">{{ __('messages.product_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <img id="modalMainImg" src="" alt="" class="modal-main-img">
                        <div class="modal-thumbs" id="modalThumbs"></div>
                    </div>
                    <div class="col-md-7">
                        <h4 id="modalName" class="bengali-text mb-1" style="font-size:17px;font-weight:600;"></h4>
                        <p class="text-muted mb-2" id="modalMeta" style="font-size:13px;"></p>
                        <p id="modalDesc" class="bengali-text mb-3" style="font-size:13.5px;line-height:1.7;color:var(--muted);"></p>
                        <h4 id="modalPrice" class="mb-2" style="color:var(--success);font-size:20px;font-weight:700;"></h4>
                        <div id="modalDiscount" class="mb-3" style="display:none;">
                            <span class="badge bg-danger" id="modalDiscountBadge"></span>
                        </div>
                        <button class="btn btn-success btn-lg w-100" id="modalCartBtn">
                            <i class="fas fa-cart-plus me-2"></i>{{ __('messages.add_to_cart') }}
                        </button>
                        <div class="mt-3 p-2" style="background:var(--surface-2);border-radius:8px;font-size:12.5px;color:var(--muted);">
                            <i class="fas fa-truck me-1" style="color:var(--primary);"></i>
                            {{ __('messages.inside_dhaka_charge') }}
                            &nbsp;&nbsp;
                            <i class="fas fa-shield-alt me-1" style="color:var(--primary);"></i>
                            {{ __('messages.pay_on_delivery') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" class="toast-container"></div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-top">
        <div class="fw-wrap">
            <div class="row g-4">

                {{-- Col 1: Brand + Contact --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand-name">আমারদরকার</div>
                    <div class="footer-brand-tagline bengali-text">{{ app()->getLocale()==='bn' ? 'বিশ্বস্ত অনলাইন শপিং — বাংলাদেশ' : 'Trusted Online Shopping — Bangladesh' }}</div>

                    <div class="mt-3">
                        <a href="https://maps.app.goo.gl/CnYXtZ14LfRQy4DK8" target="_blank" rel="noopener" class="footer-contact-item text-decoration-none">
                            <span class="footer-contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'ফেনী সদর, ফেনী, বাংলাদেশ' : 'Feni Sadar, Feni, Bangladesh' }}</span>
                        </a>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fas fa-phone"></i></span>
                            <a href="tel:+8801863719755">01863-719755</a>
                        </div>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fas fa-envelope"></i></span>
                            <a href="mailto:alimamuddin2710@gmail.com">alimamuddin2710@gmail.com</a>
                        </div>
                    </div>

                    {{-- Quick delivery badges --}}
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <span class="footer-quick-badge footer-badge-green">
                            <i class="fas fa-truck"></i>
                            {{ app()->getLocale()==='bn' ? 'ঢাকায় ১–৩ দিন' : 'Dhaka 1–3 Days' }}
                        </span>
                        <span class="footer-quick-badge footer-badge-blue">
                            <i class="fas fa-shipping-fast"></i>
                            {{ app()->getLocale()==='bn' ? 'বাইরে ৩–৫ দিন' : 'Outside 3–5 Days' }}
                        </span>
                    </div>
                </div>

                {{-- Col 2: Company --}}
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="footer-col-title bengali-text">{{ __('messages.company_info') }}</div>
                    <ul class="footer-links">
                        <li><a onclick="openPanel('panelAbout')" class="bengali-text">{{ __('messages.about_us') }}</a></li>
                        <li><a onclick="openPanel('panelPrivacy')" class="bengali-text">{{ __('messages.privacy_policy') }}</a></li>
                        <li><a onclick="openPanel('panelCareers')" class="bengali-text">{{ __('messages.careers') }}</a></li>
                        <li><a onclick="openPanel('panelContact')" class="bengali-text">{{ __('messages.contact_us') }}</a></li>
                    </ul>
                </div>

                {{-- Col 3: Customer Care --}}
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="footer-col-title bengali-text">{{ __('messages.customer_care') }}</div>
                    <ul class="footer-links">
                        <li><a onclick="openPanel('panelShipping')" class="bengali-text">{{ __('messages.shipping') }}</a></li>
                        <li><a onclick="openPanel('panelReturns')" class="bengali-text">{{ __('messages.returns_refunds') }}</a></li>
                        <li><a onclick="openPanel('panelPayments')" class="bengali-text">{{ __('messages.payments') }}</a></li>
                        <li><a onclick="openPanel('panelHelp')" class="bengali-text">{{ __('messages.help_center') }}</a></li>
                    </ul>
                </div>

                {{-- Col 4: Partner --}}
                <div class="col-lg-3 col-md-6">
                    <div class="footer-col-title bengali-text">{{ __('messages.business_partner') }}</div>
                    <ul class="footer-links">
                        <li><a onclick="openPanel('panelPartner')" class="bengali-text">{{ __('messages.become_partner') }}</a></li>
                        <li><a onclick="openPanel('panelPartner')" class="bengali-text">{{ __('messages.partner_opportunities') }}</a></li>
                        <li><a onclick="openPanel('panelPartner')" class="bengali-text">{{ __('messages.earnings_process') }}</a></li>
                    </ul>

                    {{-- COD badge --}}
                    <div class="mt-3 p-3" style="background:rgba(26,158,74,.07);border-radius:10px;border:1px solid rgba(26,158,74,.15);">
                        <div style="font-size:12px;font-weight:700;color:var(--primary);margin-bottom:4px;">
                            <i class="fas fa-shield-alt me-1"></i>{{ app()->getLocale()==='bn' ? 'নিরাপদ কেনাকাটা' : 'Safe Shopping' }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);" class="bengali-text">
                            {{ app()->getLocale()==='bn' ? 'ক্যাশ অন ডেলিভারি • ৭ দিন রিটার্ন • ১০০% অরিজিনাল' : 'Cash on Delivery • 7-Day Return • 100% Original' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Footer bottom bar --}}
    <div class="footer-bottom">
        <div class="fw-wrap">
            <div class="footer-bottom-inner">
                <p class="text-muted small mb-0">
                    &copy; 2022–{{ date('Y') }} <a href="{{ route('home') }}" class="text-success text-decoration-none fw-semibold">amardorkar</a>.
                    {{ __('messages.all_rights') }}
                </p>
                <div class="footer-bottom-links bengali-text">
                    <a onclick="openPanel('panelPrivacy')">{{ __('messages.privacy_policy') }}</a>
                    <a onclick="openPanel('panelReturns')">{{ __('messages.returns_refunds') }}</a>
                    <a onclick="openPanel('panelContact')">{{ __('messages.contact_us') }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const NO_IMG = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIHZpZXdCb3g9IjAgMCA3MiA3MiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIGZpbGw9IiNmMWY1ZjkiLz48dGV4dCB4PSIzNiIgeT0iNDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5NGEzYjgiIGZvbnQtc2l6ZT0iMTEiPk5vIGltYWdlPC90ZXh0Pjwvc3ZnPg==';

/* ── FIXED HEADER + NAV OFFSET ── */
function updateLayout(){
    const header = document.getElementById('siteHeader');
    const nav    = document.querySelector('.main-nav');
    const main   = document.getElementById('siteMain');
    if(!header) return;
    const headerH = header.offsetHeight;
    if(nav){
        nav.style.top = headerH + 'px';
        const navH = nav.offsetHeight;
        if(main) main.style.paddingTop = (headerH + navH) + 'px';
    } else {
        if(main) main.style.paddingTop = headerH + 'px';
    }
}
updateLayout();
window.addEventListener('resize', updateLayout);
window.addEventListener('load', updateLayout);

/* ── CART POSITIONING ── */
function positionCart(){
    const header = document.getElementById('siteHeader');
    const nav    = document.querySelector('.main-nav');
    const sb     = document.getElementById('cartSidebar');
    if(!header||!sb) return;
    const top = header.offsetHeight + (nav ? nav.offsetHeight : 0);
    sb.style.top = top+'px';
    sb.style.height = 'calc(100vh - '+top+'px)';
}
positionCart(); window.addEventListener('resize', positionCart);

/* ── DEPT DROPDOWN ── */
function toggleDept(){
    const sb=document.getElementById('catSidebar');
    const open=sb.classList.toggle('open');
    document.getElementById('deptBtn').classList.toggle('active',open);
    document.getElementById('deptArrow').style.transform=open?'rotate(180deg)':'';
}
function closeDept(){
    document.getElementById('catSidebar').classList.remove('open');
    document.getElementById('deptBtn').classList.remove('active');
    document.getElementById('deptArrow').style.transform='';
    return false;
}
document.addEventListener('click',e=>{
    const sb=document.getElementById('catSidebar');
    const btn=document.getElementById('deptBtn');
    if(sb&&btn&&!sb.contains(e.target)&&!btn.contains(e.target)) closeDept();
});

/* ── CART TOGGLE ── */
function toggleCart(){
    positionCart();
    const open=document.getElementById('cartSidebar').classList.toggle('open');
    document.getElementById('cartOverlay').classList.toggle('show',open);
    document.body.style.overflow=open?'hidden':'';
    if(open) loadCart();
}

/* ── LOAD CART ── */
function loadCart(){
    fetch('{{ route("cart.items") }}')
        .then(r=>r.json())
        .then(data=>{
            const body=document.getElementById('cartBody');
            const totalEl=document.getElementById('cartTotal');
            if(!data.items||!data.items.length){
                body.innerHTML='<div style="text-align:center;padding:40px 20px;"><i class="fas fa-shopping-cart" style="font-size:48px;color:var(--muted);margin-bottom:12px;display:block;"></i><p style="color:var(--muted);">{{ __("messages.cart_empty") }}</p></div>';
                totalEl.textContent='{{ __("messages.total") }}: ৳0';
                document.getElementById('cartBadge').textContent='0';
                return;
            }
            body.innerHTML=data.items.map(i=>`
                <div class="cart-item" id="ci-${i.id}">
                    <div class="cart-item-img-wrap">
                        <img src="${i.image||NO_IMG}" alt="${i.name}" onerror="this.src='${NO_IMG}'">
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-name">${i.name}</div>
                        <div class="cart-item-price">৳${Number(i.price).toLocaleString()}</div>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="cartQty(${i.id},${i.quantity-1})">−</button>
                            <span class="qty-num">${i.quantity}</span>
                            <button class="qty-btn" onclick="cartQty(${i.id},${i.quantity+1})">+</button>
                        </div>
                    </div>
                    <button class="remove-btn" onclick="cartRemove(${i.id})" title="{{ __('messages.remove') }}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>`).join('');
            const count=data.items.reduce((s,i)=>s+i.quantity,0);
            document.getElementById('cartBadge').textContent=count;
            totalEl.textContent='{{ __("messages.total") }}: ৳'+Number(data.total).toLocaleString();
        })
        .catch(()=>{
            document.getElementById('cartBody').innerHTML='<p style="text-align:center;color:var(--danger);padding:20px;"><i class="fas fa-exclamation-circle me-1"></i>Error loading cart</p>';
        });
}

function cartQty(id,qty){
    if(qty<1){cartRemove(id);return;}
    fetch('{{ route("cart.update") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({item_id:id,quantity:qty})})
        .then(r=>r.json()).then(d=>{if(d.success){loadCart();}});
}
function cartRemove(id){
    fetch('{{ route("cart.remove") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({item_id:id})})
        .then(r=>r.json()).then(d=>{if(d.success){loadCart();}});
}

/* ── ADD TO CART ── */
function addToCart(productId, quantity, selectedSize){
    quantity = quantity || 1;
    fetch('{{ route("cart.add") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({product_id:productId, quantity:quantity, selected_size:selectedSize||undefined})
    }).then(r=>{
        if(r.status === 401 || r.status === 419){
            // Not logged in — save pending item and open login modal
            localStorage.setItem('pendingCartItem', JSON.stringify({product_id:productId, quantity:quantity, selected_size:selectedSize||null}));
            closeLoginModal && closeLoginModal();
            openLoginModal('login');
            toast('{{ app()->getLocale()==="bn" ? "পণ্য যোগ করতে লগইন করুন" : "Please login to add to cart" }}','error');
            return null;
        }
        return r.json();
    }).then(d=>{
        if(!d) return;
        if(d.success){
            document.getElementById('cartBadge').textContent=d.cart_count;
            toast(d.message||'{{ __("messages.added_to_cart") }}','success');
            const m=bootstrap.Modal.getInstance(document.getElementById('productModal'));
            if(m) m.hide();
        } else {
            toast(d.message||'Error','error');
        }
    }).catch(()=>toast('Failed','error'));
}

/* ── AFTER LOGIN: auto-add pending cart item ── */
function processPendingCartItem(){
    const raw = localStorage.getItem('pendingCartItem');
    if(!raw) return;
    let item;
    try{ item = JSON.parse(raw); } catch(e){ localStorage.removeItem('pendingCartItem'); return; }
    localStorage.removeItem('pendingCartItem');
    if(!item || !item.product_id) return;

    // Show a "adding..." toast
    toast('{{ app()->getLocale()==="bn" ? "পণ্যটি কার্টে যোগ হচ্ছে..." : "Adding item to cart..." }}','success');

    fetch('{{ route("cart.add") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({product_id:item.product_id, quantity:item.quantity||1, selected_size:item.selected_size||undefined})
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            document.getElementById('cartBadge').textContent=d.cart_count;
            toast('{{ app()->getLocale()==="bn" ? "পণ্যটি সফলভাবে কার্টে যোগ হয়েছে!" : "Item added to cart successfully!" }}','success');
        } else {
            toast(d.message||'Error','error');
        }
    }).catch(()=>{});
}

/* ── PRODUCT MODAL ── */
function showProductModal(pid,name,desc,type,size,price,imgSrc,discountText){
    document.getElementById('modalTitle').textContent=name;
    document.getElementById('modalName').textContent=name;
    let meta='';
    if(type) meta+='{{ __("messages.type") }}: '+type;
    if(type&&size) meta+='  |  ';
    if(size) meta+='{{ __("messages.size") }}: '+size;
    document.getElementById('modalMeta').textContent=meta;
    document.getElementById('modalDesc').textContent=desc||'';
    document.getElementById('modalPrice').textContent='{{ __("messages.price") }}: ৳'+Number(price).toLocaleString();
    const discEl=document.getElementById('modalDiscount');
    const discBadge=document.getElementById('modalDiscountBadge');
    if(discountText){discEl.style.display='block';discBadge.textContent=discountText;}
    else{discEl.style.display='none';}
    const img=document.getElementById('modalMainImg');
    img.src=imgSrc||NO_IMG;
    const thumbs=document.getElementById('modalThumbs');
    if(imgSrc){
        thumbs.innerHTML=`<div class="modal-thumb active" onclick="setModalImg('${imgSrc}',this)"><img src="${imgSrc}" alt=""></div>`;
    } else { thumbs.innerHTML=''; }
    document.getElementById('modalCartBtn').onclick=()=>addToCart(pid);
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
function setModalImg(src,el){
    document.getElementById('modalMainImg').src=src;
    document.querySelectorAll('.modal-thumb').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
}

/* ── INFO PANELS ── */
function openPanel(id){const el=document.getElementById(id);if(el){el.classList.add('show');document.body.style.overflow='hidden';}}
function closePanel(id){const el=document.getElementById(id);if(el){el.classList.remove('show');document.body.style.overflow='';}}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('.info-panel.show').forEach(p=>p.classList.remove('show'));document.body.style.overflow='';}});

/* ── TOAST ── */
function toast(msg,type='success'){
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = `toast-notify toast-${type}`;
    el.style.position = 'relative';
    el.style.overflow = 'hidden';
    const icon = type === 'success' ? 'fa-check' : 'fa-exclamation';
    el.innerHTML = `
        <div class="toast-icon"><i class="fas ${icon}"></i></div>
        <span class="toast-text">${msg}</span>
        <button class="toast-close" onclick="this.closest('.toast-notify').remove()">×</button>
        <div class="toast-progress"></div>
    `;
    container.appendChild(el);
    setTimeout(()=>{ el.style.animation='toastOut .4s ease forwards'; setTimeout(()=>el.remove(),400); }, 2800);
}

/* ── INIT: load cart count ── */
fetch('{{ route("cart.items") }}').then(r=>r.json()).then(d=>{
    const count=d.items?d.items.reduce((s,i)=>s+i.quantity,0):0;
    document.getElementById('cartBadge').textContent=count;
}).catch(()=>{});

/* ── LOGIN MODAL ── */
function openLoginModal(tab='login'){
    document.getElementById('loginModal').style.display='flex';
    document.body.style.overflow='hidden';
    switchAuthTab(tab);
}
function closeLoginModal(){
    document.getElementById('loginModal').style.display='none';
    document.body.style.overflow='';
}
function switchAuthTab(tab){
    document.querySelectorAll('.auth-tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.auth-tab-pane').forEach(p=>p.style.display='none');
    document.getElementById('authTab_'+tab).classList.add('active');
    document.getElementById('authPane_'+tab).style.display='block';
}
document.addEventListener('keydown',e=>{
    if(e.key==='Escape') closeLoginModal();
});

/* ── USER MENU DROPDOWN ── */
function toggleUserMenu(e){
    if(e) e.stopPropagation();
    const d=document.getElementById('userMenuDropdown');
    if(d) d.style.display = d.style.display==='none' ? 'block' : 'none';
}
document.addEventListener('click',e=>{
    const wrap=document.getElementById('userMenuWrap');
    if(wrap && !wrap.contains(e.target)){
        const d=document.getElementById('userMenuDropdown');
        if(d) d.style.display='none';
    }
});

@if(session('open_login_modal'))
window.addEventListener('load',()=>openLoginModal());
@endif
</script>

{{-- ═══════════════════════════════════════════════════════════
     LOGIN / REGISTER MODAL
═══════════════════════════════════════════════════════════ --}}
<div id="loginModal" style="display:none;position:fixed;inset:0;z-index:9990;background:rgba(0,0,0,.55);align-items:center;justify-content:center;animation:fadeInBg .2s ease;">
    <div style="background:var(--surface);border-radius:20px;width:100%;max-width:430px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;animation:slideUp .3s cubic-bezier(.4,0,.2,1);">

        {{-- Header --}}
        <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;background:var(--primary);border-radius:9px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-leaf" style="color:#fff;font-size:16px;"></i>
                </div>
                <span style="font-size:16px;font-weight:700;color:var(--text);">amardorkar</span>
            </div>
            <button onclick="closeLoginModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:background .15s;" onmouseover="this.style.background='rgba(239,68,68,.1)';this.style.color='#ef4444'" onmouseout="this.style.background='none';this.style.color='var(--muted)'">×</button>
        </div>

        {{-- Tab Buttons --}}
        <div style="display:flex;gap:0;padding:16px 24px 0;border-bottom:2px solid var(--border);margin:0 0 0 0;">
            <button id="authTab_login" onclick="switchAuthTab('login')" class="auth-tab-btn active" style="flex:1;padding:10px;background:none;border:none;border-bottom:3px solid transparent;font-size:14px;font-weight:600;color:var(--muted);cursor:pointer;margin-bottom:-2px;transition:color .2s,border-color .2s;">
                <i class="fas fa-sign-in-alt me-1"></i>{{ app()->getLocale()==='bn' ? 'লগইন' : 'Login' }}
            </button>
            <button id="authTab_register" onclick="switchAuthTab('register')" class="auth-tab-btn" style="flex:1;padding:10px;background:none;border:none;border-bottom:3px solid transparent;font-size:14px;font-weight:600;color:var(--muted);cursor:pointer;margin-bottom:-2px;transition:color .2s,border-color .2s;">
                <i class="fas fa-user-plus me-1"></i>{{ app()->getLocale()==='bn' ? 'রেজিস্ট্রেশন' : 'Register' }}
            </button>
        </div>

        <div style="padding:24px;">

            {{-- ── LOGIN PANE ── --}}
            <div id="authPane_login" class="auth-tab-pane">
                <p style="font-size:13px;color:var(--muted);margin-bottom:20px;" class="bengali-text">
                    {{ app()->getLocale()==='bn' ? 'Admin বা User — একই জায়গা থেকে লগইন করুন।' : 'Admin or User — login from the same place.' }}
                </p>

                @if($errors->any())
                <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" onsubmit="handleLoginSubmit(event)">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'ইমেইল' : 'Email' }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);transition:border-color .2s;"
                            placeholder="your@email.com"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড' : 'Password' }}</label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="loginPwd" required
                                style="width:100%;padding:10px 40px 10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);transition:border-color .2s;"
                                placeholder="••••••••"
                                onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                            <button type="button" onclick="togglePwd('loginPwd',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;color:var(--muted);">
                            <input type="checkbox" name="remember" style="accent-color:var(--primary);">
                            {{ app()->getLocale()==='bn' ? 'মনে রাখুন' : 'Remember me' }}
                        </label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size:13px;color:var(--primary);text-decoration:none;" onclick="closeLoginModal()">
                            {{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড ভুলেছেন?' : 'Forgot password?' }}
                        </a>
                        @endif
                    </div>
                    <button type="submit" style="width:100%;background:var(--primary);color:#fff;border:none;padding:12px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s,transform .15s;" onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                        <i class="fas fa-sign-in-alt"></i>
                        {{ app()->getLocale()==='bn' ? 'লগইন করুন' : 'Login' }}
                    </button>
                </form>
                <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--muted);" class="bengali-text">
                    {{ app()->getLocale()==='bn' ? 'নতুন অ্যাকাউন্ট নেই?' : "Don't have an account?" }}
                    <a href="#" onclick="switchAuthTab('register');return false;" style="color:var(--primary);font-weight:600;text-decoration:none;">
                        {{ app()->getLocale()==='bn' ? 'রেজিস্ট্রেশন করুন' : 'Register' }}
                    </a>
                </p>
            </div>

            {{-- ── REGISTER PANE ── --}}
            <div id="authPane_register" class="auth-tab-pane" style="display:none;">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'নাম' : 'Name' }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);"
                            placeholder="{{ app()->getLocale()==='bn' ? 'আপনার নাম' : 'Your name' }}"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'ইমেইল' : 'Email' }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);"
                            placeholder="your@email.com"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;">{{ app()->getLocale()==='bn' ? 'মোবাইল' : 'Mobile' }}</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}"
                            style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);"
                            placeholder="01XXXXXXXXX"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="display:flex;gap:10px;margin-bottom:12px;">
                        <div style="flex:1;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড' : 'Password' }}</label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="regPwd" required
                                    style="width:100%;padding:10px 36px 10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);"
                                    placeholder="••••••••"
                                    onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                                <button type="button" onclick="togglePwd('regPwd',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'নিশ্চিত করুন' : 'Confirm' }}</label>
                            <input type="password" name="password_confirmation" required
                                style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);"
                                placeholder="••••••••"
                                onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                    </div>
                    <button type="submit" style="width:100%;background:var(--primary);color:#fff;border:none;padding:12px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s;" onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                        <i class="fas fa-user-plus"></i>
                        {{ app()->getLocale()==='bn' ? 'অ্যাকাউন্ট খুলুন' : 'Create Account' }}
                    </button>
                </form>
                <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--muted);" class="bengali-text">
                    {{ app()->getLocale()==='bn' ? 'ইতিমধ্যে অ্যাকাউন্ট আছে?' : 'Already have an account?' }}
                    <a href="#" onclick="switchAuthTab('login');return false;" style="color:var(--primary);font-weight:600;text-decoration:none;">
                        {{ app()->getLocale()==='bn' ? 'লগইন করুন' : 'Login' }}
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<style>
.auth-tab-btn.active { color:var(--primary)!important; border-bottom-color:var(--primary)!important; }
.auth-tab-btn:hover  { color:var(--primary)!important; }
</style>

<script>
function togglePwd(id,btn){
    const inp=document.getElementById(id);
    if(!inp) return;
    const show = inp.type==='password';
    inp.type = show ? 'text' : 'password';
    btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
}

/* ── AJAX LOGIN HANDLER — no page reload ── */
function handleLoginSubmit(e){
    e.preventDefault();
    const form = document.getElementById('loginForm');
    const btn = form.querySelector('button[type=submit]');
    const origHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ app()->getLocale()==="bn" ? "অপেক্ষা করুন..." : "Please wait..." }}';
    btn.disabled = true;

    fetch('{{ route("login") }}', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(async r => {
        const json = await r.json().catch(()=>null);

        if(r.ok && json && json.success){
            closeLoginModal();

            // Admin হলে redirect করো
            if(json.redirect){
                window.location.href = json.redirect;
                return;
            }

            // নতুন CSRF update করো
            if(json.csrf){
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', json.csrf);
                window.CSRF = json.csrf;
            }

            // Header auth button আপডেট করো (page reload ছাড়া)
            const authArea = document.getElementById('authBtnArea');
            if(authArea && json.user_name){
                const uname = json.user_name.substring(0,12);
                authArea.innerHTML = `
                <div class="position-relative" id="userMenuWrap">
                    <button onclick="toggleUserMenu(event)" style="display:flex;align-items:center;gap:7px;background:var(--primary-light);border:1.5px solid var(--primary);color:var(--primary);padding:7px 13px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        <i class="fas fa-user-circle" style="font-size:16px;"></i>
                        <span class="d-none d-sm-inline">${uname}</span>
                        <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                    </button>
                    <div id="userMenuDropdown" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:var(--surface);border:1px solid var(--border);border-radius:10px;min-width:180px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:2000;overflow:hidden;">
                        <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:var(--text);text-decoration:none;font-size:13px;border-bottom:1px solid var(--border);">
                            <i class="fas fa-th-large" style="color:var(--primary);width:16px;"></i>
                            {{ app()->getLocale()==='bn' ? 'ড্যাশবোর্ড' : 'Dashboard' }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            <input type="hidden" name="_token" value="${window.CSRF}">
                            <button type="submit" style="display:flex;align-items:center;gap:10px;padding:12px 16px;width:100%;background:none;border:none;color:#ef4444;font-size:13px;cursor:pointer;text-align:left;">
                                <i class="fas fa-sign-out-alt" style="width:16px;"></i>
                                {{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}
                            </button>
                        </form>
                    </div>
                </div>`;
            }

            // Pending cart item silently add করো
            const raw = localStorage.getItem('pendingCartItem');
            if(raw){
                let item; try{ item=JSON.parse(raw); }catch(ex){ item=null; }
                localStorage.removeItem('pendingCartItem');
                if(item && item.product_id){
                    setTimeout(()=>{
                        fetch('{{ route("cart.add") }}',{
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.CSRF},
                            body:JSON.stringify({product_id:item.product_id, quantity:item.quantity||1, selected_size:item.selected_size||undefined})
                        }).then(r=>r.json()).then(d=>{
                            if(d.success){
                                document.getElementById('cartBadge').textContent=d.cart_count;
                                toast('{{ app()->getLocale()==="bn" ? "পণ্যটি কার্টে যোগ হয়েছে!" : "Item added to cart!" }}','success');
                            }
                        }).catch(()=>{});
                    }, 300);
                    return;
                }
            }
            toast('{{ app()->getLocale()==="bn" ? "সফলভাবে লগইন হয়েছে!" : "Logged in successfully!" }}','success');
            return;
        }

        btn.innerHTML = origHTML;
        btn.disabled = false;
        const msg = json?.errors?.email?.[0] || json?.message || '{{ app()->getLocale()==="bn" ? "লগইন ব্যর্থ হয়েছে।" : "Login failed." }}';
        let errEl = document.getElementById('loginErrorBox');
        if(!errEl){
            errEl = document.createElement('div');
            errEl.id = 'loginErrorBox';
            errEl.style.cssText = 'background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;';
            form.insertBefore(errEl, form.querySelector('div'));
        }
        errEl.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + msg;
        errEl.style.display = 'block';
    }).catch(()=>{
        btn.innerHTML = origHTML;
        btn.disabled = false;
        toast('{{ app()->getLocale()==="bn" ? "নেটওয়ার্ক সমস্যা।" : "Network error." }}','error');
    });
}
</script>

<script>
/* ── INSTANT THEME TOGGLE (no page reload) ── */
function toggleTheme(){
    const html = document.documentElement;
    const isDark = !html.classList.contains('theme-dark');
    html.classList.toggle('theme-dark', isDark);
    html.classList.toggle('theme-light', !isDark);
    const btn = document.getElementById('themeToggleBtn');
    if(btn){
        btn.querySelector('i').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        btn.title = isDark ? '{{ app()->getLocale()==="bn" ? "লাইট মোড" : "Light Mode" }}' : '{{ app()->getLocale()==="bn" ? "ডার্ক মোড" : "Dark Mode" }}';
    }
    // Save to session via AJAX — no reload
    fetch('{{ route("theme.ajax") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({theme: isDark ? 'dark' : 'light'})
    });
}

/* ── INSTANT LANGUAGE SWITCH (no page reload) ── */
function switchLang(lang) {
    // Button active state আপডেট করো তৎক্ষণাৎ
    document.getElementById('langBtnBn').classList.toggle('active', lang === 'bn');
    document.getElementById('langBtnEn').classList.toggle('active', lang === 'en');

    // Session-এ save করো AJAX দিয়ে
    fetch('{{ route("language.switch") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({lang: lang})
    }).then(r => r.json()).then(() => {
        // Language switch-এর জন্য page reload দরকার কারণ সব text server-side render
        window.location.reload();
    }).catch(() => {
        window.location.reload();
    });
}
</script>
@stack('scripts')
</body>
</html>