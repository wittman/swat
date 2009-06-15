(function(){var p=tinymce.DOM;var q=tinymce.dom.Event;var u=tinymce.util.Dispatcher;var v=function(a){if(typeof YAHOO=='undefined'||typeof YAHOO.util=='undefined'||typeof YAHOO.util.Dom=='undefined'){var b=p.getRect(a)}else{var b=YAHOO.util.Dom.getRegion(a);b.x=b.left;b.y=b.top;b.w=b.right-b.left;b.h=b.bottom-b.top}return b};var z=function(){var a=p.getViewPort();var b=(typeof document.scrollHeight=='undefined')?document.body.scrollHeight:document.scrollHeight;var h=Math.max(b,a.h);return h};tinymce.PluginManager.requireLangPack('swat');var A={isFunction:function(o){return Object.prototype.toString.apply(o)==='[object Function]'},_IEEnumFix:(tinymce.isIE)?function(r,s){var i,fname,f;var a=['toString','valueOf'];for(i=0;i<a.length;i=i+1){fname=a[i];f=s[fname];if(A.isFunction(f)&&f!=Object.prototype[fname]){r[fname]=f}}}:function(){},hasOwnProperty:(Object.prototype.hasOwnProperty)?function(o,a){return(o&&o.hasOwnProperty(a))}:function(o,a){return(!A.isUndefined(o[a])&&o.constructor.prototype[a]!==o[a])},isUndefined:function(o){return typeof o==='undefined'},extend:function(a,b,c){if(!b||!a){throw new Error('extend failed, please check that '+'all dependencies are included.');}var F=function(){},i;F.prototype=b.prototype;a.prototype=new F();a.prototype.constructor=a;a.superclass=b.prototype;if(b.prototype.constructor==Object.prototype.constructor){b.prototype.constructor=b}if(c){for(i in c){if(A.hasOwnProperty(c,i)){a.prototype[i]=c[i]}}A._IEEnumFix(a.prototype,c)}}};A.MODE_VISUAL=1;A.MODE_SOURCE=2;A.Dialog=function(a){this.editor=a;this.selection=null;this.frame=null;this.container=null;this.drawOverlay();this.drawDialog();this.onConfirm=new u(this);this.onCancel=new u(this)};A.Dialog.prototype.reset=function(){};A.Dialog.prototype.focus=function(){};A.Dialog.prototype.getData=function(){return{}};A.Dialog.prototype.setData=function(a){};A.Dialog.prototype.close=function(a){var b=this.editor;this.overlay.style.display='none';this.container.style.display='none';if(tinymce.isIE6){selectElements=p.doc.getElementsByTagName('select');for(var i=0;i<selectElements.length;i++){if(typeof selectElements[i].style._visibility!='undefined'){selectElements[i].style.visibility=selectElements[i].style._visibility}}}var c=(b.settings.content_editable)?b.getBody():(!tinymce.isGecko)?b.getWin():b.getDoc();q.remove(c,'focus',this.handleEditorFocus);b.focus();if(this.selection){b.selection.moveToBookmark(this.selection)}if(a){this.onConfirm.dispatch(this,this.getData())}else{this.onCancel.dispatch(this)}this.reset();q.remove(p.doc,'keydown',this.handleKeyPress)};A.Dialog.prototype.open=function(a){var b=this.editor;this.selection=b.selection.getBookmark();var c=(b.settings.content_editable)?b.getBody():(!tinymce.isGecko)?b.getWin():b.getDoc();q.add(c,'focus',this.handleEditorFocus,this);this.setData(a);if(tinymce.isIE6){selectElements=p.doc.getElementsByTagName('select');for(var i=0;i<selectElements.length;i++){selectElements[i].style._visibility=selectElements[i].style.visibility;selectElements[i].style.visibility='hidden'}}this.container.style.left='-10000px';this.container.style.top='-10000px';this.container.style.display='block';var d=v(this.container);var e=b.getContainer().firstChild;var f=v(e);var x=f.x+((f.w-d.w)/2);var y=f.y+30;this.overlay.style.height=z()+'px';this.overlay.style.display='block';this.container.style.left=x+'px';this.container.style.top=y+'px';this.container.style.display='block';this.focus();q.add(p.doc,'keydown',this.handleKeyPress,this)};A.Dialog.prototype.drawDialog=function(){this.frame=p.create('div');this.frame.className='swat-frame';this.container=p.create('div');this.container.className='swat-textarea-editor-dialog';this.container.appendChild(this.frame);p.doc.body.appendChild(this.container)};A.Dialog.prototype.drawOverlay=function(){this.overlay=p.create('div');this.overlay.className='swat-textarea-editor-overlay';p.doc.body.appendChild(this.overlay)};A.Dialog.prototype.handleKeyPress=function(e){var a=(e.which)?e.which:((e.keyCode)?e.keyCode:e.charCode);if(a==27){this.close(false)}};A.Dialog.prototype.handleEditorFocus=function(e){window.focus();this.focus()};A.LinkDialog=function(a){A.LinkDialog.superclass.constructor.call(this,a)};A.extend(A.LinkDialog,A.Dialog,{focus:function(){this.uriEntry.focus()},reset:function(){this.uriEntry.value=''},getData:function(){var a=A.LinkDialog.superclass.getData.call(this);a.link_uri=this.uriEntry.value;return a},setData:function(a){A.LinkDialog.superclass.setData.call(this,a);if(a.link_uri){this.uriEntry.value=a.link_uri}},open:function(a){A.LinkDialog.superclass.open.call(this,a);if(this.uriEntry.value.length){this.insertButton.value=this.editor.getLang('swat.link_update');this.entryNote.style.display='block'}else{this.insertButton.value=this.editor.getLang('swat.link_insert');this.entryNote.style.display='none'}},drawDialog:function(){A.LinkDialog.superclass.drawDialog.call(this);var a=this.editor.id+'_link_entry';this.uriEntry=p.create('input',{id:a,type:'text'});this.uriEntry.className='swat-entry';q.add(this.uriEntry,'focus',function(e){this.select()},this.uriEntry);var b=p.create('label');b.htmlFor=a;b.appendChild(p.doc.createTextNode(this.editor.getLang('swat.link_uri_field')));var c=p.create('div');c.className='swat-form-field-contents';c.appendChild(this.uriEntry);this.entryNote=p.create('div');this.entryNote.className='swat-note';this.entryNote.appendChild(p.doc.createTextNode(this.editor.getLang('swat.link_uri_field_note')));var d=p.create('div');d.className='swat-form-field';d.appendChild(b);d.appendChild(c);d.appendChild(this.entryNote);this.insertButton=p.create('input',{type:'button'});this.insertButton.className='swat-button swat-primary';this.insertButton.value=this.editor.getLang('swat.link_insert');q.add(this.insertButton,'click',function(e){this.close(true)},this);var f=p.create('input',{type:'button'});f.className='swat-button';f.value=this.editor.getLang('swat.link_cancel');q.add(f,'click',function(e){this.close(false)},this);var g=p.create('div');g.className='swat-form-field-contents';g.appendChild(this.insertButton);g.appendChild(f);var h=p.create('div');h.className='swat-footer-form-field';h.appendChild(g);var i=p.create('form');i.className='swat-form';i.appendChild(d);i.appendChild(h);q.add(i,'submit',function(e){q.cancel(e);this.close(true)},this);this.frame.appendChild(i)}});A.ImageDialog=function(a){A.ImageDialog.superclass.constructor.call(this,a)};A.extend(A.ImageDialog,A.Dialog,{focus:function(){this.srcEntry.focus()},reset:function(){this.srcEntry.value='';this.altEntry.value=''},getData:function(){var a=A.ImageDialog.superclass.getData.call(this);a.image_src=this.srcEntry.value;a.image_alt=this.altEntry.value;return a},setData:function(a){A.ImageDialog.superclass.setData.call(this,a);if(a.image_src){this.srcEntry.value=a.image_src}if(a.image_alt){this.altEntry.value=a.image_alt}},open:function(a){A.ImageDialog.superclass.open.call(this,a);if(this.srcEntry.value.length){this.insertButton.value=this.editor.getLang('swat.image_update')}else{this.insertButton.value=this.editor.getLang('swat.image_insert')}},drawDialog:function(){A.ImageDialog.superclass.drawDialog.call(this);var a=this.editor.id+'_image_src_entry';this.srcEntry=p.create('input',{id:a,type:'text'});this.srcEntry.className='swat-entry';q.add(this.srcEntry,'focus',function(e){this.select()},this.srcEntry);var b=p.create('label');b.htmlFor=a;b.appendChild(p.doc.createTextNode(this.editor.getLang('swat.image_src_field')));var c=p.create('div');c.className='swat-form-field-contents';c.appendChild(this.srcEntry);var d=p.create('div');d.className='swat-form-field';d.appendChild(b);d.appendChild(c);var f=this.editor.id+'_image_alt_entry';this.altEntry=p.create('input',{id:f,type:'text'});this.altEntry.className='swat-entry';q.add(this.altEntry,'focus',function(e){this.select()},this.altEntry);var g=p.create('span');g.className='swat-note';g.appendChild(p.doc.createTextNode(this.editor.getLang('swat.image_optional')));var h=p.create('label');h.htmlFor=f;h.appendChild(p.doc.createTextNode(this.editor.getLang('swat.image_alt_field')));h.appendChild(p.doc.createTextNode(' '));h.appendChild(g);var i=p.create('div');i.className='swat-form-field-contents';i.appendChild(this.altEntry);var j=p.create('div');j.className='swat-form-field';j.appendChild(h);j.appendChild(i);this.insertButton=p.create('input',{type:'button'});this.insertButton.className='swat-button swat-primary';this.insertButton.value=this.editor.getLang('swat.image_insert');q.add(this.insertButton,'click',function(e){this.close(true)},this);var k=p.create('input',{type:'button'});k.className='swat-button';k.value=this.editor.getLang('swat.image_cancel');q.add(k,'click',function(e){this.close(false)},this);var l=p.create('div');l.className='swat-form-field-contents';l.appendChild(this.insertButton);l.appendChild(k);var m=p.create('div');m.className='swat-footer-form-field';m.appendChild(l);var n=p.create('form');n.className='swat-form';n.appendChild(d);n.appendChild(j);n.appendChild(m);q.add(n,'submit',function(e){q.cancel(e);this.close(true)},this);this.frame.appendChild(n)}});A.SnippetDialog=function(a){A.SnippetDialog.superclass.constructor.call(this,a)};A.extend(A.SnippetDialog,A.Dialog,{focus:function(){this.snippetEntry.focus()},reset:function(){this.snippetEntry.value=''},getData:function(){var a=A.SnippetDialog.superclass.getData.call(this);a.snippet=this.snippetEntry.value;return a},drawDialog:function(){A.SnippetDialog.superclass.drawDialog.call(this);var a=this.editor.id+'_snippet_entry';this.snippetEntry=p.create('textarea',{id:a,rows:4,cols:50});this.snippetEntry.className='swat-textarea';var b=p.create('label');b.htmlFor=a;b.appendChild(p.doc.createTextNode(this.editor.getLang('swat.snippet_field')));var c=p.create('div');c.className='swat-form-field-contents';c.appendChild(this.snippetEntry);var d=p.create('div');d.className='swat-form-field';d.appendChild(b);d.appendChild(c);insert=p.create('input',{type:'button'});insert.className='swat-button swat-primary';insert.value=this.editor.getLang('swat.snippet_insert');q.add(insert,'click',function(e){this.close(true)},this);var f=p.create('input',{type:'button'});f.className='swat-button';f.value=this.editor.getLang('swat.snippet_cancel');q.add(f,'click',function(e){this.close(false)},this);var g=p.create('div');g.className='swat-form-field-contents';g.appendChild(insert);g.appendChild(f);var h=p.create('div');h.className='swat-footer-form-field';h.appendChild(g);var i=p.create('form');i.className='swat-form';i.appendChild(d);i.appendChild(h);q.add(i,'submit',function(e){q.cancel(e);this.close(true)},this);this.frame.appendChild(i)}});tinymce.create('tinymce.plugins.SwatPlugin',{init:function(f,g){this.editor=f;var h=this;f.onPostRender.add(this.drawSourceMode,this);f.onLoadContent.add(this.initMode,this);f.onGetContent.add(function(a,o){if(this.mode!=A.MODE_SOURCE){var b='(?:p|pre|dl|div|blockquote|form|h[1-6]|table'+'|fieldset|address|ul|ol)';var c=new RegExp('('+'<\/'+b+'[^<>]*?>'+'(?:[\n\r\t ]<\/'+b+'[^<>]*?>)*'+')[\n\r\t ]*','g');o.content=o.content.replace(c,'$1\n\n');o.content=o.content.replace(/[\r\n\t ]*$/,'')}},this);f.onSubmit.addToTop(function(a,e){if(this.mode==A.MODE_SOURCE){this.setVisualMode()}},this);f.onBeforeRenderUI.add(function(){p.loadCSS(g+'/css/swat.css')});this.dialogs={'link':new A.LinkDialog(f),'snippet':new A.SnippetDialog(f),'image':new A.ImageDialog(f)};this.dialogs.link.onConfirm.add(function(a,b){var c=b.link_uri;this.insertLink(c)},this);this.dialogs.image.onConfirm.add(function(a,b){var c=b.image_src;var d=b.image_alt;this.insertImage(c,d)},this);this.dialogs.snippet.onConfirm.add(function(a,b){var c=b.snippet;this.insertSnippet(c)},this);f.addCommand('mceSwatLink',function(){var a=f.selection;if(a.isCollapsed()&&!f.dom.getParent(a.getNode(),'A')){return}var b=f.dom.getAttrib(a.getNode(),'href');var c={'link_uri':b};h.dialogs.link.open(c)});f.addCommand('mceSwatImage',function(){var a,sel=f.selection;var b=f.dom.getParent(sel.getNode(),'IMG');if(b){a={'image_src':f.dom.getAttrib(b,'src'),'image_alt':f.dom.getAttrib(b,'alt')}}else{a={}}h.dialogs.image.open(a)});f.addCommand('mceSwatSnippet',function(){var a={};h.dialogs.snippet.open(a)});f.addButton('link',{'title':'swat.link_desc','cmd':'mceSwatLink','class':'mce_link'});f.addShortcut('crtl+k','swat.link_desc','mceSwatLink');f.addButton('image',{'title':'swat.image_desc','cmd':'mceSwatImage','class':'mce_image'});f.addButton('snippet',{'title':'swat.snippet_desc','cmd':'mceSwatSnippet','class':'mce_snippet'});var t=this;f.onInit.add(function(){f.onNodeChange.add(function(a,b,n,c){b.setDisabled('link',(c&&n.nodeName!='A')||t.mode==A.MODE_SOURCE);b.setActive('link',n.nodeName=='A'&&!n.name&&t.mode!=A.MODE_SOURCE)})})},insertLink:function(a){var b=this.editor;var c=b.dom;var d=b.selection.getNode();d=c.getParent(d,'A');if(!a){b.execCommand('mceBeginUndoLevel');var i=b.selection.getBookmark();c.remove(d,true);b.selection.moveToBookmark(i);b.execCommand('mceEndUndoLevel');return}b.execCommand('mceBeginUndoLevel');if(d===null){b.getDoc().execCommand('unlink',false,null);b.execCommand('CreateLink',false,'#mce_temp_url#',{skip_undo:1});var e=tinymce.grep(c.select('a'),function(n){return(c.getAttrib(n,'href')=='#mce_temp_url#')});for(var i=0;i<e.length;i++){c.setAttrib(d=e[i],'href',a)}}else{c.setAttrib(d,'href',a)}if(d.childNodes.length!=1||d.firstChild.nodeName!='IMG'){b.focus();b.selection.select(d);b.selection.collapse(false)}b.execCommand('mceEndUndoLevel')},insertImage:function(a,b){var c=this.editor;var d=c.dom;var e=c.selection.getNode();e=d.getParent(e,'IMG');c.execCommand('mceBeginUndoLevel');if(e===null){c.execCommand('InsertImage',false,'#mce_temp_src#',{skip_undo:1});var f=tinymce.grep(d.select('img'),function(n){return(d.getAttrib(n,'src')=='#mce_temp_src#')});for(var i=0;i<f.length;i++){e=f[i];d.setAttrib(e,'src',a);if(b){d.setAttrib(e,'alt',b)}}}else{d.setAttrib(e,'src',a);if(b){d.setAttrib(e,'alt',b)}}c.execCommand('mceEndUndoLevel')},insertSnippet:function(a){var b=this.editor;b.execCommand('mceBeginUndoLevel');b.selection.setContent('#mce_temp_content##mce_temp_cursor#');b.setContent(b.getContent().replace(/#mce_temp_content#/g,a));var c=this.editor.dom.select('body *');var d=null;var e=-1;for(var i=0;i<c.length;i++){for(var j=0;j<c[i].childNodes.length;j++){var f=c[i].childNodes[j];if(f.nodeType==3){e=f.nodeValue.indexOf('#mce_temp_cursor#');if(e!==-1){d=f;break}}}if(d!==null){break}}b.focus();if(d){var g=d.nodeValue.split('#mce_temp_cursor#',2);if(g[0]===''&&g[1]===''){var h=b.getDoc().createTextNode('');d.parentNode.replaceChild(h,d);b.selection.select(h);b.selection.collapse(false)}else if(g[0]===''){var h=b.getDoc().createTextNode(g[1]);d.parentNode.replaceChild(h,d);b.selection.select(h);b.selection.collapse(true)}else if(g[1]===''){var h=b.getDoc().createTextNode(g[0]);d.parentNode.replaceChild(h,d);b.selection.select(h);b.selection.collapse(false)}else{var k=b.getDoc().createTextNode(g[0]);var l=b.getDoc().createTextNode(g[1]);d.parentNode.replaceChild(l,d);l.parentNode.insertBefore(k,l);b.selection.select(k);b.selection.collapse(false)}}b.execCommand('mceEndUndoLevel')},drawSourceMode:function(a){var a=this.editor;var b=a.getElement();b.className='swat-textarea';b.style.overflowX='hidden';b.style.overflowY='scroll';b.style.borderStyle='none';b.style.borderWidth='0';b.style.resize='none';b.style.outline='none';b.style.padding='1px';b.style.width='99.5%';b.style.maxWidth='99.5%';this.sourceContainer=document.createElement('div');this.sourceContainer.className='swat-textarea-container';this.sourceContainer.style.display='none';this.sourceContainer.style.zIndex='3';this.sourceContainer.style.position='absolute';b.parentNode.replaceChild(this.sourceContainer,b);this.sourceContainer.appendChild(b);b.style.display='block';this.drawModeSwitcher(a)},drawModeSwitcher:function(c){var c=this.editor;this.modeLink=[];visualSpan=document.createElement('span');visualSpan.appendChild(document.createTextNode(c.getLang('swat.mode_visual')));var d=document.createElement('a');d.href='#';if(this.mode!=A.MODE_SOURCE){d.className='selected'}d.appendChild(visualSpan);q.add(d,'click',function(e){q.prevent(e);var a=(this.mode!=A.MODE_VISUAL);this.setVisualMode();if(a){this.editor.focus()}},this);var f=document.createElement('li');f.appendChild(d);sourceSpan=document.createElement('span');sourceSpan.appendChild(document.createTextNode(c.getLang('swat.mode_source')));var g=document.createElement('a');g.href='#';if(this.mode==A.MODE_SOURCE){g.className='selected'}g.appendChild(sourceSpan);q.add(g,'click',function(e){q.prevent(e);var a=(this.mode!=A.MODE_SOURCE);this.setSourceMode();if(a){var b=this.editor.getElement();b.focus();if(b.setSelectionRange){b.setSelectionRange(0,0)}else{var r=b.createTextRange();r.collapse(true);r.moveToPoint(0)}}},this);var h=document.createElement('li');h.appendChild(g);var i=document.createElement('ul');i.className='swat-textarea-editor-mode-switcher';i.appendChild(h);i.appendChild(f);var j=this.editor.getContainer().firstChild;var k=v(j);i.style.width=(k.w+2)+'px';setTimeout(function(){var a=v(j);i.style.width=(a.w+2)+'px'},50);var l=document.createElement('div');l.style.clear='right';c.getContainer().appendChild(i);c.getContainer().appendChild(l);this.modeLink[A.MODE_VISUAL]=d;this.modeLink[A.MODE_SOURCE]=g},initMode:function(){var a=this.editor;var b=document.getElementById(a.id+'_mode');if(b&&b.value==A.MODE_SOURCE){this.setSourceMode()}else{this.setVisualMode()}},setVisualMode:function(){if(this.mode==A.MODE_VISUAL){return}var a=this.editor;if(this.modeLink){p.removeClass(this.modeLink[A.MODE_SOURCE],'selected');p.addClass(this.modeLink[A.MODE_VISUAL],'selected')}var b=a.controlManager;for(var c in b.controls){if(c!=a.id+'_undo'&&c!=a.id+'_redo'){b.setDisabled(c,false)}}this.sourceContainer.style.display='none';var d=a.getElement();a.setContent(d.value);this.mode=A.MODE_VISUAL;var e=document.getElementById(a.id+'_mode');if(e){e.value=this.mode}},setSourceMode:function(){if(this.mode==A.MODE_SOURCE){return}var a=this.editor;if(this.modeLink){p.removeClass(this.modeLink[A.MODE_VISUAL],'selected');p.addClass(this.modeLink[A.MODE_SOURCE],'selected')}var b=a.controlManager;for(var c in b.controls){if(c!=a.id+'_undo'&&c!=a.id+'_redo'){b.setDisabled(c,true)}}var d=a.getBody().firstChild||a.getBody();var e=a.getWin().frameElement;var f=(tinymce.isIE)?e.currentStyle:getComputedStyle(e,null);var g=f.backgroundColor;var w=e.clientWidth;var h=e.clientHeight;var i=v(a.getContentAreaContainer());var j=a.getElement();j.value=this.editor.getContent();j.style.height=(h-4)+'px';this.sourceContainer.style.width=w+'px';this.sourceContainer.style.height=h+'px';this.sourceContainer.style.left='1px';this.sourceContainer.style.top='30px';this.sourceContainer.style.backgroundColor=g;this.sourceContainer.style.display='block';this.mode=A.MODE_SOURCE;var k=document.getElementById(a.id+'_mode');if(k){k.value=this.mode}},toggleMode:function(){if(this.mode==A.MODE_VISUAL){this.setSourceMode(true)}else{this.setVisualMode(true)}},getInfo:function(){return{longname:'Swat Plugin',author:'silverorange Inc.',authorurl:'http://www.silverorange.com/',version:'1.0'}}});tinymce.PluginManager.add('swat',tinymce.plugins.SwatPlugin)})();
