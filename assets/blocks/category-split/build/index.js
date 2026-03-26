
/**
 * category-split block - built with WP Rig
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

var WPRigBlockCategory_split=(()=>{var h=Object.defineProperty;var I=Object.getOwnPropertySymbols;var b=Object.prototype.hasOwnProperty,y=Object.prototype.propertyIsEnumerable;var v=(o,n,e)=>n in o?h(o,n,{enumerable:!0,configurable:!0,writable:!0,value:e}):o[n]=e,C=(o,n)=>{for(var e in n||(n={}))b.call(n,e)&&v(o,e,n[e]);if(I)for(var e of I(n))y.call(n,e)&&v(o,e,n[e]);return o};var B=window.wp.blockEditor.useBlockProps,P=window.wp.blockEditor.InspectorControls,N=window.wp.blockEditor.MediaUpload,R=window.wp.blockEditor.MediaUploadCheck,x=window.wp.components.PanelBody,s=window.wp.components.TextControl,u=window.wp.components.Button,D=window.wp.serverSideRender;function U({label:o,imageId:n,imageUrl:e,name:a,subtitle:r,discoverUrl:i,onImageSelect:p,onImageRemove:t,onNameChange:c,onSubtitleChange:d,onUrlChange:m}){return wp.element.createElement(x,{title:o,initialOpen:!0},wp.element.createElement(R,null,wp.element.createElement(N,{onSelect:p,allowedTypes:["image"],value:n,render:({open:g})=>wp.element.createElement("div",{style:{marginBottom:"16px"}},e&&wp.element.createElement("img",{src:e,alt:"",style:{width:"100%",marginBottom:"8px"}}),wp.element.createElement(u,{variant:"secondary",onClick:g},n?"Replace Image":"Select Image"),n&&wp.element.createElement(u,{variant:"link",isDestructive:!0,onClick:t,style:{marginLeft:"8px"}},"Remove"))})),wp.element.createElement(s,{label:"Category Name",value:a,onChange:c}),wp.element.createElement(s,{label:"Subtitle",value:r,onChange:d}),wp.element.createElement(s,{label:"Discover URL",value:i,onChange:m}))}function w({name:o,attributes:n,setAttributes:e}){let a=B(),{panel1ImageId:r,panel1ImageUrl:i,panel1Name:p,panel1Subtitle:t,panel1DiscoverUrl:c,panel2ImageId:d,panel2ImageUrl:m,panel2Name:g,panel2Subtitle:k,panel2DiscoverUrl:S}=n;return wp.element.createElement(wp.element.Fragment,null,wp.element.createElement(P,null,wp.element.createElement(U,{label:"Panel 1",imageId:r,imageUrl:i,name:p,subtitle:t,discoverUrl:c,onImageSelect:l=>e({panel1ImageId:l.id,panel1ImageUrl:l.url}),onImageRemove:()=>e({panel1ImageId:0,panel1ImageUrl:""}),onNameChange:l=>e({panel1Name:l}),onSubtitleChange:l=>e({panel1Subtitle:l}),onUrlChange:l=>e({panel1DiscoverUrl:l})}),wp.element.createElement(U,{label:"Panel 2",imageId:d,imageUrl:m,name:g,subtitle:k,discoverUrl:S,onImageSelect:l=>e({panel2ImageId:l.id,panel2ImageUrl:l.url}),onImageRemove:()=>e({panel2ImageId:0,panel2ImageUrl:""}),onNameChange:l=>e({panel2Name:l}),onSubtitleChange:l=>e({panel2Subtitle:l}),onUrlChange:l=>e({panel2DiscoverUrl:l})})),wp.element.createElement("div",C({},a),wp.element.createElement(D,{block:o,attributes:n})))}var{registerBlockType:E}=wp.blocks,{__:f}=wp.i18n;E("wp-rig/category-split",{apiVersion:2,title:f("Category Split","wp-rig"),edit:w,save(){return null}});})();
