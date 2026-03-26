
/**
 * homepage-brand-statement block - built with WP Rig
 */
window.wp = window.wp || {};
window.wp.blockEditor = window.wp.blockEditor || {};
window.wp.blocks = window.wp.blocks || {};
window.wp.components = window.wp.components || {};
window.wp.element = window.wp.element || {};
window.wp.i18n = window.wp.i18n || {};
window.wp.serverSideRender = window.wp.serverSideRender || {};
window.wp.htmlEntities = window.wp.htmlEntities || {
  // Simple decoding function to handle common HTML entities
  decode: function(text) {
    if (!text) return text;
    return String(text)
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&#39;/g, "'");
  }
};

// Ensure ServerSideRender is available at runtime
if (window.wp.serverSideRender) {
  // Create alias if needed
  window.wp['server-side-render'] = window.wp.serverSideRender;
}

var WPRigBlockHomepage_brand_statement=(()=>{var p=Object.defineProperty;var s=Object.getOwnPropertySymbols;var i=Object.prototype.hasOwnProperty,d=Object.prototype.propertyIsEnumerable;var r=(o,t,e)=>t in o?p(o,t,{enumerable:!0,configurable:!0,writable:!0,value:e}):o[t]=e,l=(o,t)=>{for(var e in t||(t={}))i.call(t,e)&&r(o,e,t[e]);if(s)for(var e of s(t))d.call(t,e)&&r(o,e,t[e]);return o};var m=window.wp.blockEditor.useBlockProps,w=window.wp.blockEditor.InspectorControls,g=window.wp.blockEditor.InnerBlocks,b=window.wp.components.PanelBody,k=window.wp.components.TextareaControl,B=[["core/image",{className:"brand-statement-logo",alt:""}]];function n({attributes:o,setAttributes:t}){let e=m({className:"homepage-brand-statement-editor"}),{statementText:a}=o;return wp.element.createElement(wp.element.Fragment,null,wp.element.createElement(w,null,wp.element.createElement(b,{title:"Brand Statement",initialOpen:!0},wp.element.createElement(k,{label:"Statement Text",value:a,onChange:c=>t({statementText:c}),rows:6}))),wp.element.createElement("div",l({},e),wp.element.createElement("div",{className:"homepage-brand-statement-editor__logo"},wp.element.createElement("p",{className:"homepage-brand-statement-editor__logo-label"},"Logo SVG"),wp.element.createElement(g,{template:B,templateLock:!1})),wp.element.createElement("p",{className:"homepage-brand-statement-editor__text"},a)))}var{registerBlockType:_}=wp.blocks,{__:u}=wp.i18n,{InnerBlocks:E}=wp.blockEditor;_("wp-rig/homepage-brand-statement",{apiVersion:2,title:u("Homepage Brand Statement","wp-rig"),edit:n,save(){return wp.element.createElement(E.Content,null)}});})();
