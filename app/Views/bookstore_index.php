
<!-- DEBUG-VIEW START 1 APPPATH/Views/book_view.php -->
<!DOCTYPE html>
<html>
<head>
<script  id="debugbar_loader" data-time="1730282913.988981" src="https://leonards1.alternar.link/?debugbar"></script><script  id="debugbar_dynamic_script"></script><style  id="debugbar_dynamic_style"></style><script class="kint-rich-script">void 0===window.kintShared&&(window.kintShared=function(){"use strict";var e={dedupe:function(e,n){return[].forEach.call(document.querySelectorAll(e),function(e){e!==(n=n&&n.ownerDocument.contains(n)?n:e)&&e.parentNode.removeChild(e)}),n},runOnce:function(e){"complete"===document.readyState?e():window.addEventListener("load",e)}};return window.addEventListener("click",function(e){var n;e.target.classList.contains("kint-ide-link")&&((n=new XMLHttpRequest).open("GET",e.target.href),n.send(null),e.preventDefault())}),e}());
void 0===window.kintRich&&(window.kintRich=function(){"use strict";var l={selectText:function(e){var t=window.getSelection(),a=document.createRange();a.selectNodeContents(e),t.removeAllRanges(),t.addRange(a)},toggle:function(e,t){var a=l.getChildren(e);a&&(e.classList.toggle("kint-show",t),1===a.childNodes.length)&&(a=a.childNodes[0].childNodes[0])&&a.classList&&a.classList.contains("kint-parent")&&l.toggle(a,t)},toggleChildren:function(e,t){var a=l.getChildren(e);if(a){var o=a.getElementsByClassName("kint-parent"),s=o.length;for(void 0===t&&(t=e.classList.contains("kint-show"));s--;)l.toggle(o[s],t)}},switchTab:function(e){var t=e.previousSibling,a=0;for(e.parentNode.getElementsByClassName("kint-active-tab")[0].classList.remove("kint-active-tab"),e.classList.add("kint-active-tab");t;)1===t.nodeType&&a++,t=t.previousSibling;for(var o=e.parentNode.nextSibling.childNodes,s=0;s<o.length;s++)s===a?(o[s].classList.add("kint-show"),1===o[s].childNodes.length&&(t=o[s].childNodes[0].childNodes[0])&&t.classList&&t.classList.contains("kint-parent")&&l.toggle(t,!0)):o[s].classList.remove("kint-show")},mktag:function(e){return"<"+e+">"},openInNewWindow:function(e){var t=window.open();t&&(t.document.open(),t.document.write(l.mktag("html")+l.mktag("head")+l.mktag("title")+"Kint ("+(new Date).toISOString()+")"+l.mktag("/title")+l.mktag('meta charset="utf-8"')+l.mktag('script class="kint-rich-script" nonce="'+l.script.nonce+'"')+l.script.innerHTML+l.mktag("/script")+l.mktag('style class="kint-rich-style" nonce="'+l.style.nonce+'"')+l.style.innerHTML+l.mktag("/style")+l.mktag("/head")+l.mktag("body")+'<input class="kint-note-input" placeholder="Take some notes!"><div class="kint-rich">'+e.parentNode.outerHTML+"</div>"+l.mktag("/body")),t.document.close())},sortTable:function(e,a){var t=e.tBodies[0];[].slice.call(e.tBodies[0].rows).sort(function(e,t){if(e=e.cells[a].textContent.trim().toLocaleLowerCase(),t=t.cells[a].textContent.trim().toLocaleLowerCase(),isNaN(e)||isNaN(t)){if(isNaN(e)&&!isNaN(t))return 1;if(isNaN(t)&&!isNaN(e))return-1}else e=parseFloat(e),t=parseFloat(t);return e<t?-1:t<e?1:0}).forEach(function(e){t.appendChild(e)})},showAccessPath:function(e){for(var t=e.childNodes,a=0;a<t.length;a++)if(t[a].classList&&t[a].classList.contains("access-path"))return t[a].classList.toggle("kint-show"),void(t[a].classList.contains("kint-show")&&l.selectText(t[a]))},showSearchBox:function(e){var t=e.querySelector(".kint-search");t&&(t.classList.toggle("kint-show"),t.classList.contains("kint-show")?(e.classList.add("kint-show"),t.focus(),t.select(),l.search(e.parentNode,t.value)):e.parentNode.classList.remove("kint-search-root"))},search:function(e,t){e.querySelectorAll(".kint-search-match").forEach(function(e){e.classList.remove("kint-search-match")}),e.classList.remove("kint-search-match"),e.classList.toggle("kint-search-root",t.length),t.length&&l.findMatches(e,t)},findMatches:function(e,t){var a,o,s,n=e.cloneNode(!0);if(n.querySelectorAll(".access-path").forEach(function(e){e.remove()}),-1!=n.textContent.toUpperCase().indexOf(t.toUpperCase())){for(r in e.classList.add("kint-search-match"),e.childNodes)if("DD"==e.childNodes[r].tagName){a=e.childNodes[r];break}if(a)if([].forEach.call(a.childNodes,function(e){"DL"==e.tagName?l.findMatches(e,t):"UL"==e.tagName&&(e.classList.contains("kint-tabs")?o=e.childNodes:e.classList.contains("kint-tab-contents")&&(s=e.childNodes))}),o&&s&&o.length==s.length)for(var r=0;r<o.length;r++){var i=!1;(i=-1!=o[r].textContent.toUpperCase().indexOf(t.toUpperCase())||((n=s[r].cloneNode(!0)).querySelectorAll(".access-path").forEach(function(e){e.remove()}),-1!=n.textContent.toUpperCase().indexOf(t.toUpperCase()))?!0:i)&&(o[r].classList.add("kint-search-match"),[].forEach.call(s[r].childNodes,function(e){"DL"==e.tagName&&l.findMatches(e,t)}))}}},getParentByClass:function(e,t){for(;;){if(!(e=e.parentNode)||!e.classList||e===document)return null;if(e.classList.contains(t))return e}return null},getParentHeader:function(e,t){for(var a=e.nodeName.toLowerCase();"dd"!==a&&"dt"!==a&&l.getParentByClass(e,"kint-rich");)a=(e=e.parentNode).nodeName.toLowerCase();return l.getParentByClass(e,"kint-rich")?(e="dd"===a&&t?e.previousElementSibling:e)&&"dt"===e.nodeName.toLowerCase()&&e.classList.contains("kint-parent")?e:void 0:null},getChildren:function(e){for(;(e=e.nextElementSibling)&&"dd"!==e.nodeName.toLowerCase(););return e},isFolderOpen:function(){if(l.folder&&l.folder.querySelector("dd.kint-foldout"))return l.folder.querySelector("dd.kint-foldout").previousSibling.classList.contains("kint-show")},initLoad:function(){l.style=window.kintShared.dedupe("style.kint-rich-style",l.style),l.script=window.kintShared.dedupe("script.kint-rich-script",l.script),l.folder=window.kintShared.dedupe(".kint-rich.kint-folder",l.folder);var t,e=document.querySelectorAll("input.kint-search");[].forEach.call(e,function(t){function e(e){window.clearTimeout(a),t.value!==o&&(a=window.setTimeout(function(){o=t.value,l.search(t.parentNode.parentNode,o)},500))}var a=null,o=null;t.removeEventListener("keyup",e),t.addEventListener("keyup",e)}),l.folder&&(t=l.folder.querySelector("dd"),[].forEach.call(document.querySelectorAll(".kint-rich.kint-file"),function(e){e.parentNode!==l.folder&&t.appendChild(e)}),document.body.appendChild(l.folder),l.folder.classList.add("kint-show"))},keyboardNav:{targets:[],target:0,active:!1,fetchTargets:function(){var e=l.keyboardNav.targets[l.keyboardNav.target];l.keyboardNav.targets=[],document.querySelectorAll(".kint-rich nav, .kint-tabs>li:not(.kint-active-tab)").forEach(function(e){l.isFolderOpen()&&!l.folder.contains(e)||0===e.offsetWidth&&0===e.offsetHeight||l.keyboardNav.targets.push(e)}),e&&-1!==l.keyboardNav.targets.indexOf(e)&&(l.keyboardNav.target=l.keyboardNav.targets.indexOf(e))},sync:function(e){var t=document.querySelector(".kint-focused");t&&t.classList.remove("kint-focused"),l.keyboardNav.active&&((t=l.keyboardNav.targets[l.keyboardNav.target]).classList.add("kint-focused"),e||l.keyboardNav.scroll(t))},scroll:function(e){var t,a;l.folder&&e===l.folder.querySelector("dt > nav")||(e=(t=function(e){return e.offsetTop+(e.offsetParent?t(e.offsetParent):0)})(e),l.isFolderOpen()?(a=l.folder.querySelector("dd.kint-foldout")).scrollTo(0,e-a.clientHeight/2):window.scrollTo(0,e-window.innerHeight/2))},moveCursor:function(e){for(l.keyboardNav.target+=e;l.keyboardNav.target<0;)l.keyboardNav.target+=l.keyboardNav.targets.length;for(;l.keyboardNav.target>=l.keyboardNav.targets.length;)l.keyboardNav.target-=l.keyboardNav.targets.length;l.keyboardNav.sync()},setCursor:function(e){if(!l.isFolderOpen()||l.folder.contains(e)){l.keyboardNav.fetchTargets();for(var t=0;t<l.keyboardNav.targets.length;t++)if(e===l.keyboardNav.targets[t])return l.keyboardNav.target=t,!0}return!1}},mouseNav:{lastClickTarget:null,lastClickTimer:null,lastClickCount:0,renewLastClick:function(){window.clearTimeout(l.mouseNav.lastClickTimer),l.mouseNav.lastClickTimer=window.setTimeout(function(){l.mouseNav.lastClickTarget=null,l.mouseNav.lastClickTimer=null,l.mouseNav.lastClickCount=0},250)}},style:null,script:null,folder:null};return window.addEventListener("click",function(e){var t=e.target;if(l.mouseNav.lastClickTarget&&l.mouseNav.lastClickTimer&&l.mouseNav.lastClickCount)if(t=l.mouseNav.lastClickTarget,1===l.mouseNav.lastClickCount)l.toggleChildren(t.parentNode),l.keyboardNav.setCursor(t),l.keyboardNav.sync(!0),l.mouseNav.lastClickCount++,l.mouseNav.renewLastClick();else{for(var a=t.parentNode.classList.contains("kint-show"),o=document.getElementsByClassName("kint-parent"),s=o.length;s--;)l.toggle(o[s],a);l.keyboardNav.setCursor(t),l.keyboardNav.sync(!0),l.keyboardNav.scroll(t),window.clearTimeout(l.mouseNav.lastClickTimer),l.mouseNav.lastClickTarget=null,l.mouseNav.lastClickTarget=null,l.mouseNav.lastClickCount=0}else if(l.getParentByClass(t,"kint-rich")){var n=t.nodeName.toLowerCase();if("dfn"===n&&l.selectText(t),"th"===n)e.ctrlKey||l.sortTable(t.parentNode.parentNode.parentNode,t.cellIndex);else if((t=l.getParentHeader(t))&&(l.keyboardNav.setCursor(t.querySelector("nav")),l.keyboardNav.sync(!0)),t=e.target,"li"===n&&"kint-tabs"===t.parentNode.className)"kint-active-tab"!==t.className&&l.switchTab(t),(t=l.getParentHeader(t,!0))&&(l.keyboardNav.setCursor(t.querySelector("nav")),l.keyboardNav.sync(!0));else if("nav"===n)"footer"===t.parentNode.nodeName.toLowerCase()?(l.keyboardNav.setCursor(t),l.keyboardNav.sync(!0),(t=t.parentNode).classList.toggle("kint-show")):(l.toggle(t.parentNode),l.keyboardNav.fetchTargets(),l.mouseNav.lastClickCount=1,l.mouseNav.lastClickTarget=t,l.mouseNav.renewLastClick());else if(t.classList.contains("kint-popup-trigger")){var r=t.parentNode;if("footer"===r.nodeName.toLowerCase())r=r.previousSibling;else for(;r&&!r.classList.contains("kint-parent");)r=r.parentNode;l.openInNewWindow(r)}else t.classList.contains("kint-access-path-trigger")?l.showAccessPath(t.parentNode):t.classList.contains("kint-search-trigger")?l.showSearchBox(t.parentNode):t.classList.contains("kint-search")||("pre"===n&&3===e.detail?l.selectText(t):l.getParentByClass(t,"kint-source")&&3===e.detail?l.selectText(l.getParentByClass(t,"kint-source")):t.classList.contains("access-path")?l.selectText(t):"a"!==n&&(t=l.getParentHeader(t))&&(l.toggle(t),l.keyboardNav.fetchTargets()))}},!0),window.addEventListener("keydown",function(e){if(e.target===document.body&&!e.altKey&&!e.ctrlKey)if(68===e.keyCode){if(l.keyboardNav.active)l.keyboardNav.active=!1;else if(l.keyboardNav.active=!0,l.keyboardNav.fetchTargets(),0===l.keyboardNav.targets.length)return void(l.keyboardNav.active=!1);l.keyboardNav.sync(),e.preventDefault()}else if(l.keyboardNav.active)if(9===e.keyCode)l.keyboardNav.moveCursor(e.shiftKey?-1:1),e.preventDefault();else if(38===e.keyCode||75===e.keyCode)l.keyboardNav.moveCursor(-1),e.preventDefault();else if(40===e.keyCode||74===e.keyCode)l.keyboardNav.moveCursor(1),e.preventDefault();else{var t,a,o=l.keyboardNav.targets[l.keyboardNav.target];if("li"===o.nodeName.toLowerCase()){if(32===e.keyCode||13===e.keyCode)return l.switchTab(o),l.keyboardNav.fetchTargets(),l.keyboardNav.sync(),void e.preventDefault();if(39===e.keyCode||76===e.keyCode)return l.keyboardNav.moveCursor(1),void e.preventDefault();if(37===e.keyCode||72===e.keyCode)return l.keyboardNav.moveCursor(-1),void e.preventDefault()}o=o.parentNode,65===e.keyCode?(l.showAccessPath(o),e.preventDefault()):"footer"===o.nodeName.toLowerCase()&&o.parentNode.classList.contains("kint-rich")?32===e.keyCode||13===e.keyCode?(o.classList.toggle("kint-show"),e.preventDefault()):37===e.keyCode||72===e.keyCode?(o.classList.remove("kint-show"),e.preventDefault()):39!==e.keyCode&&76!==e.keyCode||(o.classList.add("kint-show"),e.preventDefault()):32===e.keyCode||13===e.keyCode?(l.toggle(o),l.keyboardNav.fetchTargets(),e.preventDefault()):39!==e.keyCode&&76!==e.keyCode&&37!==e.keyCode&&72!==e.keyCode||(t=39===e.keyCode||76===e.keyCode,o.classList.contains("kint-show")?l.toggleChildren(o,t):t||(a=l.getParentHeader(o.parentNode.parentNode,!0))&&(l.keyboardNav.setCursor((o=a).querySelector("nav")),l.keyboardNav.sync()),l.toggle(o,t),l.keyboardNav.fetchTargets(),e.preventDefault())}},!0),l}()),window.kintShared.runOnce(window.kintRich.initLoad);
void 0===window.kintMicrotimeInitialized&&(window.kintMicrotimeInitialized=1,window.addEventListener("load",function(){"use strict";var a={},t=Array.prototype.slice.call(document.querySelectorAll("[data-kint-microtime-group]"),0);t.forEach(function(t){var i,e;t.querySelector(".kint-microtime-lap")&&(i=t.getAttribute("data-kint-microtime-group"),e=parseFloat(t.querySelector(".kint-microtime-lap").innerHTML),t=parseFloat(t.querySelector(".kint-microtime-avg").innerHTML),void 0===a[i]&&(a[i]={}),(void 0===a[i].min||a[i].min>e)&&(a[i].min=e),(void 0===a[i].max||a[i].max<e)&&(a[i].max=e),a[i].avg=t)}),t.forEach(function(t){var i,e,r,o,n=t.querySelector(".kint-microtime-lap");null!==n&&(i=parseFloat(n.textContent),o=t.dataset.kintMicrotimeGroup,e=a[o].avg,r=a[o].max,o=a[o].min,i!==(t.querySelector(".kint-microtime-avg").textContent=e)||i!==o||i!==r)&&(n.style.background=e<i?"hsl("+(40-40*((i-e)/(r-e)))+", 100%, 65%)":"hsl("+(40+80*(e===o?0:(e-i)/(e-o)))+", 100%, 65%)")})}));
</script><style class="kint-rich-style">.kint-rich{font-size:13px;overflow-x:auto;white-space:nowrap;background:rgba(255,255,255,.9)}.kint-rich.kint-folder{position:fixed;bottom:0;left:0;right:0;z-index:999999;width:100%;margin:0;display:block}.kint-rich.kint-folder dd.kint-foldout{max-height:calc(100vh - 100px);padding-right:8px;overflow-y:scroll;display:none}.kint-rich.kint-folder dd.kint-foldout.kint-show{display:block}.kint-rich::selection,.kint-rich::-moz-selection,.kint-rich::-webkit-selection{background:#aaa;color:#1d1e1e}.kint-rich .kint-focused{box-shadow:0 0 3px 2px red}.kint-rich,.kint-rich::before,.kint-rich::after,.kint-rich *,.kint-rich *::before,.kint-rich *::after{box-sizing:border-box;border-radius:0;color:#1d1e1e;float:none !important;font-family:Consolas,Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,serif;line-height:15px;margin:0;padding:0;text-align:left}.kint-rich{margin:8px 0}.kint-rich dt,.kint-rich dl{width:auto}.kint-rich dt,.kint-rich div.access-path{background:#f8f8f8;border:1px solid #d7d7d7;color:#1d1e1e;display:block;font-weight:bold;list-style:none outside none;overflow:auto;padding:4px}.kint-rich dt:hover,.kint-rich div.access-path:hover{border-color:#aaa}.kint-rich>dl dl{padding:0 0 0 12px}.kint-rich dt.kint-parent>nav,.kint-rich>footer>nav{background:url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMCAxNTAiPjxwYXRoIGQ9Ik02IDdoMThsLTkgMTV6bTAgMzBoMThsLTkgMTV6bTAgNDVoMThsLTktMTV6bTAgMzBoMThsLTktMTV6bTAgMTJsMTggMThtLTE4IDBsMTgtMTgiIGZpbGw9IiM1NTUiLz48cGF0aCBkPSJNNiAxMjZsMTggMThtLTE4IDBsMTgtMTgiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlPSIjNTU1Ii8+PC9zdmc+") no-repeat scroll 0 0/15px 75px rgba(0,0,0,0);cursor:pointer;display:inline-block;height:15px;width:15px;margin-right:3px;vertical-align:middle}.kint-rich dt.kint-parent:hover>nav,.kint-rich>footer>nav:hover{background-position:0 25%}.kint-rich dt.kint-parent.kint-show>nav,.kint-rich>footer.kint-show>nav{background-position:0 50%}.kint-rich dt.kint-parent.kint-show:hover>nav,.kint-rich>footer.kint-show>nav:hover{background-position:0 75%}.kint-rich dt.kint-parent.kint-locked>nav{background-position:0 100%}.kint-rich dt.kint-parent+dd{display:none;border-left:1px dashed #d7d7d7}.kint-rich dt.kint-parent.kint-show+dd{display:block}.kint-rich var,.kint-rich var a{color:#06f;font-style:normal}.kint-rich dt:hover var,.kint-rich dt:hover var a{color:red}.kint-rich dfn{font-style:normal;font-family:monospace;color:#1d1e1e}.kint-rich pre{color:#1d1e1e;margin:0 0 0 12px;padding:5px;overflow-y:hidden;border-top:0;border:1px solid #d7d7d7;background:#f8f8f8;display:block;word-break:normal}.kint-rich .kint-popup-trigger,.kint-rich .kint-access-path-trigger,.kint-rich .kint-search-trigger{background:rgba(29,30,30,.8);border-radius:3px;height:16px;font-size:16px;margin-left:5px;font-weight:bold;width:16px;text-align:center;float:right !important;cursor:pointer;color:#f8f8f8;position:relative;overflow:hidden;line-height:17.6px}.kint-rich .kint-popup-trigger:hover,.kint-rich .kint-access-path-trigger:hover,.kint-rich .kint-search-trigger:hover{color:#1d1e1e;background:#f8f8f8}.kint-rich dt.kint-parent>.kint-popup-trigger{line-height:19.2px}.kint-rich .kint-search-trigger{font-size:20px}.kint-rich input.kint-search{display:none;border:1px solid #d7d7d7;border-top-width:0;border-bottom-width:0;padding:4px;float:right !important;margin:-4px 0;color:#1d1e1e;background:#f8f8f8;height:24px;width:160px;position:relative;z-index:100}.kint-rich input.kint-search.kint-show{display:block}.kint-rich .kint-search-root ul.kint-tabs>li:not(.kint-search-match){background:#f8f8f8;opacity:.5}.kint-rich .kint-search-root dl:not(.kint-search-match){opacity:.5}.kint-rich .kint-search-root dl:not(.kint-search-match)>dt{background:#f8f8f8}.kint-rich .kint-search-root dl:not(.kint-search-match) dl,.kint-rich .kint-search-root dl:not(.kint-search-match) ul.kint-tabs>li:not(.kint-search-match){opacity:1}.kint-rich div.access-path{background:#f8f8f8;display:none;margin-top:5px;padding:4px;white-space:pre}.kint-rich div.access-path.kint-show{display:block}.kint-rich footer{padding:0 3px 3px;font-size:9px;background:rgba(0,0,0,0)}.kint-rich footer>.kint-popup-trigger{background:rgba(0,0,0,0);color:#1d1e1e}.kint-rich footer nav{height:10px;width:10px;background-size:10px 50px}.kint-rich footer>ol{display:none;margin-left:32px}.kint-rich footer.kint-show>ol{display:block}.kint-rich a{color:#1d1e1e;text-shadow:none;text-decoration:underline}.kint-rich a:hover{color:#1d1e1e;border-bottom:1px dotted #1d1e1e}.kint-rich ul{list-style:none;padding-left:12px}.kint-rich ul:not(.kint-tabs) li{border-left:1px dashed #d7d7d7}.kint-rich ul:not(.kint-tabs) li>dl{border-left:none}.kint-rich ul.kint-tabs{margin:0 0 0 12px;padding-left:0;background:#f8f8f8;border:1px solid #d7d7d7;border-top:0}.kint-rich ul.kint-tabs>li{background:#f8f8f8;border:1px solid #d7d7d7;cursor:pointer;display:inline-block;height:24px;margin:2px;padding:0 12px;vertical-align:top}.kint-rich ul.kint-tabs>li:hover,.kint-rich ul.kint-tabs>li.kint-active-tab:hover{border-color:#aaa;color:red}.kint-rich ul.kint-tabs>li.kint-active-tab{background:#f8f8f8;border-top:0;margin-top:-1px;height:27px;line-height:24px}.kint-rich ul.kint-tabs>li:not(.kint-active-tab){line-height:20px}.kint-rich ul.kint-tabs li+li{margin-left:0}.kint-rich ul.kint-tab-contents>li{display:none}.kint-rich ul.kint-tab-contents>li.kint-show{display:block}.kint-rich dt:hover+dd>ul>li.kint-active-tab{border-color:#aaa;color:red}.kint-rich dt>.kint-color-preview{width:16px;height:16px;display:inline-block;vertical-align:middle;margin-left:10px;border:1px solid #d7d7d7;background-color:#ccc;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 2"><path fill="%23FFF" d="M0 0h1v2h1V1H0z"/></svg>');background-size:100%}.kint-rich dt>.kint-color-preview:hover{border-color:#aaa}.kint-rich dt>.kint-color-preview>div{width:100%;height:100%}.kint-rich table{border-collapse:collapse;empty-cells:show;border-spacing:0}.kint-rich table *{font-size:12px}.kint-rich table dt{background:none;padding:2px}.kint-rich table dt .kint-parent{min-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.kint-rich table td,.kint-rich table th{border:1px solid #d7d7d7;padding:2px;vertical-align:center}.kint-rich table th{cursor:alias}.kint-rich table td:first-child,.kint-rich table th{font-weight:bold;background:#f8f8f8;color:#1d1e1e}.kint-rich table td{background:#f8f8f8;white-space:pre}.kint-rich table td>dl{padding:0}.kint-rich table pre{border-top:0;border-right:0}.kint-rich table thead th:first-child{background:none;border:0}.kint-rich table tr:hover>td{box-shadow:0 0 1px 0 #aaa inset}.kint-rich table tr:hover var{color:red}.kint-rich table ul.kint-tabs li.kint-active-tab{height:20px;line-height:17px}.kint-rich pre.kint-source{margin-left:-1px}.kint-rich pre.kint-source[data-kint-filename]:before{display:block;content:attr(data-kint-filename);margin-bottom:4px;padding-bottom:4px;border-bottom:1px solid #f8f8f8}.kint-rich pre.kint-source>div:before{display:inline-block;content:counter(kint-l);counter-increment:kint-l;border-right:1px solid #aaa;padding-right:8px;margin-right:8px}.kint-rich pre.kint-source>div.kint-highlight{background:#f8f8f8}.kint-rich .kint-microtime-lap{text-shadow:-1px 0 #aaa,0 1px #aaa,1px 0 #aaa,0 -1px #aaa;color:#f8f8f8;font-weight:bold}input.kint-note-input{width:100%}.kint-rich .kint-focused{box-shadow:0 0 3px 2px red}.kint-rich dt{font-weight:normal}.kint-rich dt.kint-parent{margin-top:4px}.kint-rich dl dl{margin-top:4px;padding-left:25px;border-left:none}.kint-rich>dl>dt{background:#f8f8f8}.kint-rich ul{margin:0;padding-left:0}.kint-rich ul:not(.kint-tabs)>li{border-left:0}.kint-rich ul.kint-tabs{background:#f8f8f8;border:1px solid #d7d7d7;border-width:0 1px 1px 1px;padding:4px 0 0 12px;margin-left:-1px;margin-top:-1px}.kint-rich ul.kint-tabs li,.kint-rich ul.kint-tabs li+li{margin:0 0 0 4px}.kint-rich ul.kint-tabs li{border-bottom-width:0;height:25px}.kint-rich ul.kint-tabs li:first-child{margin-left:0}.kint-rich ul.kint-tabs li.kint-active-tab{border-top:1px solid #d7d7d7;background:#fff;font-weight:bold;padding-top:0;border-bottom:1px solid #fff !important;margin-bottom:-1px}.kint-rich ul.kint-tabs li.kint-active-tab:hover{border-bottom:1px solid #fff}.kint-rich ul>li>pre{border:1px solid #d7d7d7}.kint-rich dt:hover+dd>ul{border-color:#aaa}.kint-rich pre{background:#fff;margin-top:4px;margin-left:25px}.kint-rich .kint-source{margin-left:-1px}.kint-rich .kint-source .kint-highlight{background:#cfc}.kint-rich .kint-parent.kint-show>.kint-search{border-bottom-width:1px}.kint-rich table td{background:#fff}.kint-rich table td>dl{padding:0;margin:0}.kint-rich table td>dl>dt.kint-parent{margin:0}.kint-rich table td:first-child,.kint-rich table td,.kint-rich table th{padding:2px 4px}.kint-rich table dd,.kint-rich table dt{background:#fff}.kint-rich table tr:hover>td{box-shadow:none;background:#cfc}
</style>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Store - CRUD with CodeIgniter 4</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    
</head>
<body>
<style>
        a {
  color: black; 
  background-color: white 
}



.btn-default {
    color: #333;
    background-color: #fff;
    border-color: #ccc;
}
#tagsContainer {
    display: block;
    min-height: 50px;
    width: 100%;
    margin-bottom: 15px;
}
.form-group {
            margin-bottom: 0;
        }

        .remove-tag-btn {
            margin-left: 5px;
        }


        </style>
<div class="container">
    <h3>Book Store</h3>
    <br>
    <button class="btn btn-success" onclick="add_book()"><i class="glyphicon glyphicon-plus"></i> Add Book</button>
    <br><br>
    <table id="table_id" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Book ISBN</th>
                <th>Book Title</th>
                <th>Book Author</th>
                <th>Book Category</th>
                <th>Book Title Image</th> 
                <th style="width:125px;">Action</th>
            </tr>
        </thead>
        <tbody>
                            <tr>
                    <td>4</td>
                    <td>4</td>
                    <td>Rich Dar Poor dad</td>
                    <td>Robert Kiyosaki</td>
                    <td>["Auto Biography", "Educational"]</td>
                    <td>
                        <div><img src="https://leonards1.alternar.link/uploads/book-image-4.png" alt="Book Image" style="max-width: 100px; height: auto;"></div>                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(4)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(4)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>7</td>
                    <td>7</td>
                    <td>The Intelligent Investor</td>
                    <td>Benjamin Graham</td>
                    <td>["Auto Biography"]</td>
                    <td>
                        <div><img src="https://leonards1.alternar.link/uploads/book-image-7.png" alt="Book Image" style="max-width: 100px; height: auto;"></div>                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(7)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(7)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>3</td>
                    <td>3</td>
                    <td>Pride and Prejudice</td>
                    <td>Jane Austen</td>
                    <td>["Drama"]</td>
                    <td>
                        <div><img src="https://leonards1.alternar.link/uploads/book-image-3.png" alt="Book Image" style="max-width: 100px; height: auto;"></div>                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(3)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(3)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>9</td>
                    <td>9</td>
                    <td>The God of Small Things</td>
                    <td>Arundhati Roy</td>
                    <td>["Fantasy"]</td>
                    <td>
                        <div><img src="https://leonards1.alternar.link/uploads/book-image-9.png" alt="Book Image" style="max-width: 100px; height: auto;"></div>                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(9)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(9)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>140</td>
                    <td>1</td>
                    <td>1</td>
                    <td>1</td>
                    <td>["Mystery"]</td>
                    <td>
                        <div></div>No images found                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(140)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(140)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>141</td>
                    <td>1</td>
                    <td>s</td>
                    <td>s</td>
                    <td>["Mystery", "Fantasy", "History"]</td>
                    <td>
                        <div></div>No images found                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(141)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(141)">Delete</button>
                    </td>
                </tr>
                            <tr>
                    <td>5</td>
                    <td>5</td>
                    <td>Think and Grow Rich</td>
                    <td>Napoleon Hill</td>
                    <td>["Auto Biography"]</td>
                    <td>
                        <div><img src="https://leonards1.alternar.link/uploads/book-image-5_1.jpg" alt="Book Image" style="max-width: 100px; height: auto;"></div>                    </td>

                    <td>
                        <button class="btn btn-warning" onclick="edit_book(5)">Edit</button>
                        
                        <button class="btn btn-danger" onclick="delete_book(5)">Delete</button>
                    </td>
                </tr>
                    </tbody>
        <tfoot>
            <tr>
                <th>Book ID</th>
                <th>Book ISBN</th>
                <th>Book Title</th>
                <th>Book Author</th>
                <th>Book Category</th>
                <th>Book Title Image</th> 
                <th>Action</th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="modal fade" id="modal_form" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Book Form</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            
            
            <div class="modal-body form" id="form_container">
             <div id="images_preview"></div>           

            
            </div>
            <div id="tagsContainer"></div>
            <div class="tag-item">
            
          

        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.1/underscore-min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="/lib/jsonform.js"  type="text/javascript" ></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


<script type="text/javascript">
$(document).ready(function () {
    $('#table_id').DataTable();
});

    function add_book() {
        save_method = 'add';
        $('#modal_form').modal('show');
        $('#form_container').empty();
        render_form({});
    }
    
var removedFiles = [];

function filePreview(input) {
    var imagesPreview = $('#images_preview');

    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            (function(file, index) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    let imgContainer = $('<div>').css({
                        'display': 'flex', 
                        'align-items': 'center', 
                        'margin-bottom': '10px'
                    });

                    let imgName = $('<div>').text(file.name).css({
                        'margin-right': '10px',
                        'max-width': '140px',
                        'overflow': 'hidden',
                        'text-overflow': 'ellipsis',
                        'white-space': 'nowrap'
                    });

                    let img = $('<img>').attr({
                        'src': event.target.result,
                        'alt': 'Book Image Preview'
                    }).css({
                        'max-width': '100px',
                        'height': 'auto',
                        'margin-right': '10px'
                    });

                    let imgTitleInput = $('<input>').attr({
                        'type': 'text',
                        'name': 'new_image_titles[]',
                        'placeholder': 'Enter image title',
                        'value': ''
                    }).css({
                        'margin-right': '10px'
                    });

                    let removeBtn = $('<button>').addClass('btn btn-danger').text('Remove').on('click', function() {
                        removedFiles.push(i);
                        $(this).parent().remove();
                    });

                    imgContainer.append(imgName).append(img).append(imgTitleInput).append(removeBtn);
                    imagesPreview.append(imgContainer);
                };
                reader.readAsDataURL(file);
            })(input.files[i]);
        }
    }
}


$('input[type="file"][name="book_image_title"]').on('change', function() {
    filePreview(this);
});



function addIconsToButtons() {
    if (!$("link[href*='font-awesome']").length) {
        var link = document.createElement('link');
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css';
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
    $('.btn-default._jsonform-array-deletecurrent').not('.icon-added').each(function() {
        $(this).prepend('<i class="fas fa-minus-circle"></i>').addClass('icon-added');
    });
    $('.btn-default._jsonform-array-addmore').not('.icon-added').each(function() {
        $(this).prepend('<i class="fas fa-plus-circle"></i>').addClass('icon-added');
    });
    $('.btn-default._jsonform-array-deletelast').not('.icon-added').each(function() {
        $(this).prepend('<i class="fas fa-minus-circle"></i>').addClass('icon-added');
    });   
}

function save(data) {
    data.book_category = $('[name="book_category"]').val();
    
    var formData = new FormData();
    formData.append('book_id', data.book_id);
    formData.append('book_isbn', data.book_isbn);
    formData.append('book_title', data.book_title);
    formData.append('book_author', data.book_author);
    formData.append('book_category', JSON.stringify(data.book_category));
    formData.append('tags', JSON.stringify(data.tags));

    $('input[name="delete_image[]"]:checked').each(function() {
        formData.append('delete_image[]', $(this).val());
    });
    
    


    var fileInputs = $('input[type="file"][name="book_image_title"]');
    var titleInputs = $('input[type="text"][name="new_image_titles[]"]');
    var titleInput_render = $('input[type="text"][name="image_titles[]"]');
    

    fileInputs.each(function(index, fileInput) {
        if (!removedFiles.includes(index)) {
            if(fileInput.files.length > 0) {
                var file = fileInput.files[0];
                formData.append("book_image_title[]", file);
                var title = titleInputs.eq(index).val() || '';
                formData.append("new_image_titles[]", title);
            }
        }
    });

    $('input[type="text"][name="image_titles[]"]').each(function(index, input) {
        formData.append("image_titles[]", $(input).val());
    });
    var url = save_method === 'add' ? "https://leonards1.alternar.link/book/book_add" : "https://leonards1.alternar.link/book/book_update";

    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false, 
        contentType: false,  
        success: function(response) {
            $('#modal_form').modal('hide');
            location.reload(); 
            alert("Tags saved:\n" + JSON.stringify(data.tags, null, 4));
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR);
            alert('Error adding / updating book data');
        }
    });
}

function render_form(data) {
        console.log('Executing render_form function...');
        console.log("Received data:", data);

        var tagItems = [];
        if (data.tags) {
        tagItems = data.tags.map(function(tag) {
            return { "type": "string", "title": "Tag", "value": tag };
        });
    }

    var schema = {
        "title": "Book Form",
        "type": "object",
        "properties": {
            "book_id": {"type": "integer", "title": "Book ID", "default": data.book_id},
            "book_isbn": {"type": "string", "title": "Book ISBN", "default": data.book_isbn},
            "book_title": {"type": "string", "title": "Book Title", "default": data.book_title},
            "book_author": {"type": "string", "title": "Book Author", "default": data.book_author},
            "book_category": {"type": "string", "title": "Book Category", "enum": [], "default": data.book_category},
            "book_image_title": {"title": "Book Image Title", "type": "file", "format": "file"},
            "tags": {"type": "array", "title": "Tag", "items": { "type": "string"}}
        } 
    };
    var form = [{
        "type": "fieldset",
        "title": "Sadaļas:",
        "items": [{
            "type": "tabs",
            "id": "navtabs",
            "items": [
                {
                    "title": "Pamata lauki",
                    "type": "tab",
                    "items": [
                        "book_id",
                        "book_isbn",
                        "book_title",
                        "book_author",
                        "book_category"
                    ]
                },
                {
                    "title": "Bildes",
                    "type": "tab",
                    "items": [
                        "book_image_title"
                    ]
                },
                {
                    "title": "Tags",
                    "type": "tab",
                    "items": [
                        "tags"
                    ]
                },
            ]
        }]
    }];

    


    var formOptions = {
        schema: schema,
        form: form,
        value: {
            tags: data.tags
        },
        onSubmit: function (errors, values) {
            if (errors) {
                console.log('Validation errors:', errors);
            } else {
                console.log('Submitted values:', values);
                save(values);
            }
        }
    };

        var $form = $("<form>");
        $form.jsonForm(formOptions);

        setTimeout(addIconsToButtons, 50);

        var imagesPreviewContainer = $('<div id="images_preview" style="margin-bottom: 20px;"></div>');
        
        var $saveButton = $('<button>', {
        type: 'submit',
        text: 'Save',
        class: 'btn btn-primary'
    });
    $form.append($saveButton);
    


    $form.ready(function() {
        if (data.images && data.images.length > 0) {
            data.images.forEach(function(image) {
                var imgContainer = $('<div>').addClass('image-container').css('display', 'flex').css('margin-bottom', '10px').css('align-items', 'center');
                var img = $('<img>').attr('src', image.url).css('max-width', '100px').css('height', 'auto');
                var titleInput = $('<input>').attr({
                    type: 'text',
                    name: 'image_titles[]',
                    placeholder: 'Enter image title',
                    value: image.title || ''
                }).css('margin-left', '10px').css('flex-grow', '1');
                var checkbox = $(image.checkbox).css('margin-left', '10px');
        imgContainer.append(img).append(titleInput).append(checkbox);
                imagesPreviewContainer.append(imgContainer);
            });
        }
        $("#navtabs-Bildes").append(imagesPreviewContainer);
    });


        $form.find('input[name="book_image_title"]').on('change', function() {
            filePreview(this);
        });

        $form.find('input[name="book_image_title"]').attr('multiple', 'multiple');
        $form.attr('enctype', 'multipart/form-data');
        populateCategoryDropdownForForm(data.book_category || []);
        
        $('#form_container').html($form);
    }



function populateCategoryDropdownForForm(selectedCategory) {
    $.ajax({
        url: "https://leonards1.alternar.link/categories/getCategories",
        type: "GET",
        dataType: "json",
        success: function(data) {
            var categoryDropdown = $('[name="book_category"]');
            categoryDropdown.attr('multiple', 'multiple').select2(); 
            categoryDropdown.empty();
            data.forEach(function(category) {
                var option = $('<option>', {
                    value: category.category_name,
                    text: category.category_name
                });
                if (Array.isArray(selectedCategory) && selectedCategory.includes(category.category_name)) {
                    option.attr('selected', 'selected');
                }
                categoryDropdown.append(option);
            });
            categoryDropdown.trigger('change'); 
            categoryDropdown.select2().next(".select2-container").css("width", "100%"); 
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            alert('Error getting categories from server');
        }
    });
}


function edit_book(id) {
    save_method = 'update';
    
    $.ajax({
        url: "https://leonards1.alternar.link/book/ajax_edit//" + id,
        type: "GET",
        dataType: "json",
        success: function(data) {
            $('#modal_form').modal('show');
            $('#form_container').empty();
            var imagesPreview = $('#images_preview');
            imagesPreview.html(''); 
            if (data.image_url) {
                var img = $('<img>').attr('src', data.image_url).css('max-width', '100px').css('height', 'auto');
                imagesPreview.append(img);
                
            } else {
                imagesPreview.append($('<p>').text('No image available'));
            }
            // renderTags(data.tags);
            render_form(data);
            populateImageTitles(data.images);
            // populateTags(data.tags);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            alert('Error getting data from ajax');
        }
    });
}

function populateImageTitles(images) {
    if (images) {
        images.forEach(function(image, index) {
            $('input[name="image_titles[]"]').eq(index).val(image.title);
        });
    }
}

    function delete_book(id) {
    if (confirm('Are you sure you want to delete this data?')) {
        var markedForDeletion = $('input[name="delete_image[]"]:checked').map(function(){
            return $(this).val();
        }).get();
        
        $.ajax({
            url: "https://leonards1.alternar.link/book/book_delete/" + id,
            type: "POST",
            data: { delete_image: markedForDeletion },
            dataType: "JSON",
            success: function(data) {
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Error deleting data');
            }
        });
    }
}

</script>

</body>
</html>
<!-- DEBUG-VIEW ENDED 1 APPPATH/Views/book_view.php -->
