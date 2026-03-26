
/**
 * homepage-hero block - built with WP Rig
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

var WPRigBlockHomepage_hero=(()=>{var B=Object.defineProperty;var m=Object.getOwnPropertySymbols;var y=Object.prototype.hasOwnProperty,U=Object.prototype.propertyIsEnumerable;var w=(l,n,e)=>n in l?B(l,n,{enumerable:!0,configurable:!0,writable:!0,value:e}):l[n]=e,s=(l,n)=>{for(var e in n||(n={}))y.call(n,e)&&w(l,e,n[e]);if(m)for(var e of m(n))U.call(n,e)&&w(l,e,n[e]);return l};var M=window.wp.blockEditor.useBlockProps,x=window.wp.blockEditor.InspectorControls,h=window.wp.blockEditor.MediaUpload,I=window.wp.blockEditor.MediaUploadCheck,a=window.wp.components.PanelBody,d=window.wp.components.TextControl,P=window.wp.components.TextareaControl,r=window.wp.components.Button,S=window.wp.serverSideRender;function c({name:l,attributes:n,setAttributes:e}){let C=M(),{heroHeading:v,heroSubtext:u,heroCtaLabel:k,heroCtaUrl:b,heroImageId:t,heroImageUrl:p,heroMobileImageId:i,heroMobileImageUrl:g}=n;return wp.element.createElement(wp.element.Fragment,null,wp.element.createElement(x,null,wp.element.createElement(a,{title:"Hero Image",initialOpen:!0},wp.element.createElement(I,null,wp.element.createElement(h,{onSelect:o=>e({heroImageId:o.id,heroImageUrl:o.url}),allowedTypes:["image"],value:t,render:({open:o})=>wp.element.createElement("div",null,p&&wp.element.createElement("img",{src:p,alt:"",style:{width:"100%",marginBottom:"8px"}}),wp.element.createElement(r,{variant:"secondary",onClick:o},t?"Replace Image":"Select Image"),t&&wp.element.createElement(r,{variant:"link",isDestructive:!0,onClick:()=>e({heroImageId:0,heroImageUrl:""}),style:{marginLeft:"8px"}},"Remove"))}))),wp.element.createElement(a,{title:"Mobile Image (Portrait)",initialOpen:!1},wp.element.createElement(I,null,wp.element.createElement(h,{onSelect:o=>e({heroMobileImageId:o.id,heroMobileImageUrl:o.url}),allowedTypes:["image"],value:i,render:({open:o})=>wp.element.createElement("div",null,g&&wp.element.createElement("img",{src:g,alt:"",style:{width:"100%",marginBottom:"8px"}}),wp.element.createElement(r,{variant:"secondary",onClick:o},i?"Replace Image":"Select Image"),i&&wp.element.createElement(r,{variant:"link",isDestructive:!0,onClick:()=>e({heroMobileImageId:0,heroMobileImageUrl:""}),style:{marginLeft:"8px"}},"Remove"))}))),wp.element.createElement(a,{title:"Content",initialOpen:!0},wp.element.createElement(d,{label:"Heading",value:v,onChange:o=>e({heroHeading:o})}),wp.element.createElement(P,{label:"Subtext",value:u,onChange:o=>e({heroSubtext:o})})),wp.element.createElement(a,{title:"CTA",initialOpen:!0},wp.element.createElement(d,{label:"Label",value:k,onChange:o=>e({heroCtaLabel:o})}),wp.element.createElement(d,{label:"URL",value:b,onChange:o=>e({heroCtaUrl:o})}))),wp.element.createElement("div",s({},C),wp.element.createElement(S,{block:l,attributes:n})))}var{registerBlockType:T}=wp.blocks,{__:R}=wp.i18n;T("wp-rig/homepage-hero",{apiVersion:2,title:R("Homepage Hero","wp-rig"),edit:c,save(){return null}});})();
